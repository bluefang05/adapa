# Implementation notes

Haz una copia o commit del proyecto antes de aplicar el overlay.

## Qué se cambió
- Se añadió una misión gamificada por unidad, accesible desde la sección de práctica libre.
- La misión genera 23 interacciones distribuidas en 6 rondas: reconocer, discriminar, construir, usar, reto y desafío final.
- Se añadió puntuación, racha, mejor racha, 2 pistas por unidad, modo experto puntual, feedback correcto/incorrecto/casi/correcto con pista y resumen final.
- Se añadió una cola simple de recuperación diferida para conceptos fallados.
- Se reutilizan los sonidos existentes mediante `PracticeFeedbackService`.

## Archivos nuevos
- `lib/features/practice/gamified_unit_screen.dart`
- `IMPLEMENTATION_NOTES.md`
- `CHANGELOG_GAMIFICATION.md`
- `OVERLAY_MANIFEST.md`

## Archivos modificados
- `lib/features/unit/unit_screen.dart`

## Arquitectura
- `GamifiedUnitScreen` contiene el motor de sesión y la UI de misión.
- `MissionExercise`, `MissionOption`, `MissionAnswerRecord` y `MissionExerciseType` separan contenido, respuesta y estado.
- `buildMissionExercises(UnitContent unit)` crea el contenido de misión desde actividades existentes de la unidad.
- La práctica clásica se conserva como opción secundaria.

## Cómo ejecutar
- `flutter pub get`
- `flutter run`

## Cómo hacer build
- `flutter build web`

## Datos de preguntas
- Los datos base siguen en `assets/content/units/*.json`.
- La misión deriva conceptos desde las actividades cargadas en `UnitContent`.
- La generación está en `buildMissionExercises` dentro de `lib/features/practice/gamified_unit_screen.dart`.

## Futuros assets
- Cada ejercicio tiene `assetKey`.
- `_AssetHook` es el punto de inserción para pictogramas, escenas o personajes en una fase posterior.
- No se añadieron assets visuales finales.

## Añadir un nuevo ejercicio
- Añade o ajusta un concepto en los JSON de unidad, o extiende `buildMissionExercises`.
- Usa `conceptIds`, `MissionExerciseType`, `options`, `correctAnswer`, `hint` y `explanation`.
- Evita insertar lógica de evaluación directamente en widgets de presentación.

## Limitaciones actuales
- La misión no persiste sesión en `localStorage`/SharedPreferences; reiniciar la pantalla empieza de cero.
- No usa cronómetro real.
- `flutter test` no terminó dentro de 5 minutos en este entorno; `flutter analyze` y `flutter build web` sí pasaron.
