<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=adapa;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function theory_html(string $intro, array $sections, string $tip): string {
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

function theory_blocks(string $intro, array $sections, string $tip): array {
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
            $blocks[] = [
                'tipo_bloque' => str_contains(mb_strtolower($section['title'], 'UTF-8'), 'vocabulario') ? 'vocabulario' : 'explicacion',
                'titulo' => $section['title'],
                'contenido' => implode("\n", array_map(fn($item) => '- ' . $item, $section['bullets'])),
                'idioma_bloque' => str_contains(mb_strtolower($section['title'], 'UTF-8'), 'frases') ? 'aleman' : 'espanol',
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

$course = [
    'instancia_id' => 1,
    'creado_por' => 13,
    'titulo' => 'Aleman de Cero a Heroe: Ruta completa A1-C1',
    'descripcion' => 'Curso integral de aleman para hispanohablantes desde supervivencia A1 hasta dominio C1. Combina gramatica, vocabulario por contextos, metodologia de estudio, certificaciones y actividades interactivas por nivel.',
    'idioma' => 'aleman',
    'idioma_objetivo' => 'aleman',
    'idioma_ensenanza' => 'espanol',
    'nivel_cefr' => 'A1',
    'nivel_cefr_desde' => 'A1',
    'nivel_cefr_hasta' => 'C1',
    'modalidad' => 'perpetuo',
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin' => null,
    'duracion_semanas' => 72,
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
];

$lessons = [
    [
        'titulo' => 'Nivel A1: supervivencia, presente y situaciones basicas',
        'descripcion' => 'Presentarte, comprar, pedir comida y moverte con frases esenciales, articulos y presente.',
        'duracion' => 110,
        'teoria' => [
            [
                'titulo' => 'Pronunciacion, articulos y verbos clave',
                'duracion' => 20,
                'intro' => 'A1 arranca con alfabeto, umlauts y los pilares que mas sostienen la produccion inicial: der, die, das; ich, du, Sie; sein y haben.',
                'sections' => [
                    ['title' => 'Elementos fundamentales', 'bullets' => ['ä, ö, ü, ß', 'ch, sch, sp, st', 'der, die, das', 'ich, du, Sie', 'sein, haben, werden']],
                    ['title' => 'Casos y negacion', 'bullets' => ['Nominativo = sujeto', 'Acusativo = objeto directo', 'nicht para verbos y adjetivos', 'kein para sustantivos']],
                    ['title' => 'Frases de arranque', 'bullets' => ['Guten Tag', 'Ich heiße Ana', 'Ich komme aus Chile'], 'example' => 'Guten Tag, ich heiße Laura und ich komme aus Peru.'],
                ],
                'tip' => 'En A1 se aprende articulo + palabra como un solo bloque, no como dos piezas separadas.',
            ],
            [
                'titulo' => 'A1 comunicativo: restaurante, compras y direcciones',
                'duracion' => 18,
                'intro' => 'El valor del A1 es poder sobrevivir: pedir algo, preguntar precio, ubicar un lugar y hablar de tu familia o ciudad.',
                'sections' => [
                    ['title' => 'Temas de conversacion', 'bullets' => ['Wie heißt du?', 'Woher kommst du?', 'Wie alt bist du?', 'Wie viel kostet das?', 'Wo ist der Bahnhof?']],
                    ['title' => 'Vocabulario esencial', 'bullets' => ['Familie: Mutter, Vater, Bruder, Schwester', 'Essen: Brot, Wasser, Kaffee, Apfel', 'Orte: Haus, Stadt, Schule, Arbeit']],
                    ['title' => 'Dialogo minimo', 'example' => 'Ich hätte gern einen Kaffee, bitte. Die Rechnung, bitte.'],
                ],
                'tip' => 'Si el alumno puede pedir, comprar y ubicarse, ya siente progreso tangible.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Saludos y presentaciones A1', 'descripcion' => 'Selecciona la respuesta correcta en escenas basicas.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la opcion correcta.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Selecciona la respuesta correcta.', 'preguntas' => [['texto' => 'Frase correcta para presentarte:', 'opciones' => [['texto' => 'Ich heiße Marta.', 'es_correcta' => true], ['texto' => 'Ich heißt Marta.', 'es_correcta' => false], ['texto' => 'Ich name Marta.', 'es_correcta' => false]]], ['texto' => 'Frase correcta para pedir la cuenta:', 'opciones' => [['texto' => 'Die Rechnung, bitte.', 'es_correcta' => true], ['texto' => 'Rechnung links.', 'es_correcta' => false], ['texto' => 'Bitte ich Rechnung.', 'es_correcta' => false]]]]]],
            ['titulo' => 'Ordena la autopresentacion', 'descripcion' => 'Reconstruye una frase correcta.', 'tipo' => 'ordenar_palabras', 'instrucciones' => 'Ordena las palabras.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => [['id' => 'a1_o1', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'komme', 'aus', 'Chile.']]]],
            ['titulo' => 'Completa con sein o kein', 'descripcion' => 'Escribe la palabra faltante.', 'tipo' => 'completar_oracion', 'instrucciones' => 'Escribe solo la palabra que falta.', 'puntos' => 12, 'tiempo' => 5, 'contenido' => [['id' => 'a1_g1', 'oracion' => 'Ich ____ 24 Jahre alt.', 'respuesta_correcta' => 'bin'], ['id' => 'a1_g2', 'oracion' => 'Ich habe ____ Geld.', 'respuesta_correcta' => 'kein']]],
        ],
    ],
    [
        'titulo' => 'Nivel A2: pasado, dativo y vida cotidiana',
        'descripcion' => 'Perfekt, Prateritum, dativo, comparativos y contextos como vivienda, salud, ropa y viajes.',
        'duracion' => 125,
        'teoria' => [
            [
                'titulo' => 'Perfekt, Prateritum y dativo funcional',
                'duracion' => 22,
                'intro' => 'A2 ya necesita narrar experiencias pasadas y moverse con estructuras de dativo para dar, ayudar, ir con alguien o venir de algun sitio.',
                'sections' => [
                    ['title' => 'Pasado cotidiano', 'bullets' => ['Ich habe gegessen', 'Er ist gegangen', 'Ich war, ich hatte', 'anfangen -> Ich fange an']],
                    ['title' => 'Dativo y preposiciones', 'bullets' => ['dem Mann, der Frau, dem Kind', 'aus, bei, mit, nach, seit, von, zu', 'Wechselpräpositionen con dativo = posicion']],
                    ['title' => 'Modelo A2', 'example' => 'Gestern bin ich zum Arzt gegangen, weil ich Fieber hatte.'],
                ],
                'tip' => 'Perfekt sirve para hablar; Prateritum te permite leer sin perder el hilo.',
            ],
            [
                'titulo' => 'Adjetivos, comparativos y campos lexicales A2',
                'duracion' => 18,
                'intro' => 'A2 amplía el mundo del alumno: casa, ropa, cuerpo, salud, viajes y descripciones más ricas.',
                'sections' => [
                    ['title' => 'Estructuras utiles', 'bullets' => ['ein guter Mann / die gute Frau', 'groß -> größer -> am größten', 'gut -> besser -> am besten']],
                    ['title' => 'Campos lexicales', 'bullets' => ['Wohnung, Küche, Bad', 'Hose, Schuhe, Jacke', 'Kopf, Bauch, Rücken', 'Bahnhof, Zug, Hotel']],
                    ['title' => 'Frases A2', 'bullets' => ['Meine Wohnung hat zwei Zimmer.', 'Mir tut der Kopf weh.', 'Ich suche eine Hose in Größe M.'], 'example' => 'Meine Wohnung ist kleiner, aber heller als die alte.'],
                ],
                'tip' => 'A2 gana fuerza cuando el alumno describe mejor su mundo, no cuando acumula reglas sin escena.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Perfekt o dativo', 'descripcion' => 'Elige la opcion correcta.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la mejor respuesta.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Elige la opcion correcta.', 'preguntas' => [['texto' => 'Gestern ____ ich ins Kino gegangen.', 'opciones' => [['texto' => 'bin', 'es_correcta' => true], ['texto' => 'habe', 'es_correcta' => false], ['texto' => 'war', 'es_correcta' => false]]], ['texto' => 'Ich gebe ____ Mann das Buch.', 'opciones' => [['texto' => 'dem', 'es_correcta' => true], ['texto' => 'den', 'es_correcta' => false], ['texto' => 'der', 'es_correcta' => false]]]]]],
            ['titulo' => 'Respuesta corta de salud', 'descripcion' => 'Completa una frase sobre sintomas.', 'tipo' => 'respuesta_corta', 'instrucciones' => 'Escribe una sola palabra.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => ['pregunta' => 'Completa: Mir tut der ____ weh.', 'respuesta_correcta' => 'Kopf', 'respuestas_correctas' => ['Kopf'], 'placeholder' => 'Escribe una palabra']],
            ['titulo' => 'Email informal A2', 'descripcion' => 'Describe tu vivienda o tus planes.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 80 a 100 palabras.', 'puntos' => 20, 'tiempo' => 12, 'contenido' => ['tema' => 'Redacta un email informal sobre tu vivienda o tus planes futuros.', 'min_palabras' => 80]],
        ],
    ],
    [
        'titulo' => 'Nivel B1: independencia, subordinadas y opinion',
        'descripcion' => 'Subordinadas, relativas, Konjunktiv II, pasiva basica y temas de trabajo, medios y sociedad.',
        'duracion' => 155,
        'teoria' => [
            [
                'titulo' => 'Subordinadas, relativas y Konjunktiv II',
                'duracion' => 24,
                'intro' => 'B1 marca el paso a la independencia: conectar ideas, dar razones, justificar opiniones y pedir con mayor cortesía.',
                'sections' => [
                    ['title' => 'Estructuras B1', 'bullets' => ['dass, weil, obwohl, wenn, als', 'Der Mann, der dort steht...', 'Ich würde gern nach Berlin fahren', 'Könnten Sie mir helfen?']],
                    ['title' => 'Pasiva y pluscuamperfecto', 'bullets' => ['Das Haus wird gebaut', 'Das Haus wurde gebaut', 'Ich hatte gegessen', 'Er war gegangen']],
                    ['title' => 'Modelo B1', 'example' => 'Ich denke, dass soziale Medien nützlich sind, obwohl sie auch Probleme schaffen.'],
                ],
                'tip' => 'La subordinada se fija cuando sirve para defender una opinion real del alumno.',
            ],
            [
                'titulo' => 'Trabajo, educacion, medios y medio ambiente',
                'duracion' => 18,
                'intro' => 'El lexico B1 ya sale del hogar: entrevistas, universidad, noticias, cultura y medio ambiente se vuelven temas frecuentes.',
                'sections' => [
                    ['title' => 'Campos tematicos', 'bullets' => ['Bewerbung, Gehalt, Kollege, Chef', 'Studium, Prüfung, Universität', 'Nachrichten, Zeitung, Internet', 'Umwelt, Recycling, Energie']],
                    ['title' => 'Temas de conversacion', 'bullets' => ['Describir un trabajo o una entrevista', 'Hablar de noticias y redes sociales', 'Opinar sobre reciclaje y clima']],
                    ['title' => 'Meta B1', 'example' => 'Ich möchte in Deutschland studieren, weil ich internationale Erfahrung sammeln will.'],
                ],
                'tip' => 'B1 se siente real cuando ya puedes sostener una conversacion de 15 minutos sin refugiarte en ingles.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Conecta la idea en B1', 'descripcion' => 'Elige el conector o la forma adecuada.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Selecciona la mejor opcion.', 'puntos' => 15, 'tiempo' => 6, 'contenido' => ['pregunta_global' => 'Elige la mejor opcion.', 'preguntas' => [['texto' => 'Ich denke, ____ er heute nicht kommt.', 'opciones' => [['texto' => 'dass', 'es_correcta' => true], ['texto' => 'der', 'es_correcta' => false], ['texto' => 'wie', 'es_correcta' => false]]], ['texto' => 'Forma cortés correcta:', 'opciones' => [['texto' => 'Könnten Sie mir helfen?', 'es_correcta' => true], ['texto' => 'Kann mir Sie helfen?', 'es_correcta' => false], ['texto' => 'Hilfst du mir könnten?', 'es_correcta' => false]]]]]],
            ['titulo' => 'Completa la pasiva', 'descripcion' => 'Escribe la palabra faltante.', 'tipo' => 'completar_oracion', 'instrucciones' => 'Escribe solo la palabra que falta.', 'puntos' => 12, 'tiempo' => 5, 'contenido' => [['id' => 'b1_g1', 'oracion' => 'Das Haus ____ gebaut.', 'respuesta_correcta' => 'wird'], ['id' => 'b1_g2', 'oracion' => 'Wenn ich Zeit hätte, ____ ich mehr lesen.', 'respuesta_correcta' => 'würde']]],
            ['titulo' => 'Opinion guiada B1', 'descripcion' => 'Redacta una opinion breve y justificada.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 120 a 160 palabras.', 'puntos' => 25, 'tiempo' => 15, 'contenido' => ['tema' => 'Da tu opinion sobre redes sociales, educacion o medio ambiente usando al menos una subordinada.', 'min_palabras' => 120]],
        ],
    ],
    [
        'titulo' => 'Nivel B2: debate, matices y estructuras complejas',
        'descripcion' => 'Condicionales irreales, pasiva avanzada, nominalizacion y temas de economia, ciencia, politica y arte.',
        'duracion' => 185,
        'teoria' => [
            [
                'titulo' => 'Hipotesis, pasiva avanzada y conectores de alto nivel',
                'duracion' => 24,
                'intro' => 'B2 exige interactuar con espontaneidad, matizar posiciones y sostener argumentos largos con estructuras más densas.',
                'sections' => [
                    ['title' => 'Recursos clave B2', 'bullets' => ['Wenn ich Zeit hätte, würde ich...', 'Wenn ich gekommen wäre, hätte ich...', 'Das Buch wurde von Goethe geschrieben', 'zwar... aber, einerseits... andererseits, je... desto']],
                    ['title' => 'Nominalisierung y participios', 'bullets' => ['Das Laufen macht Spaß', 'Der auf dem Sofa liegende Mann', 'Die von ihm geschriebene Geschichte']],
                    ['title' => 'Modelo B2', 'example' => 'Einerseits beschleunigt künstliche Intelligenz viele Prozesse, andererseits wirft sie ernste ethische Fragen auf.'],
                ],
                'tip' => 'En B2 importa más la claridad del argumento que la ornamentacion constante.',
            ],
            [
                'titulo' => 'Lexico academico, negocios y debates culturales',
                'duracion' => 20,
                'intro' => 'B2 abre el acceso a vocabulario abstracto y profesional: economia, investigacion, politica, arte y filosofia aplicada.',
                'sections' => [
                    ['title' => 'Campos B2', 'bullets' => ['Hypothese, Analyse, Quelle, Forschung', 'Vertrag, Verhandlung, Strategie', 'Demokratie, Menschenrechte, Globalisierung']],
                    ['title' => 'Temas de conversacion', 'bullets' => ['IA y automatizacion', 'Desigualdad economica', 'Arte y literatura', 'Sistemas politicos y derechos humanos']],
                    ['title' => 'Meta B2', 'example' => 'Somit lässt sich sagen, dass Globalisierung Chancen schafft, jedoch auch neue soziale Spannungen erzeugt.'],
                ],
                'tip' => 'El salto a B2 se nota cuando el alumno puede matizar, conceder y concluir con naturalidad.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Matiza tu argumento B2', 'descripcion' => 'Escoge la estructura mas natural.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Marca la mejor opcion.', 'puntos' => 18, 'tiempo' => 7, 'contenido' => ['pregunta_global' => 'Selecciona la opcion correcta.', 'preguntas' => [['texto' => '____ mehr man liest, desto besser schreibt man.', 'opciones' => [['texto' => 'Je', 'es_correcta' => true], ['texto' => 'Als', 'es_correcta' => false], ['texto' => 'Doch', 'es_correcta' => false]]], ['texto' => 'Estructura irreal correcta:', 'opciones' => [['texto' => 'Wenn ich Zeit hätte, würde ich mehr reisen.', 'es_correcta' => true], ['texto' => 'Wenn ich Zeit habe, würde ich mehr reiste.', 'es_correcta' => false], ['texto' => 'Wenn ich hätte Zeit, ich würde reisen.', 'es_correcta' => false]]]]]],
            ['titulo' => 'Ordena el argumento', 'descripcion' => 'Reconstruye una frase compleja de opinion.', 'tipo' => 'ordenar_palabras', 'instrucciones' => 'Ordena la frase.', 'puntos' => 10, 'tiempo' => 4, 'contenido' => [['id' => 'b2_o1', 'instruction' => 'Ordena la frase.', 'items' => ['Einerseits', 'ist', 'Technologie', 'nützlich,', 'andererseits', 'schafft', 'sie', 'Probleme.']]]],
            ['titulo' => 'Ensayo corto B2', 'descripcion' => 'Redacta una opinion argumentada.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 180 a 220 palabras.', 'puntos' => 30, 'tiempo' => 18, 'contenido' => ['tema' => 'Escribe una opinion argumentada sobre IA, globalizacion o democracia digital.', 'min_palabras' => 180]],
        ],
    ],
    [
        'titulo' => 'Nivel C1 + metodologia, certificaciones y plan de estudio',
        'descripcion' => 'Registro, sintaxis expandida, formacion de palabras, recursos externos, certificaciones oficiales y checklist final de progreso.',
        'duracion' => 170,
        'teoria' => [
            [
                'titulo' => 'C1: registro, sintaxis y word formation',
                'duracion' => 24,
                'intro' => 'C1 exige comprender casi todo, expresarte con flexibilidad y adaptar el idioma al contexto social, academico o profesional.',
                'sections' => [
                    ['title' => 'Recursos C1', 'bullets' => ['Konjunktiv I y II avanzados', 'Vorfeld, Nachfeld y ellipse', 'Umgangssprache vs Hochdeutsch', 'be-, ver-, ent-, miss-, zer-', '-ung, -heit, -keit, -schaft, -tum']],
                    ['title' => 'Debates C1', 'bullets' => ['Philosophie und Religion', 'Recht und Justiz', 'Medizin und Ethik', 'Interkulturelle Kommunikation']],
                    ['title' => 'Modelo C1', 'example' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Fähigkeit, unterschiedliche Perspektiven präzise einzuordnen.'],
                ],
                'tip' => 'C1 no es acumular palabras difíciles, sino elegir el registro correcto sin perder precisión.',
            ],
            [
                'titulo' => 'Metodo semanal, recursos y certificaciones oficiales',
                'duracion' => 18,
                'intro' => 'Una ruta larga necesita metodo: estudio diario breve, repaso espaciado, escucha frecuente, escritura y objetivos medibles por nivel.',
                'sections' => [
                    ['title' => 'Plan semanal recomendado', 'bullets' => ['Lunes: gramatica', 'Martes: vocabulario + Anki', 'Miercoles: comprension auditiva', 'Jueves: lectura', 'Viernes: escritura', 'Sabado: conversacion', 'Domingo: repaso + serie o pelicula']],
                    ['title' => 'Herramientas y recursos', 'bullets' => ['Anki', 'dict.cc o Leo', 'Forvo', 'LangCorrect', 'Tandem y HelloTalk', 'Goethe, ÖSD, telc, TestDaF, DSH']],
                    ['title' => 'Checklist final', 'bullets' => ['A1: presentarte y comprar', 'B1: conversar 15 min y escribir un email formal', 'C1: debatir y escribir un ensayo largo']],
                ],
                'tip' => 'Veinte minutos diarios con intención clara sostienen mejor una ruta larga que una disciplina intermitente.',
            ],
        ],
        'actividades' => [
            ['titulo' => 'Registro y precision C1', 'descripcion' => 'Elige la formulacion mas precisa.', 'tipo' => 'opcion_multiple', 'instrucciones' => 'Selecciona la opcion mas adecuada.', 'puntos' => 20, 'tiempo' => 8, 'contenido' => ['pregunta_global' => 'Selecciona la opcion mas precisa.', 'preguntas' => [['texto' => 'Conector formal de consecuencia:', 'opciones' => [['texto' => 'folglich', 'es_correcta' => true], ['texto' => 'y luego', 'es_correcta' => false], ['texto' => 'naja', 'es_correcta' => false]]], ['texto' => 'Formula con distancia discursiva:', 'opciones' => [['texto' => 'Er erklärte, er sei mit der Entscheidung nicht einverstanden.', 'es_correcta' => true], ['texto' => 'Er sagt, nein.', 'es_correcta' => false], ['texto' => 'Er war so nein.', 'es_correcta' => false]]]]]],
            ['titulo' => 'Escucha avanzada C1', 'descripcion' => 'Escucha una frase academica y registra la idea central.', 'tipo' => 'escucha', 'instrucciones' => 'Escucha y escribe la idea principal.', 'puntos' => 22, 'tiempo' => 9, 'contenido' => ['texto_tts' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Fähigkeit, unterschiedliche Perspektiven präzise einzuordnen.', 'transcripcion' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Fähigkeit, unterschiedliche Perspektiven präzise einzuordnen.']],
            ['titulo' => 'Tu plan de 90 dias', 'descripcion' => 'Escribe un plan realista para avanzar en aleman.', 'tipo' => 'escritura', 'instrucciones' => 'Escribe 120 a 150 palabras.', 'puntos' => 20, 'tiempo' => 12, 'contenido' => ['tema' => 'Describe tu plan semanal, tus recursos y tus metas para los proximos 90 dias.', 'min_palabras' => 120]],
        ],
    ],
];

$insertCourse = $pdo->prepare('INSERT INTO cursos (instancia_id, creado_por, titulo, descripcion, idioma, idioma_objetivo, idioma_ensenanza, nivel_cefr, nivel_cefr_desde, nivel_cefr_hasta, modalidad, fecha_inicio, fecha_fin, duracion_semanas, es_publico, requiere_codigo, codigo_acceso, tipo_codigo, inscripcion_abierta, fecha_cierre_inscripcion, max_estudiantes, estado, notificar_profesor_completada, notificar_profesor_atascado) VALUES (:instancia_id, :creado_por, :titulo, :descripcion, :idioma, :idioma_objetivo, :idioma_ensenanza, :nivel_cefr, :nivel_cefr_desde, :nivel_cefr_hasta, :modalidad, :fecha_inicio, :fecha_fin, :duracion_semanas, :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :inscripcion_abierta, :fecha_cierre_inscripcion, :max_estudiantes, :estado, :notificar_profesor_completada, :notificar_profesor_atascado)');
$insertLesson = $pdo->prepare('INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (?, ?, ?, ?, ?, 1, "publicada")');
$insertTheory = $pdo->prepare('INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo) VALUES (?, ?, ?, "texto", ?, ?, 0)');
$insertBlock = $pdo->prepare('INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)');
$insertActivity = $pdo->prepare('INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")');
$insertEnrollment = $pdo->prepare('INSERT INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');

$pdo->beginTransaction();
$insertCourse->execute($course);
$courseId = (int) $pdo->lastInsertId();
$lessonCount = 0;
$theoryCount = 0;
$activityCount = 0;

foreach ($lessons as $lessonIndex => $lesson) {
    $insertLesson->execute([$courseId, $lesson['titulo'], $lesson['descripcion'], $lessonIndex + 1, $lesson['duracion']]);
    $lessonId = (int) $pdo->lastInsertId();
    $lessonCount++;

    foreach ($lesson['teoria'] as $theoryIndex => $theory) {
        $insertTheory->execute([$lessonId, $theory['titulo'], theory_html($theory['intro'], $theory['sections'], $theory['tip']), $theory['duracion'], $theoryIndex + 1]);
        $theoryId = (int) $pdo->lastInsertId();
        $theoryCount++;

        foreach (theory_blocks($theory['intro'], $theory['sections'], $theory['tip']) as $blockIndex => $block) {
            $insertBlock->execute([$theoryId, $block['tipo_bloque'], $block['titulo'], $block['contenido'], $block['idioma_bloque'], $block['tts_habilitado'], $blockIndex + 1]);
        }
    }

    foreach ($lesson['actividades'] as $activityIndex => $activity) {
        $insertActivity->execute([$lessonId, $activity['titulo'], $activity['descripcion'], $activity['tipo'], $activity['instrucciones'], json_encode($activity['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $activity['puntos'], $activity['tiempo'], $activityIndex + 1]);
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
