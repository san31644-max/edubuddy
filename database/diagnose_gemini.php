<?php
declare(strict_types=1);require_once __DIR__.'/../includes/runtime_env.php';require_once __DIR__.'/../includes/config.php';require_once __DIR__.'/../includes/gemini_transport.php';
if(GEMINI_API_KEY===''){fwrite(STDERR,"FAILED: GEMINI_API_KEY is not configured.\n");exit(1);}
$payload=json_encode(['contents'=>[['role'=>'user','parts'=>[['text'=>'Reply with exactly: GEMINI_CONNECTED']]]],'generationConfig'=>['temperature'=>0,'maxOutputTokens'=>500]],JSON_UNESCAPED_SLASHES);
$url=GEMINI_API_BASE.rawurlencode(GEMINI_MODEL).':generateContent';$r=gemini_http_json($url,['x-goog-api-key: '.GEMINI_API_KEY,'Content-Type: application/json'],(string)$payload);$body=json_decode((string)$r['body'],true);$text='';foreach($body['candidates'][0]['content']['parts']??[] as $p)$text.=$p['text']??'';
if($r['status']<200||$r['status']>=300||stripos($text,'GEMINI_CONNECTED')===false){$problem=$body['error']['message']??($body['candidates'][0]['finishReason']??$r['error']??'No response text');fwrite(STDERR,"FAILED: HTTP {$r['status']} - $problem\n");exit(1);}echo "PASS: ".GEMINI_MODEL." responded through the configured Gemini API key.\n";
