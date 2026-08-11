#!/usr/bin/env python3
from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
errors = []

def require(condition, message):
    if not condition:
        errors.append(message)

family = (ROOT / 'lib/core/models/activity_family.dart').read_text(encoding='utf-8')
dialogue = (ROOT / 'lib/features/activity/renderers/dialogue_activity_renderer.dart').read_text(encoding='utf-8')

require('String get label =>' in family, 'ActivityFamily.label is missing')
require('ActivitySessionStore? _sessionStore;' in dialogue, 'Dialogue state session-store field missing')
require('_sessionStore ??= AdapaRuntime.of(context).sessionStore;' in dialogue, 'Dialogue session-store initialization missing')

print(f'ADAPA compiler regression audit: {len(errors)} error(s)')
for error in errors:
    print('ERROR:', error)
sys.exit(1 if errors else 0)
