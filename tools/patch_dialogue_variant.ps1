$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$path = Join-Path $root 'lib\features\activity\renderers\dialogue_activity_renderer.dart'

if (-not (Test-Path $path)) {
    throw "No se encontro: $path"
}

$utf8 = New-Object System.Text.UTF8Encoding($false)
$text = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)
$original = $text
$nl = if ($text.Contains("`r`n")) { "`r`n" } else { "`n" }

function Replace-Once {
    param(
        [string]$Name,
        [string]$Old,
        [string]$New,
        [string]$AlreadyMarker = ''
    )

    if ($AlreadyMarker -and $script:text.Contains($AlreadyMarker)) {
        Write-Host "  OK ya aplicado: $Name"
        return
    }
    if (-not $script:text.Contains($Old)) {
        throw "No se pudo aplicar '$Name': no se encontro el bloque esperado. No se modifico el archivo."
    }
    $script:text = $script:text.Replace($Old, $New)
    Write-Host "  aplicado: $Name"
}

try {
    $oldImport = "import '../../../core/models/activity_content.dart';${nl}import '../../../core/runtime/adapa_runtime.dart';"
    $newImport = "import '../../../core/models/activity_content.dart';${nl}import '../../../core/practice/activity_shuffle.dart';${nl}import '../../../core/runtime/adapa_runtime.dart';"
    Replace-Once -Name 'import de ActivityShuffle' -Old $oldImport -New $newImport -AlreadyMarker "import '../../../core/practice/activity_shuffle.dart';"

    $oldChoices = @(
        '  ActivitySessionStore? _sessionStore;',
        '',
        '  List<dynamic> get _choices =>',
        "      (widget.activity.payload['choices'] as List? ?? const []).toList(growable: false);"
    ) -join $nl

    $newChoices = @(
        '  ActivitySessionStore? _sessionStore;',
        '  late final List<dynamic> _shuffledChoices;',
        '',
        '  List<dynamic> get _choices => _shuffledChoices;',
        '',
        '  @override',
        '  void initState() {',
        '    super.initState();',
        '    _shuffledChoices = ActivityShuffle.copy<dynamic>(',
        "      widget.activity.payload['choices'] as List? ?? const [],",
        '    );',
        '  }',
        '',
        '  String _valueOf(dynamic option) {',
        '    if (option is Map) {',
        "      return (option['ko'] ?? option['value'] ?? option['id'] ?? '').toString();",
        '    }',
        '    return option.toString();',
        '  }',
        '',
        '  String _labelOf(dynamic option) {',
        '    if (option is Map) {',
        "      return (option['label_es'] ??",
        "              option['label'] ??",
        "              option['ko'] ??",
        "              option['value'] ??",
        "              option['id'] ??",
        "              '')",
        '          .toString();',
        '    }',
        '    return option.toString();',
        '  }'
    ) -join $nl
    Replace-Once -Name 'barajado estable y adaptadores de choice' -Old $oldChoices -New $newChoices -AlreadyMarker 'late final List<dynamic> _shuffledChoices;'

    $oldValue = @(
        '    final value = option is Map',
        "        ? (option['ko'] ?? option['value'] ?? option['id'] ?? option).toString()",
        '        : option.toString();'
    ) -join $nl
    $newValue = '    final value = _valueOf(option);'
    Replace-Once -Name 'valor real de opcion mapa' -Old $oldValue -New $newValue -AlreadyMarker $newValue

    $oldComplete = @(
        "    final complete = widget.activity.completionRule == 'complete_all_variants'",
        '        ? _seen.length >= _choices.length',
        '        : true;'
    ) -join $nl
    $newComplete = @(
        '    final distinctChoiceCount = _choices.map(_valueOf).toSet().length;',
        "    final complete = widget.activity.completionRule == 'complete_all_variants'",
        '        ? distinctChoiceCount > 0 && _seen.length >= distinctChoiceCount',
        '        : true;'
    ) -join $nl
    Replace-Once -Name 'conteo de variantes por valor real' -Old $oldComplete -New $newComplete -AlreadyMarker 'final distinctChoiceCount = _choices.map(_valueOf).toSet().length;'

    Replace-Once -Name 'replacement coreano usa valor y no Map.toString' `
        -Old '        final choice = _selected?.toString();' `
        -New '        final choice = _selected == null ? null : _valueOf(_selected);' `
        -AlreadyMarker '        final choice = _selected == null ? null : _valueOf(_selected);'

    Replace-Once -Name 'label de ChoiceChip' `
        -Old '                    label: Text(option.toString()),' `
        -New '                    label: Text(_labelOf(option)),' `
        -AlreadyMarker '                    label: Text(_labelOf(option)),'

    Replace-Once -Name 'seleccion de ChoiceChip por valor real' `
        -Old '                    selected: choice == option.toString(),' `
        -New '                    selected: choice == _valueOf(option),' `
        -AlreadyMarker '                    selected: choice == _valueOf(option),'

    if ($text -eq $original) {
        Write-Host 'Dialogue variant ya estaba actualizado; no hubo cambios.'
        exit 0
    }

    $backup = "$path.loop3.bak"
    if (-not (Test-Path $backup)) {
        [System.IO.File]::WriteAllText($backup, $original, $utf8)
        Write-Host "Backup: $backup"
    }

    [System.IO.File]::WriteAllText($path, $text, $utf8)
    Write-Host 'dialogue_activity_renderer.dart actualizado de forma quirurgica.'
    exit 0
}
catch {
    # The file is only written after every expected transformation succeeds.
    Write-Error $_
    exit 1
}
