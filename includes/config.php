<?php
declare(strict_types=1);

const APP_NAME = 'K Education';
define('APP_URL',getenv('EDUCHAT_APP_URL')!==false?(string)getenv('EDUCHAT_APP_URL'):(PHP_OS_FAMILY==='Windows'?'/educhat':''));
define('DB_HOST',getenv('EDUCHAT_DB_HOST')?:'localhost');
define('DB_NAME',getenv('EDUCHAT_DB_NAME')?:'educhat');
define('DB_USER',getenv('EDUCHAT_DB_USER')?:'root');
define('DB_PASS',getenv('EDUCHAT_DB_PASS')?:'');
$serverSecretsFile = 'C:/xampp/edubuddy-secrets.php';
$serverSecrets = is_file($serverSecretsFile) ? require $serverSecretsFile : [];
if (!is_array($serverSecrets)) $serverSecrets = [];

define('AI_API_KEY', getenv('OPENAI_API_KEY') ?: ($serverSecrets['OPENAI_API_KEY'] ?? ''));
define('AI_API_ENDPOINT', getenv('OPENAI_API_ENDPOINT') ?: 'https://api.openai.com/v1/responses');
define('AI_MODEL', getenv('OPENAI_MODEL') ?: 'gpt-5.6');
$geminiKeyCandidates = [];
$addGeminiKeys = static function (mixed $value) use (&$geminiKeyCandidates): void {
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        $value = is_array($decoded) ? $decoded : preg_split('/[\r\n,;]+/', $value);
    }
    if (!is_array($value)) $value = [$value];
    foreach ($value as $key) {
        $key = trim((string)$key);
        if ($key !== '' && !in_array($key, $geminiKeyCandidates, true)) $geminiKeyCandidates[] = $key;
    }
};
$addGeminiKeys(getenv('GEMINI_API_KEYS') ?: []);
$addGeminiKeys($serverSecrets['GEMINI_API_KEYS'] ?? []);
for ($geminiKeyIndex = 1; $geminiKeyIndex <= 10; $geminiKeyIndex++) {
    $addGeminiKeys(getenv('GEMINI_API_KEY_'.$geminiKeyIndex) ?: []);
    $addGeminiKeys($serverSecrets['GEMINI_API_KEY_'.$geminiKeyIndex] ?? []);
}
$addGeminiKeys(getenv('GEMINI_API_KEY') ?: []);
$addGeminiKeys($serverSecrets['GEMINI_API_KEY'] ?? []);
define('GEMINI_API_KEYS', $geminiKeyCandidates);
define('GEMINI_API_KEY', $geminiKeyCandidates[0] ?? '');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: ($serverSecrets['GEMINI_MODEL'] ?? 'gemini-3.6-flash'));
define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('TEXTLK_API_TOKEN', getenv('TEXTLK_API_TOKEN') ?: ($serverSecrets['TEXTLK_API_TOKEN'] ?? ''));
define('TEXTLK_API_ENDPOINT', getenv('TEXTLK_API_ENDPOINT') ?: ($serverSecrets['TEXTLK_API_ENDPOINT'] ?? 'https://app.text.lk/api/v3/sms/send'));
define('TEXTLK_SENDER_ID', getenv('TEXTLK_SENDER_ID') ?: ($serverSecrets['TEXTLK_SENDER_ID'] ?? 'TextLKDemo'));
const PREMIUM_PRICE_LKR = 250;
const PREMIUM_DAYS = 30;
const MAX_CHAT_LENGTH = 1000;
const MAX_PROFILE_IMAGE_BYTES = 2097152;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

date_default_timezone_set('Asia/Colombo');
