param(
  [Parameter(Mandatory=$true)]
  [string]$ProjectPath
)

$ErrorActionPreference = "Stop"

$source = Join-Path $PSScriptRoot "..\assets\vocabulary\u04"
$target = Join-Path $ProjectPath "assets\vocabulary\u04"

if (!(Test-Path $ProjectPath)) {
  throw "No existe el proyecto: $ProjectPath"
}

if (!(Test-Path $target)) {
  New-Item -ItemType Directory -Force -Path $target | Out-Null
}

Write-Host "Copiando 30 visuales ADAPA..."
Copy-Item "$source\*.png" $target -Force

Write-Host ""
Write-Host "Update visual instalado en:"
Write-Host $target
Write-Host ""
Write-Host "Siguiente paso recomendado:"
Write-Host "  cd `"$ProjectPath`""
Write-Host "  flutter pub get"
Write-Host "  flutter test"
