# ADAPA Flutter — implementación v0.4

## Objetivo de este bloque

Implementar la familia `dialogue` completa sin introducir todavía persistencia permanente ni reconocimiento de voz.

## Actividades activadas

La familia cubre 16 actividades y seis tipos reales de contenido:

- `dialogue`
- `dialogue_roleplay`
- `dialogue_variant`
- `guided_dialogue_fill`
- `final_guided_conversation`
- `tts_dialogue_playback`

Con esta versión, 156 de las 171 actividades del curso tienen renderer interactivo (91.2 %).

## Catálogo de diálogos

`AssetResolver` ahora puede cargar `DialogueScene` desde:

`assets/content/resources/unit_06_dialogue_catalog_v1.0.json`

El modelo separa escena, turnos y slots de sustitución. Los ejercicios no copian el diálogo completo cuando ya existe en el catálogo.

## Roleplay

Los turnos que pertenecen al estudiante se convierten en campos de respuesta. Los demás turnos:

- permanecen visibles;
- muestran traducción cuando el contenido la permite;
- pueden reproducirse mediante TTS `ko-KR`;
- no revelan la respuesta que el alumno debe escribir.

La evaluación usa las variantes aceptadas del JSON y las mismas reglas NFC/espacios/puntuación del resto del motor.

## Variantes de diálogo y partículas

Se añadió `KoreanParticleHelper`.

Esto evita un error que produciría una sustitución textual ingenua en preguntas de ubicación. Por ejemplo, reemplazar directamente `화장실` por `학교` dentro de `화장실이 어디에 있어요?` generaría el incorrecto `학교이 ...`.

El builder selecciona la partícula de sujeto según batchim:

- `학교가 어디에 있어요?`
- `카페가 어디에 있어요?`
- `은행이 어디에 있어요?`

## Reto final

`final_guided_conversation` ahora:

- oculta romanización y traducción durante el intento;
- exige completar todos los campos requeridos;
- exige Hangul en los campos coreanos;
- rechaza texto latino en esos campos;
- evalúa automáticamente solo la respuesta controlada;
- forma el pedido con el sufijo `주세요.`;
- guarda un borrador en memoria mientras se escribe;
- construye una conversación reproducible por TTS.

La escritura abierta sigue sin recibir una falsa calificación gramatical.

## Sesión en memoria

Se añadió `ActivitySessionStore`. En v0.4 sirve para:

- conservar el borrador del reto final mientras la app sigue abierta;
- pasar la conversación terminada al ejercicio `tts_dialogue_playback`;
- preparar la interfaz que posteriormente usará la persistencia real.

No es todavía progreso persistente: cerrar la app elimina esta sesión.

## Validación

El auditor comprobó las 16 actividades conversacionales:

- todos los `dialogue_ref` existen;
- todos los índices de roleplay son válidos;
- cada turno evaluado tiene respuestas válidas;
- los slots solicitados existen;
- las opciones de variantes están permitidas por el catálogo;
- los campos controlados del reto final tienen respuestas;
- `tts_dialogue_playback` apunta a una actividad existente;
- las tres variantes de ubicación producen la partícula correcta.

Errores detectados: 0.

## Próximo bloque

Implementar `reading_speaking` y `review` (15 actividades). Eso permitirá llegar a 171/171 actividades con renderer antes de añadir progreso persistente.
