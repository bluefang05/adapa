# Android + TTS para ADAPA

ADAPA v0.2 usa `flutter_tts` para las actividades auditivas.

Al crear/completar la carpeta Android del proyecto:

- mantener `minSdk` en **21 o superior**;
- para apps que apunten a Android 11 o posterior, añadir al `AndroidManifest.xml` la consulta del servicio TTS:

```xml
<queries>
    <intent>
        <action android:name="android.intent.action.TTS_SERVICE" />
    </intent>
</queries>
```

ADAPA solicita `ko-KR`, pero acepta variantes coreanas que algunos motores reportan como `ko_KR`, `ko` o etiquetas extendidas de coreano. Si el dispositivo no ofrece ninguna voz coreana compatible, el widget muestra un aviso en lugar de reproducir deliberadamente la frase con una voz de otro idioma.
