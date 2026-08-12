param([switch]$Unsigned)
$ErrorActionPreference='Stop'
$Sdk='C:\Users\CHAMA COMPUTERS\AppData\Local\Android\Sdk'
$BuildTools=Join-Path $Sdk 'build-tools\35.0.1'
$PlatformSource=Join-Path $Sdk 'platforms\android-35\android.jar'
$JavaHome='C:\Program Files\JetBrains\PyCharm 2023.3.4\jbr'
$JavaTools='C:\Program Files\Android\Android Studio\jbr'
$Root=$PSScriptRoot
$Build=Join-Path $Root 'build'
$Deploy=Join-Path $Root 'signing'
$UnsignedApk=Join-Path $Build 'k-education-unsigned.apk'
$AlignedApk=Join-Path $Build 'k-education-aligned.apk'
$SignedApk=Join-Path $Build 'K-Education-1.0.0.apk'

New-Item -ItemType Directory -Force -Path (Join-Path $Build 'classes'),(Join-Path $Build 'dex'),(Join-Path $Build 'res'),(Join-Path $Root 'res\drawable'),$Deploy | Out-Null
$oldArtifacts=@($UnsignedApk,$AlignedApk,$SignedApk,(Join-Path $Build 'classes.jar'),(Join-Path $Build 'compiled.zip'),(Join-Path $Build 'dex\classes.dex'));foreach($artifact in $oldArtifacts){if(Test-Path -LiteralPath $artifact){Remove-Item -LiteralPath $artifact -Force}}
$Platform=Join-Path $Build 'android.jar';Copy-Item -LiteralPath $PlatformSource -Destination $Platform -Force
$env:JAVA_HOME=$JavaHome;$env:PATH=(Join-Path $JavaHome 'bin')+';'+$env:PATH
Copy-Item -LiteralPath (Join-Path (Split-Path $Root -Parent) 'logo\k-transparent.png') -Destination (Join-Path $Root 'res\drawable\k_education_logo.png') -Force

& (Join-Path $BuildTools 'aapt2.exe') compile --dir (Join-Path $Root 'res') -o (Join-Path $Build 'compiled.zip')
if($LASTEXITCODE){throw 'Android resource compilation failed.'}
& (Join-Path $BuildTools 'aapt2.exe') link -o $UnsignedApk -I $Platform --manifest (Join-Path $Root 'AndroidManifest.xml') --java (Join-Path $Build 'generated') --min-sdk-version 23 --target-sdk-version 35 --version-code 1 --version-name '1.0.0' (Join-Path $Build 'compiled.zip')
if($LASTEXITCODE){throw 'Android resource linking failed.'}

$Sources=Get-ChildItem -LiteralPath (Join-Path $Root 'src') -Recurse -Filter '*.java' | Select-Object -ExpandProperty FullName
& (Join-Path $JavaHome 'bin\javac.exe') -encoding UTF-8 -source 8 -target 8 -classpath $Platform -d (Join-Path $Build 'classes') $Sources
if($LASTEXITCODE){throw 'Java compilation failed.'}
$ClassesJar=Join-Path $Build 'classes.jar'
& (Join-Path $JavaTools 'bin\jar.exe') cf $ClassesJar -C (Join-Path $Build 'classes') .
if($LASTEXITCODE){throw 'Class archive creation failed.'}
& (Join-Path $BuildTools 'd8.bat') --lib $Platform --min-api 23 --output (Join-Path $Build 'dex') $ClassesJar
if($LASTEXITCODE){throw 'DEX compilation failed.'}
Push-Location (Join-Path $Build 'dex');try{& (Join-Path $BuildTools 'aapt.exe') add $UnsignedApk 'classes.dex';if($LASTEXITCODE){throw 'DEX packaging failed.'}}finally{Pop-Location}
& (Join-Path $BuildTools 'zipalign.exe') -f -p 4 $UnsignedApk $AlignedApk
if($LASTEXITCODE){throw 'APK alignment failed.'}

if($Unsigned){Copy-Item $AlignedApk $SignedApk -Force; return}
$Credentials=Join-Path $Deploy 'android-signing.properties'
$KeyStore=Join-Path $Deploy 'k-education-release.jks'
if(!(Test-Path -LiteralPath $Credentials)){
    $Password=[Convert]::ToBase64String((1..32|ForEach-Object{Get-Random -Maximum 256})) -replace '[^A-Za-z0-9]',''
    $Password=$Password.Substring(0,24)
    Set-Content -LiteralPath $Credentials -Value "storePassword=$Password`nkeyPassword=$Password`nalias=keducation" -Encoding utf8
}
$Values=@{};Get-Content -LiteralPath $Credentials|ForEach-Object{$k,$v=$_.Split('=',2);$Values[$k]=$v}
if(!(Test-Path -LiteralPath $KeyStore)){
    & (Join-Path $JavaHome 'bin\keytool.exe') -genkeypair -keystore $KeyStore -storepass $Values.storePassword -keypass $Values.keyPassword -alias $Values.alias -keyalg RSA -keysize 3072 -validity 10000 -dname 'CN=K Education, OU=Education, O=K Education, L=Colombo, ST=Western, C=LK'
}
& (Join-Path $BuildTools 'apksigner.bat') sign --ks $KeyStore --ks-key-alias $Values.alias --ks-pass ('pass:'+$Values.storePassword) --key-pass ('pass:'+$Values.keyPassword) --out $SignedApk $AlignedApk
if($LASTEXITCODE){throw 'APK signing failed.'}
& (Join-Path $BuildTools 'apksigner.bat') verify --verbose --print-certs $SignedApk
if($LASTEXITCODE){throw 'APK signature verification failed.'}
& (Join-Path $BuildTools 'aapt.exe') dump badging $SignedApk
if($LASTEXITCODE){throw 'APK metadata verification failed.'}
& (Join-Path $JavaTools 'bin\jar.exe') tf $SignedApk | Select-String -SimpleMatch 'classes.dex'
if($LASTEXITCODE){throw 'APK code verification failed.'}
Get-FileHash -Algorithm SHA256 -LiteralPath $SignedApk
