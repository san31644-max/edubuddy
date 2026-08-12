<?php
declare(strict_types=1);

/** Send one Gemini JSON request through Windows cURL/Schannel. */
function gemini_http_json_once(string $url, array $headers, string $payload): array
{
    $payloadFile = tempnam(sys_get_temp_dir(), 'edubuddy_gemini_');
    if ($payloadFile === false || file_put_contents($payloadFile, $payload) === false) {
        return ['status'=>0, 'body'=>'', 'error'=>'Could not prepare the Gemini request.'];
    }
    $quote = static fn(string $value): string => '"' . str_replace(['\\','"'], ['\\\\','\\"'], $value) . '"';
    $config = 'url = '.$quote($url)."\nrequest = \"POST\"\n";
    foreach ($headers as $header) $config .= 'header = '.$quote($header)."\n";
    $config .= 'data-binary = '.$quote('@'.$payloadFile)."\nconnect-timeout = 10\nmax-time = 120\nipv4\nsilent\nshow-error\n";
    $pipes = [];
    $curlBinary = PHP_OS_FAMILY === 'Windows' ? 'curl.exe' : 'curl';
    $process = proc_open(
        [$curlBinary,'--config','-','--write-out',"\nEDUBUDDY_HTTP_STATUS:%{http_code}"],
        [['pipe','r'],['pipe','w'],['pipe','w']], $pipes, null, null,
        ['bypass_shell'=>true]
    );
    if (!is_resource($process)) {
        @unlink($payloadFile);
        return ['status'=>0, 'body'=>'', 'error'=>'Could not start the Gemini transport.'];
    }
    fwrite($pipes[0], $config); fclose($pipes[0]);
    $output = (string)stream_get_contents($pipes[1]);
    $error = trim((string)stream_get_contents($pipes[2]));
    fclose($pipes[1]); fclose($pipes[2]); proc_close($process); @unlink($payloadFile);
    $status = 0;
    if (preg_match('/\nEDUBUDDY_HTTP_STATUS:(\d{3})\s*$/', $output, $match)) {
        $status = (int)$match[1];
        $output = preg_replace('/\nEDUBUDDY_HTTP_STATUS:\d{3}\s*$/', '', $output) ?? '';
    }
    if (($status < 200 || $status >= 300) && $error === '') {
        $apiError = json_decode($output, true);
        $error = trim((string)($apiError['error']['message'] ?? ''));
        if ($error === '') $error = $status === 0
            ? 'The Gemini service could not be reached from this server.'
            : 'Gemini rejected the request with HTTP '.$status.'.';
        $error = mb_strimwidth($error, 0, 500, '…');
    }
    return ['status'=>$status, 'body'=>$output, 'error'=>$error];
}

/**
 * Send Gemini JSON and rotate configured keys on quota/auth failures.
 * The caller may use either x-goog-api-key or a ?key= query parameter.
 */
function gemini_http_json(string $url, array $headers, string $payload): array
{
    $hasHeaderKey = false;
    foreach ($headers as $header) {
        if (stripos((string)$header, 'x-goog-api-key:') === 0) {
            $hasHeaderKey = true;
            break;
        }
    }
    $hasQueryKey = preg_match('/([?&])key=[^&]*/i', $url) === 1;
    $configured = defined('GEMINI_API_KEYS') && is_array(GEMINI_API_KEYS) ? GEMINI_API_KEYS : [];
    if (!$configured && defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') $configured = [GEMINI_API_KEY];
    if ((!$hasHeaderKey && !$hasQueryKey) || !$configured) {
        return gemini_http_json_once($url, $headers, $payload);
    }

    $configured = array_values(array_unique($configured));
    static $preferredKeyIndex = 0;
    if ($preferredKeyIndex >= count($configured)) $preferredKeyIndex = 0;
    $ordered = array_merge(
        array_slice($configured, $preferredKeyIndex),
        array_slice($configured, 0, $preferredKeyIndex)
    );
    $last = ['status'=>0, 'body'=>'', 'error'=>'No configured Gemini key could complete the request.'];
    foreach ($ordered as $offset => $key) {
        $index = ($preferredKeyIndex + $offset) % count($configured);
        $requestHeaders = $headers;
        if ($hasHeaderKey) {
            foreach ($requestHeaders as &$header) {
                if (stripos((string)$header, 'x-goog-api-key:') === 0) $header = 'x-goog-api-key: '.$key;
            }
            unset($header);
        }
        $requestUrl = $hasQueryKey
            ? (preg_replace('/([?&])key=[^&]*/i', '$1key='.rawurlencode($key), $url) ?? $url)
            : $url;
        $last = gemini_http_json_once($requestUrl, $requestHeaders, $payload);
        $last['key_index'] = $index + 1;
        if (!in_array((int)$last['status'], [400, 401, 403, 429], true)) {
            $preferredKeyIndex = $index;
            return $last;
        }
    }
    return $last;
}
