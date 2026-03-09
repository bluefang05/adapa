<?php

declare(strict_types=1);

require_once __DIR__ . '/german_course_expansion_lib.php';

function german_master_find_by_alias(array $items, string $title, string $key = 'aliases'): ?array
{
    foreach ($items as $item) {
        foreach ((array) ($item[$key] ?? []) as $alias) {
            if (mb_strtolower(trim((string) $alias), 'UTF-8') === mb_strtolower(trim($title), 'UTF-8')) {
                return $item;
            }
        }
    }

    return null;
}

function german_master_existing_lessons(): array
{
    $baseLessons = german_course_blueprint()['lessons'];
    $reframes = german_expand_existing_lesson_reframes();
    $additions = german_expand_existing_lesson_additions();
    $durationOverrides = [
        'Nivel A1.1: sonidos, saludos y primer contacto' => 145,
        'Nivel A1.4: compras, ciudad y verbos de accion' => 150,
        'Nivel A2.1: pasado, dativo y vida cotidiana' => 160,
        'Nivel B1.1: opinion, subordinadas y mundo real' => 175,
        'Nivel B2.1: debate, matices y lenguaje abstracto' => 185,
        'Nivel C1.1: precision, registro y ruta de consolidacion' => 195,
    ];

    $lessons = [];
    foreach ($baseLessons as $lesson) {
        $reframe = german_master_find_by_alias($reframes, $lesson['titulo']);
        $addition = german_master_find_by_alias($additions, $lesson['titulo'], 'lesson_aliases');
        $title = $reframe['titulo'] ?? $lesson['titulo'];

        $lesson['titulo'] = $title;
        $lesson['descripcion'] = $reframe['descripcion'] ?? $lesson['descripcion'];
        $lesson['orden'] = (int) ($reframe['orden'] ?? 0);
        $lesson['duracion'] = $durationOverrides[$title] ?? $lesson['duracion'];

        if ($addition) {
            foreach ((array) ($addition['extra_theories'] ?? []) as $theory) {
                $lesson['teoria'][] = $theory;
            }

            foreach ((array) ($addition['extra_activities'] ?? []) as $activity) {
                $lesson['actividades'][] = $activity;
            }
        }

        $lessons[] = $lesson;
    }

    usort($lessons, static fn(array $left, array $right): int => ($left['orden'] ?? 0) <=> ($right['orden'] ?? 0));

    return $lessons;
}

function german_expand_new_lessons_group_one(): array
{
    return array_merge(
        german_expand_new_lessons_group_one_a(),
        german_expand_new_lessons_group_one_b(),
        german_expand_new_lessons_group_one_c()
    );
}

function german_expand_new_lessons_group_two(): array
{
    return array_merge(
        german_expand_new_lessons_group_two_a(),
        german_expand_new_lessons_group_two_b(),
        german_expand_new_lessons_group_two_c()
    );
}

function german_expand_new_lessons_group_one_a(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Nivel A1.2: familia, numeros y preguntas basicas',
                'Familia, nacionalidad, edades, numeros, hora y preguntas de aula para dejar de depender de respuestas sueltas.',
                145,
                [
                    german_expand_theory(
                        'Familia, origen y relaciones cercanas',
                        16,
                        'Despues de presentarte, lo siguiente es hablar de la gente que te rodea. Esta teoria fija parentesco, origen y las primeras frases sobre tu circulo cercano.',
                        [
                            german_expand_section('Vocabulario nuclear', ['die Mutter', 'der Vater', 'die Schwester', 'der Bruder', 'die Eltern', 'die Familie'], null, null, 'aleman'),
                            german_expand_section('Preguntas utiles', ['Hast du Geschwister?', 'Wo wohnt deine Familie?', 'Ist deine Schwester Studentin?'], null, null, 'aleman'),
                            german_expand_section('Modelo base', [], null, 'Ich habe einen Bruder und eine Schwester. Meine Familie wohnt in Santiago.', 'aleman'),
                        ],
                        'Aprende familia como bloques completos: ein Bruder, eine Schwester, meine Eltern.',
                        'Escenario: un companero te pregunta por tu familia en los primeros cinco minutos de clase.',
                        ['Di cuantas personas hay en tu familia.', 'Nombra a un familiar.', 'Di donde vive tu familia.']
                    ),
                    german_expand_theory(
                        'Numeros, edad, telefono y hora',
                        16,
                        'A1 deja de sentirse infantil cuando el alumno puede decir su edad, leer numeros, dar un telefono y comprender la hora sin congelarse.',
                        [
                            german_expand_section('Numeros que necesitas ya', ['eins bis zwanzig', 'dreissig, vierzig, fuenfzig', 'hundert', 'tausend'], null, null, 'aleman'),
                            german_expand_section('Datos practicos', ['Ich bin 24 Jahre alt.', 'Meine Nummer ist ...', 'Es ist halb acht.', 'Der Kurs beginnt um neun.'], null, null, 'aleman'),
                            german_expand_section('Modelo practico', [], null, 'Ich bin neunundzwanzig Jahre alt und mein Termin ist um Viertel nach drei.', 'aleman'),
                        ],
                        'No recites numeros sin contexto: mezclalos siempre con edad, hora, precio o telefono.',
                        'Escenario: te registras en una academia y debes dar edad, telefono y hora disponible.',
                        ['Di tu edad.', 'Di una hora.', 'Di un numero de telefono corto.']
                    ),
                    german_expand_theory(
                        'Preguntas basicas de aula y supervivencia',
                        15,
                        'Una gran parte del arranque real ocurre dentro del aula: pedir repeticion, preguntar como se escribe algo y verificar si entendiste.',
                        [
                            german_expand_section('Frases de supervivencia', ['Wie schreibt man das?', 'Koennen Sie das wiederholen?', 'Was bedeutet das?', 'Ich verstehe nicht ganz.'], null, null, 'aleman'),
                            german_expand_section('Mini respuestas', ['Noch einmal, bitte.', 'Langsamer, bitte.', 'Ja, jetzt verstehe ich.'], null, null, 'aleman'),
                            german_expand_section('Modelo de clase', [], null, 'Entschuldigung, wie schreibt man dieses Wort? Koennen Sie das bitte wiederholen?', 'aleman'),
                        ],
                        'Estas frases no son relleno: son las que permiten seguir aprendiendo sin volver al espanol.',
                        'Escenario: el profesor dicta rapido y necesitas pedir ayuda sin romper la interaccion.',
                        ['Pide repeticion.', 'Pregunta como se escribe una palabra.', 'Di si ahora entiendes.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Familia, numeros y datos personales',
                        'Elige la respuesta mas natural para hablar de familia, edad y hora.',
                        'Selecciona la opcion correcta en cada situacion.',
                        [
                            german_expand_question('Respuesta natural a "Hast du Geschwister?"', 'Ja, ich habe einen Bruder.', 'Ja, ich bin einen Bruder.', 'Ja, ich habe alt.'),
                            german_expand_question('Como dices "Tengo 26 anos"?', 'Ich bin 26 Jahre alt.', 'Ich habe 26 Jahre.', 'Ich mache 26 Jahre alt.'),
                            german_expand_question('Forma correcta para decir las 7:30', 'Es ist halb acht.', 'Es ist sieben und dreissig.', 'Es ist dreissig nach sieben Uhr.'),
                        ],
                        18,
                        7
                    ),
                    german_expand_matching(
                        'Empareja la pregunta con la respuesta',
                        'Relaciona cada pregunta basica con una respuesta breve y natural.',
                        [
                            ['Wie alt bist du?', 'Ich bin 24 Jahre alt.'],
                            ['Wo wohnt deine Familie?', 'Sie wohnt in Valdivia.'],
                            ['Wie schreibt man das?', 'Mit h und ie.'],
                            ['Hast du Geschwister?', 'Ja, eine Schwester.'],
                        ]
                    ),
                    german_expand_fill(
                        'Completa edad, hora y familia',
                        'Escribe solo la palabra que falta.',
                        [
                            ['id' => 'a12_fill_1', 'oracion' => 'Ich ____ 31 Jahre alt.', 'respuesta_correcta' => 'bin'],
                            ['id' => 'a12_fill_2', 'oracion' => 'Meine ____ wohnt in Berlin.', 'respuesta_correcta' => 'Familie'],
                            ['id' => 'a12_fill_3', 'oracion' => 'Der Kurs beginnt ____ acht Uhr.', 'respuesta_correcta' => 'um'],
                        ]
                    ),
                    german_expand_order(
                        'Ordena las presentaciones familiares',
                        'Reconstruye frases cortas sobre familia y datos personales.',
                        [
                            'Meine Schwester studiert in Hamburg.',
                            'Ich habe zwei Brueder.',
                            'Mein Termin ist um zehn Uhr.',
                        ]
                    ),
                    german_expand_pronunciation(
                        'Pronuncia tu ficha personal',
                        'Lee tus datos basicos con ritmo claro y pausas naturales.',
                        [
                            'Ich heisse Camila und ich komme aus Chile.',
                            'Ich bin siebenundzwanzig Jahre alt.',
                            'Meine Familie wohnt in Valparaiso.',
                        ]
                    ),
                    german_expand_listening(
                        'Escucha: registro de estudiante nuevo',
                        'Escucha la presentacion y escribe exactamente lo que oyes.',
                        'Guten Tag, ich heisse Martin, ich bin dreiundzwanzig Jahre alt und mein Deutschkurs ist um neun Uhr.'
                    ),
                ]
            ),
            ['orden' => 2]
        ),
        array_merge(
            german_expand_lesson(
                'Nivel A1.3: rutinas, presente y verbos separables',
                'Rutinas diarias, presente, horarios y verbos separables para contar tu dia y entender acciones frecuentes.',
                150,
                [
                    german_expand_theory(
                        'Rutinas diarias y presente de alta frecuencia',
                        17,
                        'El presente sirve de verdad cuando el alumno puede explicar que hace, cuando lo hace y con que frecuencia. Aqui se fijan los verbos mas utiles del dia normal.',
                        [
                            german_expand_section('Verbos base', ['arbeiten', 'lernen', 'wohnen', 'essen', 'trinken', 'schlafen'], null, null, 'aleman'),
                            german_expand_section('Marcadores utiles', ['jeden Tag', 'am Morgen', 'am Nachmittag', 'am Abend', 'oft', 'manchmal'], null, null, 'aleman'),
                            german_expand_section('Modelo de rutina', [], null, 'Ich arbeite am Vormittag, lerne am Abend und schlafe um elf Uhr.', 'aleman'),
                        ],
                        'Mezcla verbo + momento del dia desde el principio para evitar listas sin accion.',
                        'Escenario: alguien te pregunta como es un dia normal para ti y no basta con decir "bien".',
                        ['Di que haces por la manana.', 'Di que haces por la tarde.', 'Di a que hora duermes.']
                    ),
                    german_expand_theory(
                        'Horarios y verbos separables',
                        16,
                        'Los verbos separables aparecen muy pronto en la vida real. Si se dominan junto a la hora, el alumno gana mucha soltura cotidiana.',
                        [
                            german_expand_section('Bloques separables', ['aufstehen', 'einkaufen', 'fernsehen', 'mitkommen', 'anrufen'], null, null, 'aleman'),
                            german_expand_section('Patrones utiles', ['Ich stehe um sechs Uhr auf.', 'Wir kaufen nach dem Kurs ein.', 'Rufst du mich spaeter an?'], null, null, 'aleman'),
                            german_expand_section('Modelo practico', [], null, 'Am Freitag stehe ich spaet auf und kaufe am Nachmittag ein.', 'aleman'),
                        ],
                        'Entrena siempre los separables en frase completa; memorizarlos solos produce mas errores que ayuda.',
                        'Escenario: explicas tu horario y tus pequenas tareas del dia.',
                        ['Usa aufstehen.', 'Usa einkaufen.', 'Usa anrufen en una pregunta.']
                    ),
                    german_expand_theory(
                        'Gustos, actividades y negacion basica',
                        15,
                        'La rutina se vuelve mas humana cuando el alumno puede decir lo que le gusta, lo que no hace y a que actividades dedica tiempo.',
                        [
                            german_expand_section('Actividades frecuentes', ['Musik hoeren', 'Sport machen', 'lesen', 'kochen', 'Deutsch lernen'], null, null, 'aleman'),
                            german_expand_section('Frases de gusto', ['Ich mag Kaffee.', 'Ich spiele nicht gern Tennis.', 'Am Wochenende lese ich oft.'], null, null, 'aleman'),
                            german_expand_section('Modelo personal', [], null, 'Ich koche gern, aber ich spiele nicht gern Fussball.', 'aleman'),
                        ],
                        'Para sonar natural en A1 basta con una afirmacion simple y una negacion clara bien colocada.',
                        'Escenario: hablas de tus gustos con alguien que acaba de conocerte.',
                        ['Di una actividad que te gusta.', 'Niega una actividad.', 'Anade frecuencia.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Rutinas y presente funcional',
                        'Elige la forma correcta para hablar de tu dia y de tus horarios.',
                        'Marca la opcion mas natural.',
                        [
                            german_expand_question('Forma correcta de "Yo trabajo por la manana"', 'Ich arbeite am Morgen.', 'Ich arbeiten am Morgen.', 'Ich arbeite in Morgen.'),
                            german_expand_question('Colocacion correcta del verbo separable', 'Ich stehe um sieben Uhr auf.', 'Ich aufstehe um sieben Uhr.', 'Ich stehe auf um sieben Uhr auf.'),
                            german_expand_question('Negacion natural', 'Ich spiele nicht gern Tennis.', 'Ich nicht spiele gern Tennis.', 'Ich spiele gern nicht Tennis.'),
                        ]
                    ),
                    german_expand_drag(
                        'Lleva cada verbo al momento mas logico',
                        'Arrastra las acciones al bloque del dia donde suelen aparecer.',
                        [
                            'aufstehen' => 'Morgen',
                            'arbeiten' => 'Vormittag',
                            'einkaufen' => 'Nachmittag',
                            'fernsehen' => 'Abend',
                        ]
                    ),
                    german_expand_fill(
                        'Completa tu dia',
                        'Rellena con la palabra correcta.',
                        [
                            ['id' => 'a13_fill_1', 'oracion' => 'Ich ____ um sechs Uhr auf.', 'respuesta_correcta' => 'stehe'],
                            ['id' => 'a13_fill_2', 'oracion' => 'Wir ____ jeden Tag Deutsch.', 'respuesta_correcta' => 'lernen'],
                            ['id' => 'a13_fill_3', 'oracion' => 'Ich spiele ____ gern Fussball.', 'respuesta_correcta' => 'nicht'],
                        ]
                    ),
                    german_expand_order(
                        'Ordena una rutina diaria',
                        'Reconstruye frases sobre tu dia y tus gustos.',
                        [
                            'Am Morgen trinke ich Kaffee.',
                            'Nach dem Kurs kaufe ich ein.',
                            'Am Abend sehe ich fern.',
                        ]
                    ),
                    german_expand_pronunciation(
                        'Pronuncia tu rutina breve',
                        'Lee tres frases de rutina sin cortar los grupos verbales.',
                        [
                            'Ich stehe um halb sieben auf.',
                            'Am Nachmittag kaufe ich ein.',
                            'Am Abend lerne ich Deutsch und hoere Musik.',
                        ]
                    ),
                    german_expand_writing(
                        'Escribe tu dia tipico',
                        'Redacta una rutina breve usando horas, frecuencia y al menos un verbo separable.',
                        'Describe un dia normal entre semana. Incluye manana, tarde, noche, una actividad que te gusta y una que no te gusta.',
                        70,
                        20,
                        12
                    ),
                ]
            ),
            ['orden' => 3]
        ),
    ];
}

function german_expand_new_lessons_group_one_b(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Nivel A2.2: tramites, citas y movimiento por la ciudad',
                'Citas, retrasos, oficina, transporte y ubicacion para resolver pequenos tramites sin perder claridad.',
                160,
                [
                    german_expand_theory(
                        'Citas, retrasos y cambios de horario',
                        17,
                        'A2 gana mucha utilidad cuando el alumno puede fijar una cita, avisar un retraso y mover un horario sin sonar caotico.',
                        [
                            german_expand_section('Bloques de agenda', ['einen Termin haben', 'einen Termin verschieben', 'spaeter kommen', 'frueher anfangen'], null, null, 'aleman'),
                            german_expand_section('Frases funcionales', ['Ich komme zehn Minuten spaeter.', 'Koennen wir den Termin verschieben?', 'Passt Ihnen Freitag?'], null, null, 'aleman'),
                            german_expand_section('Modelo de gestion', [], null, 'Ich habe morgen einen Termin, aber ich komme zehn Minuten spaeter.', 'aleman'),
                        ],
                        'La formula buena en A2 no es larga: basta con motivo, hora y propuesta clara.',
                        'Escenario: vas tarde a una cita y necesitas avisar sin perder la calma.',
                        ['Di que tienes un Termin.', 'Di que llegas tarde.', 'Propone otra hora.']
                    ),
                    german_expand_theory(
                        'Oficina, servicio y preguntas utiles',
                        16,
                        'Muchos tramites se resuelven con preguntas repetidas: donde, cuando, que documento y si hace falta otra accion.',
                        [
                            german_expand_section('Lexico util', ['der Schalter', 'das Formular', 'der Ausweis', 'die Nummer', 'die Oeffnungszeiten'], null, null, 'aleman'),
                            german_expand_section('Preguntas de servicio', ['Wo bekomme ich das Formular?', 'Welche Nummer brauche ich?', 'Wann ist das Buero offen?'], null, null, 'aleman'),
                            german_expand_section('Modelo de ventanilla', [], null, 'Entschuldigung, wo bekomme ich dieses Formular und welche Nummer brauche ich?', 'aleman'),
                        ],
                        'En tramites cortos, una pregunta precisa vale mas que una frase larga mal armada.',
                        'Escenario: llegas a una oficina y no sabes que documento pedir primero.',
                        ['Pregunta donde conseguir un formulario.', 'Pregunta una hora.', 'Pregunta una condicion o requisito.']
                    ),
                    german_expand_theory(
                        'Ciudad, ubicacion y movimiento con precision A2',
                        16,
                        'Moverte por la ciudad exige mas que links y rechts: necesitas estaciones, transbordos, retrasos y expresiones de ubicacion practica.',
                        [
                            german_expand_section('Vocabulario de trayecto', ['die Haltestelle', 'umsteigen', 'geradeaus', 'gegenueber', 'zwischen', 'an der Ecke'], null, null, 'aleman'),
                            german_expand_section('Frases utiles', ['Wo muss ich umsteigen?', 'Die Apotheke liegt gegenueber dem Bahnhof.', 'Der Bus faehrt in zehn Minuten.'], null, null, 'aleman'),
                            german_expand_section('Modelo urbano', [], null, 'Die Bank liegt zwischen der Post und dem Supermarkt, direkt an der Ecke.', 'aleman'),
                        ],
                        'Ubicacion buena en A2 combina punto de referencia + relacion espacial + tiempo o direccion.',
                        'Escenario: das indicaciones a alguien que debe bajar, caminar y encontrar una oficina.',
                        ['Usa zwischen.', 'Usa gegenueber.', 'Di un transbordo o tiempo de salida.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Tramites, transporte y citas',
                        'Elige la opcion correcta para resolver una gestion o moverte por la ciudad.',
                        'Selecciona la frase mas natural.',
                        [
                            german_expand_question('Quieres mover una cita', 'Koennen wir den Termin verschieben?', 'Koennen wir den Termin bewegen?', 'Wir koennen der Termin spaeter.'),
                            german_expand_question('Pregunta adecuada en una oficina', 'Wo bekomme ich dieses Formular?', 'Wo nimmt dieses Formular mich?', 'Wo ich bekomme dieses Formular?'),
                            german_expand_question('Expresion correcta de ubicacion', 'Die Apotheke liegt gegenueber dem Bahnhof.', 'Die Apotheke ist gegenueber von Bahnhof.', 'Die Apotheke liegt gegenueber den Bahnhof.'),
                        ]
                    ),
                    german_expand_matching(
                        'Empareja el tramite con la pregunta correcta',
                        'Relaciona la situacion con la pregunta que mejor la resuelve.',
                        [
                            ['cambiar una cita', 'Koennen wir den Termin verschieben?'],
                            ['pedir un formulario', 'Wo bekomme ich dieses Formular?'],
                            ['preguntar horario', 'Wann ist das Buero offen?'],
                            ['preguntar un transbordo', 'Wo muss ich umsteigen?'],
                        ]
                    ),
                    german_expand_fill(
                        'Completa el mensaje de gestion',
                        'Rellena con la palabra adecuada.',
                        [
                            ['id' => 'a22_fill_1', 'oracion' => 'Ich komme zehn Minuten ____.', 'respuesta_correcta' => 'spaeter'],
                            ['id' => 'a22_fill_2', 'oracion' => 'Wo bekomme ich das ____?', 'respuesta_correcta' => 'Formular'],
                            ['id' => 'a22_fill_3', 'oracion' => 'Die Bank liegt ____ dem Bahnhof.', 'respuesta_correcta' => 'gegenueber'],
                        ]
                    ),
                    german_expand_drag(
                        'Lleva cada palabra al contexto correcto',
                        'Arrastra cada elemento al lugar donde tiene mas sentido.',
                        [
                            'Formular' => 'Buero',
                            'umsteigen' => 'Bahnhof',
                            'Haltestelle' => 'Bus',
                            'Termin' => 'Kalender',
                        ]
                    ),
                    german_expand_listening(
                        'Escucha: retraso y cambio de horario',
                        'Escucha un mensaje corto y escribe exactamente lo que oyes.',
                        'Guten Morgen, ich habe um zehn Uhr einen Termin, aber mein Zug ist spaet und ich komme zehn Minuten spaeter.'
                    ),
                    german_expand_writing(
                        'Escribe para mover una cita',
                        'Redacta un mensaje corto para avisar un retraso o pedir otro horario.',
                        'Escribe un mensaje a una oficina o a una profesora para explicar que llegas tarde o para mover una cita. Incluye motivo, nueva hora y una formula cortesa.',
                        90,
                        22,
                        14
                    ),
                ]
            ),
            ['orden' => 6]
        ),
        array_merge(
            german_expand_lesson(
                'Nivel A2.3: comida, servicio y pequenas reclamaciones',
                'Comida, cantidades, pedidos, servicio y reclamaciones corteses para resolver situaciones frecuentes fuera de casa.',
                160,
                [
                    german_expand_theory(
                        'Comida, cantidades y pedidos naturales',
                        16,
                        'La comida aparece muy pronto en la vida real, pero no sirve solo para memorizar vocabulario: sirve para pedir, elegir, cambiar y pagar.',
                        [
                            german_expand_section('Vocabulario base', ['das Brot', 'der Saft', 'das Menue', 'die Rechnung', 'ein Glas Wasser', 'zwei Stueck Kuchen'], null, null, 'aleman'),
                            german_expand_section('Frases de pedido', ['Ich moechte einen Tee.', 'Bringen Sie mir bitte die Rechnung.', 'Haben Sie etwas Vegetarisches?'], null, null, 'aleman'),
                            german_expand_section('Modelo de mostrador', [], null, 'Ich nehme zwei Brote und ein Glas Wasser, bitte.', 'aleman'),
                        ],
                        'Cantidad + producto + cortesia es una plantilla que desbloquea muchisimas escenas.',
                        'Escenario: pides desayuno rapido antes de entrar al trabajo.',
                        ['Pide una bebida.', 'Pide una cantidad.', 'Pide la cuenta.']
                    ),
                    german_expand_theory(
                        'Servicio, preferencias y preguntas al personal',
                        16,
                        'Cuando el alumno ya pide comida, el siguiente paso es preguntar por ingredientes, alternativas y disponibilidad.',
                        [
                            german_expand_section('Preguntas utiles', ['Was empfehlen Sie?', 'Ist das scharf?', 'Kann ich das ohne Zwiebeln haben?', 'Gibt es noch einen Tisch?'], null, null, 'aleman'),
                            german_expand_section('Bloques de preferencia', ['ohne Zucker', 'mit Milch', 'nicht zu kalt', 'lieber vegetarisch'], null, null, 'aleman'),
                            german_expand_section('Modelo de servicio', [], null, 'Kann ich die Suppe ohne Zwiebeln haben und gibt es noch einen Tisch am Fenster?', 'aleman'),
                        ],
                        'Las preferencias se recuerdan mejor cuando se repiten como combinaciones fijas: ohne + sustantivo, mit + sustantivo.',
                        'Escenario: preguntas por opciones y haces una pequena modificacion al pedido.',
                        ['Pregunta una recomendacion.', 'Pide algo sin un ingrediente.', 'Pregunta si queda una mesa.']
                    ),
                    german_expand_theory(
                        'Pequenas reclamaciones y solucion cordial',
                        17,
                        'A2 tambien necesita reparar problemas pequenos: un plato frio, una cuenta incorrecta o una reserva que no aparece.',
                        [
                            german_expand_section('Frases de reclamacion suave', ['Entschuldigung, das ist kalt.', 'Ich glaube, hier stimmt etwas nicht.', 'Wir haben reserviert.'], null, null, 'aleman'),
                            german_expand_section('Frases de solucion', ['Koennen Sie das bitte wechseln?', 'Die Rechnung ist zu hoch.', 'Vielleicht gibt es ein Missverstaendnis.'], null, null, 'aleman'),
                            german_expand_section('Modelo de reclamacion', [], null, 'Entschuldigung, ich glaube, die Rechnung ist zu hoch. Koennen Sie das bitte noch einmal pruefen?', 'aleman'),
                        ],
                        'La mejor reclamacion en A2 no dramatiza: describe el problema y pide una accion concreta.',
                        'Escenario: algo sale mal en un cafe y necesitas resolverlo con cortesia firme.',
                        ['Describe el problema.', 'Pide revision o cambio.', 'Mantente cortesa.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Pedidos, preferencias y reclamaciones',
                        'Elige la opcion mas natural para una situacion de comida o servicio.',
                        'Marca la mejor respuesta.',
                        [
                            german_expand_question('Pides la cuenta', 'Bringen Sie mir bitte die Rechnung.', 'Geben Sie mir Rechnung, bitte.', 'Ich brauche Rechnung trae.'),
                            german_expand_question('Quieres algo sin cebolla', 'Kann ich das ohne Zwiebeln haben?', 'Kann ich das nicht Zwiebeln haben?', 'Kann ich ohne das Zwiebeln haben?'),
                            german_expand_question('La cuenta esta mal', 'Ich glaube, die Rechnung ist zu hoch.', 'Die Rechnung ist gross.', 'Ich glaube, die Rechnung ist mucha.'),
                        ]
                    ),
                    german_expand_matching(
                        'Empareja la necesidad con la frase',
                        'Relaciona cada problema o necesidad con una respuesta util.',
                        [
                            ['pedir una recomendacion', 'Was empfehlen Sie?'],
                            ['pedir la cuenta', 'Bringen Sie mir bitte die Rechnung.'],
                            ['cambiar un plato', 'Koennen Sie das bitte wechseln?'],
                            ['pedir sin ingrediente', 'Kann ich das ohne Zucker haben?'],
                        ]
                    ),
                    german_expand_fill(
                        'Completa el pedido',
                        'Escribe solo la palabra que falta.',
                        [
                            ['id' => 'a23_fill_1', 'oracion' => 'Ich moechte ein ____ Wasser, bitte.', 'respuesta_correcta' => 'Glas'],
                            ['id' => 'a23_fill_2', 'oracion' => 'Kann ich das ohne ____ haben?', 'respuesta_correcta' => 'Zwiebeln'],
                            ['id' => 'a23_fill_3', 'oracion' => 'Die Rechnung ist zu ____.', 'respuesta_correcta' => 'hoch'],
                        ]
                    ),
                    german_expand_order(
                        'Ordena el dialogo del cafe',
                        'Reconstruye frases breves de pedido y reclamacion.',
                        [
                            'Ich moechte einen Tee und ein Stueck Kuchen.',
                            'Bringen Sie mir bitte die Rechnung.',
                            'Entschuldigung, das ist nicht mein Essen.',
                        ]
                    ),
                    german_expand_pronunciation(
                        'Pronuncia un pedido completo',
                        'Lee tres frases de servicio con una entonacion clara y cortesa.',
                        [
                            'Ich moechte einen Kaffee und zwei Brote, bitte.',
                            'Kann ich das ohne Zucker haben?',
                            'Entschuldigung, ich glaube, hier stimmt etwas nicht.',
                        ]
                    ),
                    german_expand_writing(
                        'Escribe una pequena reclamacion',
                        'Redacta un mensaje o nota breve explicando un problema de servicio.',
                        'Escribe una reclamacion corta por una cuenta incorrecta, una reserva perdida o un plato equivocado. Explica el problema y pide una solucion.',
                        90,
                        22,
                        14
                    ),
                ]
            ),
            ['orden' => 7]
        ),
    ];
}

