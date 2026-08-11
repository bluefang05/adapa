# ADAPA Flutter v0.5 — motor de actividades completo

## Resultado

La v0.5 implementa las dos familias restantes: `reading_speaking` y `review`.

Cobertura del curso:

- 171/171 actividades con renderer funcional.
- 100 % de cobertura por familia.
- 9/9 familias implementadas.

## Reading / speaking

Tipos cubiertos:

- `reading_challenge`
- `reading_aloud`
- `speaking_practice`
- `tts_readback`

Comportamiento:

- lectura antes de revelar TTS;
- TTS normal y lento;
- confirmación de intento antes de escuchar;
- repetición frase por frase;
- no se inventa una nota de pronunciación sin reconocimiento de voz;
- `tts_readback` recupera la escritura guardada en la sesión.

## Review

Tipos cubiertos:

- `known_block_marking`
- `self_review`
- `self_check`
- `progress_reflection`
- `final_self_review`
- `answer_key_review`
- `scenario_recall`

La autoevaluación permite responder **Sí / Todavía no**. Una respuesta negativa sigue siendo una revisión válida y no obliga al estudiante a fingir dominio.

La clave de respuestas se resuelve desde el bloque teórico real de la Unidad 8 y conserva la advertencia sobre la inconsistencia de la página 10.

`scenario_recall` resuelve las ocho actividades originales por ID y permite intentar primero y revelar el modelo después.

## Infraestructura añadida

`AssetResolver` ahora puede:

- cargar una lectura completa;
- resolver actividades por ID;
- resolver bloques teóricos por ID.

`ActivitySessionStore` se reutiliza para:

- lectura intentada/escuchada;
- frases repetidas;
- autoevaluaciones;
- texto escrito para TTS posterior;
- repaso de escenarios.

Sigue siendo almacenamiento de sesión. La persistencia local real es el siguiente bloque.

## TTS readback

`TextInputActivityRenderer` ahora guarda su texto al comprobar/guardar. Esto permite que `u07l04_a03` lea mediante TTS las cinco frases escritas en `u07l04_a01`.

## Validación

`renderer_coverage_report_v0.5.json` confirma:

- 171 actividades;
- 171 interactivas;
- 100.0 % de cobertura;
- 0 errores de payload en las dos familias nuevas;
- 0 imports locales rotos;
- 0 advertencias de balance de delimitadores.

No se pudo ejecutar `flutter analyze`, `flutter test` o una build Android porque el entorno de generación no dispone del SDK Flutter/Dart.

## Próximo bloque

Con el motor de actividades completo, el siguiente paso es **progreso + persistencia local**:

- estado de actividad;
- intentos;
- mejor resultado;
- lecciones completadas;
- desbloqueo de unidades;
- continuar donde quedó;
- escritura libre persistente;
- revisión/repaso pendiente.
