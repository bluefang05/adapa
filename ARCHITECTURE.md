# Arquitectura inicial de ADAPA Flutter

## Principio

El motor no conoce Coreano de forma hardcodeada. Conoce **curso, unidad, lección, bloque y actividad**. El contenido coreano vive en JSON/assets.

## Capas

### `core/models`
Modelos inmutables que representan el esquema normalizado.

### `core/content`
`CourseRepository` abstrae el origen del curso. La v1 usa `AssetCourseRepository`; en el futuro podría existir otro repositorio sin modificar las pantallas.

### `core/services`
Puertos como `TtsService`. Los plugins de plataforma se conectan aquí, no dentro de las actividades.

### `features/*`
Pantallas y renderers. Los 39 tipos reales se agrupan en 9 familias.

## Decisión deliberada

`ActivityContent.payload` mantiene los campos específicos de cada tipo como mapa. No se crean 39 clases Dart todavía. Primero implementaremos las 9 familias y extraeremos modelos específicos solamente cuando un renderer necesite invariantes fuertes.

Esto evita dos extremos:

- un `Map<String,dynamic>` gigante en toda la app;
- 39 jerarquías de clases antes de saber cuáles comparten UI/comportamiento.

## Siguiente implementación

Primero se deben implementar los renderers con mayor cobertura: `choice`, `matching`, `text_input` y `ordering`. Esas cuatro familias cubren la mayoría de las 171 actividades.
