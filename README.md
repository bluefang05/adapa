# ADAPA Flutter — v0.6

ADAPA es una app educativa offline-first. El primer curso es **Coreano desde cero A0–A1**.

## Estado

El contenido completo vive en `assets/content` y la app navega:

`curso → unidad → lección → actividad`

Las **9 familias de renderer** están implementadas y cubren **171/171 actividades**.

La v0.6 añade progreso y persistencia local:

- intentos por actividad;
- mejor resultado;
- actividad completada;
- reglas reales de finalización por lección;
- desbloqueo de unidades por prerrequisitos;
- punto `Continuar`;
- persistencia de escritura libre y estados de repaso;
- migración defensiva por ids cuando cambia `contentVersion`;
- reinicio manual del progreso.

## Persistencia y Android 21

La v0.6 fija `shared_preferences 2.2.3` y `shared_preferences_android 2.2.2` para no subir el mínimo Android. El progreso se almacena como un documento JSON versionado por curso.

## Carpetas clave

- `lib/core/progress/`: snapshot, reglas y controlador de progreso.
- `lib/core/persistence/`: adaptador de almacenamiento local.
- `lib/core/session/`: estado de sesión y puente hacia persistencia.
- `lib/features/activity/renderers/`: 9 familias interactivas.
- `assets/content/progress_audit_v0.6.json`: auditoría de progreso.

## Android

Este shell todavía no contiene las carpetas de plataforma generadas por `flutter create`. Consulta `ANDROID_TTS_SETUP.md` al integrarlo en un proyecto Flutter Android real.

## Limitación del entorno de generación

Aquí no está instalado Flutter/Dart. No fue posible ejecutar `flutter pub get`, `flutter analyze`, `flutter test` ni compilar Android. Sí se ejecutaron auditorías estáticas de imports, balance estructural, ids del curso, reglas de finalización y referencias.

## Android baseline — v0.7

The repository now contains a real Android host project under `android/`.

Production baseline:

- package/application ID: `com.enmanuelapps.adapa`
- minSdk 24
- offline release manifest
- Android 11+ TTS engine visibility query
- Java 17
- Flutter 3.44.x-compatible Gradle/Kotlin template
- generated technical launcher icon (branding can be refined later)

See `ANDROID_PLATFORM_DECISION_V07.md` and `ANDROID_BUILD_V07.md`.

## UI/UX — v0.8

The learning flow now has a production-oriented mobile shell:

`Curso → ruta de unidades → lección → teoría real → actividad → siguiente`

TTS settings and theory rendering are data-driven and remain offline-first.
See `IMPLEMENTATION_V08.md`.

## QA preflight — v0.9

This version fixes two pre-build blockers discovered during static QA:

- legacy Kotlin Gradle Plugin is now actually applied;
- all 29 nested Hangul stroke asset directories are declared in `pubspec.yaml`.

Use `tools/preflight.ps1` on Windows or `tools/preflight.sh` on Linux/macOS.
See `QA_PREFLIGHT_V09.md`.

## Runtime QA harness — v0.10

The QA suite now pumps every production activity renderer, checks narrow-phone
navigation, includes an Android integration test, and builds a debug APK as part
of the PowerShell/Linux preflight.

A missing Gradle wrapper runtime in the ZIP is no longer treated as a manual
blocker; Flutter injects missing wrapper files when it prepares the Android build.

See `QA_RUNTIME_V010.md`.

## Technical RC — v0.12

Persistence now uses `SharedPreferencesAsync` behind ADAPA's own key/value
interface. Production dependencies are exactly pinned until the first
`pubspec.lock` is generated.

Run `tools/dependency_contract_audit.py` together with the normal preflight.

## Audit hardening — v0.13

Applied validated code/UX audit findings without changing Korean course content. See `AUDIT_HARDENING_V013.md`.
