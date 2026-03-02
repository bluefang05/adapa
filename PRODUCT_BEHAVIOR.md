# PRODUCT BEHAVIOR

## Roles

- `estudiante`: se inscribe, consume teoria, responde actividades, revisa progreso y calificaciones.
- `profesor`: crea cursos, lecciones, teoria y actividades; revisa respuestas y califica.
- `admin`: gestiona usuarios y cursos dentro de su instancia.

## Progreso

- El progreso del estudiante se calcula desde teoria leida y actividades respondidas.
- La leccion consolida ese estado y persiste el resumen en `progreso_lecciones`.
- El curso muestra un resumen agregado de lecciones, teoria y actividades completadas.
- Dashboard, progreso y navegacion del estudiante deben leer la misma logica base.

## Actividades

- Existen actividades interactivas y abiertas.
- La configuracion de cada tipo se gestiona desde el panel del profesor.
- El estudiante responde desde la vista de actividad y recibe feedback segun el tipo.
- Algunas actividades permiten repetir intento desde la misma pantalla.

## Calificaciones

- Las respuestas quedan registradas en `respuestas`.
- Profesor revisa y califica desde el panel de calificaciones.
- Algunas actividades pueden quedar pendientes de criterio docente.

## Seguridad aplicada

- Mutaciones sensibles usan `POST`.
- CSRF esta activo en formularios y flujos AJAX relevantes.
- Los controladores principales ya usan una base comun para `redirect`, `flash`, `requirePost` y `requireRole`.
- Ya existe control por propiedad de recurso en varias areas de profesor y admin, pero aun requiere QA manual final.

## Estado actual

- La UI principal de estudiante, profesor y admin ya esta bastante alineada.
- La base de datos real del entorno XAMPP es la referencia canonica actual.
- El producto necesita principalmente QA funcional/manual, definicion final de reglas de reintento y cierre fino de comportamiento.