function german_expand_new_lessons_group_one_c(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Nivel A2.4: planes, invitaciones y futuro cercano',
                'Planes, invitaciones, propuestas y futuro para hablar de fines de semana, proyectos cortos y decisiones practicas.',
                165,
                [
                    german_expand_theory(
                        'Planes y futuro con werden',
                        17,
                        'En A2 el alumno ya necesita salir del presente inmediato. Werden permite anunciar planes, decisiones y predicciones simples con claridad.',
                        [
                            german_expand_section('Bloques utiles', ['Ich werde morgen lernen.', 'Wir werden spaeter anrufen.', 'Es wird regnen.'], null, null, 'aleman'),
                            german_expand_section('Marcadores de plan', ['morgen', 'naechste Woche', 'am Wochenende', 'bald'], null, null, 'aleman'),
                            german_expand_section('Modelo de plan', [], null, 'Am Wochenende werde ich meine Freunde besuchen und am Sonntag lernen.', 'aleman'),
                        ],
                        'No conviertas werden en teoria abstracta: usalo con planes reales y cercanos.',
                        'Escenario: te preguntan que haras esta semana y necesitas sonar claro, no improvisado.',
                        ['Di un plan para manana.', 'Di un plan para el fin de semana.', 'Haz una prediccion simple.']
                    ),
                    german_expand_theory(
                        'Invitaciones, propuestas y respuestas naturales',
                        16,
                        'Las invitaciones ayudan a practicar cortesia, disponibilidad y razones. Son excelentes para unir futuro, tiempo y trato interpersonal.',
                        [
                            german_expand_section('Frases de invitacion', ['Hast du am Freitag Zeit?', 'Wollen wir ins Kino gehen?', 'Moechtest du mitkommen?'], null, null, 'aleman'),
                            german_expand_section('Aceptar o rechazar', ['Ja, gern.', 'Leider kann ich nicht.', 'Vielleicht ein anderes Mal.'], null, null, 'aleman'),
                            german_expand_section('Modelo de propuesta', [], null, 'Hast du am Samstag Zeit? Wollen wir zusammen essen gehen?', 'aleman'),
                        ],
                        'Una invitacion fuerte tiene tres piezas: propuesta, tiempo y respuesta clara.',
                        'Escenario: organizas un plan con un amigo y necesitas confirmar si puede.',
                        ['Invita a alguien.', 'Di dia u hora.', 'Acepta o rechaza con cortesia.']
                    ),
                    german_expand_theory(
                        'Pequenos proyectos, razones y coordinacion',
                        16,
                        'A2 tambien necesita conectar un plan con su motivo: estudiar porque hay examen, viajar porque hay visita, cancelar porque hay trabajo.',
                        [
                            german_expand_section('Conectores utiles', ['weil', 'deshalb', 'dann', 'zuerst'], null, null, 'aleman'),
                            german_expand_section('Frases de coordinacion', ['Ich kann nicht, weil ich arbeite.', 'Zuerst kaufen wir ein, dann fahren wir los.'], null, null, 'aleman'),
                            german_expand_section('Modelo con razon', [], null, 'Ich werde heute zu Hause bleiben, weil ich morgen eine Pruefung habe.', 'aleman'),
                        ],
                        'La razon breve bien puesta hace que el plan suene mucho mas natural y creible.',
                        'Escenario: armas un plan, pero debes explicar por que cambias una parte.',
                        ['Usa weil.', 'Usa zuerst y dann.', 'Explica un cambio de plan.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Planes, invitaciones y futuro',
                        'Elige la opcion correcta para hablar de planes y responder invitaciones.',
                        'Selecciona la respuesta mas natural.',
                        [
                            german_expand_question('Plan futuro correcto', 'Ich werde morgen frueh lernen.', 'Ich morgen werde frueh lernen.', 'Ich werden morgen lernen.'),
                            german_expand_question('Invitacion natural', 'Hast du am Samstag Zeit?', 'Du hast Samstag Zeit?', 'Hast Samstag du Zeit?'),
                            german_expand_question('Rechazo cortesa', 'Leider kann ich nicht, weil ich arbeite.', 'Leider ich kann nicht, ich arbeite.', 'Ich kann leider nicht weil arbeite ich.'),
                        ]
                    ),
                    german_expand_true_false(
                        'Chequeo rapido del futuro',
                        'Decide si la afirmacion es verdadera o falsa.',
                        'En la frase "Ich werde spaeter anrufen" el verbo principal va al final.',
                        'Verdadero',
                        10,
                        4
                    ),
                    german_expand_fill(
                        'Completa el plan',
                        'Rellena con la palabra correcta.',
                        [
                            ['id' => 'a24_fill_1', 'oracion' => 'Wir ____ morgen ins Kino gehen.', 'respuesta_correcta' => 'werden'],
                            ['id' => 'a24_fill_2', 'oracion' => 'Hast du am Freitag ____?', 'respuesta_correcta' => 'Zeit'],
                            ['id' => 'a24_fill_3', 'oracion' => 'Ich bleibe zu Hause, ____ ich lernen muss.', 'respuesta_correcta' => 'weil'],
                        ]
                    ),
                    german_expand_drag(
                        'Lleva cada accion a la fase del plan',
                        'Arrastra las acciones al momento del fin de semana donde mejor encajan.',
                        [
                            'einkaufen' => 'vorher',
                            'losfahren' => 'start',
                            'essen gehen' => 'hauptplan',
                            'anrufen' => 'bestaetigen',
                        ]
                    ),
                    german_expand_listening(
                        'Escucha: plan de fin de semana',
                        'Escucha el plan y escribe la transcripcion.',
                        'Am Samstag werden wir zuerst einkaufen, dann fahren wir zu Anna, weil sie Geburtstag hat.'
                    ),
                    german_expand_writing(
                        'Escribe una invitacion con respuesta',
                        'Redacta un mensaje breve para invitar a alguien y coordinar un plan.',
                        'Escribe un mensaje para invitar a un amigo o una amiga a salir. Incluye dia, hora, actividad y una razon.',
                        100,
                        22,
                        14
                    ),
                ]
            ),
            ['orden' => 8]
        ),
        array_merge(
            german_expand_lesson(
                'Nivel B1.2: relativas, correos y tramites',
                'Relativas, mensajes formales, formularios y resolucion de pequenas gestiones en contexto laboral o academico.',
                175,
                [
                    german_expand_theory(
                        'Relativas para describir con mas precision',
                        18,
                        'B1 pide poder identificar personas, objetos y procesos con mas detalle. Las relativas ayudan a precisar sin volver la frase torpe.',
                        [
                            german_expand_section('Modelos utiles', ['Der Mann, der dort steht...', 'Das Formular, das ich brauche...', 'Die Firma, bei der ich arbeite...'], null, null, 'aleman'),
                            german_expand_section('Usos frecuentes', ['describir personas', 'aclarar documentos', 'identificar lugares y objetos'], null, null, 'espanol'),
                            german_expand_section('Modelo B1', [], null, 'Ich suche das Formular, das man fuer die Anmeldung braucht.', 'aleman'),
                        ],
                        'Las relativas se fijan mejor cuando resuelven una necesidad concreta, no como ejercicio abstracto de pronombres.',
                        'Escenario: necesitas identificar un documento, una oficina o una persona sin senalar con el dedo.',
                        ['Describe una persona.', 'Describe un documento.', 'Usa una relativa con das o der.']
                    ),
                    german_expand_theory(
                        'Correo formal, consulta y seguimiento',
                        18,
                        'B1 ya exige escribir sin sonar brusco. Un correo correcto organiza saludo, motivo, pregunta y cierre con tono funcional.',
                        [
                            german_expand_section('Bloques de correo', ['Sehr geehrte Damen und Herren,', 'Ich schreibe Ihnen, weil...', 'Koennten Sie mir bitte mitteilen, ob...?', 'Mit freundlichen Gruessen'], null, null, 'aleman'),
                            german_expand_section('Situaciones tipicas', ['pedir informacion', 'consultar un plazo', 'confirmar una inscripcion', 'adjuntar un documento'], null, null, 'espanol'),
                            german_expand_section('Modelo formal', [], null, 'Ich schreibe Ihnen, weil ich eine Frage zur Anmeldung habe.', 'aleman'),
                        ],
                        'El salto a B1 se nota mucho cuando el alumno deja de escribir mensajes telegráficos y organiza una consulta completa.',
                        'Escenario: necesitas escribir a una escuela u oficina para aclarar un proceso.',
                        ['Abre con saludo formal.', 'Explica el motivo.', 'Formula una pregunta precisa.']
                    ),
                    german_expand_theory(
                        'Formularios, plazos y pequenos bloqueos administrativos',
                        17,
                        'La vida real trae formularios incompletos, plazos dudosos y documentos que faltan. B1 debe poder explicar el bloqueo y pedir orientacion.',
                        [
                            german_expand_section('Lexico funcional', ['die Frist', 'das Dokument', 'die Anmeldung', 'der Anhang', 'vollstaendig', 'fehlen'], null, null, 'aleman'),
                            german_expand_section('Frases de problema', ['Es fehlt ein Dokument.', 'Die Frist endet morgen.', 'Ich habe den Anhang schon geschickt.'], null, null, 'aleman'),
                            german_expand_section('Modelo de incidencia', [], null, 'Es fehlt offenbar ein Dokument, obwohl ich den Anhang schon geschickt habe.', 'aleman'),
                        ],
                        'En B1 no basta con decir "Problema": hay que nombrarlo, ubicarlo y pedir el siguiente paso.',
                        'Escenario: tu solicitud no avanza porque falta algo y debes aclararlo con calma.',
                        ['Nombra el documento.', 'Di que falta o que ya lo enviaste.', 'Pregunta que sigue.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Relativas y comunicacion formal',
                        'Elige la estructura correcta para describir y escribir con tono funcional.',
                        'Marca la mejor opcion.',
                        [
                            german_expand_question('Relativa correcta', 'Das ist das Formular, das ich brauche.', 'Das ist das Formular, der ich brauche.', 'Das ist das Formular, ich brauche das.'),
                            german_expand_question('Inicio formal adecuado', 'Sehr geehrte Damen und Herren,', 'Hallo Leute,', 'Liebe Chef,'),
                            german_expand_question('Consulta cortesa', 'Koennten Sie mir bitte mitteilen, ob noch Plaetze frei sind?', 'Sie koennen mir sagen, noch Plaetze frei?', 'Mitteilen Sie mir bitte ob frei Plaetze?'),
                        ]
                    ),
                    german_expand_matching(
                        'Empareja problema y solucion administrativa',
                        'Relaciona cada situacion con la formula que mejor la resuelve.',
                        [
                            ['falta un documento', 'Es fehlt ein Dokument.'],
                            ['consultar un plazo', 'Bis wann gilt die Frist?'],
                            ['confirmar un envio', 'Ich habe den Anhang schon geschickt.'],
                            ['pedir orientacion', 'Was soll ich als Naechstes tun?'],
                        ]
                    ),
                    german_expand_fill(
                        'Completa el correo formal',
                        'Escribe la palabra correcta.',
                        [
                            ['id' => 'b12_fill_1', 'oracion' => 'Ich schreibe Ihnen, ____ ich eine Frage habe.', 'respuesta_correcta' => 'weil'],
                            ['id' => 'b12_fill_2', 'oracion' => 'Das ist das Dokument, ____ ich brauche.', 'respuesta_correcta' => 'das'],
                            ['id' => 'b12_fill_3', 'oracion' => 'Die ____ endet am Freitag.', 'respuesta_correcta' => 'Frist'],
                        ]
                    ),
                    german_expand_order(
                        'Ordena una consulta formal',
                        'Reconstruye frases de un correo o tramite B1.',
                        [
                            'Ich schreibe Ihnen, weil ich eine Frage zur Anmeldung habe.',
                            'Koennten Sie mir bitte weiterhelfen?',
                            'Mit freundlichen Gruessen',
                        ]
                    ),
                    german_expand_listening(
                        'Escucha: llamada a la oficina',
                        'Escucha un mensaje administrativo y escribe lo que oyes.',
                        'Guten Tag, ich rufe an, weil mir noch ein Dokument fehlt und die Frist am Freitag endet.'
                    ),
                    german_expand_writing(
                        'Escribe un correo formal completo',
                        'Redacta una consulta formal sobre una inscripcion, documento o plazo.',
                        'Escribe un correo formal a una academia, universidad u oficina. Explica el problema, formula una pregunta precisa y cierra con tono correcto.',
                        130,
                        24,
                        16
                    ),
                ]
            ),
            ['orden' => 10]
        ),
    ];
}

function german_master_level_from_title(string $title): string
{
    foreach (['A1', 'A2', 'B1', 'B2', 'C1'] as $level) {
        if (stripos($title, $level) !== false) {
            return $level;
        }
    }

    return 'A1';
}

function german_master_reinforcement_bank(): array
{
    return [
        'A1' => [
            'true_false' => [
                ['statement' => 'En una oracion principal de aleman el verbo conjugado suele ocupar una posicion muy temprana.', 'correct' => 'Verdadero'],
                ['statement' => 'En A1 es buena idea memorizar los sustantivos sin articulo para avanzar mas rapido.', 'correct' => 'Falso'],
                ['statement' => 'Las expresiones de tiempo como "um acht Uhr" ayudan a que una rutina suene mas natural.', 'correct' => 'Verdadero'],
            ],
            'short' => [
                ['question' => 'Completa la formula de cortesia: Noch einmal, ____.', 'answers' => ['bitte']],
                ['question' => 'Completa la frase: Ich ____ aus Chile.', 'answers' => ['komme']],
                ['question' => 'Completa la expresion de hora: um zehn ____.', 'answers' => ['Uhr']],
            ],
        ],
        'A2' => [
            'true_false' => [
                ['statement' => 'El Perfekt necesita un auxiliar y normalmente deja el participio al final.', 'correct' => 'Verdadero'],
                ['statement' => 'Para mover una cita en aleman basta con decir la nueva hora sin explicar nada mas.', 'correct' => 'Falso'],
                ['statement' => 'Las pequenas reclamaciones suenan mejor si describen el problema y piden una accion concreta.', 'correct' => 'Verdadero'],
            ],
            'short' => [
                ['question' => 'Completa la frase: Ich komme zehn Minuten ____.', 'answers' => ['spaeter']],
                ['question' => 'Completa la formula: Koennen wir den ____ verschieben?', 'answers' => ['Termin']],
                ['question' => 'Completa: Bringen Sie mir bitte die ____.', 'answers' => ['Rechnung']],
            ],
        ],
        'B1' => [
            'true_false' => [
                ['statement' => 'B1 exige justificar ideas con conectores y no solo lanzar opiniones sueltas.', 'correct' => 'Verdadero'],
                ['statement' => 'Un correo formal B1 puede omitir saludo y cierre si la pregunta es corta.', 'correct' => 'Falso'],
                ['statement' => 'Proponer una solucion concreta vuelve mas fuerte un mensaje de problema.', 'correct' => 'Verdadero'],
            ],
            'short' => [
                ['question' => 'Completa la estructura: Ich finde, ____ Onlinekurse praktisch sind.', 'answers' => ['dass']],
                ['question' => 'Completa el cierre formal: Mit freundlichen ____.', 'answers' => ['Gruessen']],
                ['question' => 'Completa la formula de opinion: Meiner ____ nach ...', 'answers' => ['Meinung']],
            ],
        ],
        'B2' => [
            'true_false' => [
                ['statement' => 'En B2 conviene introducir matices y concesiones para no sonar simplista.', 'correct' => 'Verdadero'],
                ['statement' => 'La nominalizacion siempre mejora un texto, incluso si lo vuelve mas opaco.', 'correct' => 'Falso'],
                ['statement' => 'Una buena escucha B2 identifica giro de contraste y conclusion, no solo palabras aisladas.', 'correct' => 'Verdadero'],
            ],
            'short' => [
                ['question' => 'Completa el conector: Es gibt zwar Vorteile, ____ auch Risiken.', 'answers' => ['aber']],
                ['question' => 'Completa la formula analitica: Die Daten zeigen, ____.', 'answers' => ['dass']],
                ['question' => 'Completa el conector de consecuencia: __ braucht man klare Regeln.', 'answers' => ['Folglich', 'folglich']],
            ],
        ],
        'C1' => [
            'true_false' => [
                ['statement' => 'En C1 es util separar sintesis fiel y postura propia para no mezclar voces.', 'correct' => 'Verdadero'],
                ['statement' => 'La precision C1 depende mas de sonar complicado que de reformular con claridad.', 'correct' => 'Falso'],
                ['statement' => 'Un plan de consolidacion posterior al curso forma parte de una ruta completa seria.', 'correct' => 'Verdadero'],
            ],
            'short' => [
                ['question' => 'Completa la formula de sintesis: Zusammenfassend laesst sich ____.', 'answers' => ['sagen']],
                ['question' => 'Completa la expresion: Aus meiner ____ bleibt offen, ob ...', 'answers' => ['Sicht']],
                ['question' => 'Completa el conector formal: ____ braucht es institutionelle Verantwortung.', 'answers' => ['Demnach', 'demnach']],
            ],
        ],
    ];
}

function german_master_reinforcement_profiles(): array
{
    return [
        'Nivel A1.1: sonidos, saludos y primer contacto' => [
            'true_false' => [
                'title' => 'Chequeo de saludo y presentacion',
                'description' => 'Confirma si ya reconoces las bases de primer contacto sin traducir palabra por palabra.',
                'statement' => 'La frase "Ich heisse Lara" sirve para presentarte de forma natural en un primer contacto.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Palabra clave de saludo',
                'description' => 'Recupera una pieza minima de cortesía que necesitas desde la primera leccion.',
                'question' => 'Completa el saludo: Guten ____. ',
                'answers' => ['Tag'],
                'placeholder' => 'Escribe la palabra del saludo',
            ],
        ],
        'Nivel A1.2: familia, numeros y preguntas basicas' => [
            'true_false' => [
                'title' => 'Chequeo de preguntas basicas',
                'description' => 'Distingue si una pregunta basica realmente pide edad, origen o informacion familiar.',
                'statement' => 'La pregunta "Wie alt bist du?" se usa para preguntar el origen de una persona.',
                'correct' => 'Falso',
            ],
            'short' => [
                'title' => 'Numero funcional de familia',
                'description' => 'Fija una respuesta corta que aparece mucho al hablar de edad y familia.',
                'question' => 'Completa: Ich bin zweiundzwanzig ____ alt.',
                'answers' => ['Jahre'],
                'placeholder' => 'Escribe la palabra que completa la edad',
            ],
        ],
        'Nivel A1.3: rutinas, presente y verbos separables' => [
            'true_false' => [
                'title' => 'Chequeo de rutina y verbo separable',
                'description' => 'Verifica si ya reconoces el patron basico de rutina diaria con verbos separables.',
                'statement' => 'En "Ich stehe um sieben Uhr auf", el verbo separable queda repartido entre verbo conjugado y particula final.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Particula de rutina diaria',
                'description' => 'Recupera la particula que completa una accion cotidiana muy frecuente.',
                'question' => 'Completa: Ich stehe um sieben Uhr ____. ',
                'answers' => ['auf'],
                'placeholder' => 'Escribe la particula final',
            ],
        ],
        'Nivel A1.4: compras, ciudad y verbos de accion' => [
            'true_false' => [
                'title' => 'Chequeo de ciudad y compra',
                'description' => 'Comprueba si ya distingues preguntas utiles para ubicacion y compra cotidiana.',
                'statement' => 'La pregunta "Wo ist der Bahnhof?" sirve para orientarte en la ciudad.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Palabra funcional de compra',
                'description' => 'Fija una palabra concreta de compra para no quedarte sin vocabulario en mostrador.',
                'question' => 'Completa: Ich moechte zwei ____ kaufen.',
                'answers' => ['Brote'],
                'placeholder' => 'Escribe el producto',
            ],
        ],
        'Checkpoint A1: supervivencia completa' => [
            'true_false' => [
                'title' => 'Chequeo de supervivencia A1',
                'description' => 'Mide si ya puedes resolver las acciones minimas de supervivencia del tramo A1.',
                'statement' => 'Si puedes pedir repeticion, dar la hora y presentarte con claridad, ya tienes una base funcional de supervivencia A1.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Ancla de supervivencia inmediata',
                'description' => 'Recupera una formula corta que salva interacciones cuando aun no entiendes todo.',
                'question' => 'Completa la formula de cortesía: Noch einmal, ____. ',
                'answers' => ['bitte'],
                'placeholder' => 'Escribe la palabra de cortesía',
            ],
        ],
        'Nivel A2.1: pasado, dativo y vida cotidiana' => [
            'true_false' => [
                'title' => 'Chequeo de pasado y dativo',
                'description' => 'Verifica si ya reconoces el esquema basico de Perfekt dentro de situaciones cotidianas.',
                'statement' => 'En Perfekt necesitas un auxiliar y normalmente dejas el participio al final.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Participio de vida diaria',
                'description' => 'Fija una forma verbal de pasado que aparece mucho al hablar de rutina reciente.',
                'question' => 'Completa: Ich habe gestern lange ____. ',
                'answers' => ['gearbeitet'],
                'placeholder' => 'Escribe el participio',
            ],
        ],
        'Nivel A2.2: tramites, citas y movimiento por la ciudad' => [
            'true_false' => [
                'title' => 'Chequeo de cita y reorganizacion',
                'description' => 'Decide si una gestion de cita queda realmente completa o si todavia le falta contexto.',
                'statement' => 'Para mover una cita en aleman basta con decir la nueva hora sin explicar nada mas.',
                'correct' => 'Falso',
            ],
            'short' => [
                'title' => 'Palabra de tramite clave',
                'description' => 'Recupera el nucleo de una frase muy util para mover una cita.',
                'question' => 'Completa: Koennen wir den ____ verschieben?',
                'answers' => ['Termin'],
                'placeholder' => 'Escribe la palabra del tramite',
            ],
        ],
        'Nivel A2.3: comida, servicio y pequenas reclamaciones' => [
            'true_false' => [
                'title' => 'Chequeo de servicio y reclamacion',
                'description' => 'Comprueba si ya distingues una queja util de una reaccion demasiado vaga o brusca.',
                'statement' => 'Una pequena reclamacion suena mejor si describe el problema y pide una accion concreta.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Palabra funcional de restaurante',
                'description' => 'Fija una palabra basica para pedir servicio con claridad.',
                'question' => 'Completa: Bringen Sie mir bitte die ____. ',
                'answers' => ['Rechnung'],
                'placeholder' => 'Escribe la palabra del servicio',
            ],
        ],
        'Nivel A2.4: planes, invitaciones y futuro cercano' => [
            'true_false' => [
                'title' => 'Chequeo de planes y futuro',
                'description' => 'Verifica si ya distingues invitacion, respuesta y plan futuro cercano.',
                'statement' => 'En una invitacion natural conviene incluir dia, hora o una razon clara, no solo el verbo principal.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Verbo de plan futuro',
                'description' => 'Recupera el auxiliar que te permite hablar de planes cercanos con seguridad.',
                'question' => 'Completa: Am Wochenende ____ ich meine Freunde besuchen.',
                'answers' => ['werde'],
                'placeholder' => 'Escribe el auxiliar',
            ],
        ],
        'Checkpoint A2: autonomia cotidiana' => [
            'true_false' => [
                'title' => 'Chequeo de autonomia A2',
                'description' => 'Mide si ya puedes combinar pasado, gestiones y planes sin salirte del contexto cotidiano.',
                'statement' => 'A2 ya exige combinar pasado, gestion y futuro en un mismo mensaje breve.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Ancla de retraso y reorganizacion',
                'description' => 'Fija una pieza corta muy util para avisar retrasos con naturalidad.',
                'question' => 'Completa: Ich komme zehn Minuten ____. ',
                'answers' => ['spaeter'],
                'placeholder' => 'Escribe la palabra temporal',
            ],
        ],
        'Nivel B1.1: opinion, subordinadas y mundo real' => [
            'true_false' => [
                'title' => 'Chequeo de opinion conectada',
                'description' => 'Decide si una opinion ya cumple el nivel B1 o si todavia suena demasiado suelta.',
                'statement' => 'B1 exige justificar ideas con conectores y no solo lanzar opiniones sueltas.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Conector clave de opinion',
                'description' => 'Recupera la pieza que abre una subordinada muy frecuente en opiniones B1.',
                'question' => 'Completa: Ich finde, ____ Onlinekurse praktisch sind.',
                'answers' => ['dass'],
                'placeholder' => 'Escribe el conector',
            ],
        ],
        'Nivel B1.2: relativas, correos y tramites' => [
            'true_false' => [
                'title' => 'Chequeo de correo formal B1',
                'description' => 'Comprueba si ya reconoces las partes minimas de un correo funcional y correcto.',
                'statement' => 'Un correo formal B1 puede omitir saludo y cierre si la pregunta es corta.',
                'correct' => 'Falso',
            ],
            'short' => [
                'title' => 'Cierre formal minimo',
                'description' => 'Fija una formula de cierre que necesitas en tramites y correos B1.',
                'question' => 'Completa el cierre formal: Mit freundlichen ____. ',
                'answers' => ['Gruessen'],
                'placeholder' => 'Escribe la palabra del cierre',
            ],
        ],
        'Nivel B1.3: trabajo, estudio y resolucion de problemas' => [
            'true_false' => [
                'title' => 'Chequeo de problema y solucion',
                'description' => 'Evalua si una respuesta de problema ya incluye una salida concreta y funcional.',
                'statement' => 'Proponer una solucion concreta vuelve mas fuerte un mensaje de problema.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Verbo de propuesta util',
                'description' => 'Recupera el verbo que aparece mucho cuando propones una solucion real.',
                'question' => 'Completa: Ich schlage ____ , dass wir frueher beginnen.',
                'answers' => ['vor'],
                'placeholder' => 'Escribe la particula verbal',
            ],
        ],
        'Nivel B1.4: medios, resumen y mini presentaciones' => [
            'true_false' => [
                'title' => 'Chequeo de resumen B1',
                'description' => 'Decide si un resumen ya esta centrado en ideas o si sigue demasiado pegado al texto original.',
                'statement' => 'Un buen resumen B1 debe centrarse en ideas clave y no copiar todo literalmente.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Preposicion de resumen',
                'description' => 'Fija una preposicion que aparece constantemente al resumir contenidos.',
                'question' => 'Completa: Der Text handelt ____ digitalem Stress.',
                'answers' => ['von'],
                'placeholder' => 'Escribe la preposicion',
            ],
        ],
        'Checkpoint B1: independencia comunicativa' => [
            'true_false' => [
                'title' => 'Chequeo de independencia B1',
                'description' => 'Mide si ya puedes resumir, opinar y resolver situaciones con autonomia comunicativa real.',
                'statement' => 'B1 ya pide resumir, opinar y escribir mensajes funcionales con autonomia, no solo reaccionar con frases sueltas.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Ancla de postura personal',
                'description' => 'Recupera una palabra basica para introducir opinion con mas control.',
                'question' => 'Completa la formula: Meiner ____ nach ...',
                'answers' => ['Meinung'],
                'placeholder' => 'Escribe la palabra de la formula',
            ],
        ],
        'Nivel B2.1: debate, matices y lenguaje abstracto' => [
            'true_false' => [
                'title' => 'Chequeo de matiz B2',
                'description' => 'Verifica si ya reconoces una argumentacion menos simplista y mas matizada.',
                'statement' => 'En B2 conviene introducir matices y concesiones para no sonar simplista.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Conector de contraste B2',
                'description' => 'Fija un conector breve que sostiene muchos contrastes en debate y postura.',
                'question' => 'Completa: Es gibt zwar Vorteile, ____ auch Risiken.',
                'answers' => ['aber'],
                'placeholder' => 'Escribe el conector de contraste',
            ],
        ],
        'Nivel B2.2: informes, nominalizacion y textos densos' => [
            'true_false' => [
                'title' => 'Chequeo de informe y nominalizacion',
                'description' => 'Decide si una nominalizacion suma precision o si solo vuelve el texto mas opaco.',
                'statement' => 'La nominalizacion siempre mejora un texto, incluso si lo vuelve menos claro.',
                'correct' => 'Falso',
            ],
            'short' => [
                'title' => 'Pieza analitica del informe',
                'description' => 'Recupera una palabra funcional para convertir una observacion en formula analitica.',
                'question' => 'Completa la formula: Die Daten zeigen, ____. ',
                'answers' => ['dass'],
                'placeholder' => 'Escribe la palabra del patron',
            ],
        ],
        'Nivel B2.3: tecnologia, sociedad y escucha avanzada' => [
            'true_false' => [
                'title' => 'Chequeo de escucha avanzada B2',
                'description' => 'Comprueba si ya entiendes que una escucha densa exige seguir contraste y conclusion.',
                'statement' => 'Una buena escucha B2 identifica giro de contraste y conclusion, no solo palabras aisladas.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Conector de consecuencia densa',
                'description' => 'Fija un conector de cierre que aparece mucho en textos mas densos.',
                'question' => 'Completa el conector formal: ____ braucht man klare Regeln.',
                'answers' => ['Folglich', 'folglich'],
                'placeholder' => 'Escribe el conector de consecuencia',
            ],
        ],
        'Nivel C1.1: precision, registro y ruta de consolidacion' => [
            'true_false' => [
                'title' => 'Chequeo de precision C1',
                'description' => 'Evalua si ya distingues precision alta de complejidad vacia o artificiosa.',
                'statement' => 'La precision C1 depende mas de sonar complicado que de reformular con claridad.',
                'correct' => 'Falso',
            ],
            'short' => [
                'title' => 'Formula de distancia critica',
                'description' => 'Recupera una palabra clave para marcar distancia y precision en C1.',
                'question' => 'Completa: Aus meiner ____ bleibt offen, ob ...',
                'answers' => ['Sicht'],
                'placeholder' => 'Escribe la palabra de la formula',
            ],
        ],
        'Nivel C1.2: lectura academica, sintesis y formacion de palabras' => [
            'true_false' => [
                'title' => 'Chequeo de sintesis academica',
                'description' => 'Comprueba si ya separas bien la voz de la fuente y tu propia lectura critica.',
                'statement' => 'En una sintesis C1 conviene separar la fuente resumida de la postura propia para no mezclar voces.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Verbo de sintesis alta',
                'description' => 'Fija una pieza verbal tipica de la sintesis academica formal.',
                'question' => 'Completa: Zusammenfassend laesst sich ____. ',
                'answers' => ['sagen'],
                'placeholder' => 'Escribe el verbo',
            ],
        ],
        'Nivel C1.3: certificaciones, presentaciones y plan maestro' => [
            'true_false' => [
                'title' => 'Chequeo de presentacion C1',
                'description' => 'Verifica si ya reconoces una presentacion alta con jerarquia clara y cierre fuerte.',
                'statement' => 'Una presentacion C1 necesita jerarquia, registro estable y un cierre claramente orientado.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Apertura formal de presentacion',
                'description' => 'Recupera una palabra clave de apertura para una presentacion formal y ordenada.',
                'question' => 'Completa: Im ____ moechte ich ...',
                'answers' => ['Folgenden'],
                'placeholder' => 'Escribe la palabra de apertura',
            ],
        ],
        'Checkpoint Maestro C1: simulacion integral' => [
            'true_false' => [
                'title' => 'Chequeo integral de cierre C1',
                'description' => 'Mide si tu cierre final ya integra sintesis, postura y proyeccion con seriedad de ruta completa.',
                'statement' => 'Una ruta completa seria termina con sintesis, postura propia y plan de continuidad, no solo con un ultimo ejercicio aislado.',
                'correct' => 'Verdadero',
            ],
            'short' => [
                'title' => 'Ancla de conclusion institucional',
                'description' => 'Fija una palabra final de alto registro para cierres de conclusion o politica.',
                'question' => 'Completa: Demnach braucht es institutionelle ____. ',
                'answers' => ['Verantwortung'],
                'placeholder' => 'Escribe la palabra de cierre',
            ],
        ],
    ];
}

