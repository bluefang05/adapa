@echo off
setlocal EnableExtensions
title ADAPA - auditoria completa de 171 actividades

if not exist "pubspec.yaml" (
  echo ERROR: Ejecuta este archivo desde la raiz del proyecto ADAPA.
  pause
  exit /b 1
)

findstr /C:"name: adapa" "pubspec.yaml" >nul
if errorlevel 1 (
  echo ERROR: Este directorio no parece ser el proyecto ADAPA.
  pause
  exit /b 1
)

echo ============================================================
echo ADAPA - AUDITORIA REAL DE LAS 171 ACTIVIDADES
echo ============================================================
echo.
echo Se cargan los JSON reales de assets/content/units y resources.
echo Se separan ERRORES BLOQUEANTES de ADVERTENCIAS pedagogicas.
echo El informe enumera tipos, selecciones multiples, respuestas de un
echo caracter y posibles mojibake.
echo.

call flutter test test\content_completeness_audit_test.dart -r expanded > ADAPA_CONTENT_AUDIT.txt 2>&1
set "RC=%ERRORLEVEL%"

type ADAPA_CONTENT_AUDIT.txt

echo.
echo ============================================================
if "%RC%"=="0" (
  echo AUDITORIA TERMINADA SIN ERRORES BLOQUEANTES
) else (
  echo LA AUDITORIA DETECTO CONTENIDO O REFERENCIAS QUE DEBEN REVISARSE
)
echo Informe: %CD%\ADAPA_CONTENT_AUDIT.txt
echo ============================================================
pause
exit /b %RC%
