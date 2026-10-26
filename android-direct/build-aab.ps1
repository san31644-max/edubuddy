$ErrorActionPreference = 'Stop'

$Root = $PSScriptRoot
$Build = Join-Path $Root 'build'
$Sdk = 'C:\Users\CHAMA COMPUTERS\AppData\Local\Android\Sdk'
$BuildTools = Join-Path $Sdk 'build-tools\36.0.0'
$Platform = Join-Path $Sdk 'platforms\android-36\android.jar'
$JavaHome = 'C:\Program Files\JetBrains\PyCharm 2023.3.4\jbr'
$JavaTools = 'C:\Program Files\Android\Android Studio\jbr'
$BundleTool = Join-Path $Root 'tools\bundletool-all-1.18.3.jar'
$Credentials = Join-Path $Root 'signing\android-signing.properties'
$KeyStore = Join-Path $Root 'signing\k-education-release.jks'
$ModuleRoot = Join-Path $Build 'aab-module'
$ProtoArchive = Join-Path $Build 'base-proto.zip'
$ModuleArchive = Join-Path $Build 'base-module.zip'
$UnsignedBundle = Join-Path $Build 'K-Education-1.0.3-unsigned.aab'
$SignedBundle = Join-Path $Build 'K-Education-1.0.3.aab'

foreach ($required in @($BundleTool, $Platform, $Credentials, $KeyStore)) {
    if (!(Test-Path -LiteralPath $required)) { throw "Required file not found: $required" }
}

# Compile the Java code and resources using the existing, tested APK build.
& (Join-Path $Root 'build.ps1')
if ($LASTEXITCODE) { throw 'Base Android build failed.' }

$resolvedBuild = (Resolve-Path -LiteralPath $Build).Path
if ($resolvedBuild -ne (Join-Path $Root 'build')) { throw 'Unexpected Android build directory.' }
foreach ($oldArtifact in @($ModuleRoot, $ProtoArchive, $ModuleArchive, $UnsignedBundle, $SignedBundle)) {
    if (Test-Path -LiteralPath $oldArtifact) { Remove-Item -LiteralPath $oldArtifact -Recurse -Force }
}
New-Item -ItemType Directory -Path $ModuleRoot | Out-Null

# A bundle uses protobuf-formatted Android resources rather than APK resources.
& (Join-Path $BuildTools 'aapt2.exe') link --proto-format -o $ProtoArchive -I $Platform `
    --manifest (Join-Path $Root 'AndroidManifest.xml') --min-sdk-version 23 `
    --target-sdk-version 36 --version-code 4 --version-name '1.0.3' `
    (Join-Path $Build 'compiled.zip')
if ($LASTEXITCODE) { throw 'Bundle resource linking failed.' }

Expand-Archive -LiteralPath $ProtoArchive -DestinationPath $ModuleRoot
New-Item -ItemType Directory -Path (Join-Path $ModuleRoot 'manifest'),(Join-Path $ModuleRoot 'dex') | Out-Null
Move-Item -LiteralPath (Join-Path $ModuleRoot 'AndroidManifest.xml') -Destination (Join-Path $ModuleRoot 'manifest\AndroidManifest.xml')
Copy-Item -LiteralPath (Join-Path $Build 'dex\classes.dex') -Destination (Join-Path $ModuleRoot 'dex\classes.dex')

& (Join-Path $JavaTools 'bin\jar.exe') cMf $ModuleArchive -C $ModuleRoot .
if ($LASTEXITCODE) { throw 'Bundle module packaging failed.' }
& (Join-Path $JavaHome 'bin\java.exe') -jar $BundleTool build-bundle --modules=$ModuleArchive --output=$UnsignedBundle
if ($LASTEXITCODE) { throw 'Android App Bundle creation failed.' }

$Values = @{}
Get-Content -LiteralPath $Credentials | ForEach-Object {
    $key, $value = $_.Split('=', 2)
    $Values[$key] = $value
}
Copy-Item -LiteralPath $UnsignedBundle -Destination $SignedBundle
& (Join-Path $JavaTools 'bin\jarsigner.exe') -keystore $KeyStore -storepass $Values.storePassword `
    -keypass $Values.keyPassword $SignedBundle $Values.alias
if ($LASTEXITCODE) { throw 'Android App Bundle signing failed.' }
& (Join-Path $JavaTools 'bin\jarsigner.exe') -verify -verbose -certs $SignedBundle
if ($LASTEXITCODE) { throw 'Android App Bundle signature verification failed.' }
& (Join-Path $JavaHome 'bin\java.exe') -jar $BundleTool validate --bundle=$SignedBundle
if ($LASTEXITCODE) { throw 'Android App Bundle validation failed.' }

Get-Item -LiteralPath $SignedBundle | Select-Object FullName, Length, LastWriteTime
Get-FileHash -Algorithm SHA256 -LiteralPath $SignedBundle
