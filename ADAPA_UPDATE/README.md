# ADAPA — UPDATE ACUMULADO

Este paquete reúne los updates visuales generados para ADAPA hasta este punto.

## Incluye

- 30 imágenes PNG pedagógicas:
  `assets/vocabulary/u04/adapa_visual_01.png` ... `adapa_visual_30.png`
- Manifest del paquete.
- Script PowerShell para copiar los recursos al proyecto.
- No modifica la arquitectura Flutter.

## Instalación rápida

1. Descomprime este ZIP.
2. Copia el contenido de `assets/vocabulary/u04/` a:

   `assets/vocabulary/u04/`

   dentro de tu proyecto ADAPA.

3. Ejecuta:

   `flutter pub get`
   `flutter test`

4. Luego prueba la app.

## Importante

El proyecto ADAPA actual ya declara `assets/vocabulary/u04/` en `pubspec.yaml`, por lo que no hace falta agregar otra entrada para esta carpeta.

Estos 30 recursos están empaquetados como update acumulado. La vinculación definitiva de cada imagen con una actividad concreta debe hacerse contra el catálogo real de contenidos, después de validar que la imagen corresponde exactamente al concepto pedagógico.

Este paquete NO es una copia completa del proyecto Flutter. Es un paquete de actualización acumulativo para aplicar sobre el proyecto existente.
