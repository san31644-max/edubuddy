<?php
declare(strict_types=1);

const APP_NAME = 'K Education';
const APP_URL = '/educhat';
const DB_HOST = 'localhost';
const DB_NAME = 'educhat';
const DB_USER = 'root';
const DB_PASS = '';
$serverSecretsFile = 'C:/xampp/edubuddy-secrets.php';
$serverSecrets = is_file($serverSecretsFile) ? require $serverSecretsFile : [];
if (!is_array($serverSecrets)) $serverSecrets = [];

define('AI_API_KEY', getenv('OPENAI_API_KEY') ?: ($serverSecrets['OPENAI_API_KEY'] ?? ''));
define('AI_API_ENDPOINT', getenv('OPENAI_API_ENDPOINT') ?: 'https://api.openai.com/v1/responses');
define('AI_MODEL', getenv('OPENAI_MODEL') ?: 'gpt-5.6');
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: ($serverSecrets['GEMINI_API_KEY'] ?? ''));
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: ($serverSecrets['GEMINI_MODEL'] ?? 'gemini-3.6-flash'));
define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta/models/');
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
