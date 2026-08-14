# ADAPA — paquete acumulativo de práctica y auditoría

Este paquete consolida las actualizaciones anteriores en un solo parche instalable.

## Práctica táctil
- Respuesta por toque con feedback inmediato.
- Error: sonido + shake + reintento sin penalización académica.
- Acierto: sonido + feedback visual.
- Matching resuelto pareja por pareja.
- Práctica libre accesible desde las unidades.
- Reinicio y “Practicar otra vez”.
- Sonidos de feedback con la cola silenciosa recortada.

## Barajado y ordenamiento
- Barajado estable durante cada intento.
- Nuevo orden al remontar/reiniciar el ejercicio.
- Evita conservar el orden fuente cuando existe una permutación diferente.
- Ordering no inicia resuelto cuando puede evitarse.
- Identidad independiente del texto para soportar tokens repetidos.
- Matching maneja etiquetas de respuesta visualmente duplicadas de forma equivalente.

## Selección
- Conserva el comportamiento histórico de respuestas alternativas.
- Selección múltiple real cuando el contenido declara `selection_mode: "multiple"`
  o `select_all_correct: true`.

## Robustez
- `ActivityEvaluator` pasa a fail-closed cuando una actividad evaluable carece de
  `accepted_answers`: el contenido incompleto ya no aprueba cualquier texto no vacío.
- Corrección quirúrgica e idempotente de `dialogue_variant` para manejar correctamente
  opciones representadas como mapas y barajarlas de forma estable.

## Auditoría
- Recorre el corpus real y exige el contrato de 8 unidades, 36 lecciones y 171 actividades.
- Enumera cada actividad auditada por ID, unidad, lección y tipo.
- Detecta opciones vacías/duplicadas, respuestas fuera de opciones, matching ambiguo,
  ordering inconsistente, referencias rotas, Hangul imposible, JSON inválido y tipos sin renderer.
- Separa errores bloqueantes de advertencias pedagógicas.
- Señala respuestas de un solo carácter para revisión contextual sin asumir que son errores.
- Revisa posibles mojibake sin reemplazos masivos ciegos.

## Conservación
No sustituye deliberadamente controladores de progreso, persistencia, TTS, temas,
navegación principal, Android/Gradle ni los JSON de contenido del curso.
