# Prompt para Generar Curso de Francés (Basado en Estructura de Alemán)

## Objetivo
Generar un archivo JSON completo para un curso de "Francés de Cero a Héroe" (Niveles A1-C1) que replique EXACTAMENTE la estructura pedagógica y técnica del curso de Alemán existente, pero adaptado cultural y lingüísticamente al Francés.

## Estructura del JSON
El output debe ser un objeto JSON con la siguiente estructura raíz:
```json
{
    "course": {
        "title": "Francés de Cero a Héroe: Ruta completa A1-C1",
        "description": "Ruta extensa de francés para hispanohablantes desde supervivencia A1 hasta precisión C1...",
        "level": "A1-C1",
        "lessons": [
            // Array de lecciones
        ]
    }
}
```

## Requisitos de las Lecciones
Cada lección debe tener:
- `title`: Título en español (ej. "Nivel A1.1: Sonidos, saludos y primer contacto").
- `description`: Breve descripción del objetivo.
- `duration`: Tiempo estimado en minutos.
- `content`: Array de objetos que alternan entre `theory` y `activity`.

### Tipos de Contenido (Content Types)

#### 1. Teoría (`theory`)
- `type`: "theory"
- `title`: Título del concepto (ej. "La liaison y vocales nasales").
- `content_preview`: Texto explicativo breve (HTML básico permitido si es necesario, pero preferible texto plano).
- `order`: Número secuencial.

#### 2. Actividades (`activity`)
Deben usar los siguientes `activity_type` soportados, con su respectivo `content_json` (que debe ser un string JSON escapado):

**a) Opción Múltiple (`opcion_multiple`)**
- `content_json` estructura:
  ```json
  {
    "pregunta_global": "Selecciona la respuesta correcta.",
    "preguntas": [
      {
        "texto": "¿Cómo te llamas?",
        "opciones": [
          {"texto": "Je m'appelle Pierre.", "es_correcta": true},
          {"texto": "Ich heiße Pierre.", "es_correcta": false}
        ]
      }
    ]
  }
  ```

**b) Ordenar Palabras (`ordenar_palabras`)**
- `content_json` estructura: Array de objetos con `instruction` e `items` (array de palabras desordenadas).

**c) Completar Oración (`completar_oracion`)**
- `content_json` estructura: Array de objetos con `oracion` (con hueco ____) y `respuesta_correcta`.

**d) Escucha (`escucha`)**
- `content_json` estructura: Objeto con `texto_tts` (texto para generar audio), `transcripcion` (texto correcto), `palabras_clave`, `tts_rate` (velocidad 0.8-1.0).

**e) Emparejamiento (`emparejamiento`)**
- `content_json` estructura: Objeto con array `pares` (`left` y `right`).

**f) Pronunciación (`pronunciacion`)**
- `content_json` estructura: Array de objetos con `frase` (a leer), `texto_tts` (referencia auditiva), `focos` (puntos clave de pronunciación ej. "R francesa"), `pista`.

**g) Verdadero/Falso (`verdadero_falso`)**
- `content_json` estructura: Objeto con `pregunta` y `respuesta_correcta` ("Verdadero" o "Falso").

## Adaptación Cultural
- Reemplazar nombres alemanes (Hans, Müller) por franceses (Pierre, Marie, Dubois).
- Reemplazar ciudades (Berlin, Munich) por francesas (Paris, Lyon, Marseille).
- Reemplazar comidas (Bratwurst, Bier) por francesas (Croissant, Fromage, Vin).
- Adaptar explicaciones gramaticales:
  - En lugar de "Casos (Nominativo, Acusativo)", enfocar en "Género y Número", "Conjugación (-er, -ir, -re)", "Liaison".
  - En lugar de "Verbo al final", enfocar en "Negación (ne...pas)", "Passé Composé".

## Ejemplo de Salida Esperada (Fragmento A1.1)
```json
{
    "course": {
        "title": "Francés de Cero a Héroe: Ruta completa A1-C1",
        "level": "A1-C1",
        "lessons": [
            {
                "title": "Nivel A1.1: Sonidos, saludos y primer contacto",
                "description": "Domina la 'R' francesa, los saludos básicos y preséntate sin miedo.",
                "duration": 160,
                "content": [
                    {
                        "type": "theory",
                        "title": "La pronunciación: Nasales y la R",
                        "content_preview": "El francés tiene sonidos únicos. La R gutural y las vocales nasales (an, on, in) son claves...",
                        "order": 1
                    },
                    {
                        "type": "activity",
                        "activity_type": "opcion_multiple",
                        "title": "Saludos básicos",
                        "instructions": "Elige la opción correcta.",
                        "content_json": "{\"pregunta_global\":\"Selecciona...\",\"preguntas\":[{\"texto\":\"Saludo formal por la mañana:\",\"opciones\":[{\"texto\":\"Bonjour\",\"es_correcta\":true},{\"texto\":\"Salut\",\"es_correcta\":false}]}]}",
                        "order": 1
                    }
                ]
            }
        ]
    }
}
```

## Instrucción Final
Genera el JSON completo cubriendo al menos las primeras 5 lecciones del nivel A1 para comenzar, manteniendo la densidad y variedad de actividades del curso original de Alemán.