function german_master_writing_specs(): array
{
    return [
        'Escribe tu dia tipico' => [
            'registro' => 'informal',
            'min_oraciones' => 5,
            'criterios' => ['menciona manana, tarde y noche', 'incluye una actividad que te gusta y otra que no', 'mantiene frases completas en aleman'],
            'palabras_clave' => ['am Morgen', 'am Nachmittag', 'am Abend', 'gern', 'nicht gern'],
            'conectores_sugeridos' => ['zuerst', 'dann', 'aber'],
            'estructura_sugerida' => ['inicio breve', 'rutina por momentos del dia', 'cierre con preferencia personal'],
        ],
        'Tu micro rutina en aleman' => [
            'registro' => 'informal',
            'min_oraciones' => 4,
            'criterios' => ['describe una rutina corta y concreta', 'incluye ciudad, transporte o compra', 'cierra con un detalle personal'],
            'palabras_clave' => ['jeden Tag', 'ich gehe', 'ich kaufe', 'mit dem Bus'],
            'conectores_sugeridos' => ['zuerst', 'danach', 'aber'],
        ],
        'Escribe tu perfil funcional A1' => [
            'registro' => 'informal',
            'min_oraciones' => 5,
            'criterios' => ['se presenta con datos basicos', 'anade una rutina', 'expresa una necesidad cotidiana'],
            'palabras_clave' => ['ich heisse', 'ich komme aus', 'am Morgen', 'ich moechte'],
            'conectores_sugeridos' => ['und', 'dann', 'heute'],
        ],
        'Correo informal A2' => [
            'registro' => 'informal',
            'min_oraciones' => 5,
            'criterios' => ['cuenta algo del pasado', 'describe casa o estado', 'cierra de forma cercana'],
            'palabras_clave' => ['gestern', 'bei mir', 'ich fuehlte mich', 'bis bald'],
            'conectores_sugeridos' => ['zuerst', 'danach', 'weil'],
        ],
        'Escribe para mover una cita' => [
            'registro' => 'formal',
            'min_oraciones' => 5,
            'criterios' => ['explica el motivo', 'propone nueva hora o fecha', 'mantiene tono cortese'],
            'palabras_clave' => ['leider', 'ich komme spaeter', 'koennen wir', 'passt Ihnen'],
            'conectores_sugeridos' => ['weil', 'deshalb', 'vielleicht'],
        ],
        'Escribe una pequena reclamacion' => [
            'registro' => 'formal',
            'min_oraciones' => 5,
            'criterios' => ['describe el problema con claridad', 'pide una solucion concreta', 'evita sonar agresivo'],
            'palabras_clave' => ['leider', 'die Rechnung', 'das Problem', 'eine Loesung'],
            'conectores_sugeridos' => ['deshalb', 'ausserdem', 'bitte'],
        ],
        'Escribe una invitacion con respuesta' => [
            'registro' => 'informal',
            'min_oraciones' => 5,
            'criterios' => ['incluye dia, hora y plan', 'anade una razon o detalle', 'suena natural entre amigos'],
            'palabras_clave' => ['hast du Zeit', 'am Freitag', 'moechtest du', 'ich freue mich'],
            'conectores_sugeridos' => ['vielleicht', 'dann', 'wenn'],
        ],
        'Escribe una semana realista A2' => [
            'registro' => 'neutral',
            'min_oraciones' => 6,
            'criterios' => ['combina pasado, gestion y plan futuro', 'mantiene cronologia clara', 'usa al menos un motivo'],
            'palabras_clave' => ['gestern', 'ich musste', 'am Wochenende', 'ich werde'],
            'conectores_sugeridos' => ['zuerst', 'danach', 'deshalb'],
        ],
        'Opinion guiada B1' => [
            'registro' => 'argumentativo',
            'min_oraciones' => 6,
            'criterios' => ['expresa postura clara', 'da al menos dos razones', 'une ideas con conectores'],
            'palabras_clave' => ['ich finde', 'einerseits', 'andererseits', 'deshalb'],
            'conectores_sugeridos' => ['einerseits', 'andererseits', 'deshalb'],
        ],
        'Escribe un correo formal completo' => [
            'registro' => 'formal',
            'min_oraciones' => 6,
            'criterios' => ['abre con saludo formal', 'explica el problema o la consulta', 'cierra con formula adecuada'],
            'palabras_clave' => ['Sehr geehrte Damen und Herren', 'ich schreibe Ihnen', 'ich moechte wissen', 'Mit freundlichen Gruessen'],
            'conectores_sugeridos' => ['zunaechst', 'ausserdem', 'deshalb'],
        ],
        'Escribe un mensaje de incidencia con propuesta' => [
            'registro' => 'formal',
            'min_oraciones' => 6,
            'criterios' => ['explica el bloqueo', 'muestra impacto real', 'propone un siguiente paso concreto'],
            'palabras_clave' => ['leider', 'ich habe ein Problem', 'ich schlage vor', 'koennen wir'],
            'conectores_sugeridos' => ['weil', 'deshalb', 'damit'],
        ],
        'Escribe un resumen breve' => [
            'registro' => 'neutral',
            'min_oraciones' => 5,
            'criterios' => ['identifica el tema', 'resume dos ideas clave', 'cierra sin desviarse'],
            'palabras_clave' => ['der Beitrag', 'zunaechst', 'ausserdem', 'zusammenfassend'],
            'conectores_sugeridos' => ['zunaechst', 'ausserdem', 'zusammenfassend'],
        ],
        'Escribe un cierre B1 completo' => [
            'registro' => 'argumentativo',
            'min_oraciones' => 6,
            'criterios' => ['incluye opinion', 'resume una idea central', 'termina con propuesta clara'],
            'palabras_clave' => ['meiner Meinung nach', 'der wichtigste Punkt', 'ich schlage vor'],
            'conectores_sugeridos' => ['einerseits', 'andererseits', 'deshalb'],
        ],
        'Ensayo corto B2' => [
            'registro' => 'argumentativo',
            'min_oraciones' => 8,
            'criterios' => ['presenta tesis clara', 'trabaja un contraargumento', 'cierra con conclusion razonada'],
            'palabras_clave' => ['meiner Ansicht nach', 'einerseits', 'andererseits', 'dennoch', 'abschliessend'],
            'conectores_sugeridos' => ['einerseits', 'andererseits', 'dennoch'],
        ],
        'Escribe un informe breve' => [
            'registro' => 'analitico',
            'min_oraciones' => 7,
            'criterios' => ['formula observacion principal', 'aporta apoyo o dato', 'anade un matiz final'],
            'palabras_clave' => ['die Daten zeigen', 'auffaellig ist', 'allerdings', 'insgesamt'],
            'conectores_sugeridos' => ['zunaechst', 'ausserdem', 'allerdings'],
        ],
        'Escribe una postura argumentada B2' => [
            'registro' => 'argumentativo',
            'min_oraciones' => 7,
            'criterios' => ['presenta ventaja', 'expone objecion', 'desarrolla consecuencia'],
            'palabras_clave' => ['ein Vorteil', 'ein Nachteil', 'folglich', 'dennoch'],
            'conectores_sugeridos' => ['einerseits', 'dennoch', 'folglich'],
        ],
        'Tu plan realista de 90 dias' => [
            'registro' => 'neutral',
            'min_oraciones' => 6,
            'criterios' => ['define objetivos', 'nombra recursos', 'explica como medira avance'],
            'palabras_clave' => ['mein Ziel', 'jede Woche', 'ich werde', 'meinen Fortschritt'],
            'conectores_sugeridos' => ['zunaechst', 'danach', 'ausserdem'],
        ],
        'Escribe una sintesis con postura propia' => [
            'registro' => 'academico',
            'min_oraciones' => 8,
            'criterios' => ['resume tesis y argumentos', 'separa voz propia del texto fuente', 'anade observacion critica'],
            'palabras_clave' => ['der Text vertritt die These', 'der Autor argumentiert', 'kritisch sehe ich'],
            'conectores_sugeridos' => ['zunaechst', 'darueber hinaus', 'dennoch'],
        ],
        'Escribe tu plan maestro post-curso' => [
            'registro' => 'neutral',
            'min_oraciones' => 8,
            'criterios' => ['incluye escucha, lectura, escritura y expresion oral', 'marca frecuencia realista', 'define una revision de errores'],
            'palabras_clave' => ['ich werde', 'hoeren', 'lesen', 'schreiben', 'Fehler'],
            'conectores_sugeridos' => ['jede Woche', 'ausserdem', 'am Ende'],
        ],
        'Escribe tu diagnostico final y plan' => [
            'registro' => 'reflexivo',
            'min_oraciones' => 8,
            'criterios' => ['resume lo que ya puede hacer', 'nombra lo que aun trabaja', 'cierra con plan concreto'],
            'palabras_clave' => ['ich kann bereits', 'ich arbeite noch an', 'in den naechsten drei Monaten'],
            'conectores_sugeridos' => ['zunaechst', 'ausserdem', 'deshalb'],
        ],
    ];
}

function german_master_writing_enrichment_map(): array
{
    return [
        'Escribe tu dia tipico' => [
            'modelo_inicio' => 'Am Morgen stehe ich frueh auf und ...',
            'estructura_sugerida' => ['inicio con rutina matinal', 'desarrollo por momentos del dia', 'cierre con gusto o preferencia personal'],
            'movimientos_clave' => [
                ['label' => 'organiza el dia por momentos', 'patterns' => ['am Morgen', 'am Nachmittag', 'am Abend'], 'required_hits' => 2],
                ['label' => 'menciona gusto o rechazo', 'patterns' => ['gern', 'nicht gern', 'ich mag'], 'required_hits' => 1],
                ['label' => 'cierra con un detalle personal', 'patterns' => ['am Abend', 'heute', 'zum Schluss'], 'required_hits' => 1],
            ],
        ],
        'Tu micro rutina en aleman' => [
            'modelo_inicio' => 'Jeden Tag gehe ich zuerst ...',
            'estructura_sugerida' => ['abre con frecuencia o habito', 'describe dos acciones concretas', 'termina con contexto o destino'],
            'movimientos_clave' => [
                ['label' => 'marca una rutina real', 'patterns' => ['jeden Tag', 'normalerweise', 'oft'], 'required_hits' => 1],
                ['label' => 'incluye una accion concreta', 'patterns' => ['ich gehe', 'ich kaufe', 'ich fahre', 'ich lerne'], 'required_hits' => 1],
                ['label' => 'anade contexto cotidiano', 'patterns' => ['mit dem Bus', 'in der Stadt', 'nach Hause'], 'required_hits' => 1],
            ],
        ],
        'Escribe tu perfil funcional A1' => [
            'modelo_inicio' => 'Ich heisse ... und ich komme aus ...',
            'estructura_sugerida' => ['presentacion basica', 'rutina o ocupacion simple', 'necesidad o plan inmediato'],
            'movimientos_clave' => [
                ['label' => 'se presenta con datos basicos', 'patterns' => ['ich heisse', 'ich komme aus'], 'required_hits' => 2],
                ['label' => 'anade una rutina simple', 'patterns' => ['am Morgen', 'jeden Tag', 'ich arbeite', 'ich lerne'], 'required_hits' => 1],
                ['label' => 'expresa una necesidad cotidiana', 'patterns' => ['ich moechte', 'ich brauche', 'heute'], 'required_hits' => 1],
            ],
        ],
        'Correo informal A2' => [
            'modelo_inicio' => 'Hallo ..., gestern ist etwas passiert und ...',
            'estructura_sugerida' => ['saludo cercano e introduccion', 'desarrollo de lo ocurrido', 'cierre afectuoso o proximo contacto'],
            'movimientos_clave' => [
                ['label' => 'cuenta algo del pasado', 'patterns' => ['gestern', 'am Wochenende', 'ich habe', 'ich bin'], 'required_hits' => 1],
                ['label' => 'describe estado o situacion', 'patterns' => ['bei mir', 'ich fuehlte mich', 'zu Hause'], 'required_hits' => 1],
                ['label' => 'cierra de forma cercana', 'patterns' => ['bis bald', 'liebe Gruesse', 'ich hoffe'], 'required_hits' => 1],
            ],
        ],
        'Escribe para mover una cita' => [
            'modelo_inicio' => 'Guten Tag, leider komme ich spaeter und ...',
            'estructura_sugerida' => ['explica el problema inicial', 'propone nueva hora o fecha', 'cierre cortes y claro'],
            'movimientos_clave' => [
                ['label' => 'explica el motivo del cambio', 'patterns' => ['leider', 'weil', 'mein Zug', 'ich komme spaeter'], 'required_hits' => 2],
                ['label' => 'propone nueva hora o fecha', 'patterns' => ['koennen wir', 'verschieben', 'passt Ihnen', 'auf Freitag'], 'required_hits' => 1],
                ['label' => 'mantiene formula cortesa', 'patterns' => ['Guten Tag', 'bitte', 'Danke'], 'required_hits' => 1],
            ],
        ],
        'Escribe una pequena reclamacion' => [
            'modelo_inicio' => 'Guten Tag, leider gibt es ein Problem mit ...',
            'estructura_sugerida' => ['situa el problema', 'explica el impacto concreto', 'solicita una solucion o respuesta'],
            'movimientos_clave' => [
                ['label' => 'describe el problema', 'patterns' => ['leider', 'die Rechnung', 'das Problem', 'stimmt nicht'], 'required_hits' => 2],
                ['label' => 'pide una solucion concreta', 'patterns' => ['koennten Sie', 'eine Loesung', 'bitte', 'bringen'], 'required_hits' => 1],
                ['label' => 'mantiene tono controlado', 'patterns' => ['ich glaube', 'leider', 'bitte'], 'required_hits' => 1],
            ],
        ],
        'Escribe una invitacion con respuesta' => [
            'modelo_inicio' => 'Hast du am Freitag Zeit? Ich moechte ...',
            'estructura_sugerida' => ['lanza la invitacion', 'anade detalles de dia o plan', 'cierra con respuesta o confirmacion'],
            'movimientos_clave' => [
                ['label' => 'marca dia u hora', 'patterns' => ['am Freitag', 'am Samstag', 'um', 'heute Abend'], 'required_hits' => 1],
                ['label' => 'formula invitacion o plan', 'patterns' => ['moechtest du', 'hast du Zeit', 'ich moechte', 'wir koennen'], 'required_hits' => 1],
                ['label' => 'anade respuesta o cierre natural', 'patterns' => ['ich freue mich', 'leider kann ich nicht', 'gern'], 'required_hits' => 1],
            ],
        ],
        'Escribe una semana realista A2' => [
            'modelo_inicio' => 'Gestern musste ich ..., und am Wochenende werde ich ...',
            'estructura_sugerida' => ['abre con pasado reciente', 'incluye gestion o problema cotidiano', 'cierra con plan futuro realista'],
            'movimientos_clave' => [
                ['label' => 'combina pasado reciente', 'patterns' => ['gestern', 'ich musste', 'ich habe', 'ich bin'], 'required_hits' => 1],
                ['label' => 'incluye una gestion o problema', 'patterns' => ['Termin', 'Problem', 'anrufen', 'verschieben'], 'required_hits' => 1],
                ['label' => 'cierra con plan futuro', 'patterns' => ['am Wochenende', 'ich werde', 'naechste Woche'], 'required_hits' => 1],
            ],
        ],
        'Opinion guiada B1' => [
            'modelo_inicio' => 'Ich finde, dass ...',
            'estructura_sugerida' => ['tesis personal inicial', 'razones enlazadas', 'matiz o contraste antes del cierre'],
            'movimientos_clave' => [
                ['label' => 'declara postura clara', 'patterns' => ['ich finde', 'meiner Meinung nach'], 'required_hits' => 1],
                ['label' => 'da razones conectadas', 'patterns' => ['weil', 'deshalb', 'ausserdem'], 'required_hits' => 2],
                ['label' => 'anade contraste o matiz', 'patterns' => ['einerseits', 'andererseits', 'aber'], 'required_hits' => 1],
            ],
        ],
        'Escribe un correo formal completo' => [
            'modelo_inicio' => 'Sehr geehrte Damen und Herren, ich schreibe Ihnen, weil ...',
            'estructura_sugerida' => ['saludo formal y motivo', 'desarrollo de consulta o problema', 'cierre con formula adecuada'],
            'movimientos_clave' => [
                ['label' => 'abre con saludo formal', 'patterns' => ['Sehr geehrte Damen und Herren'], 'required_hits' => 1],
                ['label' => 'explica consulta o problema', 'patterns' => ['ich schreibe Ihnen', 'ich moechte wissen', 'Frist', 'Dokument'], 'required_hits' => 1],
                ['label' => 'cierra con formula adecuada', 'patterns' => ['Mit freundlichen Gruessen'], 'required_hits' => 1],
            ],
        ],
        'Escribe un mensaje de incidencia con propuesta' => [
            'modelo_inicio' => 'Leider habe ich ein Problem mit ...',
            'estructura_sugerida' => ['describe el fallo', 'explica la consecuencia', 'propone una solucion o siguiente paso'],
            'movimientos_clave' => [
                ['label' => 'explica el bloqueo', 'patterns' => ['leider', 'ich habe ein Problem', 'funktioniert nicht'], 'required_hits' => 1],
                ['label' => 'muestra impacto real', 'patterns' => ['deshalb', 'ich kann nicht', 'zu spaet'], 'required_hits' => 1],
                ['label' => 'propone un siguiente paso', 'patterns' => ['ich schlage vor', 'koennen wir', 'damit'], 'required_hits' => 1],
            ],
        ],
        'Escribe un resumen breve' => [
            'modelo_inicio' => 'Der Beitrag handelt von ...',
            'estructura_sugerida' => ['presenta el tema o fuente', 'resume ideas principales', 'cierra con sintesis breve'],
            'movimientos_clave' => [
                ['label' => 'identifica el tema o fuente', 'patterns' => ['der Beitrag', 'der Text', 'der Artikel'], 'required_hits' => 1],
                ['label' => 'resume dos ideas clave', 'patterns' => ['zunaechst', 'ausserdem', 'ein wichtiger Punkt'], 'required_hits' => 2],
                ['label' => 'cierra el resumen', 'patterns' => ['zusammenfassend', 'insgesamt', 'zum Schluss'], 'required_hits' => 1],
            ],
        ],
        'Escribe un cierre B1 completo' => [
            'modelo_inicio' => 'Meiner Meinung nach ist das Thema wichtig, weil ...',
            'estructura_sugerida' => ['retoma la idea central', 'resume argumento u opinion', 'termina con propuesta o conclusion'],
            'movimientos_clave' => [
                ['label' => 'incluye opinion personal', 'patterns' => ['meiner Meinung nach', 'ich finde'], 'required_hits' => 1],
                ['label' => 'resume una idea central', 'patterns' => ['der wichtigste Punkt', 'der Beitrag', 'zunaechst'], 'required_hits' => 1],
                ['label' => 'termina con propuesta', 'patterns' => ['ich schlage vor', 'deshalb', 'wir sollten'], 'required_hits' => 1],
            ],
        ],
        'Ensayo corto B2' => [
            'modelo_inicio' => 'Meiner Ansicht nach braucht dieses Thema eine differenzierte Betrachtung.',
            'estructura_sugerida' => ['tesis inicial clara', 'desarrollo con argumento y contraargumento', 'conclusion razonada'],
            'movimientos_clave' => [
                ['label' => 'presenta tesis clara', 'patterns' => ['meiner Ansicht nach', 'ich bin der Meinung', 'die zentrale Frage'], 'required_hits' => 1],
                ['label' => 'trabaja contraargumento', 'patterns' => ['einerseits', 'andererseits', 'dennoch', 'zwar'], 'required_hits' => 2],
                ['label' => 'cierra con conclusion razonada', 'patterns' => ['abschliessend', 'insgesamt', 'folglich'], 'required_hits' => 1],
            ],
        ],
        'Escribe un informe breve' => [
            'modelo_inicio' => 'Die Daten zeigen, dass ...',
            'estructura_sugerida' => ['observacion principal', 'apoyo con dato o ejemplo', 'matiz final o lectura general'],
            'movimientos_clave' => [
                ['label' => 'formula observacion principal', 'patterns' => ['die Daten zeigen', 'auffaellig ist', 'insgesamt'], 'required_hits' => 1],
                ['label' => 'aporta apoyo o dato', 'patterns' => ['zunaechst', 'ausserdem', 'zum Beispiel'], 'required_hits' => 1],
                ['label' => 'anade un matiz final', 'patterns' => ['allerdings', 'gleichzeitig', 'dennoch'], 'required_hits' => 1],
            ],
        ],
        'Escribe una postura argumentada B2' => [
            'modelo_inicio' => 'Ein Vorteil dieses Ansatzes ist, dass ...',
            'estructura_sugerida' => ['presenta una ventaja o idea base', 'introduce objecion o limite', 'cierra con consecuencia o posicion final'],
            'movimientos_clave' => [
                ['label' => 'presenta una ventaja', 'patterns' => ['ein Vorteil', 'positiv ist', 'nuetzlich ist'], 'required_hits' => 1],
                ['label' => 'expone una objecion', 'patterns' => ['ein Nachteil', 'kritisch ist', 'dennoch'], 'required_hits' => 1],
                ['label' => 'desarrolla una consecuencia', 'patterns' => ['folglich', 'deshalb', 'daher'], 'required_hits' => 1],
            ],
        ],
        'Tu plan realista de 90 dias' => [
            'modelo_inicio' => 'Mein Ziel fuer die naechsten 90 Tage ist ...',
            'estructura_sugerida' => ['define objetivo principal', 'describe rutina y recursos', 'explica como medira el avance'],
            'movimientos_clave' => [
                ['label' => 'define un objetivo claro', 'patterns' => ['mein Ziel', 'ich werde'], 'required_hits' => 1],
                ['label' => 'nombra recursos o rutina', 'patterns' => ['jede Woche', 'ich lese', 'ich hoere', 'ich schreibe'], 'required_hits' => 2],
                ['label' => 'explica como medira avance', 'patterns' => ['meinen Fortschritt', 'am Ende', 'ich kontrolliere'], 'required_hits' => 1],
            ],
        ],
        'Escribe una sintesis con postura propia' => [
            'modelo_inicio' => 'Der Text vertritt die These, dass ...',
            'estructura_sugerida' => ['resume la tesis del texto', 'separa la voz propia', 'cierra con observacion critica'],
            'movimientos_clave' => [
                ['label' => 'resume tesis o argumentos del texto', 'patterns' => ['der Text vertritt die These', 'der Autor argumentiert'], 'required_hits' => 1],
                ['label' => 'separa voz propia del texto', 'patterns' => ['aus meiner Sicht', 'ich wuerde', 'kritisch sehe ich'], 'required_hits' => 1],
                ['label' => 'anade observacion critica', 'patterns' => ['dennoch', 'allerdings', 'offen bleibt'], 'required_hits' => 1],
            ],
        ],
        'Escribe tu plan maestro post-curso' => [
            'modelo_inicio' => 'Ich werde jede Woche einen festen Plan fuer mein Deutsch einhalten.',
            'estructura_sugerida' => ['meta general post-curso', 'rutina por habilidades', 'revision y seguimiento de errores'],
            'movimientos_clave' => [
                ['label' => 'incluye varias habilidades', 'patterns' => ['hoeren', 'lesen', 'schreiben', 'sprechen'], 'required_hits' => 3],
                ['label' => 'marca frecuencia realista', 'patterns' => ['jede Woche', 'zweimal pro Woche', 'einmal im Monat'], 'required_hits' => 1],
                ['label' => 'define revision de errores', 'patterns' => ['Fehler', 'ich kontrolliere', 'ich ueberpruefe'], 'required_hits' => 1],
            ],
        ],
        'Escribe tu diagnostico final y plan' => [
            'modelo_inicio' => 'Ich kann bereits ... , aber ich arbeite noch an ...',
            'estructura_sugerida' => ['balance de lo que ya domina', 'punto que aun necesita trabajo', 'plan concreto para el siguiente tramo'],
            'movimientos_clave' => [
                ['label' => 'resume lo que ya puede hacer', 'patterns' => ['ich kann bereits', 'ich kann jetzt'], 'required_hits' => 1],
                ['label' => 'nombra lo que aun trabaja', 'patterns' => ['ich arbeite noch an', 'ich moechte weiter'], 'required_hits' => 1],
                ['label' => 'cierra con plan concreto', 'patterns' => ['in den naechsten drei Monaten', 'ich werde', 'mein Plan'], 'required_hits' => 1],
            ],
        ],
    ];
}

function german_master_writing_grammar_profiles(): array
{
    return [
        'Escribe tu dia tipico' => [
            'patrones_morfosintacticos' => [
                ['label' => 'organiza el dia con marcadores temporales', 'rule' => 'temporal_sequence_day'],
                ['label' => 'usa un verbo cotidiano o separable con naturalidad', 'rule' => 'everyday_action_frame'],
            ],
        ],
        'Tu micro rutina en aleman' => [
            'patrones_morfosintacticos' => [
                ['label' => 'marca secuencia o frecuencia basica', 'rule' => 'temporal_sequence_basic'],
                ['label' => 'incluye una accion cotidiana clara', 'rule' => 'everyday_action_frame'],
            ],
        ],
        'Escribe tu perfil funcional A1' => [
            'patrones_morfosintacticos' => [
                ['label' => 'se presenta en primera persona con estructura basica', 'rule' => 'present_identity_frame'],
                ['label' => 'expresa una necesidad o deseo simple', 'rule' => 'modal_need_frame'],
            ],
        ],
        'Correo informal A2' => [
            'patrones_morfosintacticos' => [
                ['label' => 'usa Perfekt con auxiliar y participio', 'rule' => 'perfekt'],
                ['label' => 'marca pasado o secuencia con naturalidad', 'rule' => 'temporal_sequence_past'],
            ],
        ],
        'Escribe para mover una cita' => [
            'patrones_morfosintacticos' => [
                ['label' => 'formula una peticion cortés', 'rule' => 'formal_request'],
                ['label' => 'incluye dia u hora para reorganizar la cita', 'rule' => 'day_time_reference'],
                ['label' => 'usa Konjunktiv II cortes para negociar el cambio', 'rule' => 'polite_konjunktiv'],
            ],
        ],
        'Escribe una pequena reclamacion' => [
            'patrones_morfosintacticos' => [
                ['label' => 'nombra con claridad el problema', 'rule' => 'problem_statement'],
                ['label' => 'pide una solucion con tono formal', 'rule' => 'formal_request'],
                ['label' => 'mantiene apertura o contacto formal estable', 'rule' => 'formal_salutation'],
            ],
        ],
        'Escribe una invitacion con respuesta' => [
            'patrones_morfosintacticos' => [
                ['label' => 'formula una invitacion o propuesta directa', 'rule' => 'invitation_frame'],
                ['label' => 'ancla el plan en un dia u hora', 'rule' => 'day_time_reference'],
            ],
        ],
        'Escribe una semana realista A2' => [
            'patrones_morfosintacticos' => [
                ['label' => 'combina pasado con Perfekt', 'rule' => 'perfekt'],
                ['label' => 'proyecta planes con werden o futuro cercano', 'rule' => 'future_werden'],
            ],
        ],
        'Opinion guiada B1' => [
            'patrones_morfosintacticos' => [
                ['label' => 'introduce una opinion con subordinada bien cerrada', 'rule' => 'subordinate_clause_strict'],
                ['label' => 'anade contraste o matiz argumentativo', 'rule' => 'contrast_frame'],
                ['label' => 'sostiene la opinion con causa o consecuencia', 'rule' => 'consequence_frame'],
            ],
        ],
        'Escribe un correo formal completo' => [
            'patrones_morfosintacticos' => [
                ['label' => 'formula una consulta o peticion formal', 'rule' => 'formal_request'],
                ['label' => 'cierra con formula formal estable', 'rule' => 'formal_closing'],
                ['label' => 'incluye una relativa o detalle especificador propio de B1.2', 'rule' => 'relative_clause'],
            ],
        ],
        'Escribe un mensaje de incidencia con propuesta' => [
            'patrones_morfosintacticos' => [
                ['label' => 'expone el problema con claridad', 'rule' => 'problem_statement'],
                ['label' => 'propone un siguiente paso o solucion', 'rule' => 'proposal_frame'],
            ],
        ],
        'Escribe un resumen breve' => [
            'patrones_morfosintacticos' => [
                ['label' => 'presenta la fuente o el tema con formula de resumen', 'rule' => 'source_reference'],
                ['label' => 'cierra con una sintesis breve', 'rule' => 'summary_closing'],
            ],
        ],
        'Escribe un cierre B1 completo' => [
            'patrones_morfosintacticos' => [
                ['label' => 'introduce postura con formula de opinion', 'rule' => 'argument_opinion_frame'],
                ['label' => 'termina con propuesta o consecuencia', 'rule' => 'proposal_frame'],
            ],
        ],
        'Ensayo corto B2' => [
            'patrones_morfosintacticos' => [
                ['label' => 'plantea una tesis argumentativa', 'rule' => 'argument_opinion_frame'],
                ['label' => 'usa contraste o concesion de nivel B2', 'rule' => 'contrast_frame'],
                ['label' => 'estructura el argumento con una subordinada o concesion completa', 'rule' => 'subordinate_clause_strict'],
            ],
        ],
        'Escribe un informe breve' => [
            'patrones_morfosintacticos' => [
                ['label' => 'abre con formula analitica de informe', 'rule' => 'analytic_frame'],
                ['label' => 'matiza con contraste o reserva', 'rule' => 'contrast_frame'],
                ['label' => 'usa nominalizacion o formulacion densa propia del informe', 'rule' => 'nominalization_frame'],
                ['label' => 'integra una construccion pasiva o impersonal', 'rule' => 'passive_voice'],
            ],
        ],
        'Escribe una postura argumentada B2' => [
            'patrones_morfosintacticos' => [
                ['label' => 'presenta ventaja y objecion', 'rule' => 'advantage_disadvantage'],
                ['label' => 'cierra con una consecuencia razonada', 'rule' => 'consequence_frame'],
                ['label' => 'equilibra la postura con un marco doble de contraste', 'rule' => 'argument_balance_frame'],
            ],
        ],
        'Tu plan realista de 90 dias' => [
            'patrones_morfosintacticos' => [
                ['label' => 'usa marcadores de plan con werden o meta explicita', 'rule' => 'future_werden'],
                ['label' => 'ancla el plan con frecuencia o rutina', 'rule' => 'frequency_plan_frame'],
            ],
        ],
        'Escribe una sintesis con postura propia' => [
            'patrones_morfosintacticos' => [
                ['label' => 'separa voz de la fuente y voz propia', 'rule' => 'source_voice_split'],
                ['label' => 'anade contraste o reserva critica', 'rule' => 'contrast_frame'],
                ['label' => 'usa una formulacion nominal o academica densa', 'rule' => 'nominalization_frame'],
            ],
        ],
        'Escribe tu plan maestro post-curso' => [
            'patrones_morfosintacticos' => [
                ['label' => 'marca frecuencia y plan continuo', 'rule' => 'frequency_plan_frame'],
                ['label' => 'integra varias habilidades del idioma', 'rule' => 'skill_list_frame'],
            ],
        ],
        'Escribe tu diagnostico final y plan' => [
            'patrones_morfosintacticos' => [
                ['label' => 'equilibra logro actual y proximo reto', 'rule' => 'reflection_frame'],
                ['label' => 'cierra con proyeccion o plan futuro', 'rule' => 'future_werden'],
            ],
        ],
    ];
}

function german_master_writing_diagnostic_profiles(): array
{
    $timeFront = [
        'label' => 'Si abres con heute, gestern o danach, el verbo finito suele ir pronto en segundo lugar.',
        'rule' => 'verb_second_fronted_time',
    ];
    $subordinate = [
        'label' => 'En subordinadas con weil, dass, wenn u obwohl, el verbo conjugado suele cerrar la clausula.',
        'rule' => 'subordinate_verb_final',
    ];
    $relativeComma = [
        'label' => 'Si haces una relativa, separala con comas: ..., die/der/das ...',
        'rule' => 'relative_clause_commas',
    ];
    $accusativePrep = [
        'label' => 'Con fur, ohne, gegen o um evita articulos de dativo por error.',
        'rule' => 'accusative_preposition_case',
    ];
    $dativePrep = [
        'label' => 'Con mit, nach, bei, von, zu o aus revisa bien el caso que sigue.',
        'rule' => 'dative_preposition_case',
    ];

    return [
        'Escribe tu dia tipico' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Tu micro rutina en aleman' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Escribe tu perfil funcional A1' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Correo informal A2' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Escribe para mover una cita' => ['diagnosticos_gramaticales' => [$timeFront, $subordinate, $dativePrep]],
        'Escribe una pequena reclamacion' => ['diagnosticos_gramaticales' => [$accusativePrep]],
        'Escribe una invitacion con respuesta' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Escribe una semana realista A2' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Opinion guiada B1' => ['diagnosticos_gramaticales' => [$subordinate]],
        'Escribe un correo formal completo' => ['diagnosticos_gramaticales' => [$subordinate, $relativeComma]],
        'Escribe un mensaje de incidencia con propuesta' => ['diagnosticos_gramaticales' => [$subordinate, $accusativePrep]],
        'Escribe un resumen breve' => ['diagnosticos_gramaticales' => [$subordinate]],
        'Escribe un cierre B1 completo' => ['diagnosticos_gramaticales' => [$subordinate]],
        'Ensayo corto B2' => ['diagnosticos_gramaticales' => [$subordinate]],
        'Escribe un informe breve' => ['diagnosticos_gramaticales' => [$subordinate]],
        'Escribe una postura argumentada B2' => ['diagnosticos_gramaticales' => [$subordinate]],
        'Tu plan realista de 90 dias' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Escribe una sintesis con postura propia' => ['diagnosticos_gramaticales' => [$subordinate, $relativeComma]],
        'Escribe tu plan maestro post-curso' => ['diagnosticos_gramaticales' => [$timeFront]],
        'Escribe tu diagnostico final y plan' => ['diagnosticos_gramaticales' => [$timeFront]],
    ];
}

