$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$path = Join-Path $root 'lib\core\evaluation\activity_evaluator.dart'

if (-not (Test-Path $path)) {
    throw "No se encontro: $path"
}

$utf8 = New-Object System.Text.UTF8Encoding($false)
$text = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)
$original = $text
$nl = if ($text.Contains("`r`n")) { "`r`n" } else { "`n" }

$old = @(
    '    if (answers.isEmpty) {',
    '      return EvaluationResult(isCorrect: value.trim().isNotEmpty);',
    '    }'
) -join $nl

$new = @(
    '    if (answers.isEmpty) {',
    '      return const EvaluationResult(',
    '        isCorrect: false,',
    "        message: 'Esta actividad evaluable no tiene respuestas configuradas.',",
    '      );',
    '    }'
) -join $nl

if ($text.Contains("Esta actividad evaluable no tiene respuestas configuradas.")) {
    Write-Host 'ActivityEvaluator ya esta en modo fail-closed; no hubo cambios.'
    exit 0
}

if (-not $text.Contains($old)) {
    throw 'No se encontro el fallback esperado de accepted_answers. No se modifico el archivo.'
}

$text = $text.Replace($old, $new)
$backup = "$path.loop4.bak"
if (-not (Test-Path $backup)) {
    [System.IO.File]::WriteAllText($backup, $original, $utf8)
    Write-Host "Backup: $backup"
}

[System.IO.File]::WriteAllText($path, $text, $utf8)
Write-Host 'activity_evaluator.dart endurecido: contenido evaluable incompleto ya no acepta cualquier texto.'
exit 0
