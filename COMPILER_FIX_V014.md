# ADAPA Flutter v0.14 — first real compiler fixes

The first real `flutter run --release -v` reached Flutter's Dart compilation stage and exposed two source-level regressions.

## Fixed

1. `ActivityFamily.label` was implemented only as an extension member. Screens importing `activity_content.dart` transitively know the enum type but do not import that extension, so `family.label` failed to resolve. The label is now a real instance member on the enum itself.

2. `_DialogueVariantActivityState` referenced `_sessionStore` without declaring it. The cached store field is now declared.

3. `_FinalGuidedConversationActivityState` already declared `_sessionStore` for safe draft flushing during `dispose`, but never initialized it. It is now initialized in `didChangeDependencies`, so the v0.13 500 ms autosave actually persists.

No Korean course content, scoring rules, romanization policy, or Android configuration was changed.
