<?php
declare(strict_types=1);
// Load deployed/environment secrets before config.php freezes them into
// constants. This also makes the SMS helper work when it is included directly.
require_once __DIR__.'/runtime_env.php';
require_once __DIR__.'/config.php';

function normalize_sri_lankan_phone(string $phone):?string{
    $digits=preg_replace('/\D+/','',$phone)??'';
    if(str_starts_with($digits,'0'))$digits='94'.substr($digits,1);
    elseif(strlen($digits)===9&&str_starts_with($digits,'7'))$digits='94'.$digits;
    return preg_match('/^947\d{8}$/',$digits)?$digits:null;
}
function send_textlk_otp(string $phone,string $code):array{
    if(TEXTLK_API_TOKEN==='')return [false,'SMS verification is not configured.'];
    $payload=json_encode(['recipient'=>$phone,'sender_id'=>TEXTLK_SENDER_ID,'type'=>'plain','message'=>"Your K Education verification code is $code. It expires in 5 minutes."],JSON_UNESCAPED_SLASHES);
    $ch=curl_init(TEXTLK_API_ENDPOINT);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.TEXTLK_API_TOKEN,'Content-Type: application/json','Accept: application/json'],CURLOPT_POSTFIELDS=>$payload]);
    $body=(string)curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);
    $decoded=json_decode($body,true);if($status>=200&&$status<300&&is_array($decoded)&&($decoded['status']??'')==='success')return [true,''];
    error_log('Text.lk OTP failed HTTP '.$status.' '.$error.' '.mb_substr($body,0,500));$providerMessage=is_array($decoded)?trim((string)($decoded['message']??'')):'';return [false,$providerMessage!==''?'SMS service: '.$providerMessage:'The verification SMS could not be sent. Please retry.'];
}
function send_textlk_password_reset(string $phone,string $code):array{
    if(TEXTLK_API_TOKEN==='')return [false,'SMS verification is not configured.'];
    $payload=json_encode(['recipient'=>$phone,'sender_id'=>TEXTLK_SENDER_ID,'type'=>'plain','message'=>"Your K Education password reset code is $code. It expires in 5 minutes."],JSON_UNESCAPED_SLASHES);
    $ch=curl_init(TEXTLK_API_ENDPOINT);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.TEXTLK_API_TOKEN,'Content-Type: application/json','Accept: application/json'],CURLOPT_POSTFIELDS=>$payload]);
    $body=(string)curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);
    $decoded=json_decode($body,true);if($status>=200&&$status<300&&is_array($decoded)&&($decoded['status']??'')==='success')return [true,''];
    error_log('Text.lk password reset failed HTTP '.$status.' '.$error.' '.mb_substr($body,0,500));$providerMessage=is_array($decoded)?trim((string)($decoded['message']??'')):'';return [false,$providerMessage!==''?'SMS service: '.$providerMessage:'The verification SMS could not be sent. Please retry.'];
}
