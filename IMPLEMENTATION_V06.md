# ADAPA Flutter v0.6 — progreso y persistencia local

## Qué cambia

La v0.5 ya renderizaba 171/171 actividades. La v0.6 añade la capa que faltaba para que estudiar tenga continuidad entre sesiones.

### ActivityProgress

Por actividad se conserva:

- cantidad de intentos;
- mejor puntuación;
- si alguna vez fue completada;
- fecha del último intento;
- fecha de primera finalización.

La puntuación es acumulativa por **mejor resultado**: fallar un reintento no destruye un resultado mejor conseguido anteriormente.

### Finalización de lecciones

ADAPA lee el `completion` de cada lección. Para las lecciones con `minimum_score`:

1. todas las actividades requeridas calificables deben haberse intentado;
2. las requeridas no calificables deben estar completadas;
3. se calcula la media del mejor resultado de las requeridas calificables;
4. se compara con 0.75 o 0.80 según el contenido.

Las reglas especiales `complete_required_open_activity`, `complete_required_review` y `complete_final_challenge` requieren completar sus actividades abiertas/revisión/final.

### Unidades

Una unidad se completa al completar sus lecciones requeridas. Una unidad bloqueada se habilita cuando se completan todos sus `prerequisites`.

### Continuar

Cada visita a una actividad actualiza `ResumePointer`. Tras una actividad aprobada, ADAPA intenta avanzar a la siguiente actividad disponible. Si al final de una unidad quedan huecos en una lección no completada, vuelve al primer punto pendiente en esa unidad.

### Escritura y repaso

`ActivitySessionStore` ahora se restaura desde disco al arrancar. Por eso sobreviven reinicios:

- texto guardado;
- escritura libre;
- borradores con `autosave`;
- bloques marcados;
- checks de autoevaluación;
- repetición/escucha registrada;
- conversación final guardada.

### Persistencia

Se usa `SharedPreferencesProgressStore`, que guarda un documento JSON versionado por curso. Si cambia la versión del contenido, se preservan solo ids de actividad que sigan existiendo; datos obsoletos no impiden iniciar la app.

### Compatibilidad Android

Para conservar API 21 se fijan versiones compatibles de `shared_preferences` y `shared_preferences_android`; no se usa la línea actual del paquete que ha subido el mínimo Android.

## Pruebas añadidas

- umbral 75 %;
- desbloqueo por prerrequisitos;
- acumulación de intentos;
- preservación del mejor resultado;
- persistencia/rehidratación del texto;
- round-trip del JSON mediante `SharedPreferences` mock.

## Limitación actual

El entorno de generación no incluye Flutter/Dart SDK, por lo que estas pruebas quedaron escritas pero no ejecutadas aquí.
