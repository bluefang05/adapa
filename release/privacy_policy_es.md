# Política de privacidad — ADAPA

**Fecha de vigencia:** 11 de agosto de 2026

ADAPA es una aplicación educativa offline-first. Esta versión está diseñada para estudiar
el curso **Coreano desde cero A0–A1** sin crear una cuenta.

## Datos que guarda ADAPA

ADAPA guarda localmente en el dispositivo, mediante el almacenamiento de preferencias del sistema, información necesaria para continuar el estudio:

- progreso del curso;
- actividades completadas;
- número de intentos y mejor resultado;
- respuestas o borradores que el usuario decide conservar;
- punto de continuación;
- ajustes de velocidad de texto a voz.

ADAPA no dispone en esta versión de cuentas de usuario, backend, analítica, publicidad ni
sincronización propia en la nube.

## Texto a voz (TTS)

Cuando el usuario toca una función de lectura en voz alta, ADAPA entrega el texto al motor
de texto a voz instalado o seleccionado en el dispositivo Android. El motor TTS es un
componente separado de ADAPA. Su funcionamiento, incluida cualquier capacidad online,
depende del motor elegido, de su proveedor y de la configuración del dispositivo.

ADAPA no opera un servidor TTS propio.

## Conexión a Internet

El manifiesto Android de producción de ADAPA no solicita el permiso `INTERNET`.
El contenido educativo, las imágenes y los trazos se distribuyen dentro de la aplicación.

## Copias de seguridad

ADAPA desactiva el backup en nube de sus datos de aplicación mediante
`android:allowBackup="false"`. Android y algunos fabricantes pueden manejar de forma
distinta determinadas migraciones directas entre dispositivos.

## Eliminación de datos

El usuario puede borrar el progreso guardado desde **Ajustes → Privacidad y datos →
Reiniciar mi progreso**. Desinstalar la aplicación también elimina los datos locales
administrados por la aplicación, sujeto al comportamiento del sistema operativo.

## Menores

ADAPA es una herramienta educativa general. La aplicación no solicita nombre, correo
electrónico, fecha de nacimiento ni otros datos de identificación para utilizar el curso.

## Cambios

Si una versión futura añade funciones de red, cuentas, analítica, publicidad o servicios
externos adicionales, esta política deberá actualizarse antes de publicar esa versión.

## Contacto

El desarrollador deberá completar aquí el correo de soporte público que utilizará en
Google Play antes de publicar:

**Correo:** `[PENDIENTE_CORREO_DE_SOPORTE]`
