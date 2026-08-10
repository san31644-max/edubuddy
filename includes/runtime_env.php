<?php
declare(strict_types=1);

// Production secrets are deployed as non-outputting PHP inside a denied runtime directory.
// Existing process environment variables always take precedence.
if (getenv('GEMINI_API_KEY') === false || getenv('GEMINI_API_KEY') === '') {
    $keyFile = __DIR__ . '/runtime/gemini-secret.php';
    if (is_readable($keyFile)) {
        $key = trim((string) require $keyFile);
        if ($key !== '') putenv('GEMINI_API_KEY=' . $key);
    }
}
