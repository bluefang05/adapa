<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=adapa;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$lessonTitle = 'Lesson Demo: Supported Activities Lab';
$lessonDescription = 'Leccion de prueba para QA funcional con todos los tipos de actividad actualmente soportados en la experiencia principal del estudiante.';

$theoryIntro = 'Esta leccion demo existe para verificar que los tipos de actividad soportados funcionan de extremo a extremo sin depender de features inmaduras.';
$theoryHtml = '<div class="theory-rich">'
    . '<p>' . htmlspecialchars($theoryIntro, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<h3>Objetivo</h3><ul>'
    . '<li>Probar interacciones reales del alumno</li>'
    . '<li>Validar correccion automatica cuando existe</li>'
    . '<li>Detectar rapidamente regresiones de UI o de scoring</li>'
    . '</ul>'
    . '<h3>Alcance</h3><ul>'
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
    . '<div class="alert alert-light border mt-3"><strong>Coach tip:</strong> Esta leccion no busca enseñar un tema completo; busca confirmar que la plataforma responde bien.</div>'
    . '</div>';

$blocks = [
    ['tipo_bloque' => 'explicacion', 'titulo' => 'Panorama', 'contenido' => $theoryIntro, 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
    ['tipo_bloque' => 'explicacion', 'titulo' => 'Objetivo', 'contenido' => "- Probar interacciones reales del alumno\n- Validar correccion automatica cuando existe\n- Detectar rapidamente regresiones de UI o de scoring", 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
    ['tipo_bloque' => 'explicacion', 'titulo' => 'Alcance', 'contenido' => "- Opcion multiple\n- Verdadero o falso\n- Respuesta corta\n- Completar oracion\n- Emparejamiento\n- Arrastrar y soltar\n- Ordenar palabras\n- Escritura\n- Escucha\n- Pronunciacion", 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
    ['tipo_bloque' => 'instruccion', 'titulo' => 'Coach tip', 'contenido' => 'Esta leccion no busca enseñar un tema completo; busca confirmar que la plataforma responde bien.', 'idioma_bloque' => 'espanol', 'tts_habilitado' => 1],
];

$activities = [
    [
        'titulo' => 'Demo / Multiple choice',
        'descripcion' => 'Verifica seleccion simple con varias preguntas.',
        'tipo' => 'opcion_multiple',
        'instrucciones' => 'Selecciona la mejor respuesta en cada caso.',
        'puntos' => 15,
        'tiempo' => 5,
        'contenido' => [
            'pregunta_global' => 'Choose the best answer.',
            'preguntas' => [
                ['texto' => 'Which sentence is correct?', 'opciones' => [['texto' => 'She is a teacher.', 'es_correcta' => true], ['texto' => 'She are a teacher.', 'es_correcta' => false], ['texto' => 'She am a teacher.', 'es_correcta' => false]]],
                ['texto' => 'Which word is a color?', 'opciones' => [['texto' => 'blue', 'es_correcta' => true], ['texto' => 'chair', 'es_correcta' => false], ['texto' => 'seven', 'es_correcta' => false]]],
            ],
        ],
    ],
    [
        'titulo' => 'Demo / True or false',
        'descripcion' => 'Verifica verdadero o falso con correccion automatica.',
        'tipo' => 'verdadero_falso',
        'instrucciones' => 'Elige verdadero o falso.',
        'puntos' => 10,
        'tiempo' => 3,
        'contenido' => [
            'pregunta' => 'The sentence "They are happy" is grammatically correct.',
            'respuesta_correcta' => 'Verdadero',
        ],
    ],
    [
        'titulo' => 'Demo / Short answer',
        'descripcion' => 'Verifica respuesta corta con una o varias soluciones validas.',
        'tipo' => 'respuesta_corta',
        'instrucciones' => 'Escribe solo una palabra.',
        'puntos' => 10,
        'tiempo' => 3,
        'contenido' => [
            'pregunta' => 'Complete: I ____ a student.',
            'respuesta_correcta' => 'am',
            'respuestas_correctas' => ['am'],
            'placeholder' => 'Type one word',
        ],
    ],
    [
        'titulo' => 'Demo / Complete the sentence',
        'descripcion' => 'Verifica varios huecos con scoring parcial.',
        'tipo' => 'completar_oracion',
        'instrucciones' => 'Escribe la palabra correcta en cada espacio.',
        'puntos' => 15,
        'tiempo' => 5,
        'contenido' => [
            ['id' => 'gap_1', 'oracion' => 'She ____ in Lima.', 'respuesta_correcta' => 'lives'],
            ['id' => 'gap_2', 'oracion' => 'We ____ coffee every morning.', 'respuesta_correcta' => 'drink'],
            ['id' => 'gap_3', 'oracion' => 'They ____ ready now.', 'respuesta_correcta' => 'are'],
        ],
    ],
    [
        'titulo' => 'Demo / Matching',
        'descripcion' => 'Verifica emparejamiento simple izquierda-derecha.',
        'tipo' => 'emparejamiento',
        'instrucciones' => 'Empareja cada elemento con su pareja correcta.',
        'puntos' => 12,
        'tiempo' => 5,
        'contenido' => [
            'pares' => [
                ['left' => 'Monday', 'right' => 'day of the week'],
                ['left' => 'Water', 'right' => 'drink'],
                ['left' => 'Teacher', 'right' => 'profession'],
            ],
        ],
    ],
    [
        'titulo' => 'Demo / Drag and drop',
        'descripcion' => 'Verifica asignacion de elementos a categorias.',
        'tipo' => 'arrastrar_soltar',
        'instrucciones' => 'Relaciona cada item con la categoria correcta.',
        'puntos' => 12,
        'tiempo' => 6,
        'contenido' => [
            'pairs' => [
                ['id' => 'item_1', 'left' => 'apple', 'right' => 'fruit'],
                ['id' => 'item_2', 'left' => 'carrot', 'right' => 'vegetable'],
                ['id' => 'item_3', 'left' => 'milk', 'right' => 'drink'],
            ],
        ],
    ],
    [
        'titulo' => 'Demo / Order the words',
        'descripcion' => 'Verifica reconstruccion de oraciones.',
        'tipo' => 'ordenar_palabras',
        'instrucciones' => 'Ordena las palabras para formar la oracion correcta.',
        'puntos' => 10,
        'tiempo' => 4,
        'contenido' => [
            ['id' => 'order_1', 'instruction' => 'Order the sentence.', 'items' => ['She', 'is', 'my', 'friend.']],
            ['id' => 'order_2', 'instruction' => 'Order the sentence.', 'items' => ['We', 'study', 'English', 'today.']],
        ],
    ],
    [
        'titulo' => 'Demo / Writing',
        'descripcion' => 'Verifica escritura abierta para revision manual.',
        'tipo' => 'escritura',
        'instrucciones' => 'Escribe 80 a 100 palabras.',
        'puntos' => 20,
        'tiempo' => 12,
        'contenido' => [
            'tema' => 'Write a short paragraph introducing yourself, your routine and one goal.',
            'min_palabras' => 80,
        ],
    ],
    [
        'titulo' => 'Demo / Listening',
        'descripcion' => 'Verifica TTS y transcripcion basica.',
        'tipo' => 'escucha',
        'instrucciones' => 'Escucha el audio y escribe la frase completa.',
        'puntos' => 15,
        'tiempo' => 6,
        'contenido' => [
            'texto_tts' => 'Hello, my name is Paula and I study English every afternoon.',
            'transcripcion' => 'Hello, my name is Paula and I study English every afternoon.',
        ],
    ],
    [
        'titulo' => 'Demo / Pronunciation',
        'descripcion' => 'Verifica reconocimiento de voz en frases simples.',
        'tipo' => 'pronunciacion',
        'instrucciones' => 'Presiona el microfono y lee cada frase.',
        'puntos' => 15,
        'tiempo' => 6,
        'contenido' => [
            ['id' => 'pron_1', 'frase' => 'I am ready to learn English.'],
            ['id' => 'pron_2', 'frase' => 'We study every morning.'],
        ],
    ],
];

$findCourse = $pdo->prepare("
    SELECT id
    FROM cursos
    WHERE idioma_objetivo = 'ingles'
    ORDER BY id ASC
    LIMIT 1
");
$findCourse->execute();
$course = $findCourse->fetch();

if (!$course) {
    throw new RuntimeException('No se encontro un curso de ingles para sincronizar la leccion demo.');
}

$courseId = (int) $course['id'];

$selectLesson = $pdo->prepare('SELECT id FROM lecciones WHERE curso_id = ? AND titulo = ? LIMIT 1');
$insertLesson = $pdo->prepare('INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (?, ?, ?, ?, ?, 1, "publicada")');
$updateLesson = $pdo->prepare('UPDATE lecciones SET descripcion = ?, duracion_minutos = ?, estado = "publicada" WHERE id = ?');
$maxOrderStmt = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) AS max_orden FROM lecciones WHERE curso_id = ?');

$selectTheory = $pdo->prepare('SELECT id FROM teoria WHERE leccion_id = ? AND orden = 1 LIMIT 1');
$insertTheory = $pdo->prepare('INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo) VALUES (?, ?, ?, "texto", ?, 1, 0)');
$updateTheory = $pdo->prepare('UPDATE teoria SET titulo = ?, contenido = ?, duracion_minutos = ? WHERE id = ?');
$deleteTheoryBlocks = $pdo->prepare('DELETE FROM contenido_bloques WHERE teoria_id = ?');
$insertBlock = $pdo->prepare('INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)');

$deleteActivities = $pdo->prepare('DELETE FROM actividades WHERE leccion_id = ?');
$insertActivity = $pdo->prepare('INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")');

$pdo->beginTransaction();

$selectLesson->execute([$courseId, $lessonTitle]);
$existingLesson = $selectLesson->fetch();

if ($existingLesson) {
    $lessonId = (int) $existingLesson['id'];
    $updateLesson->execute([$lessonDescription, 95, $lessonId]);
} else {
    $maxOrderStmt->execute([$courseId]);
    $maxOrder = (int) ($maxOrderStmt->fetch()['max_orden'] ?? 0);
    $insertLesson->execute([$courseId, $lessonTitle, $lessonDescription, $maxOrder + 1, 95]);
    $lessonId = (int) $pdo->lastInsertId();
}

$selectTheory->execute([$lessonId]);
$existingTheory = $selectTheory->fetch();

if ($existingTheory) {
    $theoryId = (int) $existingTheory['id'];
    $updateTheory->execute(['Demo lab overview', $theoryHtml, 10, $theoryId]);
} else {
    $insertTheory->execute([$lessonId, 'Demo lab overview', $theoryHtml, 10]);
    $theoryId = (int) $pdo->lastInsertId();
}

$deleteTheoryBlocks->execute([$theoryId]);
foreach ($blocks as $index => $block) {
    $insertBlock->execute([
        $theoryId,
        $block['tipo_bloque'],
        $block['titulo'],
        $block['contenido'],
        $block['idioma_bloque'],
        $block['tts_habilitado'],
        $index + 1,
    ]);
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

$pdo->commit();

echo json_encode([
    'course_id' => $courseId,
    'lesson_id' => $lessonId,
    'lesson_title' => $lessonTitle,
    'activity_count' => count($activities),
    'activity_types' => array_map(static fn($item) => $item['tipo'], $activities),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
