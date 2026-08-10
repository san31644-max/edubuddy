ther$ErrorActionPreference = 'Stop'

Write-Host 'EduBuddy Gemini setup' -ForegroundColor Cyan
Write-Host 'Your key will not be displayed or written into the project.'

$secureKey = Read-Host 'Paste your NEW Gemini API key' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureKey)

try {
    $plainKey = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    if ([string]::IsNullOrWhiteSpace($plainKey) -or $plainKey.Length -lt 20) {
        throw 'The value does not look like a Gemini API key.'
    }
    [Environment]::SetEnvironmentVariable('GEMINI_API_KEY', $plainKey, 'User')
    [Environment]::SetEnvironmentVariable('GEMINI_MODEL', 'gemini-2.5-flash', 'User')

    $escapedKey = $plainKey.Replace("'", "\\'")
    $secretContents = "<?php`nreturn [`n    'GEMINI_API_KEY' => '$escapedKey',`n    'GEMINI_MODEL' => 'gemini-2.5-flash',`n];`n"
    [IO.File]::WriteAllText('C:\xampp\edubuddy-secrets.php', $secretContents, [Text.UTF8Encoding]::new($false))
}
finally {
    if ($pointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    }
    $plainKey = $null
    $secureKey = $null
}

Write-Host ''
Write-Host 'Gemini environment variables saved.' -ForegroundColor Green
Write-Host 'Completely exit every XAMPP Control Panel window, reopen one, and start Apache.' -ForegroundColor Yellow
Write-Host 'Then open: http://localhost/educhat/chatbot/chat.php'
