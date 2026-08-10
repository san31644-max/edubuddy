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
    $config .= 'data-binary = '.$quote('@'.$payloadFile)."\nconnect-timeout = 10\nmax-time = 45\nipv4\nsilent\nshow-error\n";
    $pipes = [];
    $process = proc_open(
        ['curl.exe','--config','-','--write-out',"\nEDUBUDDY_HTTP_STATUS:%{http_code}"],
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
    return ['status'=>$status, 'body'=>$output, 'error'=>$error];
}
