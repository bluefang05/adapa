<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=adapa;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function theory_html(string $intro, array $sections, string $tip): string
{
    $html = '<div class="theory-rich">';
    $html .= '<p>' . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</p>';

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
            $html .= '<p><strong>Example:</strong> ' . htmlspecialchars($section['example'], ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }

    $html .= '<div class="alert alert-light border mt-3"><strong>Coach tip:</strong> ' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') . '</div>';
    $html .= '</div>';

    return $html;
}

function theory_blocks(string $intro, array $sections, string $tip): array
{
    $blocks = [
        [
            'tipo_bloque' => 'explicacion',
            'titulo' => 'Overview',
            'contenido' => $intro,
            'idioma_bloque' => 'ingles',
            'tts_habilitado' => 1,
        ],
    ];

    foreach ($sections as $section) {
        if (!empty($section['text'])) {
            $blocks[] = [
                'tipo_bloque' => 'explicacion',
                'titulo' => $section['title'],
                'contenido' => $section['text'],
                'idioma_bloque' => 'ingles',
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['bullets'])) {
            $blocks[] = [
                'tipo_bloque' => !empty($section['title']) && str_contains(strtolower($section['title']), 'vocabulary') ? 'vocabulario' : 'explicacion',
                'titulo' => $section['title'],
                'contenido' => implode("\n", array_map(fn($item) => '- ' . $item, $section['bullets'])),
                'idioma_bloque' => 'ingles',
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['example'])) {
            $blocks[] = [
                'tipo_bloque' => 'ejemplo',
                'titulo' => $section['title'],
                'contenido' => $section['example'],
                'idioma_bloque' => 'ingles',
                'tts_habilitado' => 1,
            ];
        }
    }

    $blocks[] = [
        'tipo_bloque' => 'instruccion',
        'titulo' => 'Coach tip',
        'contenido' => $tip,
        'idioma_bloque' => 'espanol',
        'tts_habilitado' => 1,
    ];

    return $blocks;
}

$course = [
    'instancia_id' => 1,
    'creado_por' => 13,
    'titulo' => 'English Zero to Hero: Foundations for Real Communication',
    'descripcion' => 'A polished English course for Spanish-speaking beginners that feels like a product demo, not a database fill. It builds confidence from introductions to everyday routines, descriptions, service language, past events, future plans and a final integrated mission.',
    'idioma' => 'ingles',
    'idioma_objetivo' => 'ingles',
    'idioma_ensenanza' => 'espanol',
    'nivel_cefr' => 'A1',
    'nivel_cefr_desde' => 'A1',
    'nivel_cefr_hasta' => 'A1',
    'modalidad' => 'perpetuo',
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin' => null,
    'duracion_semanas' => 14,
    'es_publico' => 1,
    'requiere_codigo' => 0,
    'codigo_acceso' => null,
    'tipo_codigo' => null,
    'inscripcion_abierta' => 1,
    'fecha_cierre_inscripcion' => null,
    'max_estudiantes' => 500,
    'estado' => 'activo',
    'notificar_profesor_completada' => 1,
    'notificar_profesor_atascado' => 1,
];

