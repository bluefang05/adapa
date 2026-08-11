$ErrorActionPreference = "Stop"

Write-Host "=== ADAPA upload key ===" -ForegroundColor Cyan

if (-not (Get-Command keytool -ErrorAction SilentlyContinue)) {
    throw "keytool no está en PATH. Instala/configura JDK 17 y vuelve a ejecutar."
}

$DefaultPath = Join-Path $env:USERPROFILE "adapa-upload.jks"
$Keystore = Read-Host "Ruta del keystore [$DefaultPath]"
if ([string]::IsNullOrWhiteSpace($Keystore)) {
    $Keystore = $DefaultPath
}

$Alias = Read-Host "Alias [adapa-upload]"
if ([string]::IsNullOrWhiteSpace($Alias)) {
    $Alias = "adapa-upload"
}

if (Test-Path $Keystore) {
    throw "Ya existe: $Keystore. No se sobrescribe una clave existente."
}

Write-Host ""
Write-Host "keytool pedirá las contraseñas de forma interactiva." -ForegroundColor Yellow
& keytool -genkeypair -v `
    -keystore $Keystore `
    -storetype JKS `
    -keyalg RSA `
    -keysize 2048 `
    -validity 10000 `
    -alias $Alias

if ($LASTEXITCODE -ne 0) {
    throw "keytool terminó con error."
}

Write-Host ""
Write-Host "Keystore creado: $Keystore" -ForegroundColor Green
Write-Host "Guárdalo fuera del repositorio y haz una copia segura." -ForegroundColor Yellow
Write-Host "Siguiente: .\tools\configure_signing.ps1"
