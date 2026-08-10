$ErrorActionPreference = 'Stop'

Write-Host 'EduBuddy OpenAI setup' -ForegroundColor Cyan
Write-Host 'Your key will not be displayed or written into the project.'

$secureKey = Read-Host 'Paste your NEW OpenAI API key' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureKey)

try {
    $plainKey = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    if ([string]::IsNullOrWhiteSpace($plainKey) -or -not $plainKey.StartsWith('sk-')) {
        throw 'The value does not look like an OpenAI API key.'
    }

    [Environment]::SetEnvironmentVariable('OPENAI_API_KEY', $plainKey, 'User')
    [Environment]::SetEnvironmentVariable('OPENAI_API_ENDPOINT', 'https://api.openai.com/v1/responses', 'User')
    [Environment]::SetEnvironmentVariable('OPENAI_MODEL', 'gpt-5.6', 'User')
}
finally {
    if ($pointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    }
    $plainKey = $null
    $secureKey = $null
}

Write-Host ''
Write-Host 'OpenAI environment variables were saved for your Windows user.' -ForegroundColor Green
Write-Host 'Now completely exit XAMPP Control Panel, reopen it, and start Apache again.' -ForegroundColor Yellow
Write-Host 'Then open: http://localhost/educhat/chatbot/chat.php'
