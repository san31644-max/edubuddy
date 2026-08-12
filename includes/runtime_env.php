<?php
declare(strict_types=1);

// A deployed runtime secret is the site's explicit configuration and must
// override stale values inherited from a long-running Apache/XAMPP process.
$keyFile = __DIR__ . '/runtime/gemini-secret.php';
if (is_readable($keyFile)) {
    $secret = require $keyFile;
    $key = trim((string)(is_array($secret) ? ($secret['GEMINI_API_KEY'] ?? '') : $secret));
    if ($key !== '') putenv('GEMINI_API_KEY=' . $key);
    if (is_array($secret) && !empty($secret['GEMINI_MODEL'])) putenv('GEMINI_MODEL=' . trim((string)$secret['GEMINI_MODEL']));
}
$textlkFile=__DIR__.'/runtime/textlk-secret.php';
if(is_readable($textlkFile)){
    $textlk=require $textlkFile;
    if(is_array($textlk))foreach(['TEXTLK_API_TOKEN','TEXTLK_API_ENDPOINT','TEXTLK_SENDER_ID'] as $name){
        if(!empty($textlk[$name]))putenv($name.'='.trim((string)$textlk[$name]));
    }
}
$databaseFile=__DIR__.'/runtime/database-secret.php';
if(is_readable($databaseFile)){
    $databaseSecret=require $databaseFile;
    if(is_array($databaseSecret))foreach(['EDUCHAT_DB_HOST','EDUCHAT_DB_NAME','EDUCHAT_DB_USER','EDUCHAT_DB_PASS'] as $name){
        if(isset($databaseSecret[$name]))putenv($name.'='.(string)$databaseSecret[$name]);
    }
}

// Use a stable generally available model unless the server explicitly
// provides another one. This is loaded before config.php defines GEMINI_MODEL.
if (getenv('GEMINI_MODEL') === false || getenv('GEMINI_MODEL') === '') {
    putenv('GEMINI_MODEL=gemini-3.6-flash');
}
