# Changelog

## 1.0.6+20 — Korean phonetic normalization, template evaluation, and answer expansion

- Added `KoreanPhoneticNormalizer` to handle isolated jamos and vowels cleanly in Android Google TTS.
- Added template sentence evaluation flexibility in text input activities.
- Implemented bidirectional tap interaction in matching activities so users can select right or left options first.
- Added arrow controls to ordering activities for enhanced touch accessibility.
- Expanded accepted answer variations across units U03, U04, U05, U07, and U08.
- Restored standard prerequisite progression route for production release (`unlockAllContentForTesting = false`).

## 1.0.5+19 — Temporary open testing route

- Temporarily unlocked all course units so every activity can be tested freely.
- Preserved progress tracking and the prerequisite rules for easy restoration before production release.

## 1.0.4+18 — Activity respondibility audit

- Fixed matching activities so answer options are visible before the cards to match, avoiding screens that look impossible to answer.
- Added regression coverage to ensure production activities do not fall back to unsupported or unimplemented renderer messages.
- Tightened bundled content assets so internal audit reports are not packaged with the app.
- Revalidated Korean TTS, content completeness, activity renderers, mobile navigation, and Android debug build.

## 1.0.3+17 — Activity pronunciation and prompt fixes

- Clarified the basic consonants matching prompt so learners know they are matching each consonant with its initial sound hint.
- Fixed activity pronunciation playback so Korean TTS failures show a Spanish message instead of failing silently.
- Prevented short Spanish answer labels from being sent to Korean TTS in choice activities.

## 1.0.2+16 — Korean TTS compatibility

- Improved Korean pronunciation playback by accepting compatible device TTS locale variants such as `ko_KR`, `ko`, and extended Korean tags while still rejecting non-Korean fallback voices.
- Added regression coverage for Korean TTS locale matching.
- Added `AGENTS.md` so future AI/code assistants can work safely with the project structure, content rules, TTS behavior, and release-sensitive areas.

## 0.14.0+14 — First real compiler fixes

- Made `ActivityFamily.label` an enum instance member so all UI call sites compile.
- Declared `_DialogueVariantActivityState._sessionStore`.
- Initialized final guided-conversation `_sessionStore`, restoring debounced draft persistence.
- Added a regression test and static compiler-regression audit for these failures.


## 0.13.0+13 — Audit hardening and UX cleanup

- Debounced text and final-conversation draft autosave by 500 ms and flushes pending drafts on dispose.
- Persistence failures are no longer silently swallowed; the UI exposes a retry action.
- All direct theory TTS buttons now surface missing-voice failures consistently.
- Added automated grading-contract checks for scored text activities.
- Verified the current final playback source is `final_guided_conversation`; no unused speculative guided-fill patch was applied.
- Added strict UTF-8/Hangul integrity auditing to preflight/release gate.
- Removed duplicate Home reset action; progress reset now lives under Privacy & data.
- Simplified lesson progress copy and added “No iniciada” state.
- Simplified Korean voice setup and made TTS speed labels human-readable.
- Added confirmation before skipping an untouched evaluable activity.
- Migrated ordering activities to `onReorderItem` and removed the obsolete manual `newIndex` adjustment.

## 0.12.0+12 — Release-candidate technical hardening

- Migrated local persistence to `SharedPreferencesAsync`.
- Android uses the plugin's modern async storage path (DataStore Preferences by default).
- Added injectable key/value storage for deterministic tests.
- Removed all use of `SharedPreferences.getInstance()` from application/test code.
- Removed `SharedPreferences.setMockInitialValues` dependence from widget/unit tests.
- Pinned production dependencies exactly until the first real `pubspec.lock` is generated.
- Updated `flutter_lints` to 6.0.0.
- Added a dependency/toolchain contract audit.

## 0.11.0+11

- Release signing configuration.
- Privacy/data UI and policy.
- Google Play listing preparation.
- 512×512 icon and 1024×500 feature graphic.
- Release gate and upload-key scripts.

## 0.10.0+10

- Executable runtime QA harness for 171 activity renderers.
- Narrow-phone widget smoke tests.
- Android integration test.

Earlier iterations built the course content, activity engine, progress,
persistence, Android host, and study UI.
