#!/usr/bin/env python3
from pathlib import Path
import json
import sys

ROOT = Path(__file__).resolve().parents[1]
errors = []

TEXT_SUFFIXES = {'.dart', '.json', '.yaml', '.yml', '.md', '.html', '.txt', '.kt', '.kts', '.xml'}
MOJIBAKE_MARKERS = ('ΓÇ', '├', 'φò', 'Ω░', 'πä', 'πà', '\ufffd')

for path in ROOT.rglob('*'):
    if not path.is_file() or path.suffix.lower() not in TEXT_SUFFIXES:
        continue
    if any(part in {'.dart_tool', 'build', '.gradle', 'qa_logs'} for part in path.parts):
        continue
    try:
        text = path.read_text(encoding='utf-8', errors='strict')
    except UnicodeDecodeError as exc:
        errors.append(f'Invalid UTF-8: {path.relative_to(ROOT)}: {exc}')
        continue
    for marker in MOJIBAKE_MARKERS:
        if marker in text:
            errors.append(
                f'Possible mojibake marker {marker!r}: {path.relative_to(ROOT)}'
            )

# High-value orthographic sentinels: if these fail, the Korean engine/source was altered.
app = (ROOT / 'lib/app.dart').read_text(encoding='utf-8')
composer = (ROOT / 'lib/core/hangul/hangul_composer.dart').read_text(encoding='utf-8')
particle = (ROOT / 'lib/core/dialogue/korean_particle_helper.dart').read_text(encoding='utf-8')

for expected, source, label in (
    ('한글', app, 'app startup Hangul'),
    ('ㄱ', composer, 'HangulComposer initial jamo'),
    ('ㅏ', composer, 'HangulComposer medial jamo'),
    ('가', particle, 'Korean particle helper fixture'),
):
    if expected not in source:
        errors.append(f'Missing UTF-8 Hangul sentinel {expected!r}: {label}')

# All course JSON must remain valid UTF-8 JSON.
for path in (ROOT / 'assets/content/units').glob('*.json'):
    try:
        json.loads(path.read_text(encoding='utf-8'))
    except Exception as exc:
        errors.append(f'Invalid course JSON {path.name}: {exc}')

print(f'ADAPA UTF-8/Hangul audit: {len(errors)} error(s)')
for error in errors:
    print('ERROR:', error)

sys.exit(1 if errors else 0)
