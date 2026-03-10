<?php
// Mock HTTP_HOST for CLI execution to force local environment
$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

// Disable output buffering
if (ob_get_level()) ob_end_clean();
header('Content-Type: text/plain; charset=utf-8');

$db = new Database();

// 1. Find German Course
echo "🔍 Buscando curso de Alemán...\n";
$db->query("SELECT * FROM cursos WHERE titulo LIKE '%Aleman%' OR titulo LIKE '%Alemán%' LIMIT 1");
$curso = $db->single();

if (!$curso) {
    die("❌ No se encontró ningún curso de Alemán.\n");
}

echo "✅ Curso encontrado: {$curso->titulo} (ID: {$curso->id})\n";
echo "   Nivel: {$curso->nivel_cefr_desde}-{$curso->nivel_cefr_hasta}\n";
echo "   Descripción: {$curso->descripcion}\n\n";

// 2. Get Lessons
echo "📚 Analizando Lecciones...\n";
$db->query("SELECT * FROM lecciones WHERE curso_id = :curso_id ORDER BY orden ASC");
$db->bind(':curso_id', $curso->id);
$lecciones = $db->resultSet();

$structure = [
    'course' => [
        'title' => $curso->titulo,
        'description' => $curso->descripcion,
        'level' => $curso->nivel_cefr_desde . '-' . $curso->nivel_cefr_hasta,
        'lessons' => []
    ]
];

foreach ($lecciones as $leccion) {
    echo "   📖 Lección {$leccion->orden}: {$leccion->titulo}\n";
    
    $lessonData = [
        'title' => $leccion->titulo,
        'description' => $leccion->descripcion,
        'duration' => $leccion->duracion_minutos,
        'content' => []
    ];

    // Get Theory
    $db->query("SELECT * FROM teoria WHERE leccion_id = :leccion_id ORDER BY orden ASC");
    $db->bind(':leccion_id', $leccion->id);
    $teorias = $db->resultSet();

    foreach ($teorias as $teoria) {
        $lessonData['content'][] = [
            'type' => 'theory',
            'title' => $teoria->titulo,
            'content_preview' => substr(strip_tags($teoria->contenido), 0, 100) . '...',
            'order' => $teoria->orden
        ];
    }

    // Get Activities
    $db->query("SELECT * FROM actividades WHERE leccion_id = :leccion_id ORDER BY orden ASC");
    $db->bind(':leccion_id', $leccion->id);
    $actividades = $db->resultSet();

    foreach ($actividades as $actividad) {
        $actData = [
            'type' => 'activity',
            'activity_type' => $actividad->tipo_actividad,
            'title' => $actividad->titulo,
            'instructions' => $actividad->instrucciones,
            'content_json' => $actividad->contenido, // Often JSON
            'order' => $actividad->orden,
            'options' => []
        ];

        // Get Options if applicable
        $db->query("SELECT * FROM opciones_multiples WHERE actividad_id = :actividad_id ORDER BY id ASC");
        $db->bind(':actividad_id', $actividad->id);
        $opciones = $db->resultSet();
        
        foreach ($opciones as $opcion) {
            $actData['options'][] = [
                'text' => $opcion->texto,
                'is_correct' => $opcion->es_correcta
            ];
        }

        $lessonData['content'][] = $actData;
    }

    // Sort content by order (merging theory and activities)
    usort($lessonData['content'], function($a, $b) {
        return $a['order'] <=> $b['order'];
    });

    $structure['course']['lessons'][] = $lessonData;
}

echo "\n✨ Análisis completado. Estructura generada en JSON para el prompt.\n";
file_put_contents(__DIR__ . '/german_course_structure.json', json_encode($structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ JSON guardado en " . __DIR__ . "/german_course_structure.json\n";
