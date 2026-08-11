#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

mkdir -p qa_logs
STAMP="$(date +%Y%m%d-%H%M%S)"
LOG="qa_logs/preflight-$STAMP.txt"

run_step() {
  local name="$1"
  shift
  echo
  echo "=== $name ==="
  echo "=== $name ===" >> "$LOG"
  "$@" 2>&1 | tee -a "$LOG"
}

command -v flutter >/dev/null 2>&1 || {
  echo "Flutter is not in PATH." >&2
  exit 1
}

run_step "Flutter version" flutter --version
run_step "Flutter doctor" flutter doctor -v

if command -v python3 >/dev/null 2>&1; then
  run_step "Static project audit" python3 tools/static_preflight.py
  run_step "Compiler regression audit" python3 tools/compiler_regression_audit.py
  run_step "Dependency contract" python3 tools/dependency_contract_audit.py
  run_step "UTF-8 / Hangul integrity" python3 tools/utf8_hangul_audit.py
fi

run_step "Flutter clean" flutter clean
run_step "Pub get" flutter pub get
run_step "Android dependency suggestions" flutter analyze --suggestions
run_step "Analyze" flutter analyze
run_step "Unit and widget tests" flutter test

# Flutter injects missing Gradle wrapper runtime files as part of Android build setup.
run_step "Debug APK" flutter build apk --debug
run_step "Connected devices" flutter devices

echo
echo "Preflight passed."
echo "Expected APK: build/app/outputs/flutter-apk/app-debug.apk"
echo "Log: $LOG"
