<?php
declare(strict_types=1);

// Production secrets are deployed outside the public application directory.
// Existing process environment variables always take precedence.
if (getenv('GEMINI_API_KEY') === false || getenv('GEMINI_API_KEY') === '') {
    $keyFile = dirname(__DIR__, 2) . '/.educhat-gemini-key';
    if (is_readable($keyFile)) {
        $key = trim((string) file_get_contents($keyFile));
        if ($key !== '') putenv('GEMINI_API_KEY=' . $key);
    }
}
