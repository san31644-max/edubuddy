$ErrorActionPreference = 'Stop'

Write-Host 'K Education Text.lk setup' -ForegroundColor Cyan
Write-Host 'Your existing Gemini key will be preserved.'

$secureToken = Read-Host 'Paste the Text.lk API Token' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureToken)

try {
    $token = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    if ([string]::IsNullOrWhiteSpace($token) -or $token.Length -lt 10) {
        throw 'The API token is empty or too short.'
    }

    $secretFile = 'C:\xampp\edubuddy-secrets.php'
    $existing = @{}
    if (Test-Path -LiteralPath $secretFile) {
        $json = & 'C:\xampp\php\php.exe' -r "`$v=require 'C:/xampp/edubuddy-secrets.php'; echo json_encode(is_array(`$v)?`$v:[]);"
        if ($LASTEXITCODE -ne 0) { throw 'The existing secrets file could not be read.' }
        $object = $json | ConvertFrom-Json
        if ($object) {
            foreach ($property in $object.PSObject.Properties) { $existing[$property.Name] = [string]$property.Value }
        }
    }

    $existing['TEXTLK_API_TOKEN'] = $token
    $existing['TEXTLK_API_ENDPOINT'] = 'https://app.text.lk/api/v3/sms/send'
    $existing['TEXTLK_SENDER_ID'] = 'TextLKDemo'

    $lines = @('<?php', 'return [')
    foreach ($key in ($existing.Keys | Sort-Object)) {
        $safeKey = $key.Replace("'", "\'")
        $safeValue = $existing[$key].Replace('\', '\\').Replace("'", "\'")
        $lines += "    '$safeKey' => '$safeValue',"
    }
    $lines += '];'
    [IO.File]::WriteAllText($secretFile, (($lines -join "`n") + "`n"), [Text.UTF8Encoding]::new($false))
    Write-Host 'Text.lk settings saved. Gemini settings were preserved.' -ForegroundColor Green
}
finally {
    if ($pointer -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }
    $token = $null
    $secureToken = $null
}
