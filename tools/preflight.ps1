$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

$LogDir = Join-Path $Root "qa_logs"
New-Item -ItemType Directory -Force -Path $LogDir | Out-Null
$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$Log = Join-Path $LogDir "preflight-$Stamp.txt"

function Run-Step {
    param(
        [string]$Name,
        [scriptblock]$Command
    )
    Write-Host ""
    Write-Host "=== $Name ===" -ForegroundColor Cyan
    "=== $Name ===" | Out-File -FilePath $Log -Append -Encoding utf8
    & $Command 2>&1 | Tee-Object -FilePath $Log -Append
    if ($LASTEXITCODE -ne 0) {
        throw "$Name failed with exit code $LASTEXITCODE. See $Log"
    }
}

Write-Host "ADAPA Flutter v0.10 QA preflight" -ForegroundColor Green

if (-not (Get-Command flutter -ErrorAction SilentlyContinue)) {
    throw "Flutter is not in PATH."
}

Run-Step "Flutter version" { flutter --version }
Run-Step "Flutter doctor" { flutter doctor -v }

if (Get-Command python -ErrorAction SilentlyContinue) {
    Run-Step "Static project audit" { python tools/static_preflight.py }
    Run-Step "Dependency contract" { python tools/dependency_contract_audit.py }
    Run-Step "UTF-8 / Hangul integrity" { python tools/utf8_hangul_audit.py }
}

Run-Step "Flutter clean" { flutter clean }
Run-Step "Pub get" { flutter pub get }

# This checks Java / Gradle / AGP compatibility as understood by Flutter.
Run-Step "Android dependency suggestions" { flutter analyze --suggestions }

Run-Step "Analyze" { flutter analyze }
Run-Step "Unit and widget tests" { flutter test }

# Do not skip merely because gradlew/jar were absent in the ZIP.
# Flutter's Android tooling injects missing wrapper runtime files when building.
Run-Step "Debug APK" { flutter build apk --debug }

Run-Step "Connected devices" { flutter devices }

Write-Host ""
Write-Host "Preflight passed. Debug APK should be under:" -ForegroundColor Green
Write-Host "build\app\outputs\flutter-apk\app-debug.apk"
Write-Host "Log: $Log"
