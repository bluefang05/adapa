# ADAPA Flutter v0.8 — UI/UX de estudio

Esta iteración no cambia el contenido pedagógico ni la lógica de progreso.

## Implementado

- nuevo tema Material 3 coherente;
- inicio con hero de curso, progreso y botón Continuar;
- ruta visual de 8 unidades con estados disponible/en progreso/completada/bloqueada;
- unidades y lecciones con jerarquía visual más clara;
- cabecera de progreso en unidad/lección;
- actividad con indicador `X de Y` y navegación Anterior/Siguiente;
- feedback accesible con región semántica;
- pantalla de ajustes TTS;
- velocidades normal/lenta persistentes;
- diagnóstico de disponibilidad `ko-KR`;
- TTS de actividades y diálogos usa las velocidades configuradas;
- renderizado real de los bloques pedagógicos de teoría;
- diálogos y lecturas del catálogo se muestran dentro de la lección;
- vocabulario visual reutiliza sus imágenes offline;
- política de romanización aplicada a los bloques teóricos.

## Romanización

- U1: visible por defecto;
- U2–U4: ayuda bajo demanda;
- U5–U6: oculta, disponible como ayuda cuando corresponde;
- U7–U8: desactivada.

## Límite consciente

No se añadió navegación compleja, cuenta, perfil, backend ni animaciones ornamentales.
La prioridad sigue siendo que estudiar sea claro, rápido y robusto en móvil.

## Próximo bloque

Validación y QA antes de publicación:

1. ejecutar Flutter real;
2. resolver errores de compilación/analyze/test;
3. smoke tests Android;
4. revisar accesibilidad/overflow en pantallas pequeñas;
5. pulir icono/branding y signing solo cuando la app compile limpiamente.
