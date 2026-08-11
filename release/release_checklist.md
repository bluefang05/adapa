# ADAPA v1 — checklist de publicación

## Gate técnico
- [ ] `tools\preflight.ps1` termina limpio.
- [ ] `tools\device_qa.ps1` pasa en al menos un Android real.
- [ ] `tools\release_gate.ps1` genera APK y AAB release firmados.
- [ ] No hay errores de `flutter analyze`.
- [ ] Todos los tests pasan.
- [ ] Verificar targetSdk 36 en el artefacto final.

## Firma
- [ ] Upload keystore guardado fuera del repositorio.
- [ ] Copia segura del upload keystore.
- [ ] `android/key.properties` solo local.
- [ ] Play App Signing habilitado al crear la app en Play Console.
- [ ] Certificado de upload conservado.

## Privacidad y Play
- [ ] Reemplazar `[PENDIENTE_CORREO_DE_SOPORTE]`.
- [ ] Publicar `privacy_policy_es.html` en una URL pública estable.
- [ ] Reemplazar `[PENDIENTE_URL_PUBLICA_DE_PRIVACIDAD]`.
- [ ] Completar Data safety usando la build final.
- [ ] Revisar tratamiento del motor TTS externo.
- [ ] Completar cuestionario de clasificación por edades.
- [ ] Declarar público objetivo según Play Console.

## Store listing
- [x] Nombre ES preparado.
- [x] Descripción breve ES preparada.
- [x] Descripción completa ES preparada.
- [x] Icono Play 512×512 generado.
- [x] Feature graphic 1024×500 generado.
- [ ] Capturas reales de teléfono tomadas desde la build final.
- [ ] Revisar ortografía final y screenshots.

## Antes de subir
- [ ] Cambiar versión de desarrollo a `1.0.0+1`.
- [ ] Ejecutar release gate.
- [ ] Guardar SHA-256 del AAB.
- [ ] Subir primero a una pista de prueba.
- [ ] Instalar desde Play y repetir smoke test.
