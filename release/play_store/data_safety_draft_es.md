# ADAPA — borrador para Data safety de Google Play

Este documento es una **hoja de trabajo**, no sustituye el formulario de Play Console.

## Arquitectura de v1 revisada

- Sin cuentas.
- Sin backend propio.
- Sin SDK de analítica.
- Sin SDK publicitario.
- Sin permiso INTERNET en el manifiesto de producción.
- Preferencias locales asíncronas; en Android el plugin usa DataStore Preferences por defecto para la API moderna.
- Contenido/assets empaquetados dentro de la aplicación.
- Backup en nube de la aplicación desactivado.
- TTS: ADAPA entrega texto al motor TTS seleccionado/instalado en Android.

## Declaración preliminar

Para la funcionalidad controlada directamente por ADAPA, no se ha implementado recopilación
ni compartición de datos personales con servidores del desarrollador.

### Punto que debe verificarse al completar Play Console

El motor TTS es una aplicación/servicio separado que puede ser del sistema o de un tercero y
puede tener sus propias capacidades online. Antes de marcar definitivamente “no se recopilan
datos”, revisar cómo Google Play pide tratar la interacción con un motor TTS externo elegido
por el usuario y revisar la política/documentación del motor que se usará durante las pruebas.

## Datos locales
- Progreso educativo.
- Intentos y puntuaciones.
- Borradores/respuestas guardadas.
- Ajustes de voz.

Finalidad: funcionalidad de la aplicación.
Ubicación prevista por ADAPA: dispositivo local.

## No presentes en v1
- ubicación;
- contactos;
- cámara;
- micrófono;
- fotos/archivos del usuario;
- identificadores publicitarios;
- pagos;
- cuentas;
- analítica;
- publicidad.

## Antes de enviar
- [ ] Confirmar el manifiesto final fusionado del AAB/APK.
- [ ] Confirmar dependencias finales (`flutter pub deps`).
- [ ] Confirmar comportamiento del motor TTS usado en QA.
- [ ] Sustituir los marcadores de correo/URL en la política.
- [ ] Completar Data safety en Play Console con lo observado en la build final.
