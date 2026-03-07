<?php

function french_theory_html(string $intro, array $sections, string $tip, string $scenarioTitle, array $scenarioLines): string {
    $html = '<div class="theory-rich"><p>' . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</p>';

    foreach ($sections as $section) {
        $html .= '<h3>' . htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') . '</h3>';

        if (!empty($section['text'])) {
            $html .= '<p>' . htmlspecialchars($section['text'], ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if (!empty($section['bullets'])) {
            $html .= '<ul>';
            foreach ($section['bullets'] as $bullet) {
                $html .= '<li>' . htmlspecialchars($bullet, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($section['example'])) {
            $html .= '<p><strong>Ejemplo:</strong> ' . htmlspecialchars($section['example'], ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }

    $html .= '<div class="alert alert-warning border mt-3"><strong>Escenario absurdo:</strong> ' . htmlspecialchars($scenarioTitle, ENT_QUOTES, 'UTF-8') . '</div>';
    $html .= '<ul>';
    foreach ($scenarioLines as $line) {
        $html .= '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $html .= '</ul>';
    $html .= '<div class="alert alert-light border mt-3"><strong>Coach tip:</strong> ' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') . '</div></div>';

    return $html;
}

function french_theory_blocks(string $intro, array $sections, string $tip, string $scenarioTitle, array $scenarioLines): array {
    $blocks = [[
        'tipo_bloque' => 'explicacion',
        'titulo' => 'Panorama',
        'contenido' => $intro,
        'idioma_bloque' => 'espanol',
        'tts_habilitado' => 1,
    ]];

    foreach ($sections as $section) {
        if (!empty($section['text'])) {
            $blocks[] = [
                'tipo_bloque' => 'explicacion',
                'titulo' => $section['title'],
                'contenido' => $section['text'],
                'idioma_bloque' => 'espanol',
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['bullets'])) {
            $title = mb_strtolower($section['title'], 'UTF-8');
            $blocks[] = [
                'tipo_bloque' => str_contains($title, 'vocabulario') ? 'vocabulario' : 'explicacion',
                'titulo' => $section['title'],
                'contenido' => implode("\n", array_map(static fn($item) => '- ' . $item, $section['bullets'])),
                'idioma_bloque' => 'espanol',
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['example'])) {
            $blocks[] = [
                'tipo_bloque' => 'ejemplo',
                'titulo' => $section['title'],
                'contenido' => $section['example'],
                'idioma_bloque' => 'frances',
                'tts_habilitado' => 1,
            ];
        }
    }

    $blocks[] = [
        'tipo_bloque' => 'instruccion',
        'titulo' => 'Escenario chistoso: ' . $scenarioTitle,
        'contenido' => implode("\n", array_map(static fn($item) => '- ' . $item, $scenarioLines)),
        'idioma_bloque' => 'espanol',
        'tts_habilitado' => 1,
    ];

    $blocks[] = [
        'tipo_bloque' => 'instruccion',
        'titulo' => 'Coach tip',
        'contenido' => $tip,
        'idioma_bloque' => 'espanol',
        'tts_habilitado' => 1,
    ];

    return $blocks;
}

function french_learning_path_blueprint(): array {
    $blueprint = [
        'course' => [
            'titulo' => 'Frances de Cero a Intermedio: Ruta divertida A0-B1',
            'descripcion' => 'Ruta detallada de frances para hispanohablantes con progreso guiado, actividades tipo app, repaso espaciado y escenarios absurdos para fijar mejor vocabulario, gramatica, escucha, escritura y conversacion.',
            'idioma' => 'frances',
            'idioma_objetivo' => 'frances',
            'idioma_base' => 'espanol',
            'idioma_ensenanza' => 'espanol',
            'nivel_cefr' => 'A1',
            'nivel_cefr_desde' => 'A1',
            'nivel_cefr_hasta' => 'B1',
            'modalidad' => 'perpetuo',
            'duracion_semanas' => 16,
            'es_publico' => 1,
            'requiere_codigo' => 0,
            'codigo_acceso' => null,
            'tipo_codigo' => null,
            'inscripcion_abierta' => 1,
            'max_estudiantes' => 1000,
            'estado' => 'activo',
            'notificar_profesor_completada' => 1,
            'notificar_profesor_atascado' => 1,
        ],
        'lessons' => [],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 0-1: saludos, alfabeto y presentaciones con dignidad',
        'descripcion' => 'Primer contacto con sonidos del frances, saludos, pronombres, etre, avoir y micro presentaciones utiles.',
        'duracion' => 110,
        'teoria' => [
            [
                'titulo' => 'Sobrevivir al primer contacto con el frances',
                'duracion' => 18,
                'intro' => 'El frances suena distinto a como se escribe. Antes de hablar mucho, conviene aceptar que algunas letras existen por motivos emocionales y no practicos.',
                'sections' => [
                    ['title' => 'Ideas clave', 'bullets' => ['El frances no se pronuncia como el espanol.', 'Muchas letras finales casi no suenan.', 'Escuchar primero reduce el panico al leer.']],
                    ['title' => 'Vocabulario base', 'bullets' => ['bonjour', 'salut', 'bonsoir', 'au revoir', 'merci', 'oui', 'non', 'pardon']],
                    ['title' => 'Modelo minimo', 'example' => 'Bonjour, merci, au revoir.'],
                ],
                'tip' => 'Haz que el alumno reconozca primero el sonido, luego la ortografia.',
                'scenario_title' => 'El gato diplomatico de la embajada',
                'scenario_lines' => ['Debes saludar al gato en frances para que deje de juzgarte.', 'Le dices: Bonjour, monsieur le chat.', 'El gato sigue ignorandote, pero ahora con elegancia parisina.'],
            ],
            [
                'titulo' => 'Presentarte con etre y avoir sin sonar a robot',
                'duracion' => 20,
                'intro' => 'Con etre y avoir puedes decir quien eres, de donde vienes y manejar preguntas sociales basicas.',
                'sections' => [
                    ['title' => 'Formas utiles', 'bullets' => ['je suis', 'tu es', 'il est', 'elle est', 'j ai', 'tu as', 'nous avons']],
                    ['title' => 'Frases funcionales', 'bullets' => ['Je m appelle Laura.', 'Je suis etudiante.', 'Je suis guatemalteque.', 'J ai un chien.']],
                    ['title' => 'Dialogo base', 'example' => 'Bonjour, je m appelle Marie et je suis etudiante.'],
                ],
                'tip' => 'La prioridad en esta etapa es responder cinco preguntas basicas con seguridad.',
                'scenario_title' => 'La fiesta llena de espias elegantes',
                'scenario_lines' => ['Todos te preguntan quien eres con sospechosa calma.', 'Respondes: Je m appelle Ronald. Je ne suis pas un espion.', 'Eso solo empeora la sospecha, pero tu gramatica mejora.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Saludos de supervivencia',
                'descripcion' => 'Selecciona la traduccion o respuesta correcta en interacciones basicas.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la opcion mas natural.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor respuesta.',
                    'preguntas' => [
                        ['texto' => 'Gracias en frances es:', 'opciones' => [['texto' => 'merci', 'es_correcta' => true], ['texto' => 'bonjour', 'es_correcta' => false], ['texto' => 'fromage', 'es_correcta' => false]]],
                        ['texto' => 'Forma correcta para presentarte:', 'opciones' => [['texto' => 'Je m appelle Marie.', 'es_correcta' => true], ['texto' => 'Je suis appelle Marie.', 'es_correcta' => false], ['texto' => 'Moi appelle Marie.', 'es_correcta' => false]]],
                        ['texto' => 'Respuesta natural a "Tu es etudiant ?"', 'opciones' => [['texto' => 'Oui, je suis etudiant.', 'es_correcta' => true], ['texto' => 'Oui, je avoir etudiant.', 'es_correcta' => false], ['texto' => 'Oui, merci fromage.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Ordena tu presentacion',
                'descripcion' => 'Reconstruye frases muy cortas de presentacion.',
                'tipo' => 'ordenar_palabras',
                'instrucciones' => 'Ordena las palabras correctamente.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    ['id' => 'fr_intro_1', 'instruction' => 'Ordena la frase.', 'items' => ['Je', 'm appelle', 'Lucia.']],
                    ['id' => 'fr_intro_2', 'instruction' => 'Ordena la frase.', 'items' => ['Je', 'suis', 'etudiant.']],
                ],
            ],
            [
                'titulo' => 'Completa con etre o avoir',
                'descripcion' => 'Escribe la palabra que falta.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Escribe solo la palabra correcta.',
                'puntos' => 12,
                'tiempo' => 5,
                'contenido' => [
                    ['id' => 'fr_gap_1', 'oracion' => 'Je ____ etudiant.', 'respuesta_correcta' => 'suis'],
                    ['id' => 'fr_gap_2', 'oracion' => 'Nous ____ un chat diplomatique.', 'respuesta_correcta' => 'avons'],
                    ['id' => 'fr_gap_3', 'oracion' => 'Elle ____ francaise.', 'respuesta_correcta' => 'est'],
                ],
            ],
            [
                'titulo' => 'Escucha y repite la mini presentacion',
                'descripcion' => 'Escucha y escribe la frase exacta.',
                'tipo' => 'escucha',
                'instrucciones' => 'Escucha con atencion y transcribe.',
                'puntos' => 18,
                'tiempo' => 6,
                'contenido' => ['texto_tts' => 'Bonjour, je m appelle Daniel et je suis etudiant.', 'transcripcion' => 'Bonjour, je m appelle Daniel et je suis etudiant.'],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 2: casa, familia y caos domestico controlado',
        'descripcion' => 'Describe tu casa, tu familia, objetos cotidianos y adjetivos basicos con concordancia simple.',
        'duracion' => 115,
        'teoria' => [
            [
                'titulo' => 'La familia y la casa sin pelear con el genero',
                'duracion' => 18,
                'intro' => 'El nivel inicial necesita vocabulario concreto: personas, habitaciones, muebles y relaciones cercanas.',
                'sections' => [
                    ['title' => 'Vocabulario util', 'bullets' => ['la maison', 'la cuisine', 'la chambre', 'la famille', 'le pere', 'la mere', 'le frere', 'la soeur']],
                    ['title' => 'Posesivos utiles', 'bullets' => ['mon chien', 'ma soeur', 'mes amis', 'ma maison']],
                    ['title' => 'Modelo descriptivo', 'example' => 'Ma maison est petite, mais tres calme.'],
                ],
                'tip' => 'Memoriza bloques completos como ma soeur o mon chien, no palabras aisladas.',
                'scenario_title' => 'El pez dictador que gobierna la casa',
                'scenario_lines' => ['Tu pez dorado decide quien puede usar la cocina.', 'Debes describir la casa mientras el pez te juzga.', 'Descubres que el pez es pequeno, pero muy autoritario.'],
            ],
            [
                'titulo' => 'Adjetivos y concordancia basica',
                'duracion' => 16,
                'intro' => 'Los adjetivos permiten que tu frances deje de sonar como una lista de objetos desconectados.',
                'sections' => [
                    ['title' => 'Adjetivos frecuentes', 'bullets' => ['grand / grande', 'petit / petite', 'rouge', 'bleu', 'bizarre', 'drole']],
                    ['title' => 'Frases utiles', 'bullets' => ['Mon chien est petit.', 'Ma chambre est grande.', 'La table est rouge.']],
                    ['title' => 'Modelo visual', 'example' => 'Ma chambre est petite et bleue.'],
                ],
                'tip' => 'Describe tres cosas reales de tu cuarto antes de pasar a ejemplos imaginarios.',
                'scenario_title' => 'El refrigerador que te reclama el queso',
                'scenario_lines' => ['Abres la refri y una voz te dice: Tu manges encore mon fromage ?', 'Debes responder describiendo la cocina con total inocencia.', 'La cocina sigue pequena, pero el drama es enorme.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Familia y objetos cotidianos',
                'descripcion' => 'Selecciona la opcion correcta segun el contexto.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Forma correcta:', 'opciones' => [['texto' => 'Ma soeur est intelligente.', 'es_correcta' => true], ['texto' => 'Mon soeur est intelligente.', 'es_correcta' => false], ['texto' => 'Mes soeur est intelligente.', 'es_correcta' => false]]],
                        ['texto' => 'Mi casa es grande:', 'opciones' => [['texto' => 'Ma maison est grande.', 'es_correcta' => true], ['texto' => 'Mon maison est grand.', 'es_correcta' => false], ['texto' => 'Ma maison est grand.', 'es_correcta' => false]]],
                        ['texto' => 'Animal de la casa:', 'opciones' => [['texto' => 'le chat', 'es_correcta' => true], ['texto' => 'la fromage', 'es_correcta' => false], ['texto' => 'les table', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Completa con posesivos',
                'descripcion' => 'Escribe la forma correcta.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Escribe solo una palabra.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    ['id' => 'fr_home_1', 'oracion' => 'C est ____ chat.', 'respuesta_correcta' => 'mon'],
                    ['id' => 'fr_home_2', 'oracion' => 'Voici ____ chambre.', 'respuesta_correcta' => 'ma'],
                    ['id' => 'fr_home_3', 'oracion' => 'Ce sont ____ amis.', 'respuesta_correcta' => 'mes'],
                ],
            ],
            [
                'titulo' => 'Describe tu cuarto surrealista',
                'descripcion' => 'Escribe una descripcion breve de tu habitacion con un animal raro imaginario.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 70 a 90 palabras.',
                'puntos' => 18,
                'tiempo' => 10,
                'contenido' => ['tema' => 'Describe tu cuarto, tus colores y un animal imaginario que vive alli sin pagar renta.', 'min_palabras' => 70],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 3: comida, compras y panaderias peligrosas',
        'descripcion' => 'Pide comida, pregunta precios, usa vouloir, prendre, manger y los partitivos con naturalidad.',
        'duracion' => 120,
        'teoria' => [
            [
                'titulo' => 'Pedir comida sin ordenar 47 croissants',
                'duracion' => 18,
                'intro' => 'El frances util aparece rapido cuando puedes pedir algo en una cafeteria, una panaderia o un restaurante.',
                'sections' => [
                    ['title' => 'Verbos clave', 'bullets' => ['je veux', 'je prends', 'je mange', 'je voudrais']],
                    ['title' => 'Vocabulario esencial', 'bullets' => ['le pain', 'le fromage', 'le cafe', 'le the', 'la salade', 'la soupe', 'le dessert']],
                    ['title' => 'Modelo de pedido', 'example' => 'Bonjour, je voudrais un cafe et un croissant, s il vous plait.'],
                ],
                'tip' => 'Practica pedido + cantidad + cortesia como una sola rutina oral.',
                'scenario_title' => 'La panaderia del caos matematico',
                'scenario_lines' => ['Querias cuatro croissants y terminaste ordenando cuarenta y siete.', 'Ahora debes decir: Non, pas quarante sept !', 'La panadera te mira con piedad y superioridad cultural.'],
            ],
            [
                'titulo' => 'Partitivos y cantidades utiles',
                'duracion' => 16,
                'intro' => 'Los partitivos ayudan a sonar natural cuando hablas de comida y bebida en cantidades no contables.',
                'sections' => [
                    ['title' => 'Formas utiles', 'bullets' => ['du pain', 'de la soupe', 'de l eau', 'des legumes']],
                    ['title' => 'Preguntas frecuentes', 'bullets' => ['Combien ca coute ?', 'Vous desirez ?', 'C est pour ici ou a emporter ?']],
                    ['title' => 'Modelo funcional', 'example' => 'Je veux de l eau et du pain.'],
                ],
                'tip' => 'Asocia cada partitivo con un alimento concreto para fijarlo mejor.',
                'scenario_title' => 'El menu con queso en 48 formas',
                'scenario_lines' => ['Pides algo ligero y te ofrecen siete variedades de queso dramatico.', 'Intentas sobrevivir con una ensalada.', 'El camarero no aprueba tu falta de compromiso lacteo.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Pedir con cortesia',
                'descripcion' => 'Elige la frase mas natural en una situacion de compra.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Quiero cafe:', 'opciones' => [['texto' => 'Je veux du cafe.', 'es_correcta' => true], ['texto' => 'Je veux de cafe.', 'es_correcta' => false], ['texto' => 'Je veux des cafe.', 'es_correcta' => false]]],
                        ['texto' => 'Pedido correcto en panaderia:', 'opciones' => [['texto' => 'Je voudrais un croissant.', 'es_correcta' => true], ['texto' => 'Je croissant voudrais.', 'es_correcta' => false], ['texto' => 'Croissant je pain.', 'es_correcta' => false]]],
                        ['texto' => 'Pregunta por precio:', 'opciones' => [['texto' => 'Combien ca coute ?', 'es_correcta' => true], ['texto' => 'Ou est le crocodile ?', 'es_correcta' => false], ['texto' => 'Je suis une baguette.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Completa el pedido',
                'descripcion' => 'Escribe la palabra correcta.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Escribe solo una palabra.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    ['id' => 'fr_food_1', 'oracion' => 'Je veux ____ eau.', 'respuesta_correcta' => 'de l'],
                    ['id' => 'fr_food_2', 'oracion' => 'Je ____ un the.', 'respuesta_correcta' => 'prends'],
                    ['id' => 'fr_food_3', 'oracion' => 'Elle mange ____ pain.', 'respuesta_correcta' => 'du'],
                ],
            ],
            [
                'titulo' => 'Dialogo de cafeteria deprimida',
                'descripcion' => 'Escribe un mini dialogo de compra.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 80 a 100 palabras.',
                'puntos' => 18,
                'tiempo' => 10,
                'contenido' => ['tema' => 'Haz un dialogo donde pides desayuno en una cafeteria y el cajero es un filosofo deprimido.', 'min_palabras' => 80],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 4: rutina diaria y horarios sin drama',
        'descripcion' => 'Habla de tu dia, tus habitos, la hora y tus acciones frecuentes con verbos regulares y pronominales.',
        'duracion' => 120,
        'teoria' => [
            [
                'titulo' => 'Tu rutina diaria en presente',
                'duracion' => 18,
                'intro' => 'Hablar del dia a dia da muchisimo rendimiento: te permite conversar, escribir y entender frases frecuentes con mas confianza.',
                'sections' => [
                    ['title' => 'Verbos utiles', 'bullets' => ['parler', 'etudier', 'travailler', 'habiter', 'manger', 'dormir']],
                    ['title' => 'Momentos del dia', 'bullets' => ['le matin', 'l apres midi', 'le soir', 'aujourd hui', 'demain', 'hier']],
                    ['title' => 'Modelo rutinario', 'example' => 'Le matin, je travaille et le soir, j etudie le francais.'],
                ],
                'tip' => 'Repite tus horarios reales; eso fija mejor el vocabulario temporal.',
                'scenario_title' => 'El despertador frances que te odia',
                'scenario_lines' => ['Suena a las 6:30 como un demonio elegante.', 'Intentas explicar tu rutina mientras bostezas en dos idiomas.', 'Sin cafe te sientes menos persona y mas mueble.'],
            ],
            [
                'titulo' => 'Verbos pronominales y frecuencia',
                'duracion' => 16,
                'intro' => 'Los verbos pronominales aparecen rapido cuando hablas de despertar, acostarte y organizarte.',
                'sections' => [
                    ['title' => 'Bloques utiles', 'bullets' => ['je me reveille', 'je me leve', 'je me couche', 'toujours', 'souvent', 'parfois', 'jamais']],
                    ['title' => 'Frases modelo', 'bullets' => ['Je me reveille a sept heures.', 'Parfois, j etudie le soir.', 'Je ne me couche jamais tot.']],
                    ['title' => 'Modelo de frecuencia', 'example' => 'Je me leve a six heures et parfois je travaille le soir.'],
                ],
                'tip' => 'Usa frecuencia + verbo pronominal + hora como formula repetible.',
                'scenario_title' => 'La agenda controlada por una vaca intelectual',
                'scenario_lines' => ['Tu examen oral lo toma una vaca que pregunta por tu horario.', 'Debes responder con absoluta seriedad academica.', 'La vaca toma notas y mastica juicio.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Rutina con precision',
                'descripcion' => 'Elige la opcion correcta segun contexto diario.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Me despierto a las siete:', 'opciones' => [['texto' => 'Je me reveille a sept heures.', 'es_correcta' => true], ['texto' => 'Je reveille a sept heures.', 'es_correcta' => false], ['texto' => 'Je me reveiller sept.', 'es_correcta' => false]]],
                        ['texto' => 'A veces estudio por la noche:', 'opciones' => [['texto' => 'Parfois, j etudie le soir.', 'es_correcta' => true], ['texto' => 'Jamais, j etudie le matin.', 'es_correcta' => false], ['texto' => 'Parfois, je cuisine le metro.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Completa tu horario',
                'descripcion' => 'Escribe la palabra correcta.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Escribe solo una palabra.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    ['id' => 'fr_routine_1', 'oracion' => 'Je me ____ a six heures.', 'respuesta_correcta' => 'leve'],
                    ['id' => 'fr_routine_2', 'oracion' => 'Nous ____ le francais le soir.', 'respuesta_correcta' => 'etudions'],
                    ['id' => 'fr_routine_3', 'oracion' => 'Elle se ____ tot.', 'respuesta_correcta' => 'couche'],
                ],
            ],
            [
                'titulo' => 'Describe un dia con muebles emocionales',
                'descripcion' => 'Escribe una rutina breve con un detalle absurdo.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 90 a 120 palabras.',
                'puntos' => 18,
                'tiempo' => 10,
                'contenido' => ['tema' => 'Describe tu rutina diaria y menciona en que momento dejas de ser una persona y te conviertes en mueble por falta de cafe.', 'min_palabras' => 90],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 5: ciudad, direcciones y transporte con patos gerentes',
        'descripcion' => 'Pide direcciones, usa aller, venir, pouvoir y muevete por la ciudad con mas autonomia.',
        'duracion' => 125,
        'teoria' => [
            [
                'titulo' => 'Moverte por la ciudad con aller y pouvoir',
                'duracion' => 18,
                'intro' => 'Las situaciones urbanas exigen verbos de movimiento, referencias espaciales y preguntas simples pero utiles.',
                'sections' => [
                    ['title' => 'Verbos clave', 'bullets' => ['je vais', 'tu vas', 'nous allons', 'je peux', 'vous pouvez', 'je viens']],
                    ['title' => 'Referencias espaciales', 'bullets' => ['a gauche', 'a droite', 'tout droit', 'pres de', 'loin de']],
                    ['title' => 'Modelo de direccion', 'example' => 'La gare est a droite et le musee est pres d ici.'],
                ],
                'tip' => 'Practica siempre pregunta + respuesta + punto de referencia.',
                'scenario_title' => 'Perdido en Paris con un pato de bufanda',
                'scenario_lines' => ['Solo un pato con bufanda parece dispuesto a ayudarte.', 'Le preguntas por el metro.', 'El pato hace coin y tu entiendes a gauche.'],
            ],
            [
                'titulo' => 'Futuro proximo y planes inmediatos',
                'duracion' => 16,
                'intro' => 'El futuro proximo es rapido, util y perfecto para hablar de planes inmediatos de viaje o movilidad.',
                'sections' => [
                    ['title' => 'Patrones utiles', 'bullets' => ['je vais prendre le bus', 'nous allons visiter le musee', 'tu vas partir demain']],
                    ['title' => 'Lugares frecuentes', 'bullets' => ['la gare', 'l aeroport', 'le bus', 'le metro', 'le taxi', 'le billet']],
                    ['title' => 'Modelo de plan', 'example' => 'Demain, je vais aller au musee puis je vais prendre le metro.'],
                ],
                'tip' => 'Une transporte + destino + momento para producir planes mas memorables.',
                'scenario_title' => 'El pato gerente del metro',
                'scenario_lines' => ['El encargado del boleto es un pato con corbata.', 'Debes comprar un ticket sin reirte.', 'El pato aprecia la cortesia, no el caos.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Direcciones utiles',
                'descripcion' => 'Selecciona la mejor respuesta segun la situacion.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Donde esta el hotel ?', 'opciones' => [['texto' => 'A droite.', 'es_correcta' => true], ['texto' => 'Je suis une banane.', 'es_correcta' => false], ['texto' => 'Le pigeon conduit le bus.', 'es_correcta' => false]]],
                        ['texto' => 'Voy a tomar el bus:', 'opciones' => [['texto' => 'Je vais prendre le bus.', 'es_correcta' => true], ['texto' => 'Je prendre vais le bus.', 'es_correcta' => false], ['texto' => 'Je vais bus prendre le.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Ordena la indicacion',
                'descripcion' => 'Reconstruye una direccion simple.',
                'tipo' => 'ordenar_palabras',
                'instrucciones' => 'Ordena las palabras.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [['id' => 'fr_city_1', 'instruction' => 'Ordena la frase.', 'items' => ['Le', 'musee', 'est', 'pres', 'd ici.']]],
            ],
            [
                'titulo' => 'Tu trayecto absurdo',
                'descripcion' => 'Describe un mini trayecto por la ciudad.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 80 a 110 palabras.',
                'puntos' => 18,
                'tiempo' => 10,
                'contenido' => ['tema' => 'Explica como llegas a un museo, pero el metro esta gestionado por un pato con corbata.', 'min_palabras' => 80],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 6: pasado basico y tragedias con baguettes',
        'descripcion' => 'Cuenta lo que hiciste ayer usando passe compose con avoir y etre, experiencias simples y marcadores temporales.',
        'duracion' => 130,
        'teoria' => [
            [
                'titulo' => 'Passe compose con avoir y etre',
                'duracion' => 18,
                'intro' => 'El pasado basico permite contar experiencias reales sin necesidad de estructuras literarias complicadas.',
                'sections' => [
                    ['title' => 'Estructuras clave', 'bullets' => ['j ai mange', 'tu as visite', 'elle est arrivee', 'nous sommes partis']],
                    ['title' => 'Marcadores utiles', 'bullets' => ['hier', 'la semaine derniere', 'ce matin', 'samedi']],
                    ['title' => 'Modelo breve', 'example' => 'Hier, je suis alle au marche et j ai achete du pain.'],
                ],
                'tip' => 'Fija primero el auxiliar y luego automatiza el participio.',
                'scenario_title' => 'La paloma delincuente y tu baguette',
                'scenario_lines' => ['Ayer compraste pan con ilusion.', 'Una paloma criminal robo tu baguette.', 'Tuviste que narrar la tragedia con madurez gramatical.'],
            ],
            [
                'titulo' => 'Narrar mini experiencias con detalle',
                'duracion' => 16,
                'intro' => 'Las micro historias ayudan a que el pasado deje de sentirse como lista de conjugaciones.',
                'sections' => [
                    ['title' => 'Verbos frecuentes', 'bullets' => ['voyager', 'visiter', 'acheter', 'perdre', 'trouver', 'voir']],
                    ['title' => 'Frases utiles', 'bullets' => ['J ai visite le parc.', 'Nous avons trouve un cafe.', 'Je suis arrive en retard.']],
                    ['title' => 'Modelo narrativo', 'example' => 'Samedi, nous avons visite un musee et nous avons bu du cafe.'],
                ],
                'tip' => 'Combina lugar + accion + consecuencia para historias faciles de recordar.',
                'scenario_title' => 'El museo donde las pinturas responden',
                'scenario_lines' => ['Preguntaste por una obra y el retrato te corrigio la pronunciacion.', 'Tuviste que contar la experiencia sin llorar demasiado.', 'Al menos el passe compose sobrevivio.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Pasado con precision',
                'descripcion' => 'Selecciona la forma correcta en frases sobre ayer.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Ayer fui al mercado:', 'opciones' => [['texto' => 'Hier, je suis alle au marche.', 'es_correcta' => true], ['texto' => 'Hier, j ai alle au marche.', 'es_correcta' => false], ['texto' => 'Hier, je suis aller au marche.', 'es_correcta' => false]]],
                        ['texto' => 'Compramos pan:', 'opciones' => [['texto' => 'Nous avons achete du pain.', 'es_correcta' => true], ['texto' => 'Nous sommes achete du pain.', 'es_correcta' => false], ['texto' => 'Nous avons acheter du pain.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Completa el relato',
                'descripcion' => 'Escribe la palabra que falta.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Escribe solo una palabra.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    ['id' => 'fr_past_1', 'oracion' => 'Hier, j ____ mange une pizza.', 'respuesta_correcta' => 'ai'],
                    ['id' => 'fr_past_2', 'oracion' => 'Elle est ____ a la maison.', 'respuesta_correcta' => 'arrivee'],
                    ['id' => 'fr_past_3', 'oracion' => 'Nous avons ____ le musee.', 'respuesta_correcta' => 'visite'],
                ],
            ],
            [
                'titulo' => 'Cuenta el robo de la baguette',
                'descripcion' => 'Escribe una mini historia en pasado.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 100 a 130 palabras.',
                'puntos' => 20,
                'tiempo' => 12,
                'contenido' => ['tema' => 'Cuenta como una paloma te robo la baguette y describe que paso antes y despues.', 'min_palabras' => 100],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 7: opiniones, gustos y discusiones elegantes sobre comida',
        'descripcion' => 'Expresa gustos, comparaciones, emociones y opiniones justificadas con conectores simples.',
        'duracion' => 120,
        'teoria' => [
            [
                'titulo' => 'Dar opiniones y preferencias',
                'duracion' => 18,
                'intro' => 'El salto a conversaciones mas reales aparece cuando puedes justificar tus gustos sin limitarte a me gusta o no me gusta.',
                'sections' => [
                    ['title' => 'Verbos utiles', 'bullets' => ['j aime', 'j adore', 'je prefere', 'je deteste']],
                    ['title' => 'Conectores frecuentes', 'bullets' => ['parce que', 'mais', 'donc', 'pourtant']],
                    ['title' => 'Modelo de opinion', 'example' => 'Je prefere le cafe parce qu il est delicieux.'],
                ],
                'tip' => 'Pide siempre una razon despues de cada opinion para forzar profundidad.',
                'scenario_title' => 'El gran debate croissant contra tamal',
                'scenario_lines' => ['Debes defender una postura gastronomica absurda con total seriedad.', 'Alguien sostiene que el croissant es mas elegante.', 'Tu argumentas que el tamal tiene presencia espiritual superior.'],
            ],
            [
                'titulo' => 'Comparativos y emociones',
                'duracion' => 16,
                'intro' => 'Comparar dos ideas o productos ayuda a construir argumentos mas naturales y mas utiles.',
                'sections' => [
                    ['title' => 'Comparativos utiles', 'bullets' => ['plus que', 'moins que', 'aussi que']],
                    ['title' => 'Adjetivos frecuentes', 'bullets' => ['interessant', 'ennuyeux', 'delicieux', 'rapide', 'lent', 'fatigue', 'content']],
                    ['title' => 'Modelo comparativo', 'example' => 'Ce livre est plus interessant que ce film.'],
                ],
                'tip' => 'Usa comparaciones sobre cosas reales del alumno: comida, series, clases o horarios.',
                'scenario_title' => 'La cita romantica con alguien obsesionado con caracoles',
                'scenario_lines' => ['Intentas conversar con calma mientras la otra persona habla del caracol ideal.', 'Debes comparar platos y emociones sin salir corriendo.', 'La gramatica te mantiene vivo.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Opina con elegancia',
                'descripcion' => 'Escoge la opcion correcta para expresar opinion y comparacion.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Prefiero cafe porque es delicioso:', 'opciones' => [['texto' => 'Je prefere le cafe parce qu il est delicieux.', 'es_correcta' => true], ['texto' => 'Je prefere cafe parce delicieux.', 'es_correcta' => false], ['texto' => 'Je suis cafe delicieux.', 'es_correcta' => false]]],
                        ['texto' => 'Comparativo correcto:', 'opciones' => [['texto' => 'Ce livre est plus interessant que ce film.', 'es_correcta' => true], ['texto' => 'Ce livre est plus interessant de ce film.', 'es_correcta' => false], ['texto' => 'Ce livre plus interessant ce film.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Respuesta corta emocional',
                'descripcion' => 'Completa con la emocion correcta.',
                'tipo' => 'respuesta_corta',
                'instrucciones' => 'Escribe una sola palabra.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => ['pregunta' => 'Completa: Je suis tres ____ aujourd hui.', 'respuesta_correcta' => 'fatigue', 'respuestas_correctas' => ['fatigue', 'content'], 'placeholder' => 'Escribe una palabra'],
            ],
            [
                'titulo' => 'Defiende una opinion absurda',
                'descripcion' => 'Redacta una postura breve y justificada.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 110 a 140 palabras.',
                'puntos' => 20,
                'tiempo' => 12,
                'contenido' => ['tema' => 'Defiende si el croissant es superior al tamal o viceversa usando comparativos y razones.', 'min_palabras' => 110],
            ],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel 8: intermedio funcional y proyectos absurdamente utiles',
        'descripcion' => 'Usa imperfecto basico, futuro, condicional y storytelling corto para hablar con mas soltura.',
        'duracion' => 135,
        'teoria' => [
            [
                'titulo' => 'Cuando era pequeno, futuro y deseos realistas',
                'duracion' => 18,
                'intro' => 'La conversacion mejora cuando puedes hablar del pasado habitual, de proyectos y de deseos sin quedarte en frases cortadas.',
                'sections' => [
                    ['title' => 'Formas utiles', 'bullets' => ['j etais', 'j avais', 'je serai', 'je ferai', 'j irai', 'j aimerais', 'je pourrais']],
                    ['title' => 'Frases funcionales', 'bullets' => ['Quand j etais enfant, j avais un chien.', 'Demain, je ferai de l exercice.', 'J aimerais vivre en France pendant un certain temps.']],
                    ['title' => 'Modelo integrado', 'example' => 'Quand j etais enfant, j avais un chien, et plus tard j aimerais vivre a Lyon.'],
                ],
                'tip' => 'Haz que el alumno conecte pasado, presente y futuro en una sola mini historia.',
                'scenario_title' => 'Millonario por una foto de tu gato con boina',
                'scenario_lines' => ['Una foto de tu gato se vuelve viral en Francia.', 'Ahora debes explicar que harias con el dinero.', 'Tu gato quiere oficina propia y claramente ya no te respeta.'],
            ],
            [
                'titulo' => 'Storytelling corto y plan de estudio',
                'duracion' => 16,
                'intro' => 'El objetivo intermedio no es hablar perfecto, sino sostener relatos cortos, planes y opiniones con continuidad.',
                'sections' => [
                    ['title' => 'Recursos utiles', 'bullets' => ['souvenirs', 'projets', 'probleme', 'solution', 'idee', 'chance', 'erreur']],
                    ['title' => 'Tareas de salida', 'bullets' => ['contar una historia rara', 'describir un plan de 90 dias', 'explicar una meta de viaje o estudio']],
                    ['title' => 'Modelo final', 'example' => 'Si j etais riche, j acheterais une petite maison a Lyon et mon chat aurait son propre bureau.'],
                ],
                'tip' => 'Cierra el curso con produccion libre, no solo con ejercicios de seleccion.',
                'scenario_title' => 'Entrevista para cuidar pinguinos bilingues',
                'scenario_lines' => ['Debes explicar tus habilidades con total seriedad profesional.', 'Los pinguinos requieren orden, frances y paciencia.', 'Sorprendentemente, tus planes a futuro ahora incluyen hielo.'],
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Pasado, futuro y condicional',
                'descripcion' => 'Selecciona la mejor opcion segun el contexto.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Marca la mejor opcion.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Elige la mejor opcion.',
                    'preguntas' => [
                        ['texto' => 'Cuando era nino, tenia un perro:', 'opciones' => [['texto' => 'Quand j etais enfant, j avais un chien.', 'es_correcta' => true], ['texto' => 'Quand je suis enfant, j ai un chien.', 'es_correcta' => false], ['texto' => 'Quand j etais enfant, j aurai un chien.', 'es_correcta' => false]]],
                        ['texto' => 'Me gustaria vivir en Francia un tiempo:', 'opciones' => [['texto' => 'J aimerais vivre en France pendant un certain temps.', 'es_correcta' => true], ['texto' => 'Je veux vivais en France.', 'es_correcta' => false], ['texto' => 'J aime vivre demain France.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Escucha el plan de riqueza felina',
                'descripcion' => 'Escucha y escribe la frase exacta.',
                'tipo' => 'escucha',
                'instrucciones' => 'Escucha con atencion y transcribe.',
                'puntos' => 18,
                'tiempo' => 7,
                'contenido' => ['texto_tts' => 'Si j etais riche, j acheterais une maison a Lyon et mon chat aurait son propre bureau.', 'transcripcion' => 'Si j etais riche, j acheterais une maison a Lyon et mon chat aurait son propre bureau.'],
            ],
            [
                'titulo' => 'Proyecto final de 90 dias',
                'descripcion' => 'Escribe un plan realista de continuidad en frances.',
                'tipo' => 'escritura',
                'instrucciones' => 'Escribe 140 a 180 palabras.',
                'puntos' => 25,
                'tiempo' => 15,
                'contenido' => ['tema' => 'Describe tu plan de 90 dias para seguir mejorando frances e incluye un objetivo raro pero memorable.', 'min_palabras' => 140],
            ],
            [
                'titulo' => 'Chequeo final de humor util',
                'descripcion' => 'Decide si la afirmacion es verdadera o falsa.',
                'tipo' => 'verdadero_falso',
                'instrucciones' => 'Elige verdadero o falso.',
                'puntos' => 10,
                'tiempo' => 3,
                'contenido' => ['pregunta' => 'Usar humor y escenarios absurdos puede ayudar a fijar vocabulario y estructuras con mas facilidad.', 'respuesta_correcta' => 'Verdadero'],
            ],
        ],
    ];

    return $blueprint;
}
