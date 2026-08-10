<?php
declare(strict_types=1);

/** Send Gemini JSON through Windows cURL/Schannel, avoiding XAMPP cURL stalls. */
function gemini_http_json(string $url, array $headers, string $payload): array
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