function german_master_writing_register_guides(): array
{
    return [
        'informal' => [
            'marcadores_registro' => ['Hallo ...', 'du', 'dir', 'bis bald', 'Liebe Gruesse'],
            'patrones_registro_objetivo' => ['hallo', 'du', 'dir', 'bis bald', 'liebe gruesse'],
            'patrones_registro_evitar' => ['sehr geehrte', 'mit freundlichen gruesse', 'mit freundlichen gruessen'],
            'errores_a_evitar' => ['no abras como correo oficial si la consigna es cercana', 'evita cierres demasiado rigidos o burocraticos', 'manten una voz natural entre amigos o conocidos'],
            'focos_gramaticales' => ['orden basico claro en oraciones principales', 'verbos y marcadores temporales simples', 'cierres naturales sin formulas burocraticas'],
            'errores_gramaticales_comunes' => ['evita dejar el verbo principal perdido al final sin necesidad', 'no mezcles saludo formal con voz de amigos', 'cuida puntos y mayusculas al separar ideas'],
        ],
        'formal' => [
            'marcadores_registro' => ['Sehr geehrte ...', 'ich schreibe Ihnen', 'koennten Sie', 'vielen Dank', 'Mit freundlichen Gruessen'],
            'patrones_registro_objetivo' => ['sehr geehrte', 'ich schreibe ihnen', 'koennten sie', 'vielen dank', 'mit freundlichen gruessen'],
            'patrones_registro_evitar' => ['hallo', 'hi', 'bis bald', 'liebe gruesse', 'tschuess'],
            'errores_a_evitar' => ['no mezcles saludo informal con cierre formal', 'evita sonar brusco o demasiado directo', 'no cierres con formulas cercanas si el contexto es institucional'],
            'focos_gramaticales' => ['saludo y cierre formales completos', 'Sie, Ihnen e Ihr en mayuscula cuando corresponda', 'peticiones corteses con koennen, moechten o wuerden'],
            'errores_gramaticales_comunes' => ['no escribas sie o ihnen en minuscula si es trato formal', 'evita frases telegráficas sin cierre de oracion', 'no cortes la cortesia con comandos demasiado secos'],
        ],
        'neutral' => [
            'marcadores_registro' => ['ich moechte', 'zunaechst', 'danach', 'insgesamt'],
            'patrones_registro_objetivo' => ['ich moechte', 'zunaechst', 'danach', 'insgesamt'],
            'patrones_registro_evitar' => ['yo creo', 'hello', 'whats up'],
            'errores_a_evitar' => ['evita saltar entre un tono demasiado coloquial y uno demasiado academico', 'no llenes la respuesta de traducciones o apoyos en espanol', 'manten frases claras y directas'],
            'focos_gramaticales' => ['cronologia clara con conectores basicos', 'frases completas con puntuacion estable', 'presente, pasado o futuro coherentes con la consigna'],
            'errores_gramaticales_comunes' => ['evita cadenas largas sin punto', 'no cambies de tiempo verbal sin motivo claro', 'cuida mayuscula al inicio de cada idea nueva'],
        ],
        'argumentativo' => [
            'marcadores_registro' => ['meiner Meinung nach', 'einerseits', 'andererseits', 'dennoch', 'folglich'],
            'patrones_registro_objetivo' => ['meiner meinung nach', 'meiner ansicht nach', 'einerseits', 'andererseits', 'dennoch', 'folglich', 'abschliessend'],
            'patrones_registro_evitar' => ['cool', 'super', 'hallo', 'bis bald'],
            'errores_a_evitar' => ['no te quedes en opinion vacia sin razones', 'evita un tono demasiado coloquial para una postura argumentada', 'no cierres sin conclusion o consecuencia'],
            'focos_gramaticales' => ['tesis clara con weil, dass o formula de opinion', 'contraste con einerseits, andererseits o dennoch', 'cierre razonado con deshalb, folglich o abschliessend'],
            'errores_gramaticales_comunes' => ['no acumules opiniones cortas sin unirlas', 'evita repetir ich finde sin desarrollar razones', 'cuida que la subordinada no rompa el orden verbal'],
        ],
        'analitico' => [
            'marcadores_registro' => ['die Daten zeigen', 'auffaellig ist', 'zunaechst', 'allerdings', 'insgesamt'],
            'patrones_registro_objetivo' => ['die daten zeigen', 'auffaellig ist', 'zunaechst', 'allerdings', 'insgesamt'],
            'patrones_registro_evitar' => ['cool', 'super', 'ich fuehle', 'bis bald'],
            'errores_a_evitar' => ['no conviertas el informe en una opinion sin apoyo', 'evita adjetivos coloquiales o vagos', 'manten una lectura de datos y no solo impresiones'],
            'focos_gramaticales' => ['formulas de observacion y dato', 'contraste medido con allerdings o zugleich', 'cierre analitico sin opinion coloquial'],
            'errores_gramaticales_comunes' => ['no reemplaces analisis por reaccion personal', 'evita encadenar datos sin marcar relaciones', 'cuida la puntuacion de frases densas'],
        ],
        'academico' => [
            'marcadores_registro' => ['der Text vertritt die These', 'der Autor argumentiert', 'darueber hinaus', 'kritisch sehe ich', 'abschliessend'],
            'patrones_registro_objetivo' => ['der text vertritt die these', 'der autor argumentiert', 'darueber hinaus', 'kritisch sehe ich', 'abschliessend'],
            'patrones_registro_evitar' => ['hallo', 'cool', 'super', 'bis bald'],
            'errores_a_evitar' => ['no mezcles voz academica con expresiones de chat', 'evita resumir sin separar la voz del texto y la tuya', 'manten distancia y precision terminologica'],
            'focos_gramaticales' => ['sintesis fiel de la fuente', 'distancia critica con conectores altos', 'cierres precisos con formulacion academica'],
            'errores_gramaticales_comunes' => ['no mezcles resumen y postura sin transicion', 'evita formulas de chat dentro de una sintesis academica', 'cuida que las frases largas sigan bien puntuadas'],
        ],
        'reflexivo' => [
            'marcadores_registro' => ['ich kann bereits', 'ich arbeite noch an', 'mein Plan', 'in den naechsten drei Monaten'],
            'patrones_registro_objetivo' => ['ich kann bereits', 'ich arbeite noch an', 'mein plan', 'in den naechsten drei monaten'],
            'patrones_registro_evitar' => ['hallo', 'sehr geehrte', 'cool'],
            'errores_a_evitar' => ['no dejes la reflexion en frases demasiado vagas', 'evita promesas sin plan concreto', 'manten un balance entre logro actual y siguiente paso'],
            'focos_gramaticales' => ['balance entre presente y futuro', 'verbos de capacidad y plan', 'conexion clara entre logro, limite y proximo paso'],
            'errores_gramaticales_comunes' => ['no conviertas la reflexion en una lista sin frases completas', 'evita repetir ich kann sin ampliar la idea', 'marca bien la transicion al plan futuro'],
        ],
    ];
}