$lessons = [
    [
        'titulo' => 'Lesson 1. Hello, English!',
        'descripcion' => 'Build immediate confidence with greetings, names, introductions and the verb be.',
        'duracion' => 55,
        'teoria' => [
            [
                'titulo' => 'Warm starts: greetings, names and first contact',
                'duracion' => 12,
                'intro' => 'The first lesson is about momentum. A beginner who can greet, say a name and react politely already feels progress.',
                'sections' => [
                    ['title' => 'Core patterns', 'bullets' => ['Hello, I am Sofia.', 'Hi, my name is Daniel.', 'Good morning. Nice to meet you.'], 'example' => 'Hello, I am Lucia. Nice to meet you.'],
                    ['title' => 'When to use them', 'bullets' => ['Good morning is safe before midday.', 'Hi is relaxed and natural.', 'Nice to meet you is for the first interaction.']],
                    ['title' => 'Survival line', 'text' => 'If the learner freezes, one line is enough to recover: Hi, I am ____.'],
                ],
                'tip' => 'Fluency begins with one line the student can produce without fear.',
            ],
            [
                'titulo' => 'Subject pronouns and the verb be',
                'duracion' => 14,
                'intro' => 'The verb be lets the learner say who they are, where they are from and how they feel.',
                'sections' => [
                    ['title' => 'Pronouns to control early', 'bullets' => ['I', 'you', 'he', 'she', 'we', 'they']],
                    ['title' => 'Verb map', 'bullets' => ['I am', 'you are', 'he is', 'she is', 'we are', 'they are'], 'example' => 'I am a student. She is my teacher.'],
                    ['title' => 'Typical mistakes', 'bullets' => ['Do not say I is.', 'Do not drop the verb in English.', 'Use are with plural subjects.']],
                ],
                'tip' => 'Ask Who is it? and force a full answer with pronoun plus be.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Greeting essentials check',
                'descripcion' => 'Choose the most natural answer for basic first-contact situations.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Read each situation and choose the best answer.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    'pregunta_global' => 'Select the best answer in each case.',
                    'preguntas' => [
                        ['texto' => 'What is the safest greeting at 9:00 a.m.?', 'opciones' => [['texto' => 'Good morning', 'es_correcta' => true], ['texto' => 'Good night', 'es_correcta' => false], ['texto' => 'See you later', 'es_correcta' => false]]],
                        ['texto' => 'Which sentence introduces you correctly?', 'opciones' => [['texto' => 'I am Camila.', 'es_correcta' => true], ['texto' => 'I Camila.', 'es_correcta' => false], ['texto' => 'Am Camila I.', 'es_correcta' => false]]],
                        ['texto' => 'What do you say after meeting someone for the first time?', 'opciones' => [['texto' => 'Nice to meet you.', 'es_correcta' => true], ['texto' => 'Goodbye forever.', 'es_correcta' => false], ['texto' => 'Yesterday was busy.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Build the introduction',
                'descripcion' => 'Put the words in order to create a natural self-introduction.',
                'tipo' => 'ordenar_palabras',
                'instrucciones' => 'Drag or tap the words into the correct order.',
                'puntos' => 10,
                'tiempo' => 5,
                'contenido' => [
                    ['id' => 'l1_order_1', 'instruction' => 'Order the sentence correctly.', 'items' => ['Hello,', 'I', 'am', 'Valeria.']],
                    ['id' => 'l1_order_2', 'instruction' => 'Order the sentence correctly.', 'items' => ['I', 'am', 'from', 'Chile.']],
                ],
            ],
            [
                'titulo' => 'Complete with the verb be',
                'descripcion' => 'Fill in the gaps with the correct form of be.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Write the missing word in each sentence.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    ['id' => 'l1_gap_1', 'oracion' => 'I ____ a new student.', 'respuesta_correcta' => 'am'],
                    ['id' => 'l1_gap_2', 'oracion' => 'They ____ my classmates.', 'respuesta_correcta' => 'are'],
                    ['id' => 'l1_gap_3', 'oracion' => 'She ____ from Bogota.', 'respuesta_correcta' => 'is'],
                ],
            ],
        ],
    ],
    [
        'titulo' => 'Lesson 2. Everyday sentences and routines',
        'descripcion' => 'Move from isolated phrases to useful statements about habits, schedules and daily routines.',
        'duracion' => 65,
        'teoria' => [
            [
                'titulo' => 'The present simple for routines',
                'duracion' => 14,
                'intro' => 'The present simple helps learners talk about habits, schedules and things that are generally true.',
                'sections' => [
                    ['title' => 'Base pattern', 'bullets' => ['I work.', 'You study.', 'We live in Lima.']],
                    ['title' => 'Third person alert', 'bullets' => ['He works.', 'She studies.', 'My brother lives in Quito.']],
                    ['title' => 'Negative and question support', 'bullets' => ['I do not work on Sundays.', 'Does she study at night?']],
                ],
                'tip' => 'Whenever the subject is he, she or it, check the final s.',
            ],
            [
                'titulo' => 'Routine vocabulary that sounds natural',
                'duracion' => 12,
                'intro' => 'A good routine answer needs verbs, time markers and small connectors.',
                'sections' => [
                    ['title' => 'Useful routine verbs', 'bullets' => ['wake up', 'have breakfast', 'start work', 'finish class', 'go to bed']],
                    ['title' => 'Time markers', 'bullets' => ['every day', 'usually', 'sometimes', 'in the morning', 'at 7:30']],
                    ['title' => 'Strong model', 'example' => 'I usually wake up at 6:30, have coffee and start work at 8:00.'],
                ],
                'tip' => 'Teach I usually..., I sometimes..., I never... as ready-made chunks.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Present simple quick decisions',
                'descripcion' => 'Choose the correct present simple form.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Pick the best option in each sentence.',
                'puntos' => 15,
                'tiempo' => 5,
                'contenido' => [
                    'pregunta_global' => 'Choose the correct present simple form.',
                    'preguntas' => [
                        ['texto' => 'My sister ____ coffee every morning.', 'opciones' => [['texto' => 'drink', 'es_correcta' => false], ['texto' => 'drinks', 'es_correcta' => true], ['texto' => 'drinking', 'es_correcta' => false]]],
                        ['texto' => 'We ____ English after work.', 'opciones' => [['texto' => 'study', 'es_correcta' => true], ['texto' => 'studies', 'es_correcta' => false], ['texto' => 'studying', 'es_correcta' => false]]],
                        ['texto' => 'He ____ to the gym on Fridays.', 'opciones' => [['texto' => 'go', 'es_correcta' => false], ['texto' => 'goes', 'es_correcta' => true], ['texto' => 'going', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'One-word routine answer',
                'descripcion' => 'Answer with the correct present simple form.',
                'tipo' => 'respuesta_corta',
                'instrucciones' => 'Write only one word.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    'pregunta' => 'Complete the sentence with one word: She ____ at 7:00 every day.',
                    'respuesta_correcta' => 'wakes',
                    'respuestas_correctas' => ['wakes'],
                    'placeholder' => 'Type one word',
                ],
            ],
            [
                'titulo' => 'Write your morning routine',
                'descripcion' => 'Produce a short and coherent text about your real routine.',
                'tipo' => 'escritura',
                'instrucciones' => 'Write 60 to 90 words about your morning routine. Include time, one habit and one preference.',
                'puntos' => 20,
                'tiempo' => 12,
                'contenido' => [
                    'tema' => 'Describe your morning routine using the present simple.',
                    'min_palabras' => 60,
                ],
            ],
        ],
    ],
    [
        'titulo' => 'Lesson 3. Describe people, places and your world',
        'descripcion' => 'Describe rooms, neighborhoods and people with useful adjectives and prepositions.',
        'duracion' => 60,
        'teoria' => [
            [
                'titulo' => 'There is, there are and simple descriptions',
                'duracion' => 12,
                'intro' => 'To describe places clearly, the learner needs a structure for existence: there is and there are.',
                'sections' => [
                    ['title' => 'Existence patterns', 'bullets' => ['There is a bank on the corner.', 'There are two cafes near my office.']],
                    ['title' => 'Singular and plural control', 'bullets' => ['Use there is with one thing.', 'Use there are with two or more things.']],
                    ['title' => 'Description add-on', 'example' => 'There is a quiet park behind my building.'],
                ],
                'tip' => 'Build descriptions in layers: there is or are, noun, adjective, location.',
            ],
            [
                'titulo' => 'Prepositions that help you navigate',
                'duracion' => 14,
                'intro' => 'Prepositions make English practical because they allow the learner to explain location and direction.',
                'sections' => [
                    ['title' => 'High-value prepositions', 'bullets' => ['next to', 'between', 'in front of', 'behind', 'across from']],
                    ['title' => 'Mini patterns', 'bullets' => ['The pharmacy is next to the supermarket.', 'The station is across from the hotel.']],
                    ['title' => 'Why they matter', 'text' => 'These forms appear in travel, shopping, work and daily navigation.'],
                ],
                'tip' => 'Never teach prepositions in isolation. Tie them to a visible object or map.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Describe the place accurately',
                'descripcion' => 'Choose the option that best completes each description.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Read each place description and select the best answer.',
                'puntos' => 15,
                'tiempo' => 5,
                'contenido' => [
                    'pregunta_global' => 'Select the best answer in each sentence.',
                    'preguntas' => [
                        ['texto' => '____ a supermarket near the station.', 'opciones' => [['texto' => 'There is', 'es_correcta' => true], ['texto' => 'There are', 'es_correcta' => false], ['texto' => 'There be', 'es_correcta' => false]]],
                        ['texto' => 'The cafe is ____ the bank and the pharmacy.', 'opciones' => [['texto' => 'between', 'es_correcta' => true], ['texto' => 'under', 'es_correcta' => false], ['texto' => 'inside', 'es_correcta' => false]]],
                        ['texto' => 'There are two ____ streets in my neighborhood.', 'opciones' => [['texto' => 'quiet', 'es_correcta' => true], ['texto' => 'quietly', 'es_correcta' => false], ['texto' => 'quieten', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Complete the place description',
                'descripcion' => 'Fill the gaps with the correct location word.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Write the missing location word in each sentence.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    ['id' => 'l3_gap_1', 'oracion' => 'The library is ____ to the school.', 'respuesta_correcta' => 'next'],
                    ['id' => 'l3_gap_2', 'oracion' => 'The bus stop is ____ from the hotel.', 'respuesta_correcta' => 'across'],
                    ['id' => 'l3_gap_3', 'oracion' => 'There ____ three chairs in the room.', 'respuesta_correcta' => 'are'],
                ],
            ],
            [
                'titulo' => 'Order the city sentence',
                'descripcion' => 'Rebuild a natural description of a city place.',
                'tipo' => 'ordenar_palabras',
                'instrucciones' => 'Put the words in the correct order.',
                'puntos' => 10,
                'tiempo' => 5,
                'contenido' => [
                    ['id' => 'l3_order_1', 'instruction' => 'Order the sentence correctly.', 'items' => ['There', 'is', 'a', 'small', 'park', 'behind', 'the', 'museum.']],
                ],
            ],
        ],
    ],
    [
        'titulo' => 'Lesson 4. Food, shopping and polite requests',
        'descripcion' => 'Work with menus, quantity language and polite phrases for real transactions.',
        'duracion' => 60,
        'teoria' => [
            [
                'titulo' => 'Countable, uncountable and quantity words',
                'duracion' => 12,
                'intro' => 'Food English becomes easier when the learner understands what can be counted and what is measured in portions or amounts.',
                'sections' => [
                    ['title' => 'Countable nouns', 'bullets' => ['an apple', 'two sandwiches', 'three bottles']],
                    ['title' => 'Uncountable nouns', 'bullets' => ['water', 'rice', 'coffee', 'money']],
                    ['title' => 'Useful quantity partners', 'bullets' => ['some water', 'a bottle of water', 'two cups of coffee']],
                ],
                'tip' => 'Students remember a bottle of water more easily than an abstract grammar label.',
            ],
            [
                'titulo' => 'Polite requests in service situations',
                'duracion' => 13,
                'intro' => 'A beginner can sound more professional and more human with only a few polite request frames.',
                'sections' => [
                    ['title' => 'Polite request frames', 'bullets' => ['Can I have a sandwich, please?', 'Could I get a coffee?', 'I would like a salad, please.']],
                    ['title' => 'Follow-up questions', 'bullets' => ['Anything else?', 'For here or to go?', 'How much is it?']],
                    ['title' => 'Short service exchange', 'example' => 'Good afternoon. I would like a chicken sandwich and a bottle of water, please.'],
                ],
                'tip' => 'If the student learns one service pattern well, I would like... please is enough to sound solid.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Some, any and quantity choices',
                'descripcion' => 'Choose the best quantity word or structure.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Pick the most natural answer.',
                'puntos' => 15,
                'tiempo' => 5,
                'contenido' => [
                    'pregunta_global' => 'Select the best option in each sentence.',
                    'preguntas' => [
                        ['texto' => 'I need ____ water.', 'opciones' => [['texto' => 'some', 'es_correcta' => true], ['texto' => 'an', 'es_correcta' => false], ['texto' => 'three', 'es_correcta' => false]]],
                        ['texto' => 'Can I have ____ apple, please?', 'opciones' => [['texto' => 'an', 'es_correcta' => true], ['texto' => 'some', 'es_correcta' => false], ['texto' => 'much', 'es_correcta' => false]]],
                        ['texto' => 'There is not ____ milk left.', 'opciones' => [['texto' => 'any', 'es_correcta' => true], ['texto' => 'a', 'es_correcta' => false], ['texto' => 'many', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Polite service language',
                'descripcion' => 'Answer with the missing polite expression.',
                'tipo' => 'respuesta_corta',
                'instrucciones' => 'Write one word only.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    'pregunta' => 'Complete the question: Could I ____ a coffee, please?',
                    'respuesta_correcta' => 'have',
                    'respuestas_correctas' => ['have', 'get'],
                    'placeholder' => 'Type one word',
                ],
            ],
            [
                'titulo' => 'Listen to the order',
                'descripcion' => 'Listen carefully and write the exact order.',
                'tipo' => 'escucha',
                'instrucciones' => 'Listen to the audio and write exactly what the customer says.',
                'puntos' => 20,
                'tiempo' => 8,
                'contenido' => [
                    'texto_tts' => 'Good afternoon, I would like a chicken sandwich and a bottle of water, please.',
                    'transcripcion' => 'Good afternoon, I would like a chicken sandwich and a bottle of water, please.',
                ],
            ],
        ],
    ],
    [
        'titulo' => 'Lesson 5. Talk about yesterday',
        'descripcion' => 'Use the simple past to report finished actions and tell short personal stories.',
        'duracion' => 65,
        'teoria' => [
            [
                'titulo' => 'Simple past of be and regular verbs',
                'duracion' => 14,
                'intro' => 'The simple past gives learners the power to report what happened with clear time reference.',
                'sections' => [
                    ['title' => 'Past of be', 'bullets' => ['I was tired.', 'They were at home.', 'She was in class.']],
                    ['title' => 'Regular past verbs', 'bullets' => ['work -> worked', 'study -> studied', 'visit -> visited']],
                    ['title' => 'Time anchors', 'bullets' => ['yesterday', 'last night', 'last weekend', 'two days ago']],
                ],
                'tip' => 'Give the learner a simple timeline: the action is finished, so use past forms.',
            ],
            [
                'titulo' => 'How to tell a short past story',
                'duracion' => 12,
                'intro' => 'A strong short story needs order: time, action, result.',
                'sections' => [
                    ['title' => 'Story skeleton', 'bullets' => ['Last Saturday...', 'I visited my cousin.', 'We cooked dinner and watched a movie.']],
                    ['title' => 'Useful connectors', 'bullets' => ['then', 'after that', 'later', 'finally']],
                    ['title' => 'Model', 'example' => 'Last weekend I stayed home, finished a report and called my parents.'],
                ],
                'tip' => 'One clean three-sentence story shows more control than a long messy paragraph.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Past tense choices',
                'descripcion' => 'Choose the correct past form.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Select the best past form in each sentence.',
                'puntos' => 15,
                'tiempo' => 5,
                'contenido' => [
                    'pregunta_global' => 'Choose the correct past form.',
                    'preguntas' => [
                        ['texto' => 'Yesterday I ____ at home.', 'opciones' => [['texto' => 'was', 'es_correcta' => true], ['texto' => 'am', 'es_correcta' => false], ['texto' => 'are', 'es_correcta' => false]]],
                        ['texto' => 'We ____ a movie last night.', 'opciones' => [['texto' => 'watch', 'es_correcta' => false], ['texto' => 'watched', 'es_correcta' => true], ['texto' => 'watches', 'es_correcta' => false]]],
                        ['texto' => 'They ____ happy after the class.', 'opciones' => [['texto' => 'were', 'es_correcta' => true], ['texto' => 'was', 'es_correcta' => false], ['texto' => 'be', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Complete the weekend story',
                'descripcion' => 'Write the missing past form.',
                'tipo' => 'completar_oracion',
                'instrucciones' => 'Fill the gap with the correct past form.',
                'puntos' => 15,
                'tiempo' => 6,
                'contenido' => [
                    ['id' => 'l5_gap_1', 'oracion' => 'Last night I ____ a new series.', 'respuesta_correcta' => 'watched'],
                    ['id' => 'l5_gap_2', 'oracion' => 'My friends ____ at the restaurant.', 'respuesta_correcta' => 'were'],
                    ['id' => 'l5_gap_3', 'oracion' => 'We ____ dinner at 8:00 p.m.', 'respuesta_correcta' => 'cooked'],
                ],
            ],
            [
                'titulo' => 'Weekend diary entry',
                'descripcion' => 'Write a short personal paragraph about last weekend.',
                'tipo' => 'escritura',
                'instrucciones' => 'Write 70 to 100 words about your last weekend. Include where you were, what you did and how you felt.',
                'puntos' => 20,
                'tiempo' => 12,
                'contenido' => [
                    'tema' => 'Write a short diary entry about your last weekend.',
                    'min_palabras' => 70,
                ],
            ],
        ],
    ],
    [
        'titulo' => 'Lesson 6. Plans, invitations and next steps',
        'descripcion' => 'Use future language to talk about plans, invitations and simple decisions.',
        'duracion' => 55,
        'teoria' => [
            [
                'titulo' => 'Going to for plans and intention',
                'duracion' => 13,
                'intro' => 'Going to is a beginner-friendly future form for plans, intentions and visible decisions.',
                'sections' => [
                    ['title' => 'Core form', 'bullets' => ['I am going to study tonight.', 'She is going to travel next month.', 'We are going to meet after class.']],
                    ['title' => 'Why it matters', 'text' => 'The learner can move beyond present facts and speak about plans, goals and schedules.'],
                    ['title' => 'Quick contrast', 'example' => 'Today I work. Tomorrow I am going to work from home.'],
                ],
                'tip' => 'Teach going to as one sound unit so students produce it more naturally.',
            ],
            [
                'titulo' => 'Invitation language for real conversation',
                'duracion' => 11,
                'intro' => 'A course feels practical when learners can invite, accept and refuse politely.',
                'sections' => [
                    ['title' => 'Invite', 'bullets' => ['Do you want to have lunch tomorrow?', 'Are you free this weekend?']],
                    ['title' => 'Accept', 'bullets' => ['Sure, that sounds great.', 'Yes, I would love to.']],
                    ['title' => 'Refuse politely', 'bullets' => ['Sorry, I cannot.', 'I am busy, but maybe next time.']],
                ],
                'tip' => 'Teach invitation language in full chunks so learners can react quickly.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Future plan check',
                'descripcion' => 'Decide whether the statement about future use is correct.',
                'tipo' => 'verdadero_falso',
                'instrucciones' => 'Read the statement and choose true or false.',
                'puntos' => 10,
                'tiempo' => 3,
                'contenido' => [
                    'pregunta' => '"I am going to visit my aunt tomorrow" is a correct sentence to describe a plan.',
                    'respuesta_correcta' => 'Verdadero',
                ],
            ],
            [
                'titulo' => 'Order the invitation',
                'descripcion' => 'Put the invitation in natural order.',
                'tipo' => 'ordenar_palabras',
                'instrucciones' => 'Rebuild the sentence.',
                'puntos' => 10,
                'tiempo' => 4,
                'contenido' => [
                    ['id' => 'l6_order_1', 'instruction' => 'Order the invitation correctly.', 'items' => ['Are', 'you', 'free', 'this', 'Saturday?']],
                ],
            ],
            [
                'titulo' => 'One-word future answer',
                'descripcion' => 'Complete the future plan with one key word.',
                'tipo' => 'respuesta_corta',
                'instrucciones' => 'Write only one word.',
                'puntos' => 10,
                'tiempo' => 3,
                'contenido' => [
                    'pregunta' => 'Complete the sentence: We are going to ____ a project next week.',
                    'respuesta_correcta' => 'start',
                    'respuestas_correctas' => ['start'],
                    'placeholder' => 'Type one word',
                ],
            ],
        ],
    ],
    [
        'titulo' => 'Lesson 7. Final mission: communicate with confidence',
        'descripcion' => 'Close the course with an integrated review that combines reading decisions, listening and writing.',
        'duracion' => 70,
        'teoria' => [
            [
                'titulo' => 'How to review without memorizing blindly',
                'duracion' => 10,
                'intro' => 'Strong review does not mean reading everything again. It means revisiting patterns, errors and useful chunks in a useful order.',
                'sections' => [
                    ['title' => 'Review layers', 'bullets' => ['Core grammar patterns', 'Useful chunks', 'Typical mistakes', 'One personal example for each topic']],
                    ['title' => 'Smart review question', 'text' => 'Can I use this pattern in a real sentence about my life right now?'],
                ],
                'tip' => 'The best review ends in production, not just recognition.',
            ],
            [
                'titulo' => 'Final communication checklist',
                'duracion' => 12,
                'intro' => 'A final checkpoint should show whether the learner can greet, describe routines, report a past action and mention a plan.',
                'sections' => [
                    ['title' => 'Can the learner do this?', 'bullets' => ['Introduce themselves clearly', 'Describe a place', 'Talk about yesterday', 'Talk about tomorrow']],
                    ['title' => 'What counts as success', 'text' => 'Clear meaning, acceptable grammar and enough vocabulary to complete the message.'],
                ],
                'tip' => 'A final mission should feel practical and motivating, not punitive.',
            ],
        ],
        'actividades' => [
            [
                'titulo' => 'Integrated final checkpoint',
                'descripcion' => 'Review grammar and communication choices from across the course.',
                'tipo' => 'opcion_multiple',
                'instrucciones' => 'Choose the best answer in each final review item.',
                'puntos' => 20,
                'tiempo' => 8,
                'contenido' => [
                    'pregunta_global' => 'Final review: choose the best answer.',
                    'preguntas' => [
                        ['texto' => 'Choose the best self-introduction.', 'opciones' => [['texto' => 'Hello, I am Marta from Peru.', 'es_correcta' => true], ['texto' => 'Hello, Marta from Peru am.', 'es_correcta' => false], ['texto' => 'Peru hello Marta.', 'es_correcta' => false]]],
                        ['texto' => 'Choose the correct routine sentence.', 'opciones' => [['texto' => 'He studies every night.', 'es_correcta' => true], ['texto' => 'He study every night.', 'es_correcta' => false], ['texto' => 'He studying every night.', 'es_correcta' => false]]],
                        ['texto' => 'Choose the correct past sentence.', 'opciones' => [['texto' => 'We were tired after class.', 'es_correcta' => true], ['texto' => 'We are tired after class yesterday.', 'es_correcta' => false], ['texto' => 'We was tired after class.', 'es_correcta' => false]]],
                        ['texto' => 'Choose the correct future plan.', 'opciones' => [['texto' => 'I am going to visit my grandmother tomorrow.', 'es_correcta' => true], ['texto' => 'I going visit tomorrow.', 'es_correcta' => false], ['texto' => 'I am visit tomorrow.', 'es_correcta' => false]]],
                    ],
                ],
            ],
            [
                'titulo' => 'Final listening note',
                'descripcion' => 'Listen and capture the key message exactly.',
                'tipo' => 'escucha',
                'instrucciones' => 'Listen and write the sentence exactly as you hear it.',
                'puntos' => 20,
                'tiempo' => 8,
                'contenido' => [
                    'texto_tts' => 'Hello, my name is Adrian. I work in a hotel, but next month I am going to study English every evening.',
                    'transcripcion' => 'Hello, my name is Adrian. I work in a hotel, but next month I am going to study English every evening.',
                ],
            ],
            [
                'titulo' => 'Zero to Hero final reflection',
                'descripcion' => 'Write a short final message that proves real communication control.',
                'tipo' => 'escritura',
                'instrucciones' => 'Write 90 to 120 words introducing yourself, describing one routine, one past action and one future plan.',
                'puntos' => 25,
                'tiempo' => 15,
                'contenido' => [
                    'tema' => 'Final reflection: who you are, what you usually do, what you did recently and what you are going to do next.',
                    'min_palabras' => 90,
                ],
            ],
        ],
    ],
];

$pdo->beginTransaction();

$insertCourse = $pdo->prepare('INSERT INTO cursos (instancia_id, creado_por, titulo, descripcion, idioma, idioma_objetivo, idioma_ensenanza, nivel_cefr, nivel_cefr_desde, nivel_cefr_hasta, modalidad, fecha_inicio, fecha_fin, duracion_semanas, es_publico, requiere_codigo, codigo_acceso, tipo_codigo, inscripcion_abierta, fecha_cierre_inscripcion, max_estudiantes, estado, notificar_profesor_completada, notificar_profesor_atascado) VALUES (:instancia_id, :creado_por, :titulo, :descripcion, :idioma, :idioma_objetivo, :idioma_ensenanza, :nivel_cefr, :nivel_cefr_desde, :nivel_cefr_hasta, :modalidad, :fecha_inicio, :fecha_fin, :duracion_semanas, :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :inscripcion_abierta, :fecha_cierre_inscripcion, :max_estudiantes, :estado, :notificar_profesor_completada, :notificar_profesor_atascado)');
$insertCourse->execute($course);
$courseId = (int) $pdo->lastInsertId();

$insertLesson = $pdo->prepare('INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (?, ?, ?, ?, ?, 1, "publicada")');
$insertTheory = $pdo->prepare('INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo) VALUES (?, ?, ?, "texto", ?, ?, 0)');
$insertBlock = $pdo->prepare('INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)');
$insertActivity = $pdo->prepare('INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")');
$insertEnrollment = $pdo->prepare('INSERT INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');

$lessonCount = 0;
$theoryCount = 0;
$activityCount = 0;

foreach ($lessons as $lessonIndex => $lesson) {
    $lessonCount++;
    $insertLesson->execute([$courseId, $lesson['titulo'], $lesson['descripcion'], $lessonIndex + 1, $lesson['duracion']]);
    $lessonId = (int) $pdo->lastInsertId();

    foreach ($lesson['teoria'] as $theoryIndex => $theory) {
        $insertTheory->execute([
            $lessonId,
            $theory['titulo'],
            theory_html($theory['intro'], $theory['sections'], $theory['tip']),
            $theory['duracion'],
            $theoryIndex + 1,
        ]);
        $theoryCount++;
        $theoryId = (int) $pdo->lastInsertId();

        foreach (theory_blocks($theory['intro'], $theory['sections'], $theory['tip']) as $blockIndex => $block) {
            $insertBlock->execute([
                $theoryId,
                $block['tipo_bloque'],
                $block['titulo'],
                $block['contenido'],
                $block['idioma_bloque'],
                $block['tts_habilitado'],
                $blockIndex + 1,
            ]);
        }
    }

    foreach ($lesson['actividades'] as $activityIndex => $activity) {
        $insertActivity->execute([
            $lessonId,
            $activity['titulo'],
            $activity['descripcion'],
            $activity['tipo'],
            $activity['instrucciones'],
            json_encode($activity['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $activity['puntos'],
            $activity['tiempo'],
            $activityIndex + 1,
        ]);
        $activityCount++;
    }
}

$insertEnrollment->execute([$courseId, 14]);
$pdo->commit();

echo json_encode([
    'course_id' => $courseId,
    'title' => $course['titulo'],
    'lessons' => $lessonCount,
    'theory_items' => $theoryCount,
    'activities' => $activityCount,
    'professor_id' => 13,
    'student_id' => 14,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
