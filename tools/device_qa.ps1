$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

Write-Host "=== ADAPA device QA ===" -ForegroundColor Cyan
flutter devices

$Device = Read-Host "Escribe el device id Android que quieres probar"
if ([string]::IsNullOrWhiteSpace($Device)) {
    throw "Se requiere un device id."
}

Write-Host ""
Write-Host "1/2 Ejecutando integración automatizada..." -ForegroundColor Cyan
flutter test integration_test/app_navigation_test.dart -d $Device
if ($LASTEXITCODE -ne 0) {
    throw "Falló integration_test en $Device."
}

Write-Host ""
Write-Host "2/2 Abriendo ADAPA para smoke test manual..." -ForegroundColor Cyan
Write-Host "Revisa TTS ko-KR, escritura, trazos, imágenes, progreso y reinicio."
flutter run -d $Device
