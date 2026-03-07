# Prompt Maestro Para Cursos Premium

Usa este prompt como base cuando quieras generar un curso nuevo para ADAPA.

## Prompt

```txt
Quiero que generes un curso premium de idiomas para una plataforma educativa tipo LMS.

Contexto del producto:
- La plataforma esta orientada a hispanohablantes.
- El curso debe sentirse serio, util, moderno y memorable.
- No debe sonar como contenido inflado o improvisado por IA.
- Debe ser reusable para convertirlo en curso, lecciones, bloques de teoria y actividades.

Parametros del curso:
- Idioma objetivo: [frances / ingles / aleman / italiano / portugues / japones / etc]
- Idioma base del alumno: espanol
- Nivel CEFR de entrada: [A1 / A2 / B1 / B2]
- Nivel CEFR de salida: [A1 / A2 / B1 / B2 / C1]
- Duracion en semanas: [6 / 8 / 10 / 12 / 16]
- Numero de lecciones: [6 / 8 / 10 / 12]
- Perfil del alumno: [adulto principiante / profesional / viajero / autodidacta / universitario]
- Objetivo principal: [base completa / conversacion funcional / viajes / trabajo / pronunciacion / produccion escrita]

Tono y calidad:
- premium
- claro
- pedagogicamente serio
- estructurado
- memorable
- nada infantil
- nada generico
- nada repetitivo

Reglas obligatorias:
- cada leccion debe tener una progresion clara respecto a la anterior
- cada leccion debe resolver una necesidad comunicativa concreta
- usa vocabulario de alta frecuencia y utilidad real
- la teoria debe ser corta, precisa y accionable
- evita texto de relleno
- evita listas gigantes sin explicacion
- evita frases tipo "aprenderas mucho"
- incluye 1 escenario memorable o divertido por leccion, pero sin volverlo parodia
- las actividades deben medir exactamente lo que la teoria ensena
- el curso debe parecer creado por un equipo pedagogico experto

Para cada leccion entrega:
1. titulo premium
2. descripcion
3. objetivo de aprendizaje
4. resultados esperados
5. vocabulario clave
6. gramatica funcional
7. teoria dividida en bloques cortos
8. ejemplos utiles y naturales
9. error frecuente
10. chequeo rapido
11. escenario memorable
12. actividades tipo app:
   - opcion_multiple
   - ordenar_palabras
   - respuesta_corta o completar_oracion
   - escucha o pronunciacion cuando aplique
13. cierre o tarea de consolidacion

Formato de salida:
- Devuelve SOLO JSON limpio.
- No agregues explicaciones fuera del JSON.
- Usa esta estructura exacta:

{
  "course": {
    "titulo": "",
    "descripcion": "",
    "idioma": "",
    "idioma_objetivo": "",
    "idioma_base": "",
    "idioma_ensenanza": "espanol",
    "nivel_cefr": "",
    "nivel_cefr_desde": "",
    "nivel_cefr_hasta": "",
    "modalidad": "perpetuo",
    "duracion_semanas": 0,
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
      "titulo": "",
      "descripcion": "",
      "duracion": 90,
      "objetivo": "",
      "resultados": [],
      "teoria": [
        {
          "titulo": "",
          "duracion": 15,
          "bloques": [
            {
              "tipo_bloque": "explicacion|ejemplo|traduccion|vocabulario|dialogo|instruccion|recurso",
              "titulo": "",
              "contenido": "",
              "idioma_bloque": "espanol",
              "tts_habilitado": 1
            }
          ]
        }
      ],
      "actividades": [
        {
          "titulo": "",
          "descripcion": "",
          "tipo": "opcion_multiple|ordenar_palabras|respuesta_corta|completar_oracion|escucha|pronunciacion",
          "instrucciones": "",
          "puntos": 10,
          "tiempo": 5,
          "contenido": {}
        }
      ]
    }
  ]
}
```

## Regla editorial extra

Si el modelo empieza a producir contenido demasiado generico, agrega este bloque:

```txt
Cada leccion debe sentirse distinta.
No repitas la misma estructura verbal en todas las lecciones.
No conviertas todo en listas.
Los ejemplos deben sonar naturales para hispanohablantes.
La teoria debe ser breve pero precisa.
Las actividades deben parecer parte de un producto premium, no ejercicios escolares improvisados.
```