function german_master_listening_prompts_from_transcription(string $transcription): array
{
    $transcription = trim(preg_replace('/\s+/u', ' ', $transcription));
    if ($transcription === '') {
        return [];
    }

    $countWords = static function(string $text): int {
        $words = preg_split('/\s+/u', trim($text));
        return is_array($words) ? count(array_filter($words, static fn($word) => trim((string) $word) !== '')) : 0;
    };

    $splitByConjunction = static function(string $text) use ($countWords): ?array {
        $patterns = [' und ', ' aber ', ' weil ', ' allerdings ', ' folglich ', ' zugleich ', ' sondern ', ' dass ', ' wenn '];
        foreach ($patterns as $needle) {
            $pos = mb_stripos($text, $needle, 0, 'UTF-8');
            if ($pos === false) {
                continue;
            }

            $left = trim(mb_substr($text, 0, $pos, 'UTF-8'));
            $right = trim(mb_substr($text, $pos + mb_strlen($needle, 'UTF-8'), null, 'UTF-8'));
            $connector = trim($needle);

            if ($countWords($left) >= 4 && $countWords($right) >= 4) {
                if (!preg_match('/[.!?]$/u', $left)) {
                    $left .= '.';
                }
                $right = mb_strtoupper(mb_substr($connector, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($connector, 1, null, 'UTF-8') . ' ' . ltrim($right);
                if (!preg_match('/[.!?]$/u', $right)) {
                    $right .= '.';
                }
                return [$left, $right];
            }
        }

        return null;
    };

    $clauses = preg_split('/,\s*/u', $transcription);
    $clauses = array_values(array_filter(array_map(static fn($item): string => trim((string) $item), $clauses), static fn(string $item): bool => $item !== ''));

    $segments = [];
    foreach ($clauses as $clause) {
        if (empty($segments)) {
            $segments[] = $clause;
            continue;
        }

        $lastIndex = count($segments) - 1;
        if ($countWords($segments[$lastIndex]) < 4 || $countWords($clause) < 4) {
            $segments[$lastIndex] .= ', ' . $clause;
        } else {
            $segments[] = $clause;
        }
    }

    if (count($segments) > 1 && $countWords($segments[count($segments) - 1]) < 4) {
        $segments[count($segments) - 2] .= ', ' . array_pop($segments);
    }

    $refined = [];
    foreach ($segments as $segment) {
        if ($countWords($segment) > 11 && count($refined) < 2) {
            $split = $splitByConjunction($segment);
            if ($split) {
                foreach ($split as $part) {
                    $refined[] = $part;
                }
                continue;
            }
        }
        $refined[] = $segment;
    }
    $segments = $refined;

    if (count($segments) === 1 && $countWords($segments[0]) > 9) {
        $split = $splitByConjunction($segments[0]);
        if ($split) {
            $segments = $split;
        }
    }

    if (count($segments) === 1 && $countWords($segments[0]) > 14) {
        $tokens = preg_split('/\s+/u', $segments[0]);
        if (is_array($tokens) && count($tokens) >= 8) {
            $mid = (int) floor(count($tokens) / 2);
            $left = trim(implode(' ', array_slice($tokens, 0, $mid)));
            $right = trim(implode(' ', array_slice($tokens, $mid)));
            $segments = [$left, $right];
        }
    }

    if (count($segments) > 3) {
        $head = array_slice($segments, 0, 2);
        $tail = implode(', ', array_slice($segments, 2));
        $segments = array_merge($head, [$tail]);
    }

    $prompts = [];
    foreach (array_values($segments) as $index => $segment) {
        $text = rtrim(trim($segment), ' .;') . '.';
        $prompts[] = [
            'id' => 'listen_' . ($index + 1),
            'descripcion' => 'Bloque ' . ($index + 1),
            'texto_tts' => $text,
            'transcripcion' => $text,
            'palabras_clave' => german_expand_extract_keywords($text, 4),
        ];
    }

    return $prompts;
}

function german_master_listening_specs(): array
{
    return [
        'Escucha la mini presentacion' => [
            'intro' => 'Escucha dos bloques cortos y transcribe cada uno por separado.',
            'prompts' => [
                'Guten Tag, ich heiße Daniel.',
                'Ich komme aus Chile.',
            ],
        ],
        'Escucha: registro de estudiante nuevo' => [
            'intro' => 'Escucha tres datos de registro y escribe cada bloque antes de pasar al siguiente.',
            'prompts' => [
                'Guten Tag, ich heisse Martin.',
                'Ich bin dreiundzwanzig Jahre alt.',
                'Mein Deutschkurs ist um neun Uhr.',
            ],
        ],
        'Escucha: rutina y hora de salida' => [
            'intro' => 'Escucha la rutina por bloques: hora, accion principal y cierre del dia.',
            'prompts' => [
                'Ich stehe um sechs Uhr auf.',
                'Dann raeume ich mein Zimmer auf und fruehstuecke schnell.',
                'Am Abend sehe ich fern und gehe um zehn Uhr schlafen.',
            ],
        ],
        'Escucha: compra y camino en la ciudad' => [
            'intro' => 'Escucha primero la compra y luego la pregunta de orientacion.',
            'prompts' => [
                'Ich moechte zwei Brote und eine Flasche Wasser, bitte.',
                'Entschuldigung, wo ist der Bahnhof?',
                'Gehen Sie geradeaus und dann nach links.',
            ],
        ],
        'Escucha: resumen de supervivencia A1' => [
            'intro' => 'Trabaja la presentacion A1 por bloques claros: identidad, rutina y necesidad cotidiana.',
            'prompts' => [
                'Guten Tag, ich heisse Marta.',
                'Ich komme aus Peru und arbeite am Morgen.',
                'Ich moechte heute ein Ticket kaufen.',
            ],
        ],
        'Escucha: el fin de semana y el retraso' => [
            'intro' => 'Escucha primero el fin de semana y luego el problema de transporte.',
            'prompts' => [
                'Am Wochenende habe ich meine Eltern besucht.',
                'Der Zug war spaet und ich bin erst um neun Uhr angekommen.',
            ],
        ],
        'Escucha: retraso y cambio de horario' => [
            'intro' => 'Escucha la gestion completa en dos bloques: contexto y solucion.',
            'prompts' => [
                'Guten Morgen, ich habe um zehn Uhr einen Termin.',
                'Mein Zug ist spaet und ich komme zehn Minuten spaeter.',
            ],
        ],
        'Escucha: pedido y pequena reclamacion' => [
            'intro' => 'Escucha el pedido, la correccion y el cierre cortés por separado.',
            'prompts' => [
                'Ich nehme die Suppe und einen Tee, bitte.',
                'Entschuldigung, die Suppe ist leider kalt.',
                'Koennten Sie mir bitte eine neue bringen?',
            ],
        ],
        'Escucha: plan de fin de semana' => [
            'intro' => 'Escucha el plan por pasos y transcribe cada tramo.',
            'prompts' => [
                'Am Samstag werden wir zuerst einkaufen.',
                'Dann fahren wir zu Anna.',
                'Sie hat Geburtstag.',
            ],
        ],
        'Escucha: autonomia A2' => [
            'intro' => 'Escucha una gestion cotidiana en tres partes: retraso, motivo y propuesta.',
            'prompts' => [
                'Guten Tag, ich komme spaeter.',
                'Mein Zug hat Verspaetung.',
                'Ich moechte den Termin auf Freitag verschieben.',
            ],
        ],
        'Escucha: llamada a la oficina' => [
            'intro' => 'Escucha el motivo de la llamada y el detalle administrativo por separado.',
            'prompts' => [
                'Guten Tag, ich rufe an.',
                'Mir fehlt noch ein Dokument.',
                'Die Frist endet am Freitag.',
            ],
        ],
        'Escucha: incidencia de ultima hora' => [
            'intro' => 'Escucha el problema y luego la consecuencia practica.',
            'prompts' => [
                'Ich habe ein Problem mit der Datei.',
                'Ich koennte die Abgabe heute nicht rechtzeitig schicken.',
            ],
        ],
        'Escucha: correo y propuesta B1' => [
            'intro' => 'Escucha la consulta formal por bloques: motivo, pregunta y propuesta.',
            'prompts' => [
                'Ich schreibe Ihnen, weil ich eine Frage zur Anmeldung habe.',
                'Ich schlage vor, den Termin auf Montag zu verschieben.',
            ],
        ],
        'Escucha: opinion breve con contraste' => [
            'intro' => 'Escucha la postura, la razon y el matiz como tres bloques distintos.',
            'prompts' => [
                'Ich finde, dass digitale Kurse sehr praktisch sind.',
                'Sie sparen Zeit und geben mehr Flexibilitaet im Alltag.',
                'Trotzdem braucht man klare Betreuung, damit niemand den Anschluss verliert.',
            ],
        ],
        'Escucha academica B2' => [
            'intro' => 'Escucha la idea a favor y luego el matiz critico.',
            'prompts' => [
                'Einerseits beschleunigt kuenstliche Intelligenz viele Prozesse.',
                'Andererseits wirft sie ernste ethische Fragen auf.',
            ],
        ],
        'Escucha: resumen y mini presentacion B1' => [
            'intro' => 'Escucha el tema, el resumen y el cierre oral por separado.',
            'prompts' => [
                'Der Bericht handelt von Medienkonsum im Alltag.',
                'Viele Jugendliche informieren sich zuerst ueber soziale Netzwerke.',
                'Zum Schluss moechte ich kurz erklaeren, warum Medienkompetenz so wichtig ist.',
            ],
        ],
        'Escucha: mini informe oral' => [
            'intro' => 'Escucha primero el dato central y luego la limitacion del informe.',
            'prompts' => [
                'Die Daten zeigen, dass flexible Lernformate zunehmen.',
                'Menschen mit schwacher digitaler Ausstattung profitieren allerdings weniger davon.',
            ],
        ],
        'Escucha: comentario B2 con matices' => [
            'intro' => 'Escucha el beneficio, la reserva y la conclusion como bloques separados.',
            'prompts' => [
                'Digitale Werkzeuge sparen zwar Zeit.',
                'Allerdings profitieren nicht alle Gruppen gleich davon.',
                'Folglich braucht man gerechtere Zugangsmodelle.',
            ],
        ],
        'Escucha avanzada C1' => [
            'intro' => 'Escucha primero la tesis general y luego la exigencia cognitiva que se anade.',
            'prompts' => [
                'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse.',
                'Sie verlangt auch die Faehigkeit, unterschiedliche Perspektiven praezise einzuordnen.',
            ],
        ],
        'Escucha: sintesis oral academica' => [
            'intro' => 'Escucha la sintesis y luego el matiz sobre las preguntas abiertas.',
            'prompts' => [
                'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Regulierung plaediert.',
                'Zugleich weist er auf offene Umsetzungsfragen hin.',
            ],
        ],
        'Escucha: conclusion de presentacion' => [
            'intro' => 'Escucha la conclusion en dos bloques: condicion y equilibrio final.',
            'prompts' => [
                'Abschliessend laesst sich sagen, dass nachhaltige Innovation nur dann gelingt.',
                'Technischer Fortschritt und soziale Verantwortung muessen zusammen gedacht werden.',
            ],
        ],
        'Escucha: cierre integral C1' => [
            'intro' => 'Escucha el cierre final por bloques: tesis, condicion y ultima exigencia.',
            'prompts' => [
                'Der Beitrag legt nahe, dass nachhaltige Innovation nur dann gelingt.',
                'Technischer Fortschritt und soziale Verantwortung muessen zusammen gedacht werden.',
                'Dazu braucht es auch eine klare Umsetzung.',
            ],
        ],
    ];
}

function german_master_listening_spec_for_title(string $title): ?array
{
    $normalizedTitle = mb_strtolower(trim($title), 'UTF-8');
    foreach (german_master_listening_specs() as $specTitle => $spec) {
        if (mb_strtolower(trim((string) $specTitle), 'UTF-8') === $normalizedTitle) {
            return $spec;
        }
    }

    return null;
}

function german_master_oral_profile_for_title(string $title, string $type): array
{
    $level = german_master_level_from_title($title);

    $profiles = [
        'escucha' => [
            'A1' => ['normal' => 0.84, 'slow' => 0.68, 'pitch' => 1.0, 'pause' => 700, 'goal' => 'identidad y datos basicos'],
            'A2' => ['normal' => 0.88, 'slow' => 0.72, 'pitch' => 1.0, 'pause' => 650, 'goal' => 'gestion cotidiana con algo mas de continuidad'],
            'B1' => ['normal' => 0.92, 'slow' => 0.78, 'pitch' => 1.0, 'pause' => 620, 'goal' => 'seguir ideas completas y no solo palabras sueltas'],
            'B2' => ['normal' => 0.96, 'slow' => 0.82, 'pitch' => 0.98, 'pause' => 580, 'goal' => 'captar matiz, contraste y conclusion'],
            'C1' => ['normal' => 1.0, 'slow' => 0.86, 'pitch' => 0.98, 'pause' => 540, 'goal' => 'seguir sintesis, densidad y registro alto'],
        ],
        'pronunciacion' => [
            'A1' => ['normal' => 0.86, 'slow' => 0.70, 'pitch' => 1.0, 'pause' => 700, 'goal' => 'claridad y ritmo basico'],
            'A2' => ['normal' => 0.88, 'slow' => 0.74, 'pitch' => 1.0, 'pause' => 650, 'goal' => 'fluidez cotidiana con pausas cortas'],
            'B1' => ['normal' => 0.92, 'slow' => 0.78, 'pitch' => 1.0, 'pause' => 620, 'goal' => 'articular opinion y propuesta con claridad'],
            'B2' => ['normal' => 0.96, 'slow' => 0.82, 'pitch' => 0.98, 'pause' => 580, 'goal' => 'sostener matiz y contraargumento sin correr'],
            'C1' => ['normal' => 0.98, 'slow' => 0.84, 'pitch' => 0.98, 'pause' => 560, 'goal' => 'mantener registro alto y precision estable'],
        ],
    ];

    return $profiles[$type][$level] ?? $profiles[$type]['A1'];
}

function german_master_activity_topic_fragment(string $title, string $prefix = ''): string
{
    $title = trim($title);
    if ($prefix !== '' && stripos($title, $prefix) === 0) {
        $title = trim(substr($title, strlen($prefix)));
    }

    return $title !== '' ? $title : 'esta practica';
}

function german_master_writing_instruction(array $activity, array $content): string
{
    $title = (string) ($activity['titulo'] ?? 'esta practica');
    $minPalabras = (int) ($content['min_palabras'] ?? 0);
    $maxOrientativo = $minPalabras > 0 ? ($minPalabras + max(20, (int) round($minPalabras * 0.20))) : 0;
    $criterios = array_values(array_filter(array_map('strval', (array) ($content['criterios'] ?? [])), static fn($item) => trim($item) !== ''));
    $registro = (string) ($content['registro'] ?? 'neutral');

    $lineas = [];
    if ($minPalabras > 0) {
        $lineas[] = 'Escribe en aleman unas ' . $minPalabras . ' a ' . $maxOrientativo . ' palabras para "' . $title . '".';
    } else {
        $lineas[] = 'Escribe en aleman un texto completo para "' . $title . '".';
    }

    if (!empty($criterios)) {
        $lineas[] = 'Asegura al menos: ' . implode('; ', array_slice($criterios, 0, 2)) . '.';
    }

    $lineas[] = 'Mantiene un registro ' . $registro . ' y evita listas sueltas.';

    return implode(' ', $lineas);
}

function german_master_listening_instruction(array $activity, array $content): string
{
    $topic = german_master_activity_topic_fragment((string) ($activity['titulo'] ?? 'esta escucha'), 'Escucha:');
    $prompts = is_array($content['preguntas'] ?? null) ? $content['preguntas'] : [];
    $blocks = count($prompts);
    $goal = trim((string) ($content['practice_goal'] ?? ''));

    $text = $blocks > 1
        ? 'Escucha ' . $blocks . ' bloques sobre ' . $topic . ' y transcribe cada uno por separado antes de pasar al siguiente.'
        : 'Escucha el fragmento sobre ' . $topic . ' y escribe la frase completa con el mejor detalle posible.';

    if ($goal !== '') {
        $text .= ' Enfocate en ' . $goal . '.';
    }

    return $text;
}

function german_master_pronunciation_instruction(array $activity, array $content): string
{
    $topic = german_master_activity_topic_fragment((string) ($activity['titulo'] ?? 'esta pronunciacion'), 'Pronuncia');
    $items = is_array($content) ? $content : [];
    $count = count($items);
    $goal = '';
    if (!empty($items[0]['practice_goal'])) {
        $goal = trim((string) $items[0]['practice_goal']);
    }

    $text = 'Lee en voz alta ' . max(1, $count) . ' frase' . (max(1, $count) === 1 ? '' : 's') . ' para ' . $topic . ' y repite si hace falta hasta que suenen estables.';
    if ($goal !== '') {
        $text .= ' Prioriza ' . $goal . '.';
    }

    return $text;
}

function german_master_enrich_activity(array $activity, string $lessonTitle = ''): array
{
    $type = (string) ($activity['tipo'] ?? '');
    $content = is_array($activity['contenido'] ?? null) ? $activity['contenido'] : [];
    $oralContext = $lessonTitle !== '' ? $lessonTitle : (string) ($activity['titulo'] ?? '');
    $oralProfile = german_master_oral_profile_for_title($oralContext, $type === 'pronunciacion' ? 'pronunciacion' : ($type === 'escucha' ? 'escucha' : 'escucha'));

    if ($type === 'escritura') {
        $spec = german_master_writing_specs()[$activity['titulo'] ?? ''] ?? [];
        $enrichment = german_master_writing_enrichment_map()[$activity['titulo'] ?? ''] ?? [];
        $grammarProfile = german_master_writing_grammar_profiles()[$activity['titulo'] ?? ''] ?? [];
        $diagnosticProfile = german_master_writing_diagnostic_profiles()[$activity['titulo'] ?? ''] ?? [];
        $minPalabras = (int) ($content['min_palabras'] ?? 0);
        $content['idioma_objetivo'] = 'aleman';
        $content['min_oraciones'] = (int) ($content['min_oraciones'] ?? ($spec['min_oraciones'] ?? max(3, (int) ceil(max(1, $minPalabras) / 25))));
        $content['registro'] = (string) ($spec['registro'] ?? ($content['registro'] ?? 'neutral'));
        $registroGuide = german_master_writing_register_guides()[$content['registro']] ?? [];
        $content['criterios'] = array_values(array_filter(array_map('strval', array_merge((array) ($content['criterios'] ?? []), (array) ($spec['criterios'] ?? []))), static fn($item) => trim($item) !== ''));
        $content['palabras_clave'] = array_values(array_filter(array_map('strval', array_merge((array) ($content['palabras_clave'] ?? []), (array) ($spec['palabras_clave'] ?? []))), static fn($item) => trim($item) !== ''));
        $content['conectores_sugeridos'] = array_values(array_filter(array_map('strval', array_merge((array) ($content['conectores_sugeridos'] ?? []), (array) ($spec['conectores_sugeridos'] ?? []))), static fn($item) => trim($item) !== ''));
        $content['estructura_sugerida'] = array_values(array_filter(array_map('strval', array_merge((array) ($content['estructura_sugerida'] ?? []), (array) ($spec['estructura_sugerida'] ?? []))), static fn($item) => trim($item) !== ''));
        if (empty($content['estructura_sugerida']) && !empty($enrichment['estructura_sugerida'])) {
            $content['estructura_sugerida'] = array_values(array_filter(array_map('strval', (array) $enrichment['estructura_sugerida']), static fn($item) => trim($item) !== ''));
        }
        if (empty($content['modelo_inicio']) && !empty($enrichment['modelo_inicio'])) {
            $content['modelo_inicio'] = (string) $enrichment['modelo_inicio'];
        }
        if (!empty($enrichment['movimientos_clave']) && empty($content['movimientos_clave'])) {
            $content['movimientos_clave'] = array_values($enrichment['movimientos_clave']);
        }
        if (empty($content['patrones_morfosintacticos']) && !empty($grammarProfile['patrones_morfosintacticos'])) {
            $content['patrones_morfosintacticos'] = array_values((array) $grammarProfile['patrones_morfosintacticos']);
        }
        if (empty($content['diagnosticos_gramaticales']) && !empty($diagnosticProfile['diagnosticos_gramaticales'])) {
            $content['diagnosticos_gramaticales'] = array_values((array) $diagnosticProfile['diagnosticos_gramaticales']);
        }
        if (empty($content['marcadores_registro']) && !empty($registroGuide['marcadores_registro'])) {
            $content['marcadores_registro'] = array_values(array_filter(array_map('strval', (array) $registroGuide['marcadores_registro']), static fn($item) => trim($item) !== ''));
        }
        if (empty($content['errores_a_evitar']) && !empty($registroGuide['errores_a_evitar'])) {
            $content['errores_a_evitar'] = array_values(array_filter(array_map('strval', (array) $registroGuide['errores_a_evitar']), static fn($item) => trim($item) !== ''));
        }
        if (empty($content['focos_gramaticales']) && !empty($registroGuide['focos_gramaticales'])) {
            $content['focos_gramaticales'] = array_values(array_filter(array_map('strval', (array) $registroGuide['focos_gramaticales']), static fn($item) => trim($item) !== ''));
        }
        if (empty($content['errores_gramaticales_comunes']) && !empty($registroGuide['errores_gramaticales_comunes'])) {
            $content['errores_gramaticales_comunes'] = array_values(array_filter(array_map('strval', (array) $registroGuide['errores_gramaticales_comunes']), static fn($item) => trim($item) !== ''));
        }
        if (empty($content['patrones_registro_objetivo']) && !empty($registroGuide['patrones_registro_objetivo'])) {
            $content['patrones_registro_objetivo'] = array_values(array_filter(array_map('strval', (array) $registroGuide['patrones_registro_objetivo']), static fn($item) => trim($item) !== ''));
        }
        if (empty($content['patrones_registro_evitar']) && !empty($registroGuide['patrones_registro_evitar'])) {
            $content['patrones_registro_evitar'] = array_values(array_filter(array_map('strval', (array) $registroGuide['patrones_registro_evitar']), static fn($item) => trim($item) !== ''));
        }
        $activity['instrucciones'] = german_master_writing_instruction($activity, $content);
    } elseif ($type === 'escucha') {
        $transcripcion = trim((string) ($content['transcripcion'] ?? $content['texto_tts'] ?? ''));
        $content['idioma_objetivo'] = 'aleman';
        $content['acepta_variantes_menores'] = 1;
        $content['tts_rate_normal'] = (float) ($content['tts_rate_normal'] ?? $oralProfile['normal']);
        $content['tts_rate_slow'] = (float) ($content['tts_rate_slow'] ?? $oralProfile['slow']);
        $content['tts_pitch'] = (float) ($content['tts_pitch'] ?? $oralProfile['pitch']);
        $content['tts_pause_ms'] = (int) ($content['tts_pause_ms'] ?? $oralProfile['pause']);
        $content['practice_goal'] = (string) ($content['practice_goal'] ?? $oralProfile['goal']);
        if ($transcripcion !== '' && empty($content['palabras_clave'])) {
            $content['palabras_clave'] = german_expand_extract_keywords($transcripcion, 5);
        }
        $listeningSpec = german_master_listening_spec_for_title((string) ($activity['titulo'] ?? ''));
        if ($listeningSpec) {
            $content['intro'] = $listeningSpec['intro'] ?? 'Escucha cada bloque por separado y escribe exactamente lo que oyes antes de pasar al siguiente.';
            $content['preguntas'] = [];
            foreach ((array) ($listeningSpec['prompts'] ?? []) as $index => $promptText) {
                $promptText = trim((string) $promptText);
                if ($promptText === '') {
                    continue;
                }
                $content['preguntas'][] = [
                    'id' => 'listen_' . ($index + 1),
                    'descripcion' => 'Bloque ' . ($index + 1),
                    'speaker_label' => count((array) ($listeningSpec['prompts'] ?? [])) > 2 ? 'Locutor ' . ($index + 1) : (($index % 2 === 0) ? 'Voz A' : 'Voz B'),
                    'texto_tts' => $promptText,
                    'transcripcion' => $promptText,
                    'palabras_clave' => german_expand_extract_keywords($promptText, 4),
                    'tts_rate' => max(0.6, min(1.05, (float) $oralProfile['normal'] + (($index % 2 === 0) ? 0.0 : 0.02))),
                    'tts_rate_slow' => (float) $oralProfile['slow'],
                    'tts_pitch' => max(0.85, min(1.15, (float) $oralProfile['pitch'] + (($index % 2 === 0) ? 0.0 : 0.04))),
                ];
            }
        } elseif ($transcripcion !== '' && empty($content['preguntas'])) {
            $content['preguntas'] = german_master_listening_prompts_from_transcription($transcripcion);
            $content['intro'] = 'Escucha cada bloque por separado y escribe exactamente lo que oyes antes de pasar al siguiente.';
            foreach ($content['preguntas'] as $index => &$prompt) {
                $prompt['speaker_label'] = (($index % 2) === 0) ? 'Voz A' : 'Voz B';
                $prompt['tts_rate'] = max(0.6, min(1.05, (float) $oralProfile['normal'] + (($index % 2 === 0) ? 0.0 : 0.02)));
                $prompt['tts_rate_slow'] = (float) $oralProfile['slow'];
                $prompt['tts_pitch'] = max(0.85, min(1.15, (float) $oralProfile['pitch'] + (($index % 2 === 0) ? 0.0 : 0.04)));
            }
            unset($prompt);
        }
        $activity['instrucciones'] = german_master_listening_instruction($activity, $content);
    } elseif ($type === 'pronunciacion') {
        $items = [];
        foreach ((array) $content as $index => $item) {
            $phrase = trim((string) ($item['frase'] ?? ''));
            $focuses = !empty($item['focos']) ? array_values((array) $item['focos']) : german_expand_pronunciation_focuses($phrase);
            $items[] = [
                'id' => $item['id'] ?? ('pron_' . ($index + 1)),
                'frase' => $phrase,
                'texto_tts' => $item['texto_tts'] ?? $phrase,
                'idioma_objetivo' => 'aleman',
                'focos' => $focuses,
                'palabras_clave' => !empty($item['palabras_clave']) ? array_values((array) $item['palabras_clave']) : german_expand_extract_keywords($phrase, 4),
                'pista' => $item['pista'] ?? german_expand_pronunciation_hint($focuses),
                'tts_rate' => (float) ($item['tts_rate'] ?? $oralProfile['normal']),
                'tts_rate_slow' => (float) ($item['tts_rate_slow'] ?? $oralProfile['slow']),
                'tts_pitch' => (float) ($item['tts_pitch'] ?? $oralProfile['pitch']),
                'practice_goal' => (string) ($item['practice_goal'] ?? $oralProfile['goal']),
            ];
        }
        $content = $items;
        $activity['instrucciones'] = german_master_pronunciation_instruction($activity, $content);
    }

    $activity['contenido'] = $content;

    return $activity;
}

function german_master_reinforcement_activities(array $lesson): array
{
    $lessonTitle = (string) ($lesson['titulo'] ?? 'Modulo');
    $profiles = german_master_reinforcement_profiles();
    if (isset($profiles[$lessonTitle])) {
        $profile = $profiles[$lessonTitle];

        $trueFalse = german_expand_true_false(
            (string) ($profile['true_false']['title'] ?? ('Chequeo breve: ' . $lessonTitle)),
            (string) ($profile['true_false']['description'] ?? 'Comprueba si retienes el punto central de esta leccion.'),
            (string) ($profile['true_false']['statement'] ?? ''),
            (string) ($profile['true_false']['correct'] ?? 'Verdadero'),
            8,
            3
        );
        $trueFalse['instrucciones'] = 'Decide si la afirmacion sobre esta leccion es correcta o no.';

        $short = german_expand_short(
            (string) ($profile['short']['title'] ?? ('Palabra clave: ' . $lessonTitle)),
            (string) ($profile['short']['description'] ?? 'Recupera una pieza breve y funcional sin apoyo visual.'),
            (string) ($profile['short']['question'] ?? ''),
            (array) ($profile['short']['answers'] ?? []),
            (string) ($profile['short']['placeholder'] ?? 'Escribe la palabra clave'),
            8,
            3
        );
        $short['instrucciones'] = 'Escribe la palabra exacta que completa la micro-situacion.';

        return [$trueFalse, $short];
    }

    $bank = german_master_reinforcement_bank();
    $level = german_master_level_from_title($lessonTitle);
    $pack = $bank[$level] ?? $bank['A1'];
    $seed = abs((int) crc32($lessonTitle));

    $trueFalse = $pack['true_false'][$seed % count($pack['true_false'])];
    $short = $pack['short'][$seed % count($pack['short'])];

    return [
        german_expand_true_false(
            'Pulso rapido: ' . ($lesson['titulo'] ?? 'Modulo'),
            'Chequeo corto para fijar el patron central de esta leccion.',
            $trueFalse['statement'],
            $trueFalse['correct'],
            8,
            3
        ),
        german_expand_short(
            'Ancla lexical: ' . ($lesson['titulo'] ?? 'Modulo'),
            'Recupera una palabra o expresion clave sin apoyo visual.',
            $short['question'],
            $short['answers'],
            'Escribe una palabra',
            8,
            3
        ),
    ];
}

function german_master_bonus_reinforcement_activities(array $lesson): array
{
    $title = (string) ($lesson['titulo'] ?? 'Modulo');
    $level = german_master_level_from_title($title);
    $isCheckpoint = stripos($title, 'Checkpoint') === 0;

    if ($isCheckpoint) {
        switch ($level) {
            case 'A1':
                return [
                    german_expand_order(
                        'Ordena un cierre A1: ' . $title,
                        'Reconstruye frases completas de supervivencia basica sin depender de fragmentos sueltos.',
                        [
                            'Ich möchte heute zwei Brote kaufen.',
                            'Meine Familie wohnt in Valdivia.',
                        ],
                        10,
                        4
                    ),
                    german_expand_drag(
                        'Escena y respuesta A1: ' . $title,
                        'Relaciona respuestas breves con la necesidad que resuelven.',
                        [
                            'Noch einmal, bitte.' => 'pedir repeticion',
                            'Ich heiße Laura.' => 'presentarte',
                            'Es ist halb acht.' => 'decir la hora',
                            'Ich komme aus Peru.' => 'decir origen',
                        ],
                        12,
                        5
                    ),
                ];
            case 'A2':
                return [
                    german_expand_order(
                        'Ordena una gestion A2: ' . $title,
                        'Reconstruye mensajes de retraso y reorganizacion con orden natural.',
                        [
                            'Können wir den Termin auf Freitag verschieben?',
                            'Ich komme zehn Minuten später.',
                        ],
                        10,
                        4
                    ),
                    german_expand_drag(
                        'Problema y solucion A2: ' . $title,
                        'Relaciona cada situacion cotidiana con la frase que mejor la resuelve.',
                        [
                            'Der Zug hat Verspätung.' => 'avisar retraso',
                            'Bringen Sie mir bitte die Rechnung.' => 'pedir servicio',
                            'Wo muss ich umsteigen?' => 'moverte por la ciudad',
                            'Leider kann ich am Samstag nicht.' => 'rechazar invitacion',
                        ],
                        12,
                        5
                    ),
                ];
            case 'B1':
                return [
                    german_expand_order(
                        'Ordena una propuesta B1: ' . $title,
                        'Reconstruye estructuras tipicas de correo, opinion y propuesta.',
                        [
                            'Ich schlage vor, dass wir den Termin verschieben.',
                            'Könnten Sie mir bitte kurz antworten?',
                        ],
                        12,
                        5
                    ),
                    german_expand_drag(
                        'Funcion comunicativa B1: ' . $title,
                        'Distingue opinion, resumen, consulta formal y propuesta.',
                        [
                            'Ich finde, dass flexible Kurse hilfreich sind.' => 'opinion',
                            'Der Text handelt von digitalem Stress.' => 'resumen',
                            'Könnten Sie mir bitte mitteilen, ob noch Plätze frei sind?' => 'consulta formal',
                            'Ich schlage vor, dass wir früher beginnen.' => 'propuesta',
                        ],
                        14,
                        6
                    ),
                ];
            case 'C1':
                return [
                    german_expand_order(
                        'Ordena una sintesis C1: ' . $title,
                        'Reconstruye una sintesis y una postura con registro alto y jerarquia clara.',
                        [
                            'Zusammenfassend lässt sich sagen, dass klare Regeln notwendig bleiben.',
                            'Aus meiner Sicht bleibt jedoch offen, wie sie umgesetzt werden sollen.',
                        ],
                        12,
                        6
                    ),
                    german_expand_drag(
                        'Funcion retorica C1: ' . $title,
                        'Relaciona cada formula con la funcion discursiva que cumple.',
                        [
                            'Der Beitrag legt nahe, dass ...' => 'marcar tesis',
                            'Zusammenfassend lässt sich sagen, dass ...' => 'sintetizar',
                            'Aus meiner Sicht bleibt offen, ob ...' => 'introducir postura',
                            'Im Folgenden möchte ich ...' => 'abrir presentacion',
                        ],
                        14,
                        6
                    ),
                ];
        }
    }

    if ($level === 'B2') {
        return [
            german_expand_drag(
                'Mapa de matiz B2: ' . $title,
                'Distingue tesis, evidencia, matiz y conclusion en un argumento mas denso.',
                [
                    'Digitale Bildung ist flexibel.' => 'tesis',
                    'Viele Studierende sparen Wegezeit.' => 'evidencia',
                    'Sie verlangt jedoch mehr Selbstdisziplin.' => 'matiz',
                    'Folglich reicht Technik allein nicht aus.' => 'conclusion',
                ],
                12,
                5
            ),
        ];
    }

    if ($level === 'C1') {
        return [
            german_expand_matching(
                'Registro y funcion C1: ' . $title,
                'Relaciona cada objetivo discursivo con la formula mas precisa.',
                [
                    ['resumir una fuente', 'Zusammenfassend lässt sich sagen, dass ...'],
                    ['marcar tesis', 'Der Beitrag legt nahe, dass ...'],
                    ['introducir una distancia critica', 'Aus meiner Sicht bleibt offen, ob ...'],
                    ['abrir una presentacion formal', 'Im Folgenden möchte ich ...'],
                ],
                12,
                6
            ),
        ];
    }

    return [];
}

function german_master_bonus_reinforcement_activities_v2(array $lesson): array
{
    $activities = german_master_bonus_reinforcement_activities($lesson);
    $title = (string) ($lesson['titulo'] ?? '');

    $advancedOral = [
        'Nivel B2.1: debate, matices y lenguaje abstracto' => german_expand_pronunciation(
            'Pronuncia un contraargumento B2',
            'Lee concesiones y matices sin perder claridad ni ritmo argumentativo.',
            [
                'Zwar spart digitale Arbeit Zeit, dennoch darf man ihre sozialen Kosten nicht uebersehen.',
                'Einerseits bietet die Technologie neue Chancen, andererseits erhoeht sie den Druck auf viele Teams.',
                'Ich halte diese Entwicklung fuer sinnvoll, solange klare Regeln gelten.',
            ],
            16,
            8
        ),
        'Nivel B2.2: informes, nominalizacion y textos densos' => german_expand_pronunciation(
            'Pronuncia una micro-sintesis analitica B2',
            'Lee datos, limites y conclusiones con tono mas analitico y pausas controladas.',
            [
                'Der Anteil digitaler Kurse ist in den letzten Jahren deutlich gestiegen.',
                'Gleichzeitig profitieren nicht alle Gruppen in gleichem Mass davon.',
                'Deshalb braucht man zusaetzlich klare Zugangsstrategien.',
            ],
            16,
            8
        ),
        'Nivel B2.3: tecnologia, sociedad y escucha avanzada' => german_expand_pronunciation(
            'Pronuncia una postura matizada sobre tecnologia',
            'Lee una postura con equilibrio entre beneficio, limite y propuesta.',
            [
                'Kuenstliche Intelligenz kann Lernprozesse beschleunigen, ersetzt aber keine gute Betreuung.',
                'Man sollte die Vorteile nutzen, ohne die Risiken fuer Datenschutz und Gerechtigkeit zu ignorieren.',
                'Entscheidend ist deshalb ein verantwortlicher und transparenter Einsatz.',
            ],
            16,
            8
        ),
        'Nivel C1.2: lectura academica, sintesis y formacion de palabras' => german_expand_pronunciation(
            'Pronuncia una sintesis academica con distancia',
            'Lee una sintesis C1 con registro alto, distancia y articulacion estable.',
            [
                'Der Beitrag legt nahe, dass nachhaltige Innovation institutionelle Verantwortung voraussetzt.',
                'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Regulierung plaediert.',
                'Aus meiner Sicht bleibt jedoch offen, wie diese Vorgaben konkret umgesetzt werden sollen.',
            ],
            18,
            9
        ),
    ];

    if (isset($advancedOral[$title])) {
        $activities[] = $advancedOral[$title];
    }

    return $activities;
}

function german_master_oral_balance_activities(array $lesson): array
{
    $title = (string) ($lesson['titulo'] ?? '');

    $missingListening = [
        'Nivel A1.3: rutinas, presente y verbos separables' => german_expand_listening(
            'Escucha: rutina y hora de salida',
            'Escucha una rutina diaria con verbos separables, horas y cierre del dia.',
            'Ich stehe um sechs Uhr auf. Dann raeume ich mein Zimmer auf und fruehstuecke schnell. Am Abend sehe ich fern und gehe um zehn Uhr schlafen.',
            18,
            7
        ),
        'Nivel A1.4: compras, ciudad y verbos de accion' => german_expand_listening(
            'Escucha: compra y camino en la ciudad',
            'Escucha un pedido y una indicacion basica de ciudad en bloques cortos.',
            'Ich moechte zwei Brote und eine Flasche Wasser, bitte. Entschuldigung, wo ist der Bahnhof? Gehen Sie geradeaus und dann nach links.',
            18,
            7
        ),
        'Nivel A2.3: comida, servicio y pequenas reclamaciones' => german_expand_listening(
            'Escucha: pedido y pequena reclamacion',
            'Escucha un pedido completo y una pequena reclamacion con cortesia.',
            'Ich nehme die Suppe und einen Tee, bitte. Entschuldigung, die Suppe ist leider kalt. Koennten Sie mir bitte eine neue bringen?',
            18,
            7
        ),
        'Nivel B1.1: opinion, subordinadas y mundo real' => german_expand_listening(
            'Escucha: opinion breve con contraste',
            'Escucha una opinion conectada con razon y matiz para seguir la estructura completa.',
            'Ich finde, dass digitale Kurse sehr praktisch sind. Sie sparen Zeit und geben mehr Flexibilitaet im Alltag. Trotzdem braucht man klare Betreuung, damit niemand den Anschluss verliert.',
            20,
            8
        ),
        'Nivel B1.4: medios, resumen y mini presentaciones' => german_expand_listening(
            'Escucha: resumen y mini presentacion B1',
            'Escucha un mini resumen oral y un cierre de presentacion con buena jerarquia.',
            'Der Bericht handelt von Medienkonsum im Alltag. Viele Jugendliche informieren sich zuerst ueber soziale Netzwerke. Zum Schluss moechte ich kurz erklaeren, warum Medienkompetenz so wichtig ist.',
            20,
            8
        ),
    ];

    $missingPronunciation = [
        'Nivel A2.1: pasado, dativo y vida cotidiana' => german_expand_pronunciation(
            'Pronuncia un recuento breve en Perfekt',
            'Lee un recuento cotidiano con pasado reciente, hora y pequeña gestion.',
            [
                'Gestern habe ich meine Tante besucht und ihr ein Buch gebracht.',
                'Danach bin ich spaet nach Hause gekommen.',
                'Heute muss ich dem Arzt noch eine Nachricht schicken.',
            ],
            15,
            7
        ),
        'Nivel A2.2: tramites, citas y movimiento por la ciudad' => german_expand_pronunciation(
            'Pronuncia una gestion de cita y trayecto',
            'Lee una gestion con retraso, cita y orientacion en la ciudad.',
            [
                'Ich habe morgen um zehn Uhr einen Termin beim Amt.',
                'Falls ich mich verspaete, rufe ich sofort an.',
                'Vom Bahnhof aus gehe ich zuerst geradeaus und dann nach rechts.',
            ],
            15,
            7
        ),
        'Nivel A2.4: planes, invitaciones y futuro cercano' => german_expand_pronunciation(
            'Pronuncia un plan y una invitacion',
            'Lee un plan de fin de semana con invitacion, motivo y confirmacion.',
            [
                'Am Samstag werde ich zuerst meine Grosseltern besuchen.',
                'Danach moechte ich mit Freunden ins Kino gehen.',
                'Wenn du Zeit hast, kannst du gern mitkommen.',
            ],
            15,
            7
        ),
        'Nivel B1.2: relativas, correos y tramites' => german_expand_pronunciation(
            'Pronuncia una consulta formal con relativa',
            'Lee una consulta formal con frase relativa y cierre estable.',
            [
                'Ich suche einen Kurs, der am Abend stattfindet.',
                'Koennten Sie mir bitte sagen, welche Unterlagen ich mitbringen muss?',
                'Ich waere Ihnen fuer eine kurze Rueckmeldung sehr dankbar.',
            ],
            16,
            8
        ),
        'Nivel B1.3: trabajo, estudio y resolucion de problemas' => german_expand_pronunciation(
            'Pronuncia un problema y una solucion B1',
            'Lee un problema cotidiano de trabajo o estudio y una propuesta de solucion.',
            [
                'Unser Team hat im Moment ein Problem mit der internen Kommunikation.',
                'Dadurch werden einige Aufgaben zu spaet erledigt.',
                'Ich schlage deshalb vor, dass wir jede Woche ein kurzes Planungstreffen machen.',
            ],
            16,
            8
        ),
    ];

    $activities = [];
    if (isset($missingListening[$title])) {
        $activities[] = $missingListening[$title];
    }
    if (isset($missingPronunciation[$title])) {
        $activities[] = $missingPronunciation[$title];
    }

    return $activities;
}

function german_master_enrich_lessons(array $lessons): array
{
    foreach ($lessons as &$lesson) {
        $reinforcementActivities = german_master_reinforcement_activities($lesson);
        $bonusActivities = german_master_bonus_reinforcement_activities_v2($lesson);
        $oralBalanceActivities = german_master_oral_balance_activities($lesson);
        $lesson['actividades'] = array_merge((array) ($lesson['actividades'] ?? []), $reinforcementActivities, $bonusActivities, $oralBalanceActivities);
        foreach ($lesson['actividades'] as &$activity) {
            $activity = german_master_enrich_activity($activity, (string) ($lesson['titulo'] ?? ''));
        }
        unset($activity);
        $lesson['duracion'] = (int) ($lesson['duracion'] ?? 120) + 15 + ((count($bonusActivities) + count($oralBalanceActivities)) * 5);
    }
    unset($lesson);

    return $lessons;
}

function german_master_resequence_lessons(array $lessons): array
{
    usort($lessons, static fn(array $left, array $right): int => ($left['orden'] ?? 0) <=> ($right['orden'] ?? 0));

    foreach ($lessons as $index => &$lesson) {
        $lesson['orden'] = $index + 1;
    }
    unset($lesson);

    return $lessons;
}

function german_master_checkpoint_lessons(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Checkpoint A1: supervivencia completa',
                'Repaso guiado de presentacion, familia, rutinas, compras y orientacion antes del salto firme a A2.',
                180,
                [
                    german_expand_theory(
                        'Lo que ya debe salir sin apoyo en A1',
                        16,
                        'Antes de seguir conviene comprobar que ya puedes presentarte, pedir cosas, hablar de tu dia y orientarte con frases completas y no solo con palabras sueltas.',
                        [
                            german_expand_section('Meta de salida A1', ['presentarte', 'hablar de familia', 'contar tu rutina', 'comprar y pedir', 'preguntar una direccion'], null, null, 'espanol'),
                            german_expand_section('Frases que deben salir fluidas', ['Ich heisse ...', 'Ich habe zwei Geschwister.', 'Ich stehe um sieben Uhr auf.', 'Ich moechte einen Tee, bitte.'], null, null, 'aleman'),
                            german_expand_section('Modelo de cierre A1', [], null, 'Ich heisse Laura, ich komme aus Chile, ich arbeite am Morgen und ich moechte heute zwei Brote kaufen.', 'aleman'),
                        ],
                        'Si estas frases todavia salen rotas, conviene reforzarlas antes de cargar mas gramatica.'
                    ),
                    german_expand_theory(
                        'Errores de base que conviene cortar ya',
                        15,
                        'Muchos bloqueos de niveles posteriores nacen aqui: olvidar articulos, romper el orden verbal o responder con traducciones literales.',
                        [
                            german_expand_section('Errores tipicos', ['sustantivos sin articulo', 'verbo fuera de lugar', 'negar con la palabra incorrecta', 'olvidar preposiciones de hora'], null, null, 'espanol'),
                            german_expand_section('Correcciones clave', ['der / die / das con la palabra', 'verbo temprano', 'nicht y kein con funcion clara', 'um + hora'], null, null, 'aleman'),
                            german_expand_section('Modelo corregido', [], null, 'Ich habe keinen Kaffee und mein Kurs beginnt um neun Uhr.', 'aleman'),
                        ],
                        'Cerrar estos errores ahorra mucho sufrimiento al entrar en pasado, dativo y conectores.'
                    ),
                ],
                [
                    german_expand_mcq('Chequeo integral A1', 'Repasa decisiones frecuentes de presentacion, rutina y compras.', 'Elige la opcion mas natural.', [
                        german_expand_question('Quieres pedir algo con cortesia', 'Ich moechte einen Saft, bitte.', 'Ich will einen Saft bitte.', 'Ich moechten Saft bitte.'),
                        german_expand_question('Rutina correcta', 'Ich stehe um sechs Uhr auf.', 'Ich aufstehe um sechs Uhr.', 'Ich stehe auf um sechs Uhr auf.'),
                        german_expand_question('Presentacion natural', 'Ich komme aus Peru und ich bin 25 Jahre alt.', 'Ich bin aus Peru y 25 Jahre alt.', 'Ich komme Peru und habe 25 Jahre.'),
                    ], 20, 8),
                    german_expand_matching('Empareja situacion y frase A1', 'Relaciona cada escena con la respuesta util.', [
                        ['presentarte', 'Ich heisse ...'],
                        ['pedir repeticion', 'Noch einmal, bitte.'],
                        ['decir la hora', 'Es ist halb acht.'],
                        ['pedir una bebida', 'Ich moechte einen Tee.'],
                    ]),
                    german_expand_fill('Completa el repaso A1', 'Escribe la palabra correcta.', [
                        ['id' => 'cp_a1_1', 'oracion' => 'Ich ____ aus Mexiko.', 'respuesta_correcta' => 'komme'],
                        ['id' => 'cp_a1_2', 'oracion' => 'Der Kurs beginnt ____ neun Uhr.', 'respuesta_correcta' => 'um'],
                        ['id' => 'cp_a1_3', 'oracion' => 'Ich ____ um sieben Uhr auf.', 'respuesta_correcta' => 'stehe'],
                    ]),
                    german_expand_pronunciation('Pronuncia tu mini perfil A1', 'Lee una presentacion compacta con datos, rutina y pedido.', [
                        'Ich heisse Tomas und ich komme aus Chile.',
                        'Ich stehe um sieben Uhr auf und lerne am Abend.',
                        'Ich moechte heute einen Kaffee und zwei Brote, bitte.',
                    ]),
                    german_expand_listening('Escucha: resumen de supervivencia A1', 'Escucha y escribe la transcripcion.', 'Guten Tag, ich heisse Marta, ich komme aus Peru, ich arbeite am Morgen und ich moechte heute ein Ticket kaufen.'),
                    german_expand_writing('Escribe tu perfil funcional A1', 'Redacta una presentacion practica que combine datos, rutina y una necesidad cotidiana.', 'Escribe un texto corto donde te presentes, cuentes un poco de tu rutina y digas que necesitas comprar o hacer hoy.', 90, 22, 14),
                ]
            ),
            ['orden' => 4.5]
        ),
        array_merge(
            german_expand_lesson(
                'Checkpoint A2: autonomia cotidiana',
                'Repaso de pasado, citas, ciudad, comida, reclamaciones y planes antes de entrar al trabajo argumentativo de B1.',
                185,
                [
                    german_expand_theory(
                        'Lo que ya debe salir con autonomia en A2',
                        16,
                        'Al cerrar A2 deberias poder contar lo que paso, mover una cita, hacer una pequena gestion, pedir comida y explicar un plan futuro con motivos simples.',
                        [
                            german_expand_section('Meta de salida A2', ['contar un fin de semana', 'avisar retrasos', 'hacer tramites pequenos', 'reclamar con cortesia', 'invitar y rechazar'], null, null, 'espanol'),
                            german_expand_section('Frases ancla', ['Ich habe den Termin verschoben.', 'Der Zug ist spaet.', 'Bringen Sie mir bitte die Rechnung.', 'Am Wochenende werde ich lernen.'], null, null, 'aleman'),
                            german_expand_section('Modelo A2 fuerte', [], null, 'Ich habe gestern angerufen, weil ich den Termin verschieben musste, und am Wochenende werde ich meine Freunde besuchen.', 'aleman'),
                        ],
                        'Si esto ya sale, la entrada a B1 sera mucho menos brusca.'
                    ),
                    german_expand_theory(
                        'Errores que mas castigan el salto a B1',
                        15,
                        'Los problemas mas costosos aqui suelen ser mezclar auxiliares del Perfekt, perder preposiciones y usar cortesias demasiado secas o demasiado literales.',
                        [
                            german_expand_section('Riesgos comunes', ['habe/bin mal elegido', 'preposicion ausente', 'preguntas demasiado directas', 'futuro sin orden claro'], null, null, 'espanol'),
                            german_expand_section('Ajustes clave', ['auxiliar correcto', 'frase funcional completa', 'motivo + propuesta', 'cierre cordial'], null, null, 'espanol'),
                            german_expand_section('Modelo corregido', [], null, 'Koennen wir den Termin verschieben? Ich komme spaeter, weil mein Zug Verspaetung hat.', 'aleman'),
                        ],
                        'Cortar esto ahora libera mucha energia para opinion, resumen y escritura formal.'
                    ),
                ],
                [
                    german_expand_mcq('Chequeo integral A2', 'Repasa pasado, servicio, tramites y planes.', 'Selecciona la mejor opcion.', [
                        german_expand_question('Perfekt correcto', 'Ich bin spaet angekommen.', 'Ich habe spaet angekommen.', 'Ich angekommen bin spaet.'),
                        german_expand_question('Reclamacion cortesa', 'Ich glaube, hier stimmt etwas nicht.', 'Hier nicht stimmt.', 'Ich denke, esto no.'),
                        german_expand_question('Plan futuro natural', 'Am Samstag werde ich meine Tante besuchen.', 'Am Samstag ich werde meine Tante besuchen.', 'Ich werde besuchen meine Tante Samstag.'),
                    ], 20, 8),
                    german_expand_matching('Empareja necesidad y solucion A2', 'Relaciona cada problema cotidiano con una frase util.', [
                        ['mover una cita', 'Koennen wir den Termin verschieben?'],
                        ['pedir la cuenta', 'Bringen Sie mir bitte die Rechnung.'],
                        ['preguntar un transbordo', 'Wo muss ich umsteigen?'],
                        ['rechazar una invitacion', 'Leider kann ich nicht.'],
                    ]),
                    german_expand_fill('Completa el repaso A2', 'Escribe la palabra correcta.', [
                        ['id' => 'cp_a2_1', 'oracion' => 'Ich komme zehn Minuten ____.', 'respuesta_correcta' => 'spaeter'],
                        ['id' => 'cp_a2_2', 'oracion' => 'Wir koennen den ____ verschieben.', 'respuesta_correcta' => 'Termin'],
                        ['id' => 'cp_a2_3', 'oracion' => 'Am Samstag ____ ich meine Freunde besuchen.', 'respuesta_correcta' => 'werde'],
                    ]),
                    german_expand_pronunciation('Pronuncia una gestion A2 completa', 'Lee frases de retraso, servicio y plan con tono natural.', [
                        'Ich komme zehn Minuten spaeter, weil mein Zug spaet ist.',
                        'Kann ich die Suppe ohne Zwiebeln haben?',
                        'Am Wochenende werde ich meine Eltern besuchen.',
                    ]),
                    german_expand_listening('Escucha: autonomia A2', 'Escucha y escribe la transcripcion.', 'Guten Tag, ich komme spaeter, weil mein Zug Verspaetung hat, und ich moechte den Termin auf Freitag verschieben.'),
                    german_expand_writing('Escribe una semana realista A2', 'Redacta un texto donde combines pasado, gestion y plan futuro.', 'Escribe un mensaje contando algo que paso, una pequena gestion que debes hacer y lo que haras el fin de semana.', 110, 22, 15),
                ]
            ),
            ['orden' => 8.5]
        ),
        array_merge(
            german_expand_lesson(
                'Checkpoint B1: independencia comunicativa',
                'Cierre de opinion, correos, resumen y solucion de problemas antes del salto fuerte a B2.',
                190,
                [
                    german_expand_theory(
                        'Lo que ya debe sostenerse en B1',
                        16,
                        'Al cerrar B1 deberias poder conectar ideas, escribir un correo funcional, resumir una fuente corta y proponer una solucion simple a un problema real.',
                        [
                            german_expand_section('Meta de salida B1', ['opinar con razones', 'escribir con cortesia', 'resumir brevemente', 'plantear un problema y una solucion'], null, null, 'espanol'),
                            german_expand_section('Frases ancla', ['Ich finde, dass ...', 'Koennten Sie mir bitte ...', 'Der Text handelt von ...', 'Ich schlage vor, dass ...'], null, null, 'aleman'),
                            german_expand_section('Modelo B1 fuerte', [], null, 'Ich finde, dass flexible Kurse hilfreich sind, weil sie Zeit sparen. Ich schlage jedoch vor, dass die Betreuung klarer organisiert wird.', 'aleman'),
                        ],
                        'Si ya puedes combinar estas piezas, entras a B2 con base real y no solo con ejercicios resueltos.'
                    ),
                    german_expand_theory(
                        'Errores que frenan la entrada a B2',
                        15,
                        'El paso a B2 se complica cuando las subordinadas se rompen, el tono formal se vuelve torpe y la opinion no incluye matiz alguno.',
                        [
                            german_expand_section('Riesgos comunes', ['subordinada mal cerrada', 'correo demasiado brusco', 'resumen sin jerarquia', 'opinion sin evidencia'], null, null, 'espanol'),
                            german_expand_section('Ajustes clave', ['verbo al final en subordinada', 'saludo y cierre estables', 'tema + idea clave + conclusion', 'propuesta con razon'], null, null, 'espanol'),
                            german_expand_section('Modelo corregido', [], null, 'Obwohl das Angebot attraktiv ist, bleiben einige Fragen offen, die im Text nicht beantwortet werden.', 'aleman'),
                        ],
                        'Entrar a B2 sin estos ajustes produce textos demasiado planos o demasiado rotos.'
                    ),
                ],
                [
                    german_expand_mcq('Chequeo integral B1', 'Repasa opinion, correo, resumen y propuesta.', 'Selecciona la mejor opcion.', [
                        german_expand_question('Opinion bien conectada', 'Ich finde, dass digitale Bildung praktisch ist, weil sie Zeit spart.', 'Ich finde digitale Bildung praktisch weil sie spart Zeit ist.', 'Ich finde, digitale Bildung praktisch, weil sie Zeit spart ist.'),
                        german_expand_question('Correo cortesa correcto', 'Koennten Sie mir bitte mitteilen, ob noch Plaetze frei sind?', 'Koennen Sie mir mitteilen bitte ob frei Plaetze?', 'Mitteilen Sie mir ob Plaetze frei bitte.'),
                        german_expand_question('Propuesta util', 'Ich schlage vor, dass wir den Termin verschieben.', 'Ich schlage, den Termin verschieben.', 'Ich vor schlage dass wir den Termin.'),
                    ], 20, 8),
                    german_expand_matching('Empareja tarea y formula B1', 'Relaciona cada objetivo con la formula adecuada.', [
                        ['dar opinion', 'Ich finde, dass ...'],
                        ['pedir informacion formal', 'Koennten Sie mir bitte ...'],
                        ['resumir una fuente', 'Der Text handelt von ...'],
                        ['hacer propuesta', 'Ich schlage vor, dass ...'],
                    ]),
                    german_expand_fill('Completa el repaso B1', 'Escribe la palabra correcta.', [
                        ['id' => 'cp_b1_1', 'oracion' => 'Ich finde, ____ Onlinekurse hilfreich sind.', 'respuesta_correcta' => 'dass'],
                        ['id' => 'cp_b1_2', 'oracion' => 'Mit freundlichen ____.', 'respuesta_correcta' => 'Gruessen'],
                        ['id' => 'cp_b1_3', 'oracion' => 'Ich schlage ____, dass wir spaeter beginnen.', 'respuesta_correcta' => 'vor'],
                    ]),
                    german_expand_pronunciation('Pronuncia una mini postura B1', 'Lee una opinion, una consulta y una propuesta con pausas claras.', [
                        'Ich finde, dass klare Ziele beim Lernen entscheidend sind.',
                        'Koennten Sie mir bitte sagen, ob noch Plaetze frei sind?',
                        'Ich schlage vor, dass wir spaeter anfangen.',
                    ]),
                    german_expand_listening('Escucha: correo y propuesta B1', 'Escucha y escribe la transcripcion.', 'Ich schreibe Ihnen, weil ich eine Frage zur Anmeldung habe, und ich schlage vor, den Termin auf Montag zu verschieben.'),
                    german_expand_writing('Escribe un cierre B1 completo', 'Redacta un texto donde combines opinion, resumen breve y propuesta.', 'Escribe un texto donde opines sobre un tema, resumas una idea central y propongas una mejora concreta.', 130, 24, 16),
                ]
            ),
            ['orden' => 12.5]
        ),
        array_merge(
            german_expand_lesson(
                'Checkpoint Maestro C1: simulacion integral',
                'Cierre global de lectura, escucha, presentacion, sintesis y plan de consolidacion para medir la ruta completa.',
                220,
                [
                    german_expand_theory(
                        'Lo que una ruta completa debe dejar en C1',
                        17,
                        'El cierre global debe mostrar lectura con jerarquia, sintesis fiel, postura propia, habla estructurada y un plan claro para mantener el nivel.',
                        [
                            german_expand_section('Meta de salida C1', ['leer textos densos', 'sintetizar con precision', 'mover registro', 'presentar con orden', 'mantener una rutina posterior'], null, null, 'espanol'),
                            german_expand_section('Frases ancla', ['Der Beitrag legt nahe, dass ...', 'Zusammenfassend laesst sich sagen, dass ...', 'Aus meiner Sicht ...', 'Im Folgenden moechte ich ...'], null, null, 'aleman'),
                            german_expand_section('Modelo de cierre C1', [], null, 'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Regulierung plaediert. Aus meiner Sicht bleibt jedoch offen, wie diese umgesetzt werden soll.', 'aleman'),
                        ],
                        'Si esto ya sale con cierta estabilidad, la ruta cumple su promesa mucho mejor que un simple curso bonito.'
                    ),
                    german_expand_theory(
                        'Como consolidar sin perder el nivel ganado',
                        16,
                        'La meta final no es terminar, sino sostener. Por eso conviene cerrar con una estrategia concreta de escucha, lectura, expresion oral y escritura.',
                        [
                            german_expand_section('Rutina minima viable', ['escucha semanal', 'lectura comentada', 'produccion escrita breve', 'repeticion guiada o expresion oral grabada', 'revision de errores'], null, null, 'espanol'),
                            german_expand_section('Formulas de plan', ['dreimal pro Woche', 'einmal im Monat', 'gezielt wiederholen', 'Fehler bewusst sammeln'], null, null, 'aleman'),
                            german_expand_section('Modelo de plan', [], null, 'Ich werde jede Woche zwei kurze Kommentare schreiben und einmal im Monat eine Praesentation aufnehmen.', 'aleman'),
                        ],
                        'La consolidacion buena es modesta pero constante; no depende de impulsos heroicos.'
                    ),
                ],
                [
                    german_expand_mcq('Chequeo maestro C1', 'Repasa formulas de sintesis, posicion y presentacion formal.', 'Selecciona la opcion mas precisa.', [
                        german_expand_question('Sintesis academica correcta', 'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Regulierung plaediert.', 'Zusammenfassend sagt der Text fuer mehr Regulierung.', 'Zusammen laesst sich sagen der Text plaediert fuer mehr Regulierung.'),
                        german_expand_question('Postura con distancia', 'Aus meiner Sicht bleibt offen, wie diese Reform umgesetzt werden soll.', 'Aus meiner Sicht bleibt offen, wie soll diese Reform umgesetzt werden.', 'Aus meiner Sicht offen bleibt wie diese Reform umgesetzt werden soll.'),
                        german_expand_question('Apertura formal fuerte', 'Im Folgenden moechte ich auf drei zentrale Punkte eingehen.', 'Im Folgenden ich moechte auf drei zentrale Punkte eingehen.', 'Ich moechte im Folgenden auf drei zentrale Punkte eingehen moechte.'),
                    ], 22, 9),
                    german_expand_matching('Empareja funcion y formula maestra', 'Relaciona cada tramo del cierre con la formula adecuada.', [
                        ['marcar tesis', 'Der Beitrag legt nahe, dass ...'],
                        ['resumir', 'Zusammenfassend laesst sich sagen, dass ...'],
                        ['anadir postura', 'Aus meiner Sicht ...'],
                        ['abrir presentacion', 'Im Folgenden moechte ich ...'],
                    ], 14, 7),
                    german_expand_fill('Completa el cierre maestro', 'Escribe la palabra correcta.', [
                        ['id' => 'cp_c1_1', 'oracion' => 'Zusammenfassend laesst sich ____.', 'respuesta_correcta' => 'sagen'],
                        ['id' => 'cp_c1_2', 'oracion' => 'Aus meiner ____ bleibt offen, ob ...', 'respuesta_correcta' => 'Sicht'],
                        ['id' => 'cp_c1_3', 'oracion' => 'Im Folgenden moechte ich auf drei zentrale ____ eingehen.', 'respuesta_correcta' => 'Punkte'],
                    ]),
                    german_expand_pronunciation('Pronuncia tu cierre formal maestro', 'Lee una apertura, una sintesis y una conclusion con articulacion estable.', [
                        'Im Folgenden moechte ich auf drei zentrale Punkte eingehen.',
                        'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Verantwortung plaediert.',
                        'Aus meiner Sicht braucht diese Debatte mehr soziale Perspektive und weniger Vereinfachung.',
                    ], 18, 9),
                    german_expand_listening('Escucha: cierre integral C1', 'Escucha y escribe la transcripcion.', 'Der Beitrag legt nahe, dass nachhaltige Innovation nur dann gelingt, wenn technischer Fortschritt, soziale Verantwortung und klare Umsetzung zusammengedacht werden.'),
                    german_expand_writing('Escribe tu diagnostico final y plan', 'Redacta una sintesis final de tu nivel y un plan concreto de consolidacion.', 'Escribe un texto donde resumas que ya puedes hacer en aleman, que puntos sigues trabajando y como vas a consolidarlos en los proximos tres meses.', 200, 30, 20),
                ]
            ),
            ['orden' => 18.5]
        ),
    ];
}

