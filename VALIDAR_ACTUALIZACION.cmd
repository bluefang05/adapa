@echo off
setlocal EnableExtensions
title ADAPA - loop 4 auditoria pedagogica y seleccion multiple

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

set "SUMMARY=ADAPA_LOOP4_SUMMARY.txt"
> "%SUMMARY%" echo ADAPA LOOP 4 - RESUMEN DE VALIDACION
>>"%SUMMARY%" echo Fecha: %DATE% %TIME%
>>"%SUMMARY%" echo Directorio: %CD%
>>"%SUMMARY%" echo.

echo ============================================================
echo ADAPA - LOOP 4: APLICAR + AUDITAR + VALIDAR
echo ============================================================
echo.

echo [0/8] Aplicando correccion quirurgica de dialogue_variant...
powershell -NoProfile -ExecutionPolicy Bypass -File "tools\patch_dialogue_variant.ps1" > ADAPA_PATCH_DIALOGUE.txt 2>&1
set "RC_PATCH_DIALOGUE=%ERRORLEVEL%"
type ADAPA_PATCH_DIALOGUE.txt
>>"%SUMMARY%" echo patch_dialogue_variant: %RC_PATCH_DIALOGUE%
if not "%RC_PATCH_DIALOGUE%"=="0" goto :fatal

echo.
echo [1/8] Endureciendo ActivityEvaluator contra contenido incompleto...
powershell -NoProfile -ExecutionPolicy Bypass -File "tools\patch_activity_evaluator.ps1" > ADAPA_PATCH_EVALUATOR.txt 2>&1
set "RC_PATCH_EVALUATOR=%ERRORLEVEL%"
type ADAPA_PATCH_EVALUATOR.txt
>>"%SUMMARY%" echo patch_activity_evaluator: %RC_PATCH_EVALUATOR%
if not "%RC_PATCH_EVALUATOR%"=="0" goto :fatal

echo.
echo [2/8] Resolviendo dependencias...
call flutter pub get > ADAPA_PUB_GET.txt 2>&1
set "RC_PUB=%ERRORLEVEL%"
type ADAPA_PUB_GET.txt
>>"%SUMMARY%" echo flutter pub get: %RC_PUB%
if not "%RC_PUB%"=="0" goto :fatal

echo.
echo [3/8] Formateando el proyecto...
call dart format . > ADAPA_FORMAT.txt 2>&1
set "RC_FORMAT=%ERRORLEVEL%"
type ADAPA_FORMAT.txt
>>"%SUMMARY%" echo dart format .: %RC_FORMAT%
if not "%RC_FORMAT%"=="0" goto :fatal

echo.
echo [4/8] Analizando codigo...
call flutter analyze > ADAPA_ANALYZE.txt 2>&1
set "RC_ANALYZE=%ERRORLEVEL%"
type ADAPA_ANALYZE.txt
>>"%SUMMARY%" echo flutter analyze: %RC_ANALYZE%

echo.
echo [5/8] Auditando las 171 actividades y sus referencias...
call flutter test test\content_completeness_audit_test.dart -r expanded > ADAPA_CONTENT_AUDIT.txt 2>&1
set "RC_AUDIT=%ERRORLEVEL%"
type ADAPA_CONTENT_AUDIT.txt
>>"%SUMMARY%" echo content audit: %RC_AUDIT%

echo.
echo [6/8] Probando seleccion multiple y evaluacion fail-closed...
call flutter test test\choice_multi_select_test.dart test\activity_evaluator_fail_closed_test.dart -r expanded > ADAPA_LOOP4_CORE_TESTS.txt 2>&1
set "RC_CORE=%ERRORLEVEL%"
type ADAPA_LOOP4_CORE_TESTS.txt
>>"%SUMMARY%" echo loop 4 core tests: %RC_CORE%

echo.
echo [7/8] Ejecutando pruebas de barajado, matching, ordering y dialogue_variant...
call flutter test test\activity_shuffle_test.dart test\ordering_duplicate_token_test.dart test\ordering_reorder_contract_test.dart test\matching_duplicate_label_test.dart test\dialogue_variant_renderer_contract_test.dart test\practice_interaction_test.dart -r expanded > ADAPA_LOOP4_INTERACTION_TESTS.txt 2>&1
set "RC_INTERACTION=%ERRORLEVEL%"
type ADAPA_LOOP4_INTERACTION_TESTS.txt
>>"%SUMMARY%" echo loop 4 interaction tests: %RC_INTERACTION%

echo.
echo [8/8] Ejecutando la bateria completa de pruebas...
call flutter test > ADAPA_TESTS.txt 2>&1
set "RC_TEST=%ERRORLEVEL%"
type ADAPA_TESTS.txt
>>"%SUMMARY%" echo flutter test: %RC_TEST%

set "RC_FINAL=0"
if not "%RC_ANALYZE%"=="0" set "RC_FINAL=1"
if not "%RC_AUDIT%"=="0" set "RC_FINAL=1"
if not "%RC_CORE%"=="0" set "RC_FINAL=1"
if not "%RC_INTERACTION%"=="0" set "RC_FINAL=1"
if not "%RC_TEST%"=="0" set "RC_FINAL=1"

>>"%SUMMARY%" echo.
if "%RC_FINAL%"=="0" (
  >>"%SUMMARY%" echo RESULTADO: OK
  echo.
  echo ============================================================
  echo LOOP 4 VALIDADO CORRECTAMENTE
  echo ============================================================
) else (
  >>"%SUMMARY%" echo RESULTADO: REQUIERE REVISION
  echo.
  echo ============================================================
  echo EL LOOP 4 REQUIERE REVISION.
  echo Revisa ADAPA_ANALYZE.txt, ADAPA_CONTENT_AUDIT.txt y ADAPA_TESTS.txt.
  echo ============================================================
)

echo.
echo Resumen: %CD%\%SUMMARY%
pause
exit /b %RC_FINAL%

:fatal
>>"%SUMMARY%" echo.
>>"%SUMMARY%" echo RESULTADO: ERROR PREVIO A VALIDACION

echo.
echo ============================================================
echo ERROR PREVIO A LAS PRUEBAS. No se continuo sobre un estado incompleto.
echo ============================================================
pause
exit /b 1
