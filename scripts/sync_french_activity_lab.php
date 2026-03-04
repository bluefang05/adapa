<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=adapa;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$course = [
    'instancia_id' => 1,
    'creado_por' => 13,
    'titulo' => 'French Activity Lab: Demo de Actividades',
    'descripcion' => 'Curso de prueba en frances para validar todos los tipos de actividad soportados actualmente por ADAPA. No es un learning path; es un laboratorio funcional para QA y demostracion.',
    'idioma' => 'frances',
    'idioma_objetivo' => 'frances',
    'idioma_ensenanza' => 'espanol',
    'nivel_cefr' => 'A1',
    'nivel_cefr_desde' => 'A1',
    'nivel_cefr_hasta' => 'A1',
    'modalidad' => 'perpetuo',
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin' => null,
    'duracion_semanas' => 1,
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

$lessonTitle = 'Lecon Demo: Activites Supportees';
$lessonDescription = 'Leccion unica para probar todas las actividades posibles hoy en la experiencia principal del estudiante, usando contenido sencillo en frances.';
$theoryTitle = 'Vue d ensemble du labo';
$theoryIntro = 'Esta leccion existe para comprobar que las actividades soportadas hoy funcionan bien tambien con contenido de frances, sin prometer features inmaduras.';
$theoryHtml = '<div class="theory-rich">'
    . '<p>' . htmlspecialchars($theoryIntro, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<h3>Objetivo</h3><ul>'
    . '<li>Validar la UX de actividades en un tercer idioma</li>'
    . '<li>Comprobar correccion automatica y flujos manuales</li>'
    . '<li>Tener un curso de pruebas rapido para QA</li>'
    . '</ul>'
    . '<h3>Tipos cubiertos</h3><ul>'
    . '<li>Opcion multiple</li>'
    . '<li>Verdadero o falso</li>'
    . '<li>Respuesta corta</li>'
    . '<li>Completar oracion</li>'
    . '<li>Emparejamiento</li>'
    . '<li>Arrastrar y soltar</li>'
    . '<li>Ordenar palabras</li>'
    . '<li>Escritura</li>'
    . '<li>Escucha</li>'
    . '<li>Pronunciacion</li>'
    . '</ul>'
    . '<div class="alert alert-light border mt-3"><strong>Coach tip:</strong> Este curso no busca cubrir frances A1 completo; busca comprobar que la plataforma responde bien.</div>'
    . '</div>';

$blocks = [
    ['tipo_bloque' => 'explicacion', 'titulo' => 'Panorama', 'contenido' => $theoryIntro, 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
    ['tipo_bloque' => 'explicacion', 'titulo' => 'Objetivo', 'contenido' => "- Validar la UX de actividades en un tercer idioma\n- Comprobar correccion automatica y flujos manuales\n- Tener un curso de pruebas rapido para QA", 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
    ['tipo_bloque' => 'explicacion', 'titulo' => 'Tipos cubiertos', 'contenido' => "- Opcion multiple\n- Verdadero o falso\n- Respuesta corta\n- Completar oracion\n- Emparejamiento\n- Arrastrar y soltar\n- Ordenar palabras\n- Escritura\n- Escucha\n- Pronunciacion", 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
    ['tipo_bloque' => 'instruccion', 'titulo' => 'Coach tip', 'contenido' => 'Este curso no busca cubrir frances A1 completo; busca comprobar que la plataforma responde bien.', 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
];

$activities = [
    [
        'titulo' => 'Francais / Choix multiple',
        'descripcion' => 'Verifica seleccion multiple con frases simples en frances.',
        'tipo' => 'opcion_multiple',
        'instrucciones' => 'Selecciona la mejor respuesta en cada caso.',
        'puntos' => 15,
        'tiempo' => 5,
        'contenido' => [
            'pregunta_global' => 'Choisissez la bonne reponse.',
            'preguntas' => [
                ['texto' => 'Quelle phrase est correcte ?', 'opciones' => [['texto' => 'Je suis etudiant.', 'es_correcta' => true], ['texto' => 'Je es etudiant.', 'es_correcta' => false], ['texto' => 'Je etre etudiant.', 'es_correcta' => false]]],
                ['texto' => 'Quel mot est une couleur ?', 'opciones' => [['texto' => 'bleu', 'es_correcta' => true], ['texto' => 'chaise', 'es_correcta' => false], ['texto' => 'neuf', 'es_correcta' => false]]],
            ],
        ],
    ],
    [
        'titulo' => 'Francais / Vrai ou faux',
        'descripcion' => 'Verifica verdadero o falso con una frase basica.',
        'tipo' => 'verdadero_falso',
        'instrucciones' => 'Elige verdadero o falso.',
        'puntos' => 10,
        'tiempo' => 3,
        'contenido' => [
            'pregunta' => 'La phrase "Ils sont contents" est correcte.',
            'respuesta_correcta' => 'Verdadero',
        ],
    ],
    [
        'titulo' => 'Francais / Reponse courte',
        'descripcion' => 'Verifica respuesta corta con una sola palabra.',
        'tipo' => 'respuesta_corta',
        'instrucciones' => 'Escribe solo una palabra.',
        'puntos' => 10,
        'tiempo' => 3,
        'contenido' => [
            'pregunta' => 'Complete : Je ____ a Paris.',
            'respuesta_correcta' => 'vis',
            'respuestas_correctas' => ['vis'],
            'placeholder' => 'Escribe una palabra',
        ],
    ],
    [
        'titulo' => 'Francais / Completer la phrase',
        'descripcion' => 'Completa varios huecos simples en frances.',
        'tipo' => 'completar_oracion',
        'instrucciones' => 'Escribe la palabra correcta en cada espacio.',
        'puntos' => 15,
        'tiempo' => 5,
        'contenido' => [
            ['id' => 'fr_gap_1', 'oracion' => 'Elle ____ a Lyon.', 'respuesta_correcta' => 'habite'],
            ['id' => 'fr_gap_2', 'oracion' => 'Nous ____ le cafe le matin.', 'respuesta_correcta' => 'buvons'],
            ['id' => 'fr_gap_3', 'oracion' => 'Ils ____ prets.', 'respuesta_correcta' => 'sont'],
        ],
    ],
    [
        'titulo' => 'Francais / Association',
        'descripcion' => 'Empareja palabras francesas con su categoria.',
        'tipo' => 'emparejamiento',
        'instrucciones' => 'Empareja cada elemento con su pareja correcta.',
        'puntos' => 12,
        'tiempo' => 5,
        'contenido' => [
            'pares' => [
                ['left' => 'lundi', 'right' => 'jour de la semaine'],
                ['left' => 'eau', 'right' => 'boisson'],
                ['left' => 'professeur', 'right' => 'profession'],
            ],
        ],
    ],
    [
        'titulo' => 'Francais / Glisser et deposer',
        'descripcion' => 'Relaciona items con categorias simples.',
        'tipo' => 'arrastrar_soltar',
        'instrucciones' => 'Relaciona cada item con la categoria correcta.',
        'puntos' => 12,
        'tiempo' => 6,
        'contenido' => [
            'pairs' => [
                ['id' => 'fr_item_1', 'left' => 'pomme', 'right' => 'fruit'],
                ['id' => 'fr_item_2', 'left' => 'carotte', 'right' => 'legume'],
                ['id' => 'fr_item_3', 'left' => 'lait', 'right' => 'boisson'],
            ],
        ],
    ],
    [
        'titulo' => 'Francais / Ordonner les mots',
        'descripcion' => 'Reconstruye oraciones cortas en frances.',
        'tipo' => 'ordenar_palabras',
        'instrucciones' => 'Ordena las palabras para formar la oracion correcta.',
        'puntos' => 10,
        'tiempo' => 4,
        'contenido' => [
            ['id' => 'fr_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Je', 'suis', 'ton', 'ami.']],
            ['id' => 'fr_order_2', 'instruction' => 'Ordena la frase.', 'items' => ['Nous', 'etudions', 'le', 'francais', 'aujourdhui.']],
        ],
    ],
    [
        'titulo' => 'Francais / Ecriture',
        'descripcion' => 'Produccion escrita abierta para revision manual.',
        'tipo' => 'escritura',
        'instrucciones' => 'Escribe 80 a 100 palabras.',
        'puntos' => 20,
        'tiempo' => 12,
        'contenido' => [
            'tema' => 'Presente-toi, decris ta routine et mentionne un objectif en francais.',
            'min_palabras' => 80,
        ],
    ],
    [
        'titulo' => 'Francais / Ecoute',
        'descripcion' => 'Verifica escucha y transcripcion basica.',
        'tipo' => 'escucha',
        'instrucciones' => 'Escucha el audio y escribe la frase completa.',
        'puntos' => 15,
        'tiempo' => 6,
        'contenido' => [
            'texto_tts' => 'Bonjour, je m appelle Claire et j etudie le francais chaque apres midi.',
            'transcripcion' => 'Bonjour, je m appelle Claire et j etudie le francais chaque apres midi.',
        ],
    ],
    [
        'titulo' => 'Francais / Prononciation',
        'descripcion' => 'Verifica reconocimiento de voz en frases simples.',
        'tipo' => 'pronunciacion',
        'instrucciones' => 'Presiona el microfono y lee cada frase.',
        'puntos' => 15,
        'tiempo' => 6,
        'contenido' => [
            ['id' => 'fr_pron_1', 'frase' => 'Je suis pret a apprendre le francais.'],
            ['id' => 'fr_pron_2', 'frase' => 'Nous etudions chaque matin.'],
        ],
    ],
];

$findCourse = $pdo->prepare('SELECT id FROM cursos WHERE titulo = ? LIMIT 1');
$insertCourse = $pdo->prepare('INSERT INTO cursos (instancia_id, creado_por, titulo, descripcion, idioma, idioma_objetivo, idioma_ensenanza, nivel_cefr, nivel_cefr_desde, nivel_cefr_hasta, modalidad, fecha_inicio, fecha_fin, duracion_semanas, es_publico, requiere_codigo, codigo_acceso, tipo_codigo, inscripcion_abierta, fecha_cierre_inscripcion, max_estudiantes, estado, notificar_profesor_completada, notificar_profesor_atascado) VALUES (:instancia_id, :creado_por, :titulo, :descripcion, :idioma, :idioma_objetivo, :idioma_ensenanza, :nivel_cefr, :nivel_cefr_desde, :nivel_cefr_hasta, :modalidad, :fecha_inicio, :fecha_fin, :duracion_semanas, :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :inscripcion_abierta, :fecha_cierre_inscripcion, :max_estudiantes, :estado, :notificar_profesor_completada, :notificar_profesor_atascado)');
$updateCourse = $pdo->prepare('UPDATE cursos SET descripcion = :descripcion, idioma = :idioma, idioma_objetivo = :idioma_objetivo, idioma_ensenanza = :idioma_ensenanza, nivel_cefr = :nivel_cefr, nivel_cefr_desde = :nivel_cefr_desde, nivel_cefr_hasta = :nivel_cefr_hasta, modalidad = :modalidad, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, duracion_semanas = :duracion_semanas, es_publico = :es_publico, requiere_codigo = :requiere_codigo, codigo_acceso = :codigo_acceso, tipo_codigo = :tipo_codigo, inscripcion_abierta = :inscripcion_abierta, fecha_cierre_inscripcion = :fecha_cierre_inscripcion, max_estudiantes = :max_estudiantes, estado = :estado, notificar_profesor_completada = :notificar_profesor_completada, notificar_profesor_atascado = :notificar_profesor_atascado WHERE id = :id');
$selectLesson = $pdo->prepare('SELECT id FROM lecciones WHERE curso_id = ? AND titulo = ? LIMIT 1');
$insertLesson = $pdo->prepare('INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (?, ?, ?, ?, ?, 1, "publicada")');
$updateLesson = $pdo->prepare('UPDATE lecciones SET descripcion = ?, duracion_minutos = ?, estado = "publicada" WHERE id = ?');
$selectTheory = $pdo->prepare('SELECT id FROM teoria WHERE leccion_id = ? AND orden = 1 LIMIT 1');
$insertTheory = $pdo->prepare('INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo) VALUES (?, ?, ?, "texto", ?, 1, 0)');
$updateTheory = $pdo->prepare('UPDATE teoria SET titulo = ?, contenido = ?, duracion_minutos = ? WHERE id = ?');
$deleteTheoryBlocks = $pdo->prepare('DELETE FROM contenido_bloques WHERE teoria_id = ?');
$insertBlock = $pdo->prepare('INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)');
$deleteActivities = $pdo->prepare('DELETE FROM actividades WHERE leccion_id = ?');
$insertActivity = $pdo->prepare('INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")');
$selectEnrollment = $pdo->prepare('SELECT 1 FROM inscripciones WHERE curso_id = ? AND estudiante_id = ? LIMIT 1');
$insertEnrollment = $pdo->prepare('INSERT INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');

$pdo->beginTransaction();

$findCourse->execute([$course['titulo']]);
$existingCourse = $findCourse->fetch();
if ($existingCourse) {
    $courseData = $course;
    $courseData['id'] = (int) $existingCourse['id'];
    $courseId = $courseData['id'];
    $updateCourse->execute($courseData);
} else {
    $insertCourse->execute($course);
    $courseId = (int) $pdo->lastInsertId();
}

$selectLesson->execute([$courseId, $lessonTitle]);
$existingLesson = $selectLesson->fetch();
if ($existingLesson) {
    $lessonId = (int) $existingLesson['id'];
    $updateLesson->execute([$lessonDescription, 95, $lessonId]);
} else {
    $insertLesson->execute([$courseId, $lessonTitle, $lessonDescription, 1, 95]);
    $lessonId = (int) $pdo->lastInsertId();
}

$selectTheory->execute([$lessonId]);
$existingTheory = $selectTheory->fetch();
if ($existingTheory) {
    $theoryId = (int) $existingTheory['id'];
    $updateTheory->execute([$theoryTitle, $theoryHtml, 10, $theoryId]);
} else {
    $insertTheory->execute([$lessonId, $theoryTitle, $theoryHtml, 10]);
    $theoryId = (int) $pdo->lastInsertId();
}

$deleteTheoryBlocks->execute([$theoryId]);
foreach ($blocks as $index => $block) {
    $insertBlock->execute([$theoryId, $block['tipo_bloque'], $block['titulo'], $block['contenido'], $block['idioma_bloque'], $block['tts_habilitado'], $index + 1]);
}

$deleteActivities->execute([$lessonId]);
foreach ($activities as $index => $activity) {
    $insertActivity->execute([
        $lessonId,
        $activity['titulo'],
        $activity['descripcion'],
        $activity['tipo'],
        $activity['instrucciones'],
        json_encode($activity['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $activity['puntos'],
        $activity['tiempo'],
        $index + 1,
    ]);
}

$selectEnrollment->execute([$courseId, 14]);
if (!$selectEnrollment->fetchColumn()) {
    $insertEnrollment->execute([$courseId, 14]);
}

$pdo->commit();

echo json_encode([
    'course_id' => $courseId,
    'title' => $course['titulo'],
    'lesson_id' => $lessonId,
    'lesson_title' => $lessonTitle,
    'activity_count' => count($activities),
    'activity_types' => array_values(array_map(static fn($activity) => $activity['tipo'], $activities)),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
