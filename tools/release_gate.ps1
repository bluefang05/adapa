$ErrorActionPreference = "Stop"
if (Get-Variable PSNativeCommandUseErrorActionPreference -ErrorAction SilentlyContinue) {
    $PSNativeCommandUseErrorActionPreference = $false
}

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

$KeyProps = Join-Path $Root "android\key.properties"
if (-not (Test-Path $KeyProps)) {
    throw "Falta android\key.properties. Ejecuta generate_upload_key.ps1 y configure_signing.ps1."
}

$Props = @{}
Get-Content $KeyProps | ForEach-Object {
    if ($_ -match '^\s*([^#][^=]*)=(.*)$') {
        $Props[$matches[1].Trim()] = $matches[2].Trim()
    }
}

foreach ($Required in @("storePassword","keyPassword","keyAlias","storeFile")) {
    if (-not $Props.ContainsKey($Required) -or [string]::IsNullOrWhiteSpace($Props[$Required])) {
        throw "Falta '$Required' en android\key.properties."
    }
}

$StoreFile = $Props["storeFile"] -replace '\\\\', '\'
if (-not (Test-Path $StoreFile)) {
    throw "El keystore configurado no existe: $StoreFile"
}

if (-not (Get-Command flutter -ErrorAction SilentlyContinue)) {
    throw "Flutter no está en PATH."
}

$LogDir = Join-Path $Root "qa_logs"
New-Item -ItemType Directory -Force -Path $LogDir | Out-Null
$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$Log = Join-Path $LogDir "release-$Stamp.txt"

function Run-Step {
    param([string]$Name, [scriptblock]$Command)
    Write-Host ""
    Write-Host "=== $Name ===" -ForegroundColor Cyan
    "=== $Name ===" | Out-File $Log -Append -Encoding utf8
    $PreviousErrorActionPreference = $ErrorActionPreference
    $ErrorActionPreference = "Continue"
    try {
        & $Command 2>&1 | Tee-Object -FilePath $Log -Append
        $ExitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $PreviousErrorActionPreference
    }
    if ($ExitCode -ne 0) {
        throw "$Name falló. Revisa $Log"
    }
}

Run-Step "Flutter version" { flutter --version }
Run-Step "Doctor" { flutter doctor -v }
Run-Step "Static audit" { python tools/static_preflight.py }
Run-Step "Compiler regression audit" { python tools/compiler_regression_audit.py }
Run-Step "Dependency contract" { python tools/dependency_contract_audit.py }
Run-Step "UTF-8 / Hangul integrity" { python tools/utf8_hangul_audit.py }
Run-Step "Clean" { flutter clean }
Run-Step "Packages" { flutter pub get }
Run-Step "Dependency compatibility" { flutter analyze --suggestions }
Run-Step "Analyze" { flutter analyze }
Run-Step "Tests" {
    $Tests = Get-ChildItem test -Filter *.dart |
        Where-Object { $_.Name -ne "generate_play_store_screenshots_test.dart" } |
        ForEach-Object { $_.FullName }
    flutter test $Tests
}
Run-Step "Release APK" { flutter build apk --release }
Run-Step "Release AAB" { flutter build appbundle --release }

$Apk = Join-Path $Root "build\app\outputs\flutter-apk\app-release.apk"
$Aab = Join-Path $Root "build\app\outputs\bundle\release\app-release.aab"

if (-not (Test-Path $Apk)) { throw "No se encontró $Apk" }
if (-not (Test-Path $Aab)) { throw "No se encontró $Aab" }

Write-Host ""
Write-Host "RELEASE GATE PASSED" -ForegroundColor Green
Write-Host "APK: $Apk"
Write-Host "AAB: $Aab"
Write-Host "Log: $Log"
Write-Host ""
Write-Host "Antes de Play: completa correo/URL de privacidad y Data safety." -ForegroundColor Yellow
