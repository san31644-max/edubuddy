<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
$source='C:/xampp/edubuddy-secrets.php';$all=is_file($source)?require $source:[];
if(!is_array($all)||empty($all['TEXTLK_API_TOKEN']))throw new RuntimeException('TEXTLK_API_TOKEN is missing.');
$values=['TEXTLK_API_TOKEN'=>(string)$all['TEXTLK_API_TOKEN'],'TEXTLK_API_ENDPOINT'=>(string)($all['TEXTLK_API_ENDPOINT']??'https://app.text.lk/api/v3/sms/send'),'TEXTLK_SENDER_ID'=>(string)($all['TEXTLK_SENDER_ID']??'TextLKDemo')];
$php="<?php\nreturn ".var_export($values,true).";\n";$target=__DIR__.'/../includes/runtime/textlk-secret.php';
if(file_put_contents($target,$php)===false)throw new RuntimeException('Could not write protected Text.lk runtime secret.');
echo "Protected Text.lk runtime secret prepared.\n";
