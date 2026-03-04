<?php

function german_theory_html(string $intro, array $sections, string $tip): string {
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

    $html .= '<div class="alert alert-light border mt-3"><strong>Coach tip:</strong> ' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') . '</div></div>';

    return $html;
}

function german_theory_blocks(string $intro, array $sections, string $tip): array {
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
                'idioma_bloque' => (str_contains($title, 'frases') || str_contains($title, 'dialogo')) ? 'aleman' : 'espanol',
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['example'])) {
            $blocks[] = [
                'tipo_bloque' => 'ejemplo',
                'titulo' => $section['title'],
                'contenido' => $section['example'],
                'idioma_bloque' => 'aleman',
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

function german_course_blueprint(): array {
    $blueprint = [
        'course' => [
            'instancia_id' => 1,
            'creado_por' => 13,
            'titulo' => 'Aleman de Cero a Heroe: Ruta completa A1-C1',
            'descripcion' => 'Ruta completa de aleman para hispanohablantes desde supervivencia A1 hasta precision C1. Trabaja pronunciacion, gramatica funcional, situaciones reales, opinion, escritura, escucha y un plan claro de progreso.',
            'idioma' => 'aleman',
            'idioma_objetivo' => 'aleman',
            'idioma_ensenanza' => 'espanol',
            'nivel_cefr' => 'A1',
            'nivel_cefr_desde' => 'A1',
            'nivel_cefr_hasta' => 'C1',
            'modalidad' => 'perpetuo',
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => null,
            'duracion_semanas' => 80,
            'es_publico' => 1,
            'requiere_codigo' => 0,
            'codigo_acceso' => null,
            'tipo_codigo' => null,
            'inscripcion_abierta' => 1,
            'fecha_cierre_inscripcion' => null,
            'max_estudiantes' => 1000,
            'estado' => 'activo',
            'notificar_profesor_completada' => 1,
            'notificar_profesor_atascado' => 1,
        ],
        'lessons' => [],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel A1: sonidos, presentaciones y primer contacto',
        'descripcion' => 'Pronunciacion esencial, articulos, sein y haben para presentarte, preguntar datos basicos y reaccionar con seguridad.',
        'duracion' => 125,
        'teoria' => [
            [
                'titulo' => 'Pronunciacion base y sonidos que mas bloquean',
                'duracion' => 18,
                'intro' => 'El arranque en aleman no se cae por gramatica avanzada sino por sonidos inseguros. Si el alumno domina umlauts, ich-Laut, ach-Laut y los grupos sch, sp y st, la produccion inicial mejora enseguida.',
                'sections' => [
                    ['title' => 'Sonidos que exigen atencion', 'bullets' => ['a, e, i, o, u frente a ä, ö, ü', 'ß y doble consonante', 'ich frente a ach', 'sch, sp y st al inicio']],
                    ['title' => 'Frases de arranque', 'bullets' => ['Guten Morgen', 'Ich heiße Laura', 'Ich komme aus Chile', 'Freut mich']],
                    ['title' => 'Modelo oral', 'example' => 'Guten Tag. Ich heiße Tomas und ich komme aus Peru.'],
                ],
                'tip' => 'Haz que el estudiante repita una mini presentacion completa antes de explicarle demasiadas reglas.',
            ],
            [
                'titulo' => 'Articulos, pronombres y el verbo sein',
                'duracion' => 20,
                'intro' => 'A1 necesita control rapido de sujeto, articulo y verbo. No hace falta explicar todo el sistema de casos al inicio, pero si fijar los bloques mas frecuentes.',
                'sections' => [
                    ['title' => 'Bloques de alta frecuencia', 'bullets' => ['der Lehrer', 'die Stadt', 'das Buch', 'ich, du, er, sie, wir', 'ich bin, du bist, Sie sind']],
                    ['title' => 'Negacion basica', 'bullets' => ['nicht para verbos y adjetivos', 'kein para sustantivos', 'Ich bin nicht muede', 'Ich habe kein Geld']],
                    ['title' => 'Modelo funcional', 'example' => 'Ich bin Studentin und meine Schwester ist Lehrerin.'],
                ],
                'tip' => 'En A1 conviene memorizar articulo + palabra como una sola unidad.',
            ],
            [
                'titulo' => 'Preguntas personales, haben y microdialogos',
                'duracion' => 16,
                'intro' => 'Con sein y haben ya puedes abrir interacciones reales: nombre, edad, origen, profesion y objetos personales.',
                'sections' => [
                    ['title' => 'Preguntas utiles', 'bullets' => ['Wie heißt du?', 'Woher kommst du?', 'Wie alt bist du?', 'Hast du Zeit?', 'Was hast du dabei?']],
                    ['title' => 'Respuestas cortas solidas', 'bullets' => ['Ich heiße Ana.', 'Ich bin 22 Jahre alt.', 'Ich komme aus Mexiko.', 'Ja, ich habe Zeit.']],
                    ['title' => 'Dialogo minimo', 'example' => 'A: Wie heißt du? B: Ich heiße Sofia. A: Woher kommst du? B: Ich komme aus Ecuador.'],
                ],
                'tip' => 'La primera meta comunicativa no es hablar mucho, sino responder bien a cinco preguntas basicas.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Saludos y datos personales', 'descripcion' => 'Elige la opcion correcta en situaciones basicas de presentacion.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la mejor respuesta.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Selecciona la respuesta correcta.', 'preguntas' => [['texto' => 'Frase correcta para presentarte:', 'opciones' => [['texto' => 'Ich heiße Marta.', 'es_correcta' => true], ['texto' => 'Ich heißt Marta.', 'es_correcta' => false], ['texto' => 'Ich name Marta.', 'es_correcta' => false]]], ['texto' => 'Respuesta natural a "Woher kommst du?"', 'opciones' => [['texto' => 'Ich komme aus Chile.', 'es_correcta' => true], ['texto' => 'Ich bin Chile.', 'es_correcta' => false], ['texto' => 'Aus komme ich Chile.', 'es_correcta' => false]]], ['texto' => 'Forma correcta de "to be" con ich:', 'opciones' => [['texto' => 'bin', 'es_correcta' => true], ['texto' => 'bist', 'es_correcta' => false], ['texto' => 'seid', 'es_correcta' => false]]]]]],
            ['titulo' => 'Ordena tu presentacion', 'descripcion' => 'Reconstruye una presentacion breve y natural.', 'tipo' => 'ordenar_palabras', 'instrucciones' => 'Ordena las palabras correctamente.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => [['id' => 'a1_intro_1', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'heiße', 'Lucia.']], ['id' => 'a1_intro_2', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'komme', 'aus', 'Kolumbien.']]]],
            ['titulo' => 'Sein y haben en contexto', 'descripcion' => 'Escribe la palabra que falta.', 'tipo' => 'completar_oracion', 'instrucciones' => 'Escribe solo la palabra correcta.', 'puntos' => 12, 'tiempo' => 5, 'contenido' => [['id' => 'a1_gap_1', 'oracion' => 'Ich ____ 19 Jahre alt.', 'respuesta_correcta' => 'bin'], ['id' => 'a1_gap_2', 'oracion' => 'Wir ____ einen Deutschkurs.', 'respuesta_correcta' => 'haben'], ['id' => 'a1_gap_3', 'oracion' => 'Sie ____ aus Peru.', 'respuesta_correcta' => 'ist']]],
            ['titulo' => 'Escucha la mini presentacion', 'descripcion' => 'Escucha y escribe la frase exacta.', 'tipo' => 'escucha', 'instrucciones' => 'Escucha con atencion y transcribe.', 'puntos' => 18, 'tiempo' => 6, 'contenido' => ['texto_tts' => 'Guten Tag, ich heiße Daniel und ich komme aus Chile.', 'transcripcion' => 'Guten Tag, ich heiße Daniel und ich komme aus Chile.']],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel A2: pasado, dativo y vida cotidiana',
        'descripcion' => 'Perfekt, dativo, vivienda, salud y comparativos para describir experiencias y necesidades reales.',
        'duracion' => 140,
        'teoria' => [
            [
                'titulo' => 'Perfekt para contar lo que hiciste',
                'duracion' => 20,
                'intro' => 'En A2 el alumno debe salir del presente. Perfekt permite narrar experiencias recientes sin exigir todavia control literario del pasado.',
                'sections' => [
                    ['title' => 'Esqueleto del Perfekt', 'bullets' => ['ich habe gearbeitet', 'ich bin gegangen', 'wir haben gelernt', 'sie ist angekommen']],
                    ['title' => 'Marcadores de tiempo', 'bullets' => ['gestern', 'letzte Woche', 'am Wochenende', 'vor zwei Tagen']],
                    ['title' => 'Modelo A2', 'example' => 'Am Wochenende habe ich meine Grosseltern besucht und wir haben zusammen gekocht.'],
                ],
                'tip' => 'Primero fija el auxiliar correcto y luego la forma del participio.',
            ],
            [
                'titulo' => 'Dativo funcional y preposiciones de alta frecuencia',
                'duracion' => 18,
                'intro' => 'El dativo se vuelve util cuando el alumno empieza a dar cosas, hablar con personas y moverse con preposiciones frecuentes.',
                'sections' => [
                    ['title' => 'Bloques utiles', 'bullets' => ['mit dem Freund', 'bei der Arbeit', 'nach dem Kurs', 'von der Schule', 'zu meiner Mutter']],
                    ['title' => 'Verbos que piden dativo', 'bullets' => ['helfen', 'danken', 'gefallen', 'antworten']],
                    ['title' => 'Modelo funcional', 'example' => 'Ich helfe meiner Schwester und fahre danach mit dem Bus nach Hause.'],
                ],
                'tip' => 'El dativo se recuerda mejor si siempre aparece unido a una preposicion o a un verbo concreto.',
            ],
            [
                'titulo' => 'Casa, salud y comparativos que si sirven',
                'duracion' => 18,
                'intro' => 'A2 gana peso cuando el alumno puede describir su entorno, decir que le duele algo y comparar opciones.',
                'sections' => [
                    ['title' => 'Campos lexicales', 'bullets' => ['Wohnung, Zimmer, Kueche, Balkon', 'Kopf, Bauch, Ruecken', 'groesser, kleiner, besser, billiger']],
                    ['title' => 'Frases utiles', 'bullets' => ['Meine Wohnung ist klein, aber ruhig.', 'Mir tut der Kopf weh.', 'Dieses Hotel ist teurer als das andere.']],
                    ['title' => 'Modelo descriptivo', 'example' => 'Meine neue Wohnung ist heller und guenstiger als die alte.'],
                ],
                'tip' => 'Comparar dos cosas reales del mundo del alumno genera mas retencion que comparar ejemplos vacios.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Perfekt y dativo en contexto', 'descripcion' => 'Elige la forma correcta en frases de vida diaria.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Selecciona la mejor opcion.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Elige la opcion correcta.', 'preguntas' => [['texto' => 'Gestern ____ ich sehr frueh aufgestanden.', 'opciones' => [['texto' => 'bin', 'es_correcta' => true], ['texto' => 'habe', 'es_correcta' => false], ['texto' => 'war', 'es_correcta' => false]]], ['texto' => 'Ich helfe ____ Mutter.', 'opciones' => [['texto' => 'meiner', 'es_correcta' => true], ['texto' => 'meine', 'es_correcta' => false], ['texto' => 'meinen', 'es_correcta' => false]]], ['texto' => 'Comparativo correcto:', 'opciones' => [['texto' => 'groesser', 'es_correcta' => true], ['texto' => 'groess', 'es_correcta' => false], ['texto' => 'mehr gross', 'es_correcta' => false]]]]]],
            ['titulo' => 'Completa el relato breve', 'descripcion' => 'Escribe la palabra que falta en pasado y dativo.', 'tipo' => 'completar_oracion', 'instrucciones' => 'Escribe solo una palabra por espacio.', 'puntos' => 12, 'tiempo' => 5, 'contenido' => [['id' => 'a2_gap_1', 'oracion' => 'Ich ____ gestern im Kino gewesen.', 'respuesta_correcta' => 'bin'], ['id' => 'a2_gap_2', 'oracion' => 'Ich habe ____ Freundin geholfen.', 'respuesta_correcta' => 'meiner'], ['id' => 'a2_gap_3', 'oracion' => 'Mein Zimmer ist ____ als frueher.', 'respuesta_correcta' => 'groesser']]],
            ['titulo' => 'Respuesta corta de salud', 'descripcion' => 'Completa una frase comun sobre sintomas.', 'tipo' => 'respuesta_corta', 'instrucciones' => 'Escribe una sola palabra.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => ['pregunta' => 'Completa: Mir tut der ____ weh.', 'respuesta_correcta' => 'Kopf', 'respuestas_correctas' => ['Kopf'], 'placeholder' => 'Escribe una palabra']],
            ['titulo' => 'Email informal A2', 'descripcion' => 'Describe tu casa, tu fin de semana o una molestia de salud.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 90 a 120 palabras.', 'puntos' => 20, 'tiempo' => 12, 'contenido' => ['tema' => 'Escribe un email informal contando que hiciste, como es tu casa o como te sentias ayer.', 'min_palabras' => 90]],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel B1: opinion, subordinadas y mundo real',
        'descripcion' => 'Conecta ideas, justifica opiniones, escribe mensajes formales y habla de trabajo, estudio y medios.',
        'duracion' => 155,
        'teoria' => [
            [
                'titulo' => 'Subordinadas que organizan el pensamiento',
                'duracion' => 20,
                'intro' => 'B1 empieza cuando el alumno deja de lanzar frases sueltas y comienza a explicar causas, condiciones y contrastes.',
                'sections' => [
                    ['title' => 'Conectores clave', 'bullets' => ['dass', 'weil', 'obwohl', 'wenn', 'als']],
                    ['title' => 'Patrones utiles', 'bullets' => ['Ich denke, dass...', 'Ich lerne Deutsch, weil...', 'Obwohl ich muede bin...']],
                    ['title' => 'Modelo argumentativo', 'example' => 'Ich lerne Deutsch, weil ich in Deutschland arbeiten moechte.'],
                ],
                'tip' => 'Haz que el alumno use cada conector para hablar de su vida, no de ejemplos impersonales.',
            ],
            [
                'titulo' => 'Relativas, cortesia y estructuras de trabajo',
                'duracion' => 18,
                'intro' => 'B1 tambien exige describir personas, objetos y situaciones con mas precision, y pedir cosas con un tono adecuado.',
                'sections' => [
                    ['title' => 'Recursos funcionales', 'bullets' => ['Der Mann, der dort arbeitet...', 'Das Buch, das ich lese...', 'Koennten Sie mir helfen?', 'Ich wuerde gern mehr erfahren.']],
                    ['title' => 'Escenas tipicas', 'bullets' => ['entrevista', 'correo formal', 'consulta en oficina', 'pregunta a un profesor']],
                    ['title' => 'Modelo formal', 'example' => 'Koennten Sie mir bitte sagen, ob noch Plaetze frei sind?'],
                ],
                'tip' => 'La cortesia en B1 se siente real cuando sirve para resolver algo concreto.',
            ],
            [
                'titulo' => 'Trabajo, estudio, medios y sociedad',
                'duracion' => 18,
                'intro' => 'En B1 el lexico ya sale del circulo personal y entra en trabajo, universidad, noticias y debates cotidianos.',
                'sections' => [
                    ['title' => 'Campos tematicos', 'bullets' => ['Bewerbung, Gehalt, Kollege, Chef', 'Studium, Pruefung, Universitaet', 'Nachrichten, Internet, soziale Medien']],
                    ['title' => 'Frases de opinion', 'bullets' => ['Meiner Meinung nach...', 'Ich finde, dass...', 'Einerseits..., andererseits...']],
                    ['title' => 'Meta B1', 'example' => 'Ich finde, dass soziale Medien nuetzlich sind, obwohl sie auch Stress verursachen.'],
                ],
                'tip' => 'B1 debe empujar al alumno a justificar, no solo a afirmar.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Conecta la idea en B1', 'descripcion' => 'Elige el conector o la forma adecuada.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la mejor opcion.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Elige la mejor opcion.', 'preguntas' => [['texto' => 'Ich glaube, ____ er heute nicht kommt.', 'opciones' => [['texto' => 'dass', 'es_correcta' => true], ['texto' => 'denn', 'es_correcta' => false], ['texto' => 'wie', 'es_correcta' => false]]], ['texto' => 'Peticion formal correcta:', 'opciones' => [['texto' => 'Koennten Sie mir helfen?', 'es_correcta' => true], ['texto' => 'Kann mir Sie helfen?', 'es_correcta' => false], ['texto' => 'Hilfst du mir koennten?', 'es_correcta' => false]]], ['texto' => 'Frase con contraste:', 'opciones' => [['texto' => 'Obwohl ich muede bin, lerne ich weiter.', 'es_correcta' => true], ['texto' => 'Obwohl ich bin muede, lerne ich weiter.', 'es_correcta' => false], ['texto' => 'Ich obwohl muede bin, lerne.', 'es_correcta' => false]]]]]],
            ['titulo' => 'Ordena el correo formal', 'descripcion' => 'Reconstruye una pregunta formal.', 'tipo' => 'ordenar_palabras', 'instrucciones' => 'Ordena las palabras.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => [['id' => 'b1_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Koennten', 'Sie', 'mir', 'bitte', 'weiterhelfen?']]]],
            ['titulo' => 'Completa la estructura B1', 'descripcion' => 'Escribe la palabra que falta.', 'tipo' => 'completar_oracion', 'instrucciones' => 'Escribe solo una palabra.', 'puntos' => 12, 'tiempo' => 5, 'contenido' => [['id' => 'b1_gap_1', 'oracion' => 'Ich finde, ____ Onlinekurse praktisch sind.', 'respuesta_correcta' => 'dass'], ['id' => 'b1_gap_2', 'oracion' => 'Der Mann, ____ dort steht, ist mein Lehrer.', 'respuesta_correcta' => 'der'], ['id' => 'b1_gap_3', 'oracion' => 'Ich ____ gern im Ausland studieren.', 'respuesta_correcta' => 'wuerde']]],
            ['titulo' => 'Opinion guiada B1', 'descripcion' => 'Redacta una opinion breve y justificada.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 120 a 160 palabras.', 'puntos' => 25, 'tiempo' => 15, 'contenido' => ['tema' => 'Da tu opinion sobre redes sociales, trabajo remoto o educacion usando al menos dos conectores.', 'min_palabras' => 120]],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel A1-A2: compras, ciudad y verbos de accion',
        'descripcion' => 'Acusativo, modales, peticiones, direcciones y verbos separables para moverte con mas autonomia.',
        'duracion' => 130,
        'teoria' => [
            [
                'titulo' => 'Acusativo funcional y objetos cotidianos',
                'duracion' => 18,
                'intro' => 'El acusativo deja de ser teoria abstracta cuando el alumno empieza a pedir, comprar y describir lo que necesita.',
                'sections' => [
                    ['title' => 'Patrones utiles', 'bullets' => ['Ich habe einen Kaffee', 'Ich brauche eine Karte', 'Wir kaufen das Ticket', 'Hast du einen Stift?']],
                    ['title' => 'Vocabulario de uso inmediato', 'bullets' => ['der Kaffee', 'die Karte', 'das Ticket', 'der Supermarkt', 'die Apotheke']],
                    ['title' => 'Modelo de compra', 'example' => 'Ich hätte gern einen Kaffee und eine Brezel, bitte.'],
                ],
                'tip' => 'Introduce el acusativo dentro de escenas de compra y no como una tabla aislada.',
            ],
            [
                'titulo' => 'Modalverben y peticiones educadas',
                'duracion' => 17,
                'intro' => 'Con koennen, muessen y moechten el alumno gana margen para pedir, preguntar permiso y decir necesidad.',
                'sections' => [
                    ['title' => 'Verbos utiles', 'bullets' => ['ich kann', 'ich muss', 'ich moechte', 'Kann ich?', 'Muss ich?']],
                    ['title' => 'Frases de servicio', 'bullets' => ['Kann ich mit Karte zahlen?', 'Ich moechte bestellen.', 'Wo kann ich ein Ticket kaufen?']],
                    ['title' => 'Modelo de interaccion', 'example' => 'Entschuldigung, kann ich hier mit Karte zahlen?'],
                ],
                'tip' => 'En niveles bajos, una sola estructura cortés bien dominada vale mas que diez formulas decorativas.',
            ],
            [
                'titulo' => 'Ciudad, direcciones y verbos separables',
                'duracion' => 18,
                'intro' => 'Moverse por una ciudad exige preposiciones simples, referencias espaciales y algunos verbos que en aleman cambian de posicion.',
                'sections' => [
                    ['title' => 'Frases de orientacion', 'bullets' => ['Wo ist der Bahnhof?', 'Gehen Sie geradeaus.', 'Links, rechts, gegenueber', 'Der Bus faehrt um acht Uhr ab.']],
                    ['title' => 'Verbos frecuentes', 'bullets' => ['ankommen', 'abfahren', 'einkaufen', 'aufstehen']],
                    ['title' => 'Modelo de direccion', 'example' => 'Der Bahnhof ist neben dem Hotel und der Zug faehrt um 18 Uhr ab.'],
                ],
                'tip' => 'Los verbos separables se fijan mas rapido si aparecen siempre con una hora, un lugar o una rutina concreta.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Comprar y pedir con naturalidad', 'descripcion' => 'Selecciona la opcion adecuada en escenas de compra.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la mejor opcion.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Elige la respuesta mas natural.', 'preguntas' => [['texto' => 'Forma correcta de pedir algo:', 'opciones' => [['texto' => 'Ich moechte einen Tee, bitte.', 'es_correcta' => true], ['texto' => 'Ich moechten Tee bitte.', 'es_correcta' => false], ['texto' => 'Tee ich bitte moechte.', 'es_correcta' => false]]], ['texto' => 'Pregunta correcta para pagar con tarjeta:', 'opciones' => [['texto' => 'Kann ich mit Karte zahlen?', 'es_correcta' => true], ['texto' => 'Kann ich Karte zahlen mit?', 'es_correcta' => false], ['texto' => 'Ich Karte kann zahlen?', 'es_correcta' => false]]], ['texto' => 'Articulo correcto en acusativo:', 'opciones' => [['texto' => 'einen', 'es_correcta' => true], ['texto' => 'einem', 'es_correcta' => false], ['texto' => 'einer', 'es_correcta' => false]]]]]],
            ['titulo' => 'Respuesta corta con modal', 'descripcion' => 'Completa la frase con un verbo modal.', 'tipo' => 'respuesta_corta', 'instrucciones' => 'Escribe una sola palabra.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => ['pregunta' => 'Completa: Ich ____ heute in die Stadt gehen.', 'respuesta_correcta' => 'kann', 'respuestas_correctas' => ['kann'], 'placeholder' => 'Escribe una palabra']],
            ['titulo' => 'Ordena la direccion', 'descripcion' => 'Reconstruye una instruccion de ciudad.', 'tipo' => 'ordenar_palabras', 'instrucciones' => 'Ordena las palabras.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => [['id' => 'city_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Der', 'Bahnhof', 'ist', 'gegenueber', 'dem', 'Museum.']]]],
            ['titulo' => 'Tu micro rutina en aleman', 'descripcion' => 'Escribe una rutina breve con una accion diaria.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 60 a 80 palabras.', 'puntos' => 18, 'tiempo' => 10, 'contenido' => ['tema' => 'Describe una pequena rutina diaria e incluye una accion de compra, transporte o ciudad.', 'min_palabras' => 60]],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel B2: debate, matices y lenguaje abstracto',
        'descripcion' => 'Hipotesis, pasiva avanzada, nominalizacion y argumentacion en temas academicos, sociales y culturales.',
        'duracion' => 175,
        'teoria' => [
            [
                'titulo' => 'Condicionales irreales y pasiva con funcion argumentativa',
                'duracion' => 22,
                'intro' => 'B2 ya exige plantear hipotesis, evaluar escenarios y construir argumentos con una sintaxis mas compacta.',
                'sections' => [
                    ['title' => 'Recursos clave', 'bullets' => ['Wenn ich Zeit haette, wuerde ich...', 'Wenn ich gekommen waere, haette ich...', 'Das Gesetz wurde verabschiedet.', 'Das Problem kann geloest werden.']],
                    ['title' => 'Conectores utiles', 'bullets' => ['zwar... aber', 'einerseits... andererseits', 'je... desto', 'dennoch']],
                    ['title' => 'Modelo B2', 'example' => 'Einerseits spart Technologie Zeit, andererseits schafft sie neue Abhaengigkeiten.'],
                ],
                'tip' => 'No ensenes la condicion irreal sola; usala para debatir decisiones reales.',
            ],
            [
                'titulo' => 'Nominalisierung, participios y densidad expresiva',
                'duracion' => 18,
                'intro' => 'La marca B2 no es sonar rebuscado, sino poder compactar ideas sin perder claridad.',
                'sections' => [
                    ['title' => 'Patrones frecuentes', 'bullets' => ['das Lernen', 'die Entwicklung', 'der steigende Druck', 'die von Experten entwickelte Loesung']],
                    ['title' => 'Para que sirve', 'bullets' => ['resumen academico', 'informe', 'comentario de prensa', 'argumento formal']],
                    ['title' => 'Modelo compacto', 'example' => 'Die zunehmende Digitalisierung veraendert sowohl den Arbeitsmarkt als auch den Bildungsbereich.'],
                ],
                'tip' => 'Primero busca claridad semantica; la densidad sintactica viene despues.',
            ],
            [
                'titulo' => 'Lexico de academia, negocios y cultura',
                'duracion' => 18,
                'intro' => 'B2 abre debates mas abstractos sobre investigacion, politica, economia y cultura sin depender siempre de ejemplos domesticos.',
                'sections' => [
                    ['title' => 'Campos B2', 'bullets' => ['Hypothese, Analyse, Quelle, Forschung', 'Vertrag, Verhandlung, Strategie', 'Demokratie, Verantwortung, Globalisierung']],
                    ['title' => 'Temas de debate', 'bullets' => ['IA y automatizacion', 'desigualdad social', 'arte y memoria historica', 'cambio climatico']],
                    ['title' => 'Meta B2', 'example' => 'Somit laesst sich sagen, dass Digitalisierung Chancen schafft, jedoch auch neue soziale Spannungen erzeugt.'],
                ],
                'tip' => 'El salto a B2 se nota cuando el alumno sostiene una posicion y tambien sabe matizarla.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Matiza tu argumento B2', 'descripcion' => 'Escoge la estructura mas natural.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la mejor opcion.', 'puntos' => 18, 'tiempo' => 7, 'contenido' => ['pregunta_global' => 'Selecciona la opcion correcta.', 'preguntas' => [['texto' => '____ mehr man liest, desto besser schreibt man.', 'opciones' => [['texto' => 'Je', 'es_correcta' => true], ['texto' => 'Als', 'es_correcta' => false], ['texto' => 'Doch', 'es_correcta' => false]]], ['texto' => 'Estructura irreal correcta:', 'opciones' => [['texto' => 'Wenn ich Zeit haette, wuerde ich mehr reisen.', 'es_correcta' => true], ['texto' => 'Wenn ich Zeit habe, wuerde ich mehr reiste.', 'es_correcta' => false], ['texto' => 'Wenn ich haette Zeit, ich wuerde reisen.', 'es_correcta' => false]]], ['texto' => 'Conector de contraste equilibrado:', 'opciones' => [['texto' => 'einerseits... andererseits', 'es_correcta' => true], ['texto' => 'weil... deshalb', 'es_correcta' => false], ['texto' => 'ob... oder', 'es_correcta' => false]]]]]],
            ['titulo' => 'Ordena el argumento complejo', 'descripcion' => 'Reconstruye una frase de opinion con contraste.', 'tipo' => 'ordenar_palabras', 'instrucciones' => 'Ordena la frase.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => [['id' => 'b2_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Einerseits', 'ist', 'digitale', 'Bildung', 'flexibel,', 'andererseits', 'braucht', 'sie', 'mehr', 'Selbstdisziplin.']]]],
            ['titulo' => 'Escucha academica B2', 'descripcion' => 'Escucha una frase argumentativa y escribela.', 'tipo' => 'escucha', 'instrucciones' => 'Escucha y transcribe.', 'puntos' => 20, 'tiempo' => 8, 'contenido' => ['texto_tts' => 'Einerseits beschleunigt kuenstliche Intelligenz viele Prozesse, andererseits wirft sie ernste ethische Fragen auf.', 'transcripcion' => 'Einerseits beschleunigt kuenstliche Intelligenz viele Prozesse, andererseits wirft sie ernste ethische Fragen auf.']],
            ['titulo' => 'Ensayo corto B2', 'descripcion' => 'Redacta una opinion argumentada.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 180 a 220 palabras.', 'puntos' => 30, 'tiempo' => 18, 'contenido' => ['tema' => 'Escribe una opinion argumentada sobre IA, globalizacion o democracia digital.', 'min_palabras' => 180]],
        ],
    ];

    $blueprint['lessons'][] = [
        'titulo' => 'Nivel C1: precision, registro y ruta de consolidacion',
        'descripcion' => 'Registro, discurso referido, formacion de palabras, certificaciones y plan de estudio para sostener un C1 usable.',
        'duracion' => 165,
        'teoria' => [
            [
                'titulo' => 'Registro, Konjunktiv y precision discursiva',
                'duracion' => 22,
                'intro' => 'C1 no consiste en acumular palabras raras, sino en elegir el registro correcto y mantener precision bajo presion academica o profesional.',
                'sections' => [
                    ['title' => 'Recursos C1', 'bullets' => ['Konjunktiv I para discurso referido', 'Vorfeld y Nachfeld', 'marcadores como folglich, allerdings, demnach', 'distancia y matiz en textos formales']],
                    ['title' => 'Contraste de registros', 'bullets' => ['Umgangssprache frente a Hochdeutsch', 'comentario informal frente a informe formal', 'respuesta emocional frente a posicion razonada']],
                    ['title' => 'Modelo C1', 'example' => 'Die Expertin betonte, die Reform sei notwendig, obwohl ihre Umsetzung Zeit in Anspruch nehmen werde.'],
                ],
                'tip' => 'En C1 vale mas la precision argumentativa que la ornamentacion constante.',
            ],
            [
                'titulo' => 'Word formation y lectura academica',
                'duracion' => 18,
                'intro' => 'La formacion de palabras acelera la comprension de textos densos y permite producir con mayor economia.',
                'sections' => [
                    ['title' => 'Prefijos y sufijos utiles', 'bullets' => ['be-, ver-, ent-, miss-, zer-', '-ung, -heit, -keit, -schaft, -tum']],
                    ['title' => 'Lectura guiada', 'bullets' => ['identificar tesis', 'seguir concesiones', 'marcar causa y consecuencia', 'detectar valoracion del autor']],
                    ['title' => 'Modelo analitico', 'example' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Faehigkeit, unterschiedliche Perspektiven praezise einzuordnen.'],
                ],
                'tip' => 'Leer mejor en C1 significa mapear estructura, no solo traducir palabra por palabra.',
            ],
            [
                'titulo' => 'Certificaciones, ritmo semanal y plan de 90 dias',
                'duracion' => 16,
                'intro' => 'Una ruta larga necesita metodologia visible. El alumno avanza mas si sabe que practica cada semana y como se mide.',
                'sections' => [
                    ['title' => 'Certificaciones comunes', 'bullets' => ['Goethe', 'telc', 'OeSD', 'TestDaF', 'DSH']],
                    ['title' => 'Semana util', 'bullets' => ['Lunes: gramatica y correccion', 'Martes: vocabulario activo', 'Miercoles: escucha', 'Jueves: lectura', 'Viernes: escritura', 'Sabado: conversacion', 'Domingo: repaso']],
                    ['title' => 'Meta final', 'example' => 'En 90 dias quiero consolidar escucha academica, escribir mejor y sostener discusiones de veinte minutos.'],
                ],
                'tip' => 'El progreso estable en aleman sale de bloques pequenos y constantes, no de sesiones heroicas aisladas.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Registro y precision C1', 'descripcion' => 'Elige la formulacion mas precisa.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Selecciona la opcion mas adecuada.', 'puntos' => 20, 'tiempo' => 8, 'contenido' => ['pregunta_global' => 'Selecciona la opcion mas precisa.', 'preguntas' => [['texto' => 'Conector formal de consecuencia:', 'opciones' => [['texto' => 'folglich', 'es_correcta' => true], ['texto' => 'y luego', 'es_correcta' => false], ['texto' => 'naja', 'es_correcta' => false]]], ['texto' => 'Frase con distancia discursiva:', 'opciones' => [['texto' => 'Er erklaerte, er sei mit der Entscheidung nicht einverstanden.', 'es_correcta' => true], ['texto' => 'Er sagt, nein.', 'es_correcta' => false], ['texto' => 'Er war so nein.', 'es_correcta' => false]]], ['texto' => 'Sufijo que forma abstractos frecuentes:', 'opciones' => [['texto' => '-keit', 'es_correcta' => true], ['texto' => '-los', 'es_correcta' => false], ['texto' => '-bar', 'es_correcta' => false]]]]]],
            ['titulo' => 'Escucha avanzada C1', 'descripcion' => 'Escucha una frase academica y registrala con precision.', 'tipo' => 'escucha', 'instrucciones' => 'Escucha y escribe la frase exacta.', 'puntos' => 22, 'tiempo' => 9, 'contenido' => ['texto_tts' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Faehigkeit, unterschiedliche Perspektiven praezise einzuordnen.', 'transcripcion' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Faehigkeit, unterschiedliche Perspektiven praezise einzuordnen.']],
            ['titulo' => 'Tu plan realista de 90 dias', 'descripcion' => 'Escribe un plan de consolidacion para seguir avanzando.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 140 a 180 palabras.', 'puntos' => 22, 'tiempo' => 14, 'contenido' => ['tema' => 'Describe tu plan de estudio de 90 dias con objetivos, recursos y formas de medir tu avance.', 'min_palabras' => 140]],
            ['titulo' => 'Chequeo final de certificaciones', 'descripcion' => 'Decide si la afirmacion es correcta.', 'tipo' => 'verdadero_falso', 'instrucciones' => 'Elige verdadero o falso.', 'puntos' => 10, 'tiempo' => 3, 'contenido' => ['pregunta' => 'TestDaF y DSH aparecen con frecuencia en rutas vinculadas a estudios universitarios en Alemania.', 'respuesta_correcta' => 'Verdadero']],
        ],
    ];

    return $blueprint;
}