function german_master_additional_theory_support(): array
{
    return [
        [
            'lesson_aliases' => ['Nivel A1.2: familia, numeros y preguntas basicas'],
            'theory_aliases' => ['Familia, origen y relaciones cercanas'],
            'blocks' => german_expand_support_pack(
                'Escenario: conoces a una familia nueva y necesitas presentarte, preguntar de donde son y describir relaciones sin quedarte mudo.',
                ['Di de donde vienes.', 'Nombra dos familiares.', 'Pregunta por un hermano o hermana.'],
                ['Olvidar posesivos basicos.', 'Responder solo con palabras sueltas.', 'Confundir singular y plural familiar.'],
                ['Usa mein o meine.', 'Haz una pregunta con Wer?', 'Cierra con una frase completa.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.2: familia, numeros y preguntas basicas'],
            'theory_aliases' => ['Numeros, edad, telefono y hora'],
            'blocks' => german_expand_support_pack(
                'Escenario: completas un registro rapido donde te piden edad, telefono y hora del curso sin margen para improvisar.',
                ['Di tu edad.', 'Deletrea o dicta un numero.', 'Di a que hora empieza una clase.'],
                ['Romper los numeros largos.', 'Mezclar Uhr con Jahre.', 'Responder la hora en formato espanol.'],
                ['Di un numero de telefono.', 'Di Viertel nach o halb.', 'Haz una frase con um.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.2: familia, numeros y preguntas basicas'],
            'theory_aliases' => ['Preguntas basicas de aula y supervivencia'],
            'blocks' => german_expand_support_pack(
                'Escenario: sigues una clase donde necesitas pedir repeticion, confirmar una palabra y ganar tiempo con frases utiles.',
                ['Pide que repitan.', 'Di que no entendiste del todo.', 'Pregunta como se escribe una palabra.'],
                ['Quedarte callado cuando no entiendes.', 'Traducir directo desde el espanol.', 'Usar frases sueltas sin verbo.'],
                ['Usa Noch einmal, bitte.', 'Usa Wie schreibt man das?', 'Usa Ich verstehe nicht ganz.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.3: rutinas, presente y verbos separables'],
            'theory_aliases' => ['Rutinas diarias y presente de alta frecuencia'],
            'blocks' => german_expand_support_pack(
                'Escenario: cuentas tu dia a un companero y necesitas sonar estable desde la manana hasta la noche.',
                ['Di a que hora te levantas.', 'Di una actividad de manana.', 'Di que haces por la noche.'],
                ['Olvidar el verbo en presente.', 'Encadenar acciones sin orden.', 'Cambiar de persona sin querer.'],
                ['Usa ich stehe auf.', 'Usa dann.', 'Cierra con am Abend.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.3: rutinas, presente y verbos separables'],
            'theory_aliases' => ['Horarios y verbos separables'],
            'blocks' => german_expand_support_pack(
                'Escenario: coordinas un dia completo y alguien solo acepta respuestas con hora clara y verbo separable bien cerrado.',
                ['Da una hora exacta.', 'Usa un verbo separable.', 'Anade una accion posterior.'],
                ['Dejar la particula en medio.', 'Usar la hora sin preposicion.', 'Cerrar la frase antes del verbo.'],
                ['Usa aufstehen o anrufen.', 'Usa um + hora.', 'Haz una pregunta corta.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.3: rutinas, presente y verbos separables'],
            'theory_aliases' => ['Gustos, actividades y negacion basica'],
            'blocks' => german_expand_support_pack(
                'Escenario: hablas de lo que te gusta hacer y de lo que no haces nunca, sin sonar robotico.',
                ['Di algo que te gusta.', 'Di una actividad que no haces.', 'Compara dos gustos breves.'],
                ['Usar nicht y kein al azar.', 'Negar sin objeto claro.', 'Repetir mag siempre igual.'],
                ['Usa gern.', 'Haz una frase con nicht.', 'Haz una frase con kein.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint A1: supervivencia completa'],
            'theory_aliases' => ['Lo que ya debe salir sin apoyo en A1'],
            'blocks' => german_expand_support_pack(
                'Escenario: te mueves por un primer dia de curso donde debes presentarte, pedir algo y entender una indicacion simple sin traducir todo.',
                ['Presentate en dos lineas.', 'Pide algo con cortesia.', 'Di una hora o una direccion simple.'],
                ['Responder solo con palabras clave.', 'Olvidar articulos muy basicos.', 'Cambiar al espanol ante el primer bloqueo.'],
                ['Haz una mini presentacion.', 'Haz un pedido.', 'Haz una pregunta de supervivencia.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint A1: supervivencia completa'],
            'theory_aliases' => ['Errores de base que conviene cortar ya'],
            'blocks' => german_expand_support_pack(
                'Escenario: corriges tus propios errores antes de que se vuelvan habitos que arruinen A2.',
                ['Corrige una frase con sein.', 'Corrige una negacion.', 'Corrige una frase con orden verbal basico.'],
                ['Aprender la forma equivocada por repeticion.', 'Negar con la palabra incorrecta.', 'Soltar el verbo fuera de lugar.'],
                ['Detecta un error.', 'Reescribe la frase.', 'Di la version correcta en voz alta.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.2: tramites, citas y movimiento por la ciudad'],
            'theory_aliases' => ['Citas, retrasos y cambios de horario'],
            'blocks' => german_expand_support_pack(
                'Escenario: llegas tarde a una cita y tienes que avisar, justificar y proponer una solucion sin perder la calma.',
                ['Di que tienes un retraso.', 'Explica el motivo.', 'Propone otro horario.'],
                ['Avisar demasiado tarde.', 'Olvidar la hora concreta.', 'No ofrecer una alternativa.'],
                ['Usa Ich komme spaeter.', 'Usa weil.', 'Usa verschieben.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.2: tramites, citas y movimiento por la ciudad'],
            'theory_aliases' => ['Oficina, servicio y preguntas utiles'],
            'blocks' => german_expand_support_pack(
                'Escenario: preguntas en una oficina donde todo depende de formular bien la duda desde el principio.',
                ['Pregunta donde hacer el tramite.', 'Pregunta que documento falta.', 'Pide confirmacion con cortesia.'],
                ['Hacer preguntas demasiado cortas.', 'Olvidar el contexto del tramite.', 'Sonar brusco con personal de servicio.'],
                ['Usa Wo bekomme ich...?', 'Usa Welche Unterlagen...?', 'Usa Koennen Sie mir bitte...?']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.2: tramites, citas y movimiento por la ciudad'],
            'theory_aliases' => ['Ciudad, ubicacion y movimiento con precision A2'],
            'blocks' => german_expand_support_pack(
                'Escenario: das indicaciones mas detalladas a alguien que viene del Bahnhof y no puede perderse otra vez.',
                ['Da una direccion en dos pasos.', 'Usa una referencia espacial.', 'Cierra con un punto de llegada claro.'],
                ['Quedarte en geradeaus y nada mas.', 'Perder la referencia espacial.', 'Confundir links y rechts en cadena.'],
                ['Usa gegenueber o neben.', 'Usa zuerst / dann.', 'Haz una pregunta de confirmacion.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.3: comida, servicio y pequenas reclamaciones'],
            'theory_aliases' => ['Comida, cantidades y pedidos naturales'],
            'blocks' => german_expand_support_pack(
                'Escenario: pides comida para varias personas y necesitas ajustar cantidades sin sonar mecanico.',
                ['Pide una bebida.', 'Pide dos cantidades distintas.', 'Anade bitte y cierre natural.'],
                ['Olvidar el articulo con comida.', 'Usar cantidad sin sustantivo.', 'Pedir como lista en vez de frase.'],
                ['Usa Ich nehme...', 'Usa noch einmal.', 'Usa zwei Portionen.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.3: comida, servicio y pequenas reclamaciones'],
            'theory_aliases' => ['Servicio, preferencias y preguntas al personal'],
            'blocks' => german_expand_support_pack(
                'Escenario: ajustas un pedido porque no comes ciertos ingredientes y necesitas preguntar opciones al personal.',
                ['Expresa una preferencia.', 'Pregunta si algo lleva un ingrediente.', 'Pide una alternativa.'],
                ['Sonar demasiado directo.', 'Olvidar bitte en contexto de servicio.', 'No marcar claramente la preferencia.'],
                ['Usa ohne.', 'Usa Haben Sie etwas...? ', 'Usa Koennte ich...?.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.3: comida, servicio y pequenas reclamaciones'],
            'theory_aliases' => ['Pequenas reclamaciones y solucion cordial'],
            'blocks' => german_expand_support_pack(
                'Escenario: algo llega mal a la mesa y quieres corregirlo sin crear un drama innecesario.',
                ['Di que hay un problema.', 'Explica que esperabas.', 'Pide una solucion concreta.'],
                ['Reclamar sin contexto.', 'Sonar agresivo por falta de formula.', 'No indicar cual seria la solucion util.'],
                ['Usa Ich glaube, hier stimmt etwas nicht.', 'Usa leider.', 'Usa Koennten Sie...?.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.4: planes, invitaciones y futuro cercano'],
            'theory_aliases' => ['Planes y futuro con werden'],
            'blocks' => german_expand_support_pack(
                'Escenario: organizas tu fin de semana y necesitas marcar planes, orden y expectativa con werden.',
                ['Di un plan para manana.', 'Di un plan para el fin de semana.', 'Anade una razon corta.'],
                ['Usar presente donde ya hace falta claridad futura.', 'Perder el verbo al final.', 'Amontonar planes sin orden temporal.'],
                ['Usa zuerst / danach.', 'Usa werden.', 'Cierra con am Samstag o am Sonntag.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.4: planes, invitaciones y futuro cercano'],
            'theory_aliases' => ['Invitaciones, propuestas y respuestas naturales'],
            'blocks' => german_expand_support_pack(
                'Escenario: invitas a alguien, recibes una duda y tienes que responder con naturalidad sin salirte del registro cotidiano.',
                ['Haz una invitacion.', 'Propone una hora.', 'Acepta o rechaza con tacto.'],
                ['Invitar sin contexto.', 'Responder solo con ja o nein.', 'Olvidar una salida cortesa al rechazar.'],
                ['Usa Moechtest du...?', 'Usa Hast du Zeit?', 'Usa Leider kann ich nicht.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.4: planes, invitaciones y futuro cercano'],
            'theory_aliases' => ['Pequenos proyectos, razones y coordinacion'],
            'blocks' => german_expand_support_pack(
                'Escenario: coordinas una tarea pequena con otra persona y necesitas repartir pasos y motivos con claridad.',
                ['Di que vas a hacer.', 'Explica por que.', 'Reparte una accion a otra persona.'],
                ['No marcar quien hace que.', 'Dar razones demasiado sueltas.', 'Confundir plan con promesa firme.'],
                ['Usa weil.', 'Usa zuerst / dann.', 'Usa wir oder du.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint A2: autonomia cotidiana'],
            'theory_aliases' => ['Lo que ya debe salir con autonomia en A2'],
            'blocks' => german_expand_support_pack(
                'Escenario: atraviesas una semana real con citas, servicio, pequenos problemas y planes futuros sin volver a A1.',
                ['Cuenta algo que paso.', 'Gestiona una cita o servicio.', 'Menciona un plan futuro.'],
                ['Usar frases demasiado cortas para todo.', 'No enlazar motivo y solucion.', 'Perder la cortesia en servicio.'],
                ['Usa Perfekt.', 'Usa una peticion cortesa.', 'Usa werden.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint A2: autonomia cotidiana'],
            'theory_aliases' => ['Errores que mas castigan el salto a B1'],
            'blocks' => german_expand_support_pack(
                'Escenario: revisas tus errores de A2 antes de que te saboteen cuando quieras opinar o escribir mas formalmente.',
                ['Corrige una frase con Perfekt.', 'Corrige una peticion cortesa.', 'Corrige una frase de futuro.'],
                ['Elegir mal el auxiliar.', 'Pedir algo sin formula social minima.', 'Romper el orden cuando aparece werden.'],
                ['Detecta el verbo.', 'Detecta la cortesia.', 'Di la version corregida completa.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.2: relativas, correos y tramites'],
            'theory_aliases' => ['Relativas para describir con mas precision'],
            'blocks' => german_expand_support_pack(
                'Escenario: debes describir una persona, un curso o un documento con suficiente precision para que no haya malentendidos.',
                ['Describe una persona con der o die.', 'Describe un curso con una relativa.', 'Haz una frase con das.'],
                ['Romper la relativa por mitad.', 'Usar principal y relativa con el mismo orden.', 'No conectar claramente el referente.'],
                ['Usa der Kurs, der...', 'Usa die Person, die...', 'Usa das Dokument, das...']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.2: relativas, correos y tramites'],
            'theory_aliases' => ['Correo formal, consulta y seguimiento'],
            'blocks' => german_expand_support_pack(
                'Escenario: escribes un correo para resolver una duda real y necesitas sonar amable, claro y suficientemente profesional.',
                ['Abre el correo.', 'Formula la consulta.', 'Cierra con seguimiento cordial.'],
                ['Entrar directo sin saludo.', 'No indicar claramente la duda.', 'Cerrar sin formula estable.'],
                ['Usa Sehr geehrte...', 'Usa Koennten Sie mir bitte...?', 'Usa Mit freundlichen Gruessen.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.2: relativas, correos y tramites'],
            'theory_aliases' => ['Formularios, plazos y pequenos bloqueos administrativos'],
            'blocks' => german_expand_support_pack(
                'Escenario: un tramite se atasca por un documento o una fecha y necesitas preguntar, aclarar y reactivar el proceso.',
                ['Pregunta por un plazo.', 'Pregunta por un documento faltante.', 'Pide confirmacion del siguiente paso.'],
                ['Hablar del tramite sin nombrar el problema.', 'Olvidar la fecha limite.', 'No pedir accion concreta.'],
                ['Usa Frist.', 'Usa Unterlage.', 'Usa Was soll ich jetzt tun?']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.3: trabajo, estudio y resolucion de problemas'],
            'theory_aliases' => ['Trabajo, tareas y responsabilidades'],
            'blocks' => german_expand_support_pack(
                'Escenario: explicas quien hace que en un equipo y que tarea depende de otra para no quedar como caos con patas.',
                ['Nombra una responsabilidad.', 'Explica una prioridad.', 'Marca una dependencia simple.'],
                ['Hablar de tareas sin sujeto claro.', 'Enumerar sin jerarquia.', 'Olvidar verbos de responsabilidad.'],
                ['Usa zustandig fuer.', 'Usa muessen.', 'Usa zuerst / danach.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.3: trabajo, estudio y resolucion de problemas'],
            'theory_aliases' => ['Problemas, bloqueos y peticion de ayuda'],
            'blocks' => german_expand_support_pack(
                'Escenario: algo falla en trabajo o estudio y necesitas explicar el bloqueo con claridad antes de pedir ayuda.',
                ['Describe el problema.', 'Di la consecuencia.', 'Pide ayuda concreta.'],
                ['Quejarte sin explicar el bloqueo.', 'No decir que ya intentaste.', 'Pedir ayuda demasiado vaga.'],
                ['Usa Problem mit...', 'Usa deshalb.', 'Usa Koennen Sie mir helfen?']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.3: trabajo, estudio y resolucion de problemas'],
            'theory_aliases' => ['Propuesta, solucion y seguimiento'],
            'blocks' => german_expand_support_pack(
                'Escenario: propones una mejora y necesitas dejar claro quien hara el seguimiento y cuando.',
                ['Formula una propuesta.', 'Da una razon.', 'Define el siguiente paso.'],
                ['Proponer sin justificar.', 'Cerrar sin seguimiento.', 'Confundir idea y accion concreta.'],
                ['Usa Ich schlage vor...', 'Usa damit.', 'Usa morgen / naechste Woche.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.4: medios, resumen y mini presentaciones'],
            'theory_aliases' => ['Noticias, medios y fuentes frecuentes'],
            'blocks' => german_expand_support_pack(
                'Escenario: comentas una noticia y necesitas decir de que fuente viene y por que te parece fiable o no.',
                ['Nombra una fuente.', 'Di de que trata una noticia.', 'Da una opinion corta sobre la fuente.'],
                ['Confundir noticia con opinion.', 'No nombrar la fuente.', 'Usar vocabulario demasiado domestico para medios.'],
                ['Usa Bericht o Artikel.', 'Usa Quelle.', 'Usa ich finde...']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.4: medios, resumen y mini presentaciones'],
            'theory_aliases' => ['Resumen breve con estructura clara'],
            'blocks' => german_expand_support_pack(
                'Escenario: resumes una fuente en menos de un minuto y necesitas mantener tema, idea central y cierre sin perder el hilo.',
                ['Nombra el tema.', 'Da una idea principal.', 'Haz un cierre breve.'],
                ['Contar detalles sin jerarquia.', 'Olvidar el tema general.', 'Cerrar sin conclusion minima.'],
                ['Usa Der Text handelt von...', 'Usa ein wichtiger Punkt ist...', 'Usa zum Schluss...']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.4: medios, resumen y mini presentaciones'],
            'theory_aliases' => ['Mini presentaciones de un minuto'],
            'blocks' => german_expand_support_pack(
                'Escenario: tienes sesenta segundos para presentar una idea y no puedes esconderte detras de una diapositiva.',
                ['Abre con una frase clara.', 'Da dos ideas ordenadas.', 'Cierra con una conclusion.'],
                ['Empezar sin tema visible.', 'Acumular ideas sin respiracion.', 'No marcar el cierre.'],
                ['Usa zuerst.', 'Usa ausserdem.', 'Usa zum Schluss.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint B1: independencia comunicativa'],
            'theory_aliases' => ['Lo que ya debe sostenerse en B1'],
            'blocks' => german_expand_support_pack(
                'Escenario: cierras B1 demostrando que ya puedes opinar, escribir, resumir y resolver problemas sin caer en frases escolares.',
                ['Da una opinion con razon.', 'Resume una idea.', 'Propone una mejora o solucion.'],
                ['Opinar sin justificar.', 'Resumir sin jerarquia.', 'Proponer sin accion concreta.'],
                ['Usa weil o obwohl.', 'Usa Der Text handelt von...', 'Usa Ich schlage vor...']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint B1: independencia comunicativa'],
            'theory_aliases' => ['Errores que frenan la entrada a B2'],
            'blocks' => german_expand_support_pack(
                'Escenario: haces una ultima limpieza antes de entrar a textos y debates mas densos.',
                ['Corrige una subordinada.', 'Corrige una frase formal.', 'Corrige un resumen demasiado plano.'],
                ['Subordinadas abiertas.', 'Formalidad forzada.', 'Falta de matiz al opinar.'],
                ['Detecta el error de orden.', 'Mejora el registro.', 'Anade un matiz.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.2: informes, nominalizacion y textos densos'],
            'theory_aliases' => ['Informes breves y registro analitico'],
            'blocks' => german_expand_support_pack(
                'Escenario: presentas un mini informe oral o escrito y necesitas sonar analitico sin volverte ilegible.',
                ['Formula un dato central.', 'Introduce una limitacion.', 'Cierra con una implicacion.'],
                ['Dar datos sin lectura.', 'Sonar demasiado conversacional.', 'Cerrar sin inferencia.'],
                ['Usa Die Daten zeigen...', 'Usa allerdings.', 'Usa daher.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.2: informes, nominalizacion y textos densos'],
            'theory_aliases' => ['Nominalizacion y compresion del discurso'],
            'blocks' => german_expand_support_pack(
                'Escenario: te piden condensar ideas para que suenen mas academicas, pero sin perder sentido ni control.',
                ['Convierte un verbo en sustantivo.', 'Compacta una frase.', 'Mantiene una relacion clara entre conceptos.'],
                ['Nominalizar por decorar.', 'Perder el agente o la accion central.', 'Crear frases demasiado pesadas.'],
                ['Usa -ung o -keit.', 'Reescribe una frase larga.', 'Lee la nueva version y comprueba si sigue clara.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.2: informes, nominalizacion y textos densos'],
            'theory_aliases' => ['Lectura densa y jerarquia de ideas'],
            'blocks' => german_expand_support_pack(
                'Escenario: lees un texto espeso y necesitas separar tesis, detalle y conclusion antes de resumirlo.',
                ['Detecta la tesis.', 'Detecta una evidencia.', 'Detecta la conclusion o limite.'],
                ['Leer todo con el mismo peso.', 'Confundir detalle con argumento principal.', 'Perder el cierre del autor.'],
                ['Marca una idea central.', 'Marca una prueba.', 'Marca un matiz o una reserva.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.3: tecnologia, sociedad y escucha avanzada'],
            'theory_aliases' => ['Tecnologia, impacto y lenguaje de debate'],
            'blocks' => german_expand_support_pack(
                'Escenario: discutes tecnologia y sociedad y te exigen una postura con beneficio, riesgo y responsabilidad.',
                ['Nombra una ventaja.', 'Nombra un riesgo.', 'Formula una posicion equilibrada.'],
                ['Quedarte solo en entusiasmo o miedo.', 'No vincular tecnologia con personas reales.', 'Cerrar sin criterio de responsabilidad.'],
                ['Usa einerseits / andererseits.', 'Usa Risiko.', 'Usa Verantwortung.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.3: tecnologia, sociedad y escucha avanzada'],
            'theory_aliases' => ['Concesion, contraste y consecuencia'],
            'blocks' => german_expand_support_pack(
                'Escenario: necesitas hilar una postura con concesion, contraste y resultado sin sonar como lista de conectores.',
                ['Concede un punto.', 'Contrasta con otra idea.', 'Extrae una consecuencia.'],
                ['Encadenar conectores sin funcion.', 'Oponer ideas sin matiz.', 'No llegar a conclusion.'],
                ['Usa zwar... aber.', 'Usa allerdings.', 'Usa folglich.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.3: tecnologia, sociedad y escucha avanzada'],
            'theory_aliases' => ['Escucha avanzada y reconstruccion de sentido'],
            'blocks' => german_expand_support_pack(
                'Escenario: escuchas un comentario denso y tienes que reconstruir la idea aunque no captures todas las palabras.',
                ['Detecta la tesis oral.', 'Detecta un matiz.', 'Reconstruye la conclusion.'],
                ['Obsesionarte con cada palabra suelta.', 'No retener los conectores clave.', 'Perder la estructura global.'],
                ['Escucha por bloques.', 'Anota palabras clave.', 'Resume en una frase propia.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.2: lectura academica, sintesis y formacion de palabras'],
            'theory_aliases' => ['Lectura academica y deteccion de tesis'],
            'blocks' => german_expand_support_pack(
                'Escenario: abres un texto academico y necesitas localizar rapido la tesis para no hundirte en la densidad.',
                ['Marca la tesis.', 'Marca un argumento de apoyo.', 'Marca una restriccion o matiz.'],
                ['Subrayar todo igual.', 'Confundir marco teorico con tesis.', 'No notar la restriccion del autor.'],
                ['Busca verbos de postura.', 'Busca conectores de limite.', 'Resume la tesis en una linea.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.2: lectura academica, sintesis y formacion de palabras'],
            'theory_aliases' => ['Sintesis fiel y posicion propia con distancia'],
            'blocks' => german_expand_support_pack(
                'Escenario: debes resumir una fuente con fidelidad y luego entrar tu postura sin invadir la voz original.',
                ['Resume al autor.', 'Marca distancia critica.', 'Anade tu posicion con cuidado.'],
                ['Mezclar voz propia y voz ajena.', 'Afirmar sin marcar distancia.', 'Repetir formulas sin contenido real.'],
                ['Usa Der Beitrag legt nahe...', 'Usa Aus meiner Sicht...', 'Usa allerdings si hace falta matiz.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.2: lectura academica, sintesis y formacion de palabras'],
            'theory_aliases' => ['Formacion de palabras y precision lexical'],
            'blocks' => german_expand_support_pack(
                'Escenario: necesitas entender y producir palabras complejas sin depender del diccionario en cada linea.',
                ['Detecta un prefijo.', 'Detecta un sufijo.', 'Explica el sentido general de una palabra larga.'],
                ['Leer la palabra como bloque opaco.', 'Ignorar morfemas repetidos.', 'Elegir sinonimos demasiado vagos.'],
                ['Usa -ung o -heit.', 'Usa ent- o ver-.', 'Relaciona la palabra con su campo semantico.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.3: certificaciones, presentaciones y plan maestro'],
            'theory_aliases' => ['Presentaciones formales y arquitectura del discurso'],
            'blocks' => german_expand_support_pack(
                'Escenario: expones frente a una audiencia y necesitas una arquitectura visible desde la primera frase.',
                ['Abre la presentacion.', 'Marca dos o tres puntos.', 'Anticipa el cierre.'],
                ['Empezar sin estructura visible.', 'Moverse entre puntos sin transicion.', 'No recordar al publico a donde vas.'],
                ['Usa Im Folgenden...', 'Usa zunaechst / zweitens.', 'Usa abschliessend.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.3: certificaciones, presentaciones y plan maestro'],
            'theory_aliases' => ['Registro alto, reformulacion y precision final'],
            'blocks' => german_expand_support_pack(
                'Escenario: necesitas reformular una idea en registro alto sin caer ni en coloquialismo ni en grandilocuencia vacia.',
                ['Reformula una idea simple.', 'Eleva el registro.', 'Mantiene la precision del significado.'],
                ['Cambiar palabras sin mejorar el registro.', 'Sonar artificial por exceso de formalidad.', 'Perder el significado exacto al reformular.'],
                ['Usa allerdings o folglich.', 'Evita muletillas.', 'Compara la frase base y la version refinada.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.3: certificaciones, presentaciones y plan maestro'],
            'theory_aliases' => ['Plan maestro de consolidacion despues del curso'],
            'blocks' => german_expand_support_pack(
                'Escenario: terminas el curso y tienes que disenar una rutina que mantenga nivel alto sin depender del impulso del momento.',
                ['Define una rutina semanal.', 'Elige una metrica simple.', 'Reserva una practica oral y una escrita.'],
                ['Hacer planes enormes imposibles de sostener.', 'No medir nada.', 'Olvidar produccion activa.'],
                ['Escribe un objetivo.', 'Escribe una frecuencia.', 'Escribe un criterio de revision.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint Maestro C1: simulacion integral'],
            'theory_aliases' => ['Lo que una ruta completa debe dejar en C1'],
            'blocks' => german_expand_support_pack(
                'Escenario: haces balance final de la ruta y necesitas demostrar lectura, sintesis, posicion y habla con registro alto.',
                ['Resume una tesis compleja.', 'Formula una postura propia.', 'Explica que habilidad oral has consolidado.'],
                ['Confundir comprension con produccion.', 'No distinguir sintesis de opinion.', 'Cerrar sin evidencia de progreso real.'],
                ['Usa una formula de sintesis.', 'Usa una formula de postura.', 'Nombra una rutina de consolidacion.']
            ),
        ],
        [
            'lesson_aliases' => ['Checkpoint Maestro C1: simulacion integral'],
            'theory_aliases' => ['Como consolidar sin perder el nivel ganado'],
            'blocks' => german_expand_support_pack(
                'Escenario: diseñas la etapa posterior al curso para que el nivel no se derrumbe en dos semanas.',
                ['Elige una rutina minima.', 'Elige una practica oral.', 'Elige una practica de lectura o escritura.'],
                ['Pensar solo en consumir contenido.', 'No planear revision de errores.', 'Trazar metas sin frecuencia realista.'],
                ['Escribe una rutina semanal.', 'Escribe una practica oral.', 'Escribe una metrica mensual.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.1: sonidos, saludos y primer contacto'],
            'theory_aliases' => ['Alphabet, spelling y supervivencia en clase'],
            'blocks' => german_expand_support_pack(
                'Escenario: rellenas un formulario en clase y tienes que deletrear tu nombre, pedir repeticion y confirmar una palabra clave.',
                ['Deletrea tu nombre.', 'Pide que repitan una instruccion.', 'Pregunta como se escribe una palabra.'],
                ['Confundir el nombre de las letras.', 'Quedarte sin frase para pedir repeticion.', 'Deletrear demasiado rapido y perder claridad.'],
                ['Usa Mein Name ist...', 'Usa Noch einmal, bitte.', 'Usa Wie schreibt man das?']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1.4: compras, ciudad y verbos de accion'],
            'theory_aliases' => ['Precio, cantidad y preguntas de mostrador'],
            'blocks' => german_expand_support_pack(
                'Escenario: compras deprisa antes de que cierre la tienda y necesitas preguntar precio, cantidad y disponibilidad sin improvisar demasiado.',
                ['Pregunta el precio.', 'Pide una cantidad concreta.', 'Pregunta si todavia queda una unidad.'],
                ['Olvidar la pregunta completa y quedarte solo con el sustantivo.', 'Mezclar cantidad y objeto en orden torpe.', 'No marcar cortesia al pedir en mostrador.'],
                ['Usa Wie viel kostet...?', 'Usa Ich nehme zwei...', 'Usa Haben Sie noch...?']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2.1: pasado, dativo y vida cotidiana'],
            'theory_aliases' => ['Citas, transporte y pequenas gestiones'],
            'blocks' => german_expand_support_pack(
                'Escenario: gestionas un retraso real con transporte, cita y cambio de hora sin perder la estructura de la frase.',
                ['Avisa un retraso.', 'Explica el motivo.', 'Propone una nueva hora o confirmacion.'],
                ['Dar solo el motivo sin la accion.', 'Olvidar la hora concreta.', 'Usar Perfekt o presente sin cierre claro de la gestion.'],
                ['Usa Ich komme spaeter.', 'Usa weil mein Zug...', 'Usa Koennen wir den Termin verschieben?']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1.1: opinion, subordinadas y mundo real'],
            'theory_aliases' => ['Resumen oral y presentacion corta'],
            'blocks' => german_expand_support_pack(
                'Escenario: tienes un minuto para resumir una idea delante de un grupo y no puedes esconderte detras de frases sueltas.',
                ['Abre el tema.', 'Da dos ideas enlazadas.', 'Cierra con una conclusion breve.'],
                ['Entrar sin presentar el tema.', 'Acumular frases sin conectores.', 'Cerrar de golpe sin marcar conclusion.'],
                ['Usa Ich moechte kurz erklaeren...', 'Usa ein wichtiger Punkt ist...', 'Usa zum Schluss...']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2.1: debate, matices y lenguaje abstracto'],
            'theory_aliases' => ['Concesion, contraargumento y cierre elegante'],
            'blocks' => german_expand_support_pack(
                'Escenario: sostienes una postura en un debate y aun asi tienes que conceder algo, matizar y volver a tu tesis con control.',
                ['Concede un punto al otro lado.', 'Recupera tu tesis.', 'Cierra con una conclusion equilibrada.'],
                ['Usar zwar... aber sin verdadero contraste.', 'Repetir la misma postura sin matiz.', 'Cerrar sin recuperar la idea principal.'],
                ['Usa zwar... aber.', 'Usa dennoch o allerdings.', 'Haz una conclusion breve.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1.1: precision, registro y ruta de consolidacion'],
            'theory_aliases' => ['Resumen academico y toma de postura'],
            'blocks' => german_expand_support_pack(
                'Escenario: resumes un texto academico y luego entras tu posicion propia sin confundir la voz del autor con la tuya.',
                ['Resume la tesis del autor.', 'Marca una distancia critica.', 'Formula una postura propia breve.'],
                ['Mezclar resumen y opinion en la misma frase sin marca.', 'Copiar la voz del texto en vez de sintetizar.', 'Tomar postura sin haber fijado primero la tesis.'],
                ['Usa Der Beitrag legt nahe...', 'Usa Aus meiner Sicht...', 'Usa allerdings para introducir matiz.']
            ),
        ],
    ];
}

function german_master_dialogue_support(): array
{
    return [
        [
            'lesson_aliases' => ['Nivel A1.1: sonidos, saludos y primer contacto'],
            'theory_aliases' => ['Preguntas personales, haben y microdialogos'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Guten Tag. Wie heisst du?',
                'B: Ich heisse Laura. Und du?',
                'A: Ich heisse Tomas. Woher kommst du?',
                'B: Ich komme aus Chile.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A1.2: familia, numeros y preguntas basicas'],
            'theory_aliases' => ['Numeros, edad, telefono y hora'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Wie alt bist du?',
                'B: Ich bin vierundzwanzig Jahre alt.',
                'A: Und wann beginnt dein Kurs?',
                'B: Um Viertel nach neun.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A1.3: rutinas, presente y verbos separables'],
            'theory_aliases' => ['Horarios y verbos separables'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Stehst du am Samstag frueh auf?',
                'B: Nicht so frueh. Ich stehe um acht Uhr auf und kaufe spaeter ein.',
                'A: Rufst du mich danach an?',
                'B: Ja, ich rufe dich gegen zehn Uhr an.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A1.4: compras, ciudad y verbos de accion'],
            'theory_aliases' => ['Precio, cantidad y preguntas de mostrador'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Guten Tag. Wie viel kostet das Brot?',
                'B: Ein Brot kostet zwei Euro zwanzig.',
                'A: Gut, dann nehme ich zwei Brote und eine Flasche Wasser.',
                'B: Gern. Sonst noch etwas?',
            ])],
        ],
        [
            'lesson_aliases' => ['Checkpoint A1: supervivencia completa'],
            'theory_aliases' => ['Lo que ya debe salir sin apoyo en A1'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Guten Morgen. Ich heisse Elena und ich komme aus Peru.',
                'B: Freut mich. Hast du heute Zeit fuer einen Kaffee?',
                'A: Ja, aber erst nach dem Kurs um zehn Uhr.',
                'B: Perfekt, dann sehen wir uns spaeter.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A2.1: pasado, dativo y vida cotidiana'],
            'theory_aliases' => ['Citas, transporte y pequenas gestiones'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Guten Morgen, Frau Becker. Ich komme zehn Minuten spaeter.',
                'B: Kein Problem. Haben Sie um halb elf Zeit?',
                'A: Ja, das passt gut.',
                'B: Dann sehen wir uns spaeter im Buero.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A2.2: tramites, citas y movimiento por la ciudad'],
            'theory_aliases' => ['Oficina, servicio y preguntas utiles'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Entschuldigung, wo bekomme ich dieses Formular?',
                'B: Am Schalter drei, direkt neben dem Eingang.',
                'A: Und welche Nummer brauche ich?',
                'B: Heute brauchen Sie die Nummer vierundzwanzig.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A2.3: comida, servicio y pequenas reclamaciones'],
            'theory_aliases' => ['Pequenas reclamaciones y solucion cordial'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Entschuldigung, ich glaube, die Rechnung ist zu hoch.',
                'B: Einen Moment bitte. Ich pruefe das sofort.',
                'A: Danke. Ich hatte nur eine Suppe und ein Wasser.',
                'B: Sie haben recht. Ich korrigiere das gleich.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel A2.4: planes, invitaciones y futuro cercano'],
            'theory_aliases' => ['Invitaciones, propuestas y respuestas naturales'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Hast du am Freitagabend Zeit?',
                'B: Vielleicht. Was wolltest du machen?',
                'A: Ich werde mit ein paar Freunden essen gehen. Moechtest du mitkommen?',
                'B: Ja, gern. Schreib mir spaeter die Uhrzeit.',
            ])],
        ],
        [
            'lesson_aliases' => ['Checkpoint A2: autonomia cotidiana'],
            'theory_aliases' => ['Lo que ya debe salir con autonomia en A2'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Ich habe morgen einen Termin in der Stadt.',
                'B: Kommst du mit dem Zug?',
                'A: Ja, aber wenn er spaet ist, rufe ich sofort an.',
                'B: Gut. Und am Wochenende werden wir dann zusammen kochen.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B1.1: opinion, subordinadas y mundo real'],
            'theory_aliases' => ['Trabajo, estudio, medios y sociedad'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Was denkst du ueber soziale Medien?',
                'B: Einerseits sind sie nuetzlich, andererseits machen sie viel Stress.',
                'A: Ja, ich finde auch, dass man klare Grenzen braucht.',
                'B: Sonst verliert man zu viel Zeit.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B1.2: relativas, correos y tramites'],
            'theory_aliases' => ['Correo formal, consulta y seguimiento'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Guten Tag, spreche ich mit dem Sekretariat?',
                'B: Ja, wie kann ich Ihnen helfen?',
                'A: Ich moechte wissen, ob fuer den Kurs noch Plaetze frei sind.',
                'B: Ja, aber wir brauchen noch ein Dokument von Ihnen.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B1.3: trabajo, estudio y resolucion de problemas'],
            'theory_aliases' => ['Propuesta, solucion y seguimiento'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Ich habe ein Problem mit der Datei. Sie laesst sich nicht oeffnen.',
                'B: Dann schicken Sie sie bitte noch einmal als PDF.',
                'A: Gute Idee. Ich mache das sofort und melde mich spaeter noch einmal.',
                'B: Perfekt, dann koennen wir weiterarbeiten.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B1.4: medios, resumen y mini presentaciones'],
            'theory_aliases' => ['Mini presentaciones de un minuto'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Worum geht es in deinem Artikel?',
                'B: Er handelt von Stress im Studium.',
                'A: Was ist der wichtigste Punkt?',
                'B: Gute Planung hilft, den Zeitdruck zu reduzieren.',
            ])],
        ],
        [
            'lesson_aliases' => ['Checkpoint B1: independencia comunicativa'],
            'theory_aliases' => ['Lo que ya debe sostenerse en B1'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Wie war dein Text ueber Onlinekurse?',
                'B: Ich habe erklaert, warum sie praktisch sind, und ich habe eine Verbesserung vorgeschlagen.',
                'A: Welche?',
                'B: Mehr Betreuung und klarere Rueckmeldungen.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B2.1: debate, matices y lenguaje abstracto'],
            'theory_aliases' => ['Concesion, contraargumento y cierre elegante'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Digitale Arbeit spart zwar Zeit, aber sie macht manche Teams auch distanzierter.',
                'B: Das stimmt. Dennoch sollte man die Vorteile nicht ignorieren.',
                'A: Genau. Man braucht eher bessere Regeln als weniger Technik.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B2.2: informes, nominalizacion y textos densos'],
            'theory_aliases' => ['Informes breves y registro analitico'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Was zeigen die Daten?',
                'B: Der Anteil digitaler Kurse ist deutlich gestiegen.',
                'A: Gibt es auch eine Einschraenkung?',
                'B: Ja, Menschen mit schwacher technischer Ausstattung profitieren weniger.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel B2.3: tecnologia, sociedad y escucha avanzada'],
            'theory_aliases' => ['Tecnologia, impacto y lenguaje de debate'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Findest du kuenstliche Intelligenz im Unterricht sinnvoll?',
                'B: Grundsaetzlich ja, aber nur mit klaren Grenzen.',
                'A: Wegen des Datenschutzes?',
                'B: Auch. Und weil nicht alle Lernenden gleich davon profitieren.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel C1.1: precision, registro y ruta de consolidacion'],
            'theory_aliases' => ['Resumen academico y toma de postura'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Wie fasst du den Beitrag zusammen?',
                'B: Der Autor betont, dass Innovation sozial eingebettet werden muss.',
                'A: Und deine Position?',
                'B: Ich stimme weitgehend zu, wuerde aber die lokale Umsetzung staerker betonen.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel C1.2: lectura academica, sintesis y formacion de palabras'],
            'theory_aliases' => ['Sintesis fiel y posicion propia con distancia'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Was ist die zentrale These des Textes?',
                'B: Dass Regulierung keine Bremse, sondern eine Voraussetzung fuer nachhaltige Innovation ist.',
                'A: Bleibt etwas offen?',
                'B: Ja, wie diese Regulierung konkret umgesetzt werden soll.',
            ])],
        ],
        [
            'lesson_aliases' => ['Nivel C1.3: certificaciones, presentaciones y plan maestro'],
            'theory_aliases' => ['Presentaciones formales y arquitectura del discurso'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Im Folgenden moechte ich auf drei zentrale Punkte eingehen.',
                'B: Zunaechst interessiert mich Ihre Leitfrage.',
                'A: Wie digitale Bildung gerechter gestaltet werden kann.',
                'B: Gut, dann koennen Sie mit dem ersten Argument beginnen.',
            ])],
        ],
        [
            'lesson_aliases' => ['Checkpoint Maestro C1: simulacion integral'],
            'theory_aliases' => ['Lo que una ruta completa debe dejar en C1'],
            'blocks' => [german_expand_dialogue_block('Mini dialogo', [
                'A: Was nimmst du aus der ganzen Route mit?',
                'B: Ich kann jetzt komplexe Texte besser zusammenfassen und klarer argumentieren.',
                'A: Und wie willst du das Niveau halten?',
                'B: Mit regelmaessiger Lesepraxis, kurzen Kommentaren und monatlichen Praesentationen.',
            ])],
        ],
    ];
}

function german_master_advanced_dialogue_support(): array
{
    return [
        [
            'lesson_aliases' => ['Nivel B2.1: debate, matices y lenguaje abstracto'],
            'theory_aliases' => ['Concesion, contraargumento y cierre elegante'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo de matiz', [
                    'A: Zwar bringt die Digitalisierung viele Vorteile, aber manche Lernende geraten schneller unter Druck.',
                    'B: Das stimmt, dennoch sollte man nicht nur die Risiken betonen.',
                    'A: Genau, entscheidend ist eher, wie die Werkzeuge eingefuehrt werden.',
                    'B: Dann braucht man also Regeln statt blosser Ablehnung.',
                ]),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel B2.2: informes, nominalizacion y textos densos'],
            'theory_aliases' => ['Informes breves y registro analitico'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo analitico', [
                    'A: Laesst sich aus den Daten schon eine klare Tendenz ableiten?',
                    'B: Ja, vor allem im Bereich flexibler Lernangebote ist ein deutlicher Zuwachs erkennbar.',
                    'A: Gibt es trotzdem eine Einschraenkung?',
                    'B: Ja, die ungleiche technische Ausstattung verzerrt einen Teil der Ergebnisse.',
                ]),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel B2.3: tecnologia, sociedad y escucha avanzada'],
            'theory_aliases' => ['Tecnologia, impacto y lenguaje de debate'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo de postura', [
                    'A: Sollte kuenstliche Intelligenz staerker im Unterricht eingesetzt werden?',
                    'B: Grundsaetzlich ja, sofern Transparenz und Datenschutz ernst genommen werden.',
                    'A: Dann geht es also nicht um Technik gegen Menschen.',
                    'B: Genau, sondern um einen verantwortlichen Rahmen fuer ihren Einsatz.',
                ]),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel C1.1: precision, registro y ruta de consolidacion'],
            'theory_aliases' => ['Resumen academico y toma de postura'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo de sintesis', [
                    'A: Wie wuerdest du die Argumentation in einem Satz zusammenfassen?',
                    'B: Der Text zeigt, dass Innovation ohne soziale Einbettung langfristig instabil bleibt.',
                    'A: Und wo setzt deine Kritik an?',
                    'B: Vor allem bei der Frage, wie diese Verantwortung institutionell abgesichert werden soll.',
                ]),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel C1.2: lectura academica, sintesis y formacion de palabras'],
            'theory_aliases' => ['Sintesis fiel y posicion propia con distancia'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo academico', [
                    'A: Wuerdest du sagen, dass der Autor neutral bleibt?',
                    'B: Nicht ganz. Die Wortwahl signalisiert deutlich Zustimmung zu staerkerer Regulierung.',
                    'A: Also laesst sich auch die Haltung des Autors rekonstruieren.',
                    'B: Ja, und genau das gehoert zu einer praezisen C1-Synthese dazu.',
                ]),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel C1.3: certificaciones, presentaciones y plan maestro'],
            'theory_aliases' => ['Presentaciones formales y arquitectura del discurso'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo de presentacion', [
                    'A: Wie werden Sie Ihre Argumentation strukturieren?',
                    'B: Zunaechst formuliere ich die Leitfrage, dann ordne ich drei zentrale Punkte.',
                    'A: Und wie sichern Sie den roten Faden?',
                    'B: Indem ich jede Phase sichtbar markiere und mit einer klaren Schlussfolgerung ende.',
                ]),
            ],
        ],
        [
            'lesson_aliases' => ['Checkpoint Maestro C1: simulacion integral'],
            'theory_aliases' => ['Lo que una ruta completa debe dejar en C1'],
            'blocks' => [
                german_expand_dialogue_block('Dialogo de cierre', [
                    'A: Woran merkst du, dass du heute deutlich weiter bist als am Anfang der Route?',
                    'B: Ich kann komplexe Texte strukturierter lesen, genauer zusammenfassen und differenzierter argumentieren.',
                    'A: Und wie willst du dieses Niveau halten?',
                    'B: Mit regelmaessiger Produktion, gezielter Fehlerarbeit und einer stabilen Lernroutine.',
                ]),
            ],
        ],
    ];
}

function german_master_dialogue_blocks_for_theory(string $lessonTitle, string $theoryTitle): array
{
    static $dialogues = null;
    if ($dialogues === null) {
        $dialogues = array_merge(
            german_master_dialogue_support(),
            german_master_advanced_dialogue_support()
        );
    }

    $lessonNeedle = mb_strtolower(trim($lessonTitle), 'UTF-8');
    $theoryNeedle = mb_strtolower(trim($theoryTitle), 'UTF-8');
    $blocks = [];

    foreach ($dialogues as $entry) {
        $lessonMatch = false;
        foreach ((array) ($entry['lesson_aliases'] ?? []) as $alias) {
            if (mb_strtolower(trim((string) $alias), 'UTF-8') === $lessonNeedle) {
                $lessonMatch = true;
                break;
            }
        }

        if (!$lessonMatch) {
            continue;
        }

        foreach ((array) ($entry['theory_aliases'] ?? []) as $alias) {
            if (mb_strtolower(trim((string) $alias), 'UTF-8') === $theoryNeedle) {
                $blocks = array_merge($blocks, (array) ($entry['blocks'] ?? []));
                break;
            }
        }
    }

    return $blocks;
}

function german_course_master_blueprint(): array
{
    $profile = german_expand_course_profile();
    $profile['instancia_id'] = 1;
    $profile['creado_por'] = 13;
    $profile['fecha_inicio'] = date('Y-m-d');
    $profile['fecha_fin'] = null;
    $profile['duracion_semanas'] = 132;

    $lessons = array_merge(
        german_master_existing_lessons(),
        german_expand_new_lessons_group_one(),
        german_expand_new_lessons_group_two(),
        german_master_checkpoint_lessons()
    );

    $lessons = german_master_enrich_lessons($lessons);
    $lessons = german_master_resequence_lessons($lessons);
    $profile = german_expand_restore_umlauts_recursive($profile);
    $lessons = german_expand_restore_umlauts_recursive($lessons);

    return [
        'course' => $profile,
        'lessons' => $lessons,
    ];
}

function german_expand_new_lessons_group_two_a(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Nivel B1.3: trabajo, estudio y resolucion de problemas',
                'Trabajo, universidad, reuniones, dificultades practicas y estrategias para explicar, pedir ayuda y proponer soluciones.',
                180,
                [
                    german_expand_theory(
                        'Trabajo, tareas y responsabilidades',
                        18,
                        'B1 exige salir del "me gusta" y entrar en obligaciones, tareas, prioridades y pequenos roces del trabajo o el estudio.',
                        [
                            german_expand_section('Lexico de trabajo y estudio', ['die Aufgabe', 'die Besprechung', 'die Schicht', 'die Abgabe', 'verantwortlich sein fuer'], null, null, 'aleman'),
                            german_expand_section('Frases utiles', ['Ich bin fuer dieses Projekt verantwortlich.', 'Wir haben heute eine Besprechung.', 'Die Abgabe ist morgen.'], null, null, 'aleman'),
                            german_expand_section('Modelo funcional', [], null, 'Ich bin fuer den Bericht verantwortlich und die Abgabe ist morgen um acht Uhr.', 'aleman'),
                        ],
                        'En B1 conviene unir responsabilidad + plazo + accion. Eso ya suena a vida real.',
                        'Escenario: explicas que tarea tienes y por que no puedes improvisar hoy.',
                        ['Nombra una tarea.', 'Di quien es responsable.', 'Di el plazo.']
                    ),
                    german_expand_theory(
                        'Problemas, bloqueos y peticion de ayuda',
                        18,
                        'Cuando aparece un problema, el alumno necesita describirlo, contextualizarlo y pedir apoyo sin sonar agresivo ni confuso.',
                        [
                            german_expand_section('Bloques de incidencia', ['ein Problem mit ... haben', 'nicht funktionieren', 'zu spaet sein', 'etwas erklaeren', 'jemanden informieren'], null, null, 'aleman'),
                            german_expand_section('Frases utiles', ['Ich habe ein Problem mit dem System.', 'Der Link funktioniert nicht.', 'Koenntest du mir kurz helfen?'], null, null, 'aleman'),
                            german_expand_section('Modelo de bloqueo', [], null, 'Ich habe ein Problem mit dem Formular, weil der Link nicht funktioniert.', 'aleman'),
                        ],
                        'Describe primero el problema y despues la ayuda que necesitas. Ese orden evita mensajes torpes.',
                        'Escenario: una plataforma falla y tienes que avisar a tiempo.',
                        ['Nombra el problema.', 'Explica la causa.', 'Pide ayuda concreta.']
                    ),
                    german_expand_theory(
                        'Propuesta, solucion y seguimiento',
                        17,
                        'B1 mejora mucho cuando el alumno no solo reporta un problema, sino que propone una salida o un siguiente paso.',
                        [
                            german_expand_section('Frases de solucion', ['Wir koennten ...', 'Vielleicht waere es besser, wenn ...', 'Ich schlage vor, dass ...'], null, null, 'aleman'),
                            german_expand_section('Frases de seguimiento', ['Ich melde mich spaeter noch einmal.', 'Dann koennen wir entscheiden.', 'So sparen wir Zeit.'], null, null, 'aleman'),
                            german_expand_section('Modelo con propuesta', [], null, 'Ich schlage vor, dass wir den Termin verschieben, damit alle Unterlagen vollstaendig sind.', 'aleman'),
                        ],
                        'La solucion buena en B1 tiene accion concreta y razon visible.',
                        'Escenario: debes proponer un cambio para que un proyecto avance mejor.',
                        ['Haz una propuesta.', 'Da una razon.', 'Cierra con el siguiente paso.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Trabajo, problemas y soluciones',
                        'Elige la opcion mas natural en contexto laboral o academico.',
                        'Marca la mejor respuesta.',
                        [
                            german_expand_question('Quieres decir que eres responsable', 'Ich bin fuer dieses Projekt verantwortlich.', 'Ich verantworte fuer dieses Projekt.', 'Ich bin verantwortlich dieses Projekt.'),
                            german_expand_question('El sistema no funciona', 'Der Link funktioniert nicht.', 'Der Link arbeitet nicht.', 'Der Link ist nicht funktion.'),
                            german_expand_question('Quieres proponer una solucion', 'Ich schlage vor, dass wir spaeter anfangen.', 'Ich schlage, dass spaeter wir anfangen.', 'Ich vor schlage wir spaeter anfangen.'),
                        ]
                    ),
                    german_expand_matching(
                        'Empareja el problema con la reaccion util',
                        'Relaciona cada situacion con la frase que mejor la resuelve.',
                        [
                            ['el enlace falla', 'Der Link funktioniert nicht.'],
                            ['pedir ayuda breve', 'Koenntest du mir kurz helfen?'],
                            ['hacer propuesta', 'Ich schlage vor, dass ...'],
                            ['avisar seguimiento', 'Ich melde mich spaeter noch einmal.'],
                        ]
                    ),
                    german_expand_fill(
                        'Completa el reporte del problema',
                        'Rellena con la palabra correcta.',
                        [
                            ['id' => 'b13_fill_1', 'oracion' => 'Ich habe ein ____ mit dem System.', 'respuesta_correcta' => 'Problem'],
                            ['id' => 'b13_fill_2', 'oracion' => 'Kannst du mir kurz ____?', 'respuesta_correcta' => 'helfen'],
                            ['id' => 'b13_fill_3', 'oracion' => 'Ich schlage vor, dass wir spaeter ____.', 'respuesta_correcta' => 'anfangen'],
                        ]
                    ),
                    german_expand_drag(
                        'Lleva cada frase a su funcion',
                        'Arrastra cada bloque al papel que cumple en la conversacion.',
                        [
                            'Ich habe ein Problem mit dem Bericht.' => 'problema',
                            'Der Link funktioniert nicht.' => 'detalle',
                            'Wir koennten den Termin verschieben.' => 'solucion',
                            'Ich melde mich spaeter noch einmal.' => 'seguimiento',
                        ]
                    ),
                    german_expand_listening(
                        'Escucha: incidencia de ultima hora',
                        'Escucha el mensaje y escribe lo que oyes.',
                        'Ich habe ein Problem mit der Datei und koennte die Abgabe heute nicht rechtzeitig schicken.'
                    ),
                    german_expand_writing(
                        'Escribe un mensaje de incidencia con propuesta',
                        'Redacta un correo o mensaje breve donde expliques un problema y propongas una solucion.',
                        'Escribe a un colega, profesor o jefa para explicar un bloqueo y proponer un siguiente paso realista.',
                        130,
                        24,
                        16
                    ),
                ]
            ),
            ['orden' => 11]
        ),
        array_merge(
            german_expand_lesson(
                'Nivel B1.4: medios, resumen y mini presentaciones',
                'Medios, noticias, resumen breve y presentaciones de un minuto para sostener ideas con orden y claridad.',
                180,
                [
                    german_expand_theory(
                        'Noticias, medios y fuentes frecuentes',
                        18,
                        'B1 necesita vocabulario para hablar de lo que lee, escucha o mira. Eso abre opinion, resumen y comparacion de fuentes.',
                        [
                            german_expand_section('Vocabulario de medios', ['die Nachricht', 'der Bericht', 'die Quelle', 'der Artikel', 'der Podcast', 'die Schlagzeile'], null, null, 'aleman'),
                            german_expand_section('Frases utiles', ['Ich habe einen Artikel gelesen.', 'Die Quelle ist nicht sehr klar.', 'Im Podcast ging es um ...'], null, null, 'aleman'),
                            german_expand_section('Modelo de referencia', [], null, 'Ich habe einen kurzen Artikel ueber digitale Bildung gelesen.', 'aleman'),
                        ],
                        'Leer o escuchar algo debe terminar en una frase de referencia clara, no solo en "me gusto".',
                        'Escenario: cuentas a otra persona de que trataba una noticia o un podcast.',
                        ['Nombra la fuente.', 'Di el tema.', 'Evalua brevemente la claridad.']
                    ),
                    german_expand_theory(
                        'Resumen breve con estructura clara',
                        18,
                        'Resumir no es repetir. En B1 basta con identificar tema, dos ideas clave y una conclusion sencilla.',
                        [
                            german_expand_section('Pasos del resumen', ['tema principal', 'dos ideas importantes', 'cierre o consecuencia'], null, null, 'espanol'),
                            german_expand_section('Frases de apoyo', ['Der Text handelt von ...', 'Ein wichtiger Punkt ist ...', 'Am Ende wird deutlich, dass ...'], null, null, 'aleman'),
                            german_expand_section('Modelo de resumen', [], null, 'Der Text handelt von Stress im Studium. Ein wichtiger Punkt ist der Zeitdruck. Am Ende wird deutlich, dass gute Planung hilft.', 'aleman'),
                        ],
                        'Resumen util en B1 significa seleccionar, no copiar.',
                        'Escenario: un profesor te pide contar un texto en tres frases claras.',
                        ['Di el tema.', 'Selecciona dos ideas.', 'Cierra con una conclusion.']
                    ),
                    german_expand_theory(
                        'Mini presentaciones de un minuto',
                        17,
                        'Hablar un minuto con orden es una meta muy realista de B1. Requiere abrir, organizar dos ideas y cerrar sin perder el hilo.',
                        [
                            german_expand_section('Estructura oral', ['tema', 'dos argumentos', 'ejemplo', 'cierre'], null, null, 'espanol'),
                            german_expand_section('Frases de arranque', ['Ich moechte kurz ueber ... sprechen.', 'Ein wichtiger Punkt ist ...', 'Zum Schluss denke ich ...'], null, null, 'aleman'),
                            german_expand_section('Modelo oral', [], null, 'Ich moechte kurz ueber Onlinekurse sprechen. Sie sind flexibel und sparen Zeit. Zum Schluss denke ich, dass sie gute Organisation brauchen.', 'aleman'),
                        ],
                        'La presentacion mejora mucho cuando el alumno se apoya en una estructura fija en lugar de improvisar.',
                        'Escenario: debes hablar un minuto sobre un tema conocido delante de un grupo pequeno.',
                        ['Abre el tema.', 'Da dos ideas.', 'Cierra con una frase final.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Medios, resumen y presentacion',
                        'Elige la estructura correcta para resumir o presentar una idea.',
                        'Selecciona la opcion correcta.',
                        [
                            german_expand_question('Frase adecuada para resumir', 'Der Text handelt von digitaler Bildung.', 'Der Text sagt digitaler Bildung.', 'Der Text handelt digitaler Bildung.'),
                            german_expand_question('Inicio natural de mini presentacion', 'Ich moechte kurz ueber dieses Thema sprechen.', 'Ich kurz moechte ueber Thema sprechen.', 'Moeglich ich ueber dieses Thema sprechen.'),
                            german_expand_question('Cierre simple correcto', 'Zum Schluss denke ich, dass gute Planung wichtig ist.', 'Zum Schluss ich denke gute Planung ist wichtig.', 'Zum Schluss denke ich gute Planung wichtig ist.'),
                        ]
                    ),
                    german_expand_matching(
                        'Empareja la tarea con la formula',
                        'Relaciona cada objetivo con la frase que mejor lo cumple.',
                        [
                            ['presentar el tema', 'Der Text handelt von ...'],
                            ['marcar una idea clave', 'Ein wichtiger Punkt ist ...'],
                            ['cerrar una mini charla', 'Zum Schluss denke ich ...'],
                            ['mencionar una fuente', 'Ich habe einen Artikel gelesen.'],
                        ]
                    ),
                    german_expand_fill(
                        'Completa el mini resumen',
                        'Escribe la palabra que falta.',
                        [
                            ['id' => 'b14_fill_1', 'oracion' => 'Der Text ____ von sozialem Stress.', 'respuesta_correcta' => 'handelt'],
                            ['id' => 'b14_fill_2', 'oracion' => 'Ein wichtiger ____ ist der Zeitdruck.', 'respuesta_correcta' => 'Punkt'],
                            ['id' => 'b14_fill_3', 'oracion' => 'Zum Schluss denke ich, ____ Planung hilft.', 'respuesta_correcta' => 'dass'],
                        ]
                    ),
                    german_expand_order(
                        'Ordena el guion de una mini charla',
                        'Reconstruye frases de apertura, desarrollo y cierre.',
                        [
                            'Ich moechte kurz ueber soziale Medien sprechen.',
                            'Ein wichtiger Punkt ist der Zeitverlust.',
                            'Zum Schluss denke ich, dass klare Grenzen helfen.',
                        ]
                    ),
                    german_expand_pronunciation(
                        'Pronuncia una mini presentacion',
                        'Lee tres frases de una charla breve con pausas claras.',
                        [
                            'Ich moechte kurz ueber digitales Lernen sprechen.',
                            'Ein wichtiger Punkt ist die Flexibilitaet.',
                            'Zum Schluss denke ich, dass gute Planung entscheidend ist.',
                        ]
                    ),
                    german_expand_writing(
                        'Escribe un resumen breve',
                        'Redacta un resumen corto de una noticia, articulo o podcast.',
                        'Escribe un resumen de 3 a 5 frases sobre una noticia o podcast que hayas escuchado o imaginado. Incluye tema y dos ideas clave.',
                        120,
                        24,
                        16
                    ),
                ]
            ),
            ['orden' => 12]
        ),
    ];
}

function german_expand_new_lessons_group_two_b(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Nivel B2.2: informes, nominalizacion y textos densos',
                'Informes breves, lenguaje abstracto, nominalizacion y lectura de textos con mayor densidad conceptual.',
                190,
                [
                    german_expand_theory(
                        'Informes breves y registro analitico',
                        19,
                        'En B2 ya no basta con opinar. Tambien hay que describir datos, tendencias y hallazgos con un registro mas analitico.',
                        [
                            german_expand_section('Lexico de informe', ['der Befund', 'die Entwicklung', 'der Anteil', 'der Rueckgang', 'der Anstieg', 'auswerten'], null, null, 'aleman'),
                            german_expand_section('Frases utiles', ['Die Daten zeigen, dass ...', 'Es laesst sich beobachten, dass ...', 'Der Anteil ist gestiegen.'], null, null, 'aleman'),
                            german_expand_section('Modelo de informe', [], null, 'Die Daten zeigen, dass der Anteil der Onlinekurse in den letzten Jahren deutlich gestiegen ist.', 'aleman'),
                        ],
                        'El tono B2 mejora cuando el verbo personal cede espacio a formulas mas analiticas.',
                        'Escenario: resumes una pequena grafica o un resultado de encuesta en una reunion.',
                        ['Nombra una tendencia.', 'Usa die Daten zeigen, dass ...', 'Cierra con una observacion.']
                    ),
                    german_expand_theory(
                        'Nominalizacion y compresion del discurso',
                        19,
                        'La nominalizacion permite compactar ideas y sonar mas academico o institucional, siempre que no destruya la claridad.',
                        [
                            german_expand_section('Patrones de compactacion', ['die Entscheidung', 'die Veraenderung', 'die Nutzung', 'die Bewertung', 'die Einfuehrung'], null, null, 'aleman'),
                            german_expand_section('Transformaciones utiles', ['Wir haben entschieden. -> die Entscheidung', 'Man fuehrt etwas ein. -> die Einfuehrung'], null, null, 'aleman'),
                            german_expand_section('Modelo condensado', [], null, 'Die Einfuehrung digitaler Formate fuehrte zu einer deutlichen Veraenderung des Lernalltags.', 'aleman'),
                        ],
                        'Nominaliza solo lo necesario. Si todo se vuelve sustantivo, el texto se ahoga.',
                        'Escenario: debes reescribir una idea simple para que suene mas formal y compacta.',
                        ['Crea una nominalizacion.', 'Inserta la nominalizacion en una frase.', 'Mantiene claridad.']
                    ),
                    german_expand_theory(
                        'Lectura densa y jerarquia de ideas',
                        18,
                        'B2 necesita soportar textos menos amables. La clave no es traducir todo, sino distinguir tesis, apoyo, matiz y conclusion.',
                        [
                            german_expand_section('Pistas de estructura', ['These', 'Beleg', 'Einschraenkung', 'Folgerung'], null, null, 'aleman'),
                            german_expand_section('Frases de lectura', ['Der Autor argumentiert, dass ...', 'Ein zentrales Argument ist ...', 'Allerdings wird auch betont, dass ...'], null, null, 'aleman'),
                            german_expand_section('Modelo de lectura', [], null, 'Der Autor argumentiert, dass technischer Fortschritt nur mit klarer Regulierung nachhaltig bleibt.', 'aleman'),
                        ],
                        'Leer mejor en B2 significa localizar funciones del texto, no entender palabra por palabra.',
                        'Escenario: te dan un parrafo denso y debes explicar que parte es tesis y que parte es matiz.',
                        ['Detecta la tesis.', 'Detecta un argumento.', 'Detecta un matiz.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Informes y lectura densa',
                        'Elige la opcion que mejor encaja en un contexto B2 analitico.',
                        'Selecciona la mejor opcion.',
                        [
                            german_expand_question('Formula analitica correcta', 'Die Daten zeigen, dass der Anteil gestiegen ist.', 'Die Daten dicen que el Anteil gestiegen ist.', 'Die Daten zeigen, der Anteil ist gestiegen dass.'),
                            german_expand_question('Nominalizacion valida', 'die Einfuehrung', 'das einfuehren', 'die einfuehrtung'),
                            german_expand_question('Formula para marcar tesis', 'Der Autor argumentiert, dass ...', 'Der Autor macht, dass ...', 'Der Autor sagt das argumentiert ...'),
                        ]
                    ),
                    german_expand_matching(
                        'Empareja funcion y formula academica',
                        'Relaciona cada objetivo del texto con la expresion adecuada.',
                        [
                            ['mostrar datos', 'Die Daten zeigen, dass ...'],
                            ['marcar tesis', 'Der Autor argumentiert, dass ...'],
                            ['introducir matiz', 'Allerdings wird auch betont, dass ...'],
                            ['nombrar una tendencia', 'Es laesst sich beobachten, dass ...'],
                        ],
                        12,
                        6
                    ),
                    german_expand_fill(
                        'Completa el mini informe',
                        'Rellena con la palabra correcta.',
                        [
                            ['id' => 'b22_fill_1', 'oracion' => 'Die Daten zeigen, ____ der Anteil gestiegen ist.', 'respuesta_correcta' => 'dass'],
                            ['id' => 'b22_fill_2', 'oracion' => 'Die ____ digitaler Formate veraenderte den Alltag.', 'respuesta_correcta' => 'Einfuehrung'],
                            ['id' => 'b22_fill_3', 'oracion' => 'Der Autor ____ , dass klare Regeln noetig sind.', 'respuesta_correcta' => 'argumentiert'],
                        ],
                        14,
                        7
                    ),
                    german_expand_drag(
                        'Lleva cada frase a su papel en el texto',
                        'Arrastra cada bloque segun su funcion argumentativa.',
                        [
                            'Digitale Bildung waechst schnell.' => 'these',
                            'Die Daten der letzten Jahre bestaetigen das.' => 'beleg',
                            'Allerdings profitieren nicht alle Gruppen gleich.' => 'matiz',
                            'Deshalb braucht man klare Zugangsstrategien.' => 'folgerung',
                        ],
                        16,
                        7
                    ),
                    german_expand_listening(
                        'Escucha: mini informe oral',
                        'Escucha un resumen analitico y escribe exactamente lo que oyes.',
                        'Die Daten zeigen, dass flexible Lernformate zunehmen, allerdings profitieren Menschen mit schwacher digitaler Ausstattung weniger davon.'
                    ),
                    german_expand_writing(
                        'Escribe un informe breve',
                        'Redacta un parrafo analitico con tesis, dato y matiz.',
                        'Escribe un informe breve sobre un cambio social o educativo. Incluye una observacion principal, un apoyo y un matiz.',
                        150,
                        26,
                        18
                    ),
                ]
            ),
            ['orden' => 14]
        ),
        array_merge(
            german_expand_lesson(
                'Nivel B2.3: tecnologia, sociedad y escucha avanzada',
                'Tecnologia, sociedad, escucha mas larga y argumentos con concesion, contraste y consecuencias.',
                195,
                [
                    german_expand_theory(
                        'Tecnologia, impacto y lenguaje de debate',
                        19,
                        'B2 necesita vocabulario para discutir digitalizacion, automatizacion, privacidad y cambios sociales sin quedarse en etiquetas simples.',
                        [
                            german_expand_section('Lexico de debate', ['die Digitalisierung', 'die Privatsphaere', 'die Abhaengigkeit', 'der Nutzen', 'die Folge', 'regulieren'], null, null, 'aleman'),
                            german_expand_section('Frases utiles', ['Digitale Werkzeuge bringen Vorteile mit sich.', 'Man darf die Risiken nicht uebersehen.', 'Die Folgen sind nicht fuer alle gleich.'], null, null, 'aleman'),
                            german_expand_section('Modelo de postura', [], null, 'Die Digitalisierung bringt viele Vorteile mit sich, man darf ihre sozialen Folgen jedoch nicht uebersehen.', 'aleman'),
                        ],
                        'Buen B2 no elimina la complejidad: reconoce ventaja y riesgo en la misma intervencion.',
                        'Escenario: participas en un debate sobre tecnologia y no quieres sonar binario.',
                        ['Nombra una ventaja.', 'Nombra un riesgo.', 'Mantiene el equilibrio.']
                    ),
                    german_expand_theory(
                        'Concesion, contraste y consecuencia',
                        18,
                        'Para debatir bien en B2 hace falta conceder algo, contrastar y luego extraer una consecuencia razonada.',
                        [
                            german_expand_section('Conectores utiles', ['zwar ... aber', 'dennoch', 'allerdings', 'folglich', 'somit'], null, null, 'aleman'),
                            german_expand_section('Frases de movimiento', ['Es gibt zwar Vorteile, aber ...', 'Dennoch sollte man beachten, dass ...', 'Folglich braucht man ...'], null, null, 'aleman'),
                            german_expand_section('Modelo con concesion', [], null, 'Es gibt zwar effiziente digitale Loesungen, dennoch sollte man ihre Grenzen klar benennen.', 'aleman'),
                        ],
                        'Conceder no debilita el argumento; lo vuelve mas creible.',
                        'Escenario: defiendes una postura mientras reconoces la fuerza parcial del otro lado.',
                        ['Haz una concesion.', 'Introduce contraste.', 'Formula una consecuencia.']
                    ),
                    german_expand_theory(
                        'Escucha avanzada y reconstruccion de sentido',
                        18,
                        'La escucha B2 ya no es solo cazar palabras. Exige sostener una idea larga y reconstruir su logica principal.',
                        [
                            german_expand_section('Pistas para escuchar mejor', ['tema central', 'ejemplo clave', 'giro de contraste', 'conclusion'], null, null, 'espanol'),
                            german_expand_section('Senales auditivas', ['einerseits', 'andererseits', 'allerdings', 'deshalb', 'am Ende'], null, null, 'aleman'),
                            german_expand_section('Modelo de escucha', [], null, 'Am Ende wird deutlich, dass technischer Fortschritt ohne soziale Begleitung zu neuen Ungleichheiten fuehren kann.', 'aleman'),
                        ],
                        'Escuchar bien en B2 significa retener la arquitectura del mensaje, no solo frases aisladas.',
                        'Escenario: oyes un comentario largo y luego debes resumirlo sin repetirlo entero.',
                        ['Detecta la idea central.', 'Detecta un giro de contraste.', 'Detecta la conclusion.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Debate sobre tecnologia y sociedad',
                        'Elige la estructura que mejor expresa matiz y contraste.',
                        'Selecciona la opcion correcta.',
                        [
                            german_expand_question('Postura equilibrada', 'Die Digitalisierung bringt Vorteile, dennoch hat sie Risiken.', 'Die Digitalisierung Vorteile bringt, dennoch sie hat Risiken.', 'Die Digitalisierung bringt Vorteile dennoch sie Risiken.'),
                            german_expand_question('Concesion correcta', 'Es gibt zwar Vorteile, aber auch neue Probleme.', 'Es gibt zwar Vorteile, dennoch aber neue Probleme.', 'Es gibt Vorteile zwar, aber auch neue Probleme.'),
                            german_expand_question('Consecuencia adecuada', 'Folglich braucht man klare Regeln.', 'Folglich man braucht klare Regeln.', 'Man braucht folglich klare Regeln braucht.'),
                        ],
                        20,
                        8
                    ),
                    german_expand_true_false(
                        'Chequeo de concesion B2',
                        'Decide si la afirmacion es verdadera o falsa.',
                        'En la estructura "zwar ... aber" el segundo bloque introduce un contraste importante.',
                        'Verdadero',
                        10,
                        4
                    ),
                    german_expand_matching(
                        'Empareja concepto y consecuencia',
                        'Relaciona cada tema con una consecuencia o matiz plausible.',
                        [
                            ['Digitalisierung', 'veraendert Arbeitsablaeufe'],
                            ['Privatsphaere', 'braucht Schutz und Regeln'],
                            ['Automatisierung', 'kann Berufe verschieben'],
                            ['Ungleichheit', 'nimmt ohne Zugangshilfen zu'],
                        ],
                        14,
                        7
                    ),
                    german_expand_drag(
                        'Lleva cada frase a la funcion correcta',
                        'Arrastra cada bloque segun su papel en el argumento.',
                        [
                            'Digitale Systeme sparen Zeit.' => 'ventaja',
                            'Nicht alle Menschen haben den gleichen Zugang.' => 'matiz',
                            'Dennoch sollte man den Ausbau nicht stoppen.' => 'postura',
                            'Folglich braucht man faire Regeln.' => 'consecuencia',
                        ],
                        16,
                        7
                    ),
                    german_expand_listening(
                        'Escucha: comentario B2 con matices',
                        'Escucha el comentario y escribe la transcripcion.',
                        'Digitale Werkzeuge sparen zwar Zeit, allerdings profitieren nicht alle Gruppen gleich davon, folglich braucht man gerechtere Zugangsmodelle.'
                    ),
                    german_expand_writing(
                        'Escribe una postura argumentada B2',
                        'Redacta una opinion matizada sobre tecnologia, trabajo o sociedad.',
                        'Escribe un texto donde presentes una ventaja, una objecion y una consecuencia razonada sobre un tema social o tecnologico.',
                        160,
                        26,
                        18
                    ),
                ]
            ),
            ['orden' => 15]
        ),
    ];
}

function german_expand_new_lessons_group_two_c(): array
{
    return [
        array_merge(
            german_expand_lesson(
                'Nivel C1.2: lectura academica, sintesis y formacion de palabras',
                'Lectura academica, sintesis fiel, distancia analitica y formacion de palabras para sostener textos y discursos de mayor precision.',
                205,
                [
                    german_expand_theory(
                        'Lectura academica y deteccion de tesis',
                        20,
                        'C1 exige una lectura menos lineal y mas estrategica. La prioridad es detectar tesis, recorrido argumental, limites y tono del autor.',
                        [
                            german_expand_section('Funciones del texto', ['These', 'Argumentationslinie', 'Gegenposition', 'Einschraenkung', 'Implikation'], null, null, 'aleman'),
                            german_expand_section('Frases de lectura', ['Der Beitrag legt nahe, dass ...', 'Zentral ist dabei die Frage, ob ...', 'Der Autor grenzt jedoch ein, dass ...'], null, null, 'aleman'),
                            german_expand_section('Modelo academico', [], null, 'Der Beitrag legt nahe, dass Innovation nur dann tragfaehig bleibt, wenn ihre sozialen Folgen mitgedacht werden.', 'aleman'),
                        ],
                        'Leer en C1 no es descodificar todo; es ver la arquitectura y el punto de vista con precision.',
                        'Escenario: recibes un texto academico y debes explicar en dos minutos que defiende realmente.',
                        ['Nombra la tesis.', 'Detecta una limitacion.', 'Detecta la implicacion central.']
                    ),
                    german_expand_theory(
                        'Sintesis fiel y posicion propia con distancia',
                        19,
                        'La sintesis fuerte conserva la voz del autor sin copiarla. Luego permite introducir una posicion propia con distancia y control.',
                        [
                            german_expand_section('Frases de sintesis', ['Zusammenfassend laesst sich sagen, dass ...', 'Der Autor betont, ...', 'Im Kern geht es darum, dass ...'], null, null, 'aleman'),
                            german_expand_section('Frases de posicion', ['Ich wuerde allerdings ergaenzen, dass ...', 'Aus meiner Sicht bleibt offen, ob ...'], null, null, 'aleman'),
                            german_expand_section('Modelo con distancia', [], null, 'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Regulierung plaediert. Aus meiner Sicht bleibt jedoch offen, wie diese umgesetzt werden soll.', 'aleman'),
                        ],
                        'Primero resume con fidelidad. Despues toma postura. Mezclar ambas cosas demasiado pronto debilita el texto.',
                        'Escenario: resumes una lectura para un seminario y luego anades tu propia observacion.',
                        ['Resume la idea central.', 'Menciona un limite.', 'Introduce tu posicion con distancia.']
                    ),
                    german_expand_theory(
                        'Formacion de palabras y precision lexical',
                        19,
                        'C1 mejora mucho cuando el alumno reconoce prefijos, sufijos y familias lexicas. Eso acelera lectura, escritura y precision.',
                        [
                            german_expand_section('Patrones utiles', ['ver- / ent- / be-', '-heit / -keit / -ung', '-los / -bar / -lich'], null, null, 'aleman'),
                            german_expand_section('Observaciones', ['verantwortlich -> Verantwortung', 'sichtbar -> Unsichtbarkeit', 'entwickeln -> Entwicklung'], null, null, 'aleman'),
                            german_expand_section('Modelo de precision', [], null, 'Die Verantwortlichkeit institutioneller Akteure bleibt in der Diskussion oft unterbelichtet.', 'aleman'),
                        ],
                        'La formacion de palabras no es decoracion: es una llave de lectura y una fuente enorme de vocabulario reutilizable.',
                        'Escenario: aparece una palabra larga nueva y debes inferir su sentido por estructura.',
                        ['Detecta un prefijo.', 'Detecta un sufijo.', 'Relaciona una palabra con su familia.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Lectura academica y sintesis C1',
                        'Elige la formula mas precisa para resumir o analizar una fuente.',
                        'Selecciona la opcion correcta.',
                        [
                            german_expand_question('Formula academica precisa', 'Der Beitrag legt nahe, dass soziale Folgen mitgedacht werden muessen.', 'Der Beitrag sagt, soziale Folgen sind dabei.', 'Der Beitrag legt, dass soziale Folgen mitdenken muessen.'),
                            german_expand_question('Sintesis con distancia', 'Zusammenfassend laesst sich sagen, dass ...', 'Zusammen macht es sagen, dass ...', 'Zusammenfassung sich sagen laesst dass ...'),
                            german_expand_question('Familia lexical correcta', 'Entwicklung', 'Entwickelungkeit', 'Entwickbarung'),
                        ],
                        20,
                        8
                    ),
                    german_expand_matching(
                        'Empareja funcion y formula C1',
                        'Relaciona cada objetivo academico con la expresion que mejor lo cumple.',
                        [
                            ['marcar tesis', 'Der Beitrag legt nahe, dass ...'],
                            ['resumir', 'Zusammenfassend laesst sich sagen, dass ...'],
                            ['introducir limite', 'Offen bleibt jedoch, ob ...'],
                            ['anadir postura propia', 'Aus meiner Sicht ...'],
                        ],
                        14,
                        7
                    ),
                    german_expand_fill(
                        'Completa la sintesis academica',
                        'Rellena con la palabra adecuada.',
                        [
                            ['id' => 'c12_fill_1', 'oracion' => 'Der Autor ____ , dass klare Regeln noetig sind.', 'respuesta_correcta' => 'betont'],
                            ['id' => 'c12_fill_2', 'oracion' => 'Zusammenfassend laesst ____ sagen, dass ...', 'respuesta_correcta' => 'sich'],
                            ['id' => 'c12_fill_3', 'oracion' => 'Die ____ der Institutionen bleibt zentral.', 'respuesta_correcta' => 'Verantwortung'],
                        ],
                        14,
                        7
                    ),
                    german_expand_drag(
                        'Lleva cada frase a la funcion academica',
                        'Arrastra cada bloque segun su papel en una sintesis.',
                        [
                            'Der Text untersucht digitale Ungleichheit.' => 'tema',
                            'Ein zentrales Argument betrifft den Zugang.' => 'argumento',
                            'Offen bleibt jedoch die praktische Umsetzung.' => 'limite',
                            'Aus meiner Sicht braucht man zusaetzlich lokale Massnahmen.' => 'postura',
                        ],
                        16,
                        8
                    ),
                    german_expand_listening(
                        'Escucha: sintesis oral academica',
                        'Escucha un resumen academico y escribe la transcripcion.',
                        'Zusammenfassend laesst sich sagen, dass der Text fuer mehr Regulierung plaediert, zugleich aber auf offene Umsetzungsfragen hinweist.'
                    ),
                    german_expand_writing(
                        'Escribe una sintesis con postura propia',
                        'Redacta una sintesis academica breve y anade una posicion personal bien marcada.',
                        'Escribe una sintesis de una fuente academica real o imaginada. Resume tesis y argumentos, luego anade una observacion critica propia.',
                        180,
                        28,
                        20
                    ),
                ]
            ),
            ['orden' => 17]
        ),
        array_merge(
            german_expand_lesson(
                'Nivel C1.3: certificaciones, presentaciones y plan maestro',
                'Simulacion de cierre C1 con presentaciones, registro alto, intervencion oral y plan de consolidacion posterior al curso.',
                210,
                [
                    german_expand_theory(
                        'Presentaciones formales y arquitectura del discurso',
                        20,
                        'El cierre C1 debe preparar al alumno para hablar ante audiencias, no solo para aprobar ejercicios. La estructura importa tanto como el vocabulario.',
                        [
                            german_expand_section('Partes del discurso', ['Einleitung', 'Fragestellung', 'Argumentation', 'Beispiel', 'Schlussfolgerung'], null, null, 'aleman'),
                            german_expand_section('Frases de exposicion', ['Im Folgenden moechte ich ...', 'Zunaechst ist festzuhalten, dass ...', 'Abschliessend laesst sich sagen ...'], null, null, 'aleman'),
                            german_expand_section('Modelo oral formal', [], null, 'Im Folgenden moechte ich auf die Frage eingehen, wie digitale Bildung gerechter gestaltet werden kann.', 'aleman'),
                        ],
                        'Una presentacion avanzada se sostiene en orden visible: el oyente debe saber siempre donde esta.',
                        'Escenario: expones un tema ante una comision o grupo exigente.',
                        ['Abre con la pregunta central.', 'Organiza la ruta del discurso.', 'Cierra con conclusion.']
                    ),
                    german_expand_theory(
                        'Registro alto, reformulacion y precision final',
                        19,
                        'Al final de la ruta importa poder reformular con precision, elevar o bajar registro y escoger expresiones menos planas.',
                        [
                            german_expand_section('Recursos de precision', ['folglich', 'demnach', 'hingegen', 'unter anderem', 'nicht zuletzt'], null, null, 'aleman'),
                            german_expand_section('Reformulacion util', ['anders formuliert', 'praeziser gesagt', 'mit Blick auf ...'], null, null, 'aleman'),
                            german_expand_section('Modelo de registro', [], null, 'Demnach reicht technischer Fortschritt allein nicht aus; vielmehr braucht es institutionelle Verantwortung.', 'aleman'),
                        ],
                        'La precision final no viene de adornar, sino de reformular mejor y elegir conectores con mas filo.',
                        'Escenario: mejoras una respuesta que suena correcta, pero todavia demasiado plana.',
                        ['Reformula una idea.', 'Eleva el registro.', 'Mantiene claridad.']
                    ),
                    german_expand_theory(
                        'Plan maestro de consolidacion despues del curso',
                        18,
                        'Un curso completo no termina en la ultima leccion. Debe dejar una estrategia para mantener escucha, lectura, produccion y correccion en el tiempo.',
                        [
                            german_expand_section('Pilares de consolidacion', ['escucha extensa', 'lectura guiada', 'escritura corta frecuente', 'repeticion guiada y expresion oral', 'revision de errores'], null, null, 'espanol'),
                            german_expand_section('Formulas de plan', ['dreimal pro Woche', 'jede Woche ein kurzer Text', 'monatlich eine Praesentation', 'Fehlerliste aktualisieren'], null, null, 'aleman'),
                            german_expand_section('Modelo de cierre', [], null, 'Ich werde jede Woche einen kurzen Kommentar schreiben und einmal im Monat eine Mini-Praesentation aufnehmen.', 'aleman'),
                        ],
                        'Sin plan de consolidacion, incluso un curso fuerte se diluye rapido.',
                        'Escenario: terminas la ruta y necesitas un sistema realista para no perder el nivel.',
                        ['Define una rutina semanal.', 'Define una meta mensual.', 'Define una forma de correccion.']
                    ),
                ],
                [
                    german_expand_mcq(
                        'Presentacion, registro y cierre C1',
                        'Elige la formula mas precisa para una exposicion o conclusion formal.',
                        'Selecciona la opcion correcta.',
                        [
                            german_expand_question('Inicio formal fuerte', 'Im Folgenden moechte ich auf diese Frage eingehen.', 'Im Folgend ich moechte auf diese Frage eingehen.', 'Ich moechte in folgend auf diese Frage.'),
                            german_expand_question('Conector de conclusion preciso', 'Abschliessend laesst sich sagen, dass ...', 'Abschluss laesst sich sagen, dass ...', 'Am Schluss sagen sich laesst, dass ...'),
                            german_expand_question('Registro alto correcto', 'Demnach braucht es institutionelle Verantwortung.', 'Demnach es braucht institutionelle Verantwortung.', 'Demnach braucht institutionelle Verantwortung es.'),
                        ],
                        20,
                        8
                    ),
                    german_expand_matching(
                        'Empareja momento y formula de discurso',
                        'Relaciona cada tramo de una presentacion con la formula mas adecuada.',
                        [
                            ['abrir tema', 'Im Folgenden moechte ich ...'],
                            ['marcar primer punto', 'Zunaechst ist festzuhalten, dass ...'],
                            ['reformular', 'Praeziser gesagt ...'],
                            ['cerrar', 'Abschliessend laesst sich sagen, dass ...'],
                        ],
                        14,
                        7
                    ),
                    german_expand_fill(
                        'Completa la conclusion formal',
                        'Rellena con la palabra correcta.',
                        [
                            ['id' => 'c13_fill_1', 'oracion' => 'Im Folgenden moechte ich auf diese ____ eingehen.', 'respuesta_correcta' => 'Frage'],
                            ['id' => 'c13_fill_2', 'oracion' => '____ gesagt braucht man klare Regeln.', 'respuesta_correcta' => 'Praeziser'],
                            ['id' => 'c13_fill_3', 'oracion' => 'Abschliessend laesst sich ____, dass ...', 'respuesta_correcta' => 'sagen'],
                        ],
                        14,
                        7
                    ),
                    german_expand_pronunciation(
                        'Pronuncia un cierre formal C1',
                        'Lee frases de exposicion con pausas y articulacion estables.',
                        [
                            'Im Folgenden moechte ich auf drei zentrale Punkte eingehen.',
                            'Praeziser gesagt braucht es nicht nur Technik, sondern auch Verantwortung.',
                            'Abschliessend laesst sich sagen, dass nachhaltige Bildung Planung und Zugang vereinen muss.',
                        ],
                        18,
                        9
                    ),
                    german_expand_listening(
                        'Escucha: conclusion de presentacion',
                        'Escucha la conclusion y escribe exactamente lo que oyes.',
                        'Abschliessend laesst sich sagen, dass nachhaltige Innovation nur dann gelingt, wenn technischer Fortschritt und soziale Verantwortung zusammen gedacht werden.'
                    ),
                    german_expand_writing(
                        'Escribe tu plan maestro post-curso',
                        'Redacta un plan realista de mantenimiento del aleman despues de terminar la ruta.',
                        'Escribe un plan de consolidacion para los proximos tres meses. Incluye escucha, lectura, escritura, expresion oral y una forma de revision de errores.',
                        190,
                        28,
                        20
                    ),
                ]
            ),
            ['orden' => 18]
        ),
    ];
}
