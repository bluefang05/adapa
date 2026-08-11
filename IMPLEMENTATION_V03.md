# ADAPA Flutter — implementación v0.3

## Objetivo del bloque

Activar las familias `hangul_structure` y `visual_reference` sobre la v0.2 sin modificar las cuatro familias ya interactivas.

## Implementado

### Hangul structure

Tipos cubiertos:

- `syllable_builder`
- `batchim_finder`
- `batchim_finder_multi`
- `highlight_token`
- `hangul_recall_grid`

Se añadió `HangulComposer`, un compositor Unicode determinista que convierte jamo compatibles en sílabas modernas. Ejemplos cubiertos por pruebas:

- `ㄱ + ㅏ → 가`
- `ㅁ + ㅜ → 무`
- `ㅅ + ㅏ + ㄴ → 산`
- `ㅁ + ㅜ + ㄹ → 물`
- `ㄲ + ㅏ → 까`

No se usan imágenes ni tablas hard-coded para cada sílaba posible.

### Visual reference

`stroke_viewer` ahora:

- resuelve los conjuntos mediante `AssetResolver`;
- permite elegir el jamo;
- recorre cada paso secuencialmente;
- muestra la forma final;
- registra en memoria qué caracteres fueron revisados durante la sesión;
- usa los PNG reales ya incluidos en assets.

### Corrección detectada

`u01l01_a05` referenciaba solo seis vocales en vez de las diez enseñadas. Se añadieron:

`ㅑ ㅕ ㅛ ㅠ`

El visor queda ahora conectado a las 29 familias de jamo de la Unidad 1.

## Cobertura

- total de actividades: 171
- interactivas: 140
- cobertura: 81.9 %
- familias interactivas: 6/9

Pendientes:

- `dialogue`: 16
- `reading_speaking`: 7
- `review`: 8

## Verificación en este entorno

Se validaron estructuralmente:

- los 11 payloads `hangul_structure`;
- composición Unicode de las actividades `syllable_builder`;
- 3 actividades `stroke_viewer`;
- 29/29 conjuntos de trazos;
- 111/111 PNG de trazos referenciados;
- imports Dart locales;
- balance básico de delimitadores en archivos Dart.

No se ejecutaron `flutter analyze` ni `flutter test` porque el SDK Flutter/Dart no está instalado en este entorno.
