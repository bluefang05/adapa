# Plantilla JSON Para ADAPA

Usa esta plantilla como contrato para generar cursos listos para sembrar.

```json
{
  "course": {
    "titulo": "Aleman funcional A1-A2 para hispanohablantes",
    "descripcion": "Ruta practica para empezar aleman con foco en supervivencia diaria, conversacion basica, estructura util y situaciones memorables.",
    "idioma": "aleman",
    "idioma_objetivo": "aleman",
    "idioma_base": "espanol",
    "idioma_ensenanza": "espanol",
    "nivel_cefr": "A1",
    "nivel_cefr_desde": "A1",
    "nivel_cefr_hasta": "A2",
    "modalidad": "perpetuo",
    "duracion_semanas": 8,
    "es_publico": 1,
    "requiere_codigo": 0,
    "codigo_acceso": null,
    "tipo_codigo": null,
    "inscripcion_abierta": 1,
    "max_estudiantes": 1000,
    "estado": "activo",
    "notificar_profesor_completada": 1,
    "notificar_profesor_atascado": 1
  },
  "lessons": [
    {
      "titulo": "Saludos, presentaciones y primeras respuestas",
      "descripcion": "Arranque controlado para saludar, presentarte y responder preguntas basicas sin congelarte.",
      "duracion": 95,
      "objetivo": "Poder presentarte y responder preguntas personales basicas.",
      "resultados": [
        "saludar con naturalidad",
        "decir tu nombre y origen",
        "usar sein y haben en frases basicas"
      ],
      "teoria": [
        {
          "titulo": "Panorama de la leccion",
          "duracion": 12,
          "bloques": [
            {
              "tipo_bloque": "explicacion",
              "titulo": "Que vas a resolver hoy",
              "contenido": "Esta leccion te da las piezas minimas para presentarte, decir de donde vienes y responder preguntas sociales simples.",
              "idioma_bloque": "espanol",
              "tts_habilitado": 1
            },
            {
              "tipo_bloque": "vocabulario",
              "titulo": "Vocabulario base",
              "contenido": "- hallo\n- guten tag\n- ich heisse\n- ich komme aus\n- ich bin",
              "idioma_bloque": "aleman",
              "tts_habilitado": 1
            },
            {
              "tipo_bloque": "ejemplo",
              "titulo": "Mini dialogo",
              "contenido": "Hallo, ich heisse Laura und ich komme aus Chile.",
              "idioma_bloque": "aleman",
              "tts_habilitado": 1
            }
          ]
        }
      ],
      "actividades": [
        {
          "titulo": "Selecciona la respuesta natural",
          "descripcion": "Practica saludos y presentaciones en escenas cortas.",
          "tipo": "opcion_multiple",
          "instrucciones": "Marca la mejor opcion en cada caso.",
          "puntos": 15,
          "tiempo": 6,
          "contenido": {
            "pregunta_global": "Elige la respuesta correcta.",
            "preguntas": [
              {
                "texto": "Forma correcta para presentarte:",
                "opciones": [
                  { "texto": "Ich heisse Marta.", "es_correcta": true },
                  { "texto": "Ich heisst Marta.", "es_correcta": false },
                  { "texto": "Ich name Marta.", "es_correcta": false }
                ]
              }
            ]
          }
        },
        {
          "titulo": "Ordena la frase",
          "descripcion": "Reconstruye una presentacion simple.",
          "tipo": "ordenar_palabras",
          "instrucciones": "Ordena las palabras hasta formar una frase natural.",
          "puntos": 10,
          "tiempo": 4,
          "contenido": [
            {
              "id": "intro_1",
              "instruction": "Ordena la frase.",
              "items": ["Ich", "heisse", "Marta."]
            }
          ]
        },
        {
          "titulo": "Completa con sein",
          "descripcion": "Escribe la forma correcta del verbo.",
          "tipo": "completar_oracion",
          "instrucciones": "Escribe solo la palabra que falta.",
          "puntos": 10,
          "tiempo": 4,
          "contenido": [
            {
              "id": "gap_1",
              "oracion": "Ich ____ Studentin.",
              "respuesta_correcta": "bin"
            }
          ]
        }
      ]
    }
  ]
}
```

## Regla practica

- `course` define metadata del curso.
- `lessons[].teoria[].bloques[]` es lo mas importante para que el contenido se vea bien en ADAPA.
- evita meter HTML crudo dentro del JSON si no es necesario.
- usa bloques cortos, no parrafos gigantes.
