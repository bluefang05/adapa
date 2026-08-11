# ADAPA Flutter v0.2 — renderers interactivos

Esta iteración implementa cuatro familias de actividad basadas en el contenido real del curso:

- `choice`
- `matching`
- `text_input`
- `ordering`

## Cobertura funcional

### Choice
Soporta:

- `multiple_choice`
- `listen_and_choose`
- `image_choice`
- `scenario_choice`

Incluye TTS real mediante `flutter_tts`, velocidad normal/lenta, imágenes resueltas desde catálogo y feedback específico cuando existe.

### Matching
Soporta `matching` con selección izquierda → derecha, reasignación y comprobación completa.

### Text input
Soporta:

- `short_answer`
- `listen_and_type`
- `fill_blank`
- `free_practice`
- `free_writing`
- `copy_practice`
- `pattern_response`

La normalización usa NFC real mediante `unorm_dart`, además de trim, colapso de espacios, puntuación terminal y sensibilidad a mayúsculas según los datos.

Las respuestas abiertas con `scoreMode: none` se validan por reglas de finalización, no como supuesta corrección lingüística.

### Ordering
Soporta:

- `word_order`
- `sequence_order`
- `sentence_sequence`
- `conversation_shuffle`

Usa reordenamiento por arrastre y compara contra `correct_order`.

## TTS

`NoopTtsService` deja de ser el servicio de producción. La app crea `FlutterTtsService`, mientras que la interfaz `TtsService` permanece desacoplada de los widgets.

## Aún pendiente

Las cinco familias siguientes continúan mostrando un renderer seguro de espera:

- `hangul_structure`
- `dialogue`
- `reading_speaking`
- `review`
- `visual_reference`

No se pierde contenido y no se crean widgets ficticios antes de necesitarlos.
