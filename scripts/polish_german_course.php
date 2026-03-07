<?php

require_once __DIR__ . '/../config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    DB_HOST,
    defined('DB_PORT') ? DB_PORT : 3306,
    DB_NAME,
    defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
);

$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$courseUpdate = [
    'titulo' => 'Aleman de Cero a Heroe: Ruta completa A1-C1',
    'descripcion' => 'Ruta completa de aleman para hispanohablantes desde supervivencia A1 hasta precision C1. Combina pronunciacion, gramatica funcional, situaciones reales, opinion, escritura, escucha y bloques de practica con escenarios memorables para fijar mejor el idioma.',
    'id' => 17,
];

$lessonUpdates = [
    45 => [
        'titulo' => 'Nivel A1: sonidos, presentaciones y primer contacto',
        'descripcion' => 'Arranque con pronunciacion esencial, saludo, presentacion personal y las primeras estructuras que evitan que el aleman se sienta hostil desde el dia uno.',
    ],
    48 => [
        'titulo' => 'Nivel A1+: compras, ciudad y verbos de accion',
        'descripcion' => 'Modulo puente entre A1 y A2 para pedir cosas, moverte por la ciudad, usar modalverben y ganar autonomia practica sin salto brusco.',
    ],
    46 => [
        'titulo' => 'Nivel A2: pasado, dativo y vida cotidiana',
        'descripcion' => 'Perfekt, dativo, vivienda, salud y comparativos para contar lo que paso, resolver necesidades reales y describir mejor tu entorno.',
    ],
    47 => [
        'titulo' => 'Nivel B1: opinion, subordinadas y mundo real',
        'descripcion' => 'Conecta ideas, justifica opiniones, escribe mensajes formales y habla de trabajo, estudio y medios sin quedarte en frases sueltas.',
    ],
    49 => [
        'titulo' => 'Nivel B2: debate, matices y lenguaje abstracto',
        'descripcion' => 'Hipotesis, pasiva avanzada, nominalizacion y argumentacion en temas academicos, sociales y culturales con mas densidad expresiva.',
    ],
    50 => [
        'titulo' => 'Nivel C1: precision, registro y ruta de consolidacion',
        'descripcion' => 'Registro, discurso referido, lectura academica, formacion de palabras y estrategia de consolidacion para sostener un C1 realmente usable.',
    ],
];

$theoryExtras = [
    71 => ['scenario' => "Escenario: el portero del edificio solo abre si pronuncias bien 'ich' y 'ach'.\nMision: presenta tu nombre y pais sin perder la dignidad.", 'mission' => "Mision express:\n- Repite Guten Morgen tres veces.\n- Di Ich heiße ...\n- Cierra con Freut mich."],
    72 => ['scenario' => "Escenario: una libreria clasifica clientes segun der, die y das.\nMision: identifica tres sustantivos antes de que te miren con decepcion gramatical.", 'mission' => "Mision express:\n- Memoriza der Lehrer, die Stadt y das Buch.\n- Crea una frase con ich bin.\n- Niega una frase con nicht o kein."],
    73 => ['scenario' => "Escenario: una vaca entrevistadora te hace cinco preguntas personales en un ascensor.\nMision: responder sin entrar en crisis.", 'mission' => "Mision express:\n- Responde Wie heißt du?\n- Responde Woher kommst du?\n- Responde Hast du Zeit?"],
    80 => ['scenario' => "Escenario: en una cafeteria de Berlin pides cafe y terminas comprando un mapa, un lapiz y media papeleria.\nMision: usar acusativo sin sonar culpable.", 'mission' => "Mision express:\n- Pide un objeto con ich brauche.\n- Formula una compra con ich habe.\n- Haz una pregunta con Hast du...?"],
    81 => ['scenario' => "Escenario: el cajero parece amable, pero juzga cada modalverb que usas.\nMision: pedir ayuda y pagar con cortesía impecable.", 'mission' => "Mision express:\n- Usa kann en una pregunta.\n- Usa muss para una necesidad.\n- Usa möchte para pedir algo."],
    82 => ['scenario' => "Escenario: un pato con gorra administra el tranvia y te da direcciones ambiguas.\nMision: llegar a la estacion antes de que el pato cambie de humor.", 'mission' => "Mision express:\n- Da una direccion con links o rechts.\n- Usa un verbo separable.\n- Añade una hora de salida."],
    74 => ['scenario' => "Escenario: ayer perdiste el tren, encontraste una panaderia y terminaste feliz pero confundido.\nMision: contar el desastre con Perfekt estable.", 'mission' => "Mision express:\n- Di una frase con habe.\n- Di una frase con bin.\n- Añade gestern o am Wochenende."],
    75 => ['scenario' => "Escenario: tu hermana, tu profesor y el conductor del bus te piden favores a la vez.\nMision: sobrevivir usando dativo con calma.", 'mission' => "Mision express:\n- Construye una frase con helfen.\n- Usa una preposicion de dativo.\n- Añade un destino o una persona."],
    76 => ['scenario' => "Escenario: visitas tres apartamentos, te duele la espalda y todos los balcones parecen sospechosos.\nMision: comparar y quejarte con propiedad.", 'mission' => "Mision express:\n- Describe una habitacion.\n- Di que te duele algo.\n- Compara dos opciones con groesser o billiger."],
    77 => ['scenario' => "Escenario: debes explicar por que estudias aleman mientras una paloma te observa desde la ventana.\nMision: conectar ideas sin romper la subordinada.", 'mission' => "Mision express:\n- Usa weil.\n- Usa dass.\n- Usa obwohl en una frase personal."],
    78 => ['scenario' => "Escenario: estas en una oficina donde todos suenan amables pero potencialmente peligrosos.\nMision: pedir informacion con cortesía premium.", 'mission' => "Mision express:\n- Formula una pregunta con Könnten Sie...?\n- Describe una persona con una relativa.\n- Pide mas informacion."],
    79 => ['scenario' => "Escenario: un colega, una red social y una noticia discuten dentro de tu cabeza.\nMision: dar una opinion con dos lados del tema.", 'mission' => "Mision express:\n- Usa meiner Meinung nach.\n- Añade einerseits... andererseits.\n- Cierra con una conclusion corta."],
    83 => ['scenario' => "Escenario: imaginas un mundo donde todos los correos se responden y las reuniones duran nueve minutos.\nMision: usar irrealidad y pasiva sin perder elegancia.", 'mission' => "Mision express:\n- Formula una condicion irreal.\n- Usa würde.\n- Añade una frase en pasiva."],
    84 => ['scenario' => "Escenario: un profesor te exige sonar más denso, pero aun comprensible.\nMision: compactar una idea sin sacrificar claridad.", 'mission' => "Mision express:\n- Crea un sustantivo con -ung o -keit.\n- Usa un participio en una descripcion.\n- Reescribe una idea corta con mas densidad."],
    85 => ['scenario' => "Escenario: moderas un debate sobre tecnologia, cultura y economia con cero tiempo para improvisar.\nMision: sonar B2 y no telepredicador del caos.", 'mission' => "Mision express:\n- Usa tres palabras de academia o negocios.\n- Formula una postura.\n- Matiza la conclusion."],
    86 => ['scenario' => "Escenario: una periodista cita a un experto y tu debes resumirlo con precision quirurgica.\nMision: manejar registro y Konjunktiv sin temblar.", 'mission' => "Mision express:\n- Reformula una cita con sei.\n- Usa allerdings o demnach.\n- Mantén tono formal."],
    87 => ['scenario' => "Escenario: te lanzan un articulo denso y cinco palabras largas que parecen inventadas por un castillo.\nMision: desarmarlo con estrategia, no con miedo.", 'mission' => "Mision express:\n- Detecta un prefijo.\n- Detecta un sufijo.\n- Resume la tesis de un texto en una frase."],
    88 => ['scenario' => "Escenario: planificas noventa dias de estudio mientras tu calendario intenta sabotearte.\nMision: salir con un plan realista y medible.", 'mission' => "Mision express:\n- Define un objetivo de escucha.\n- Define un objetivo de escritura.\n- Define una rutina semanal minima."],
];

$deleteStmt = $pdo->prepare("DELETE FROM contenido_bloques WHERE teoria_id = ? AND titulo IN ('Escenario de practica', 'Mision express')");
$maxOrderStmt = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) AS max_orden FROM contenido_bloques WHERE teoria_id = ?');
$insertStmt = $pdo->prepare('
    INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden)
    VALUES (?, ?, ?, ?, ?, ?, NULL, ?)
');

$pdo->beginTransaction();

$stmt = $pdo->prepare('UPDATE cursos SET titulo = :titulo, descripcion = :descripcion WHERE id = :id');
$stmt->execute($courseUpdate);

$stmt = $pdo->prepare('UPDATE lecciones SET titulo = :titulo, descripcion = :descripcion WHERE id = :id');
foreach ($lessonUpdates as $lessonId => $lessonData) {
    $stmt->execute([
        'titulo' => $lessonData['titulo'],
        'descripcion' => $lessonData['descripcion'],
        'id' => $lessonId,
    ]);
}

foreach ($theoryExtras as $theoryId => $extra) {
    $deleteStmt->execute([$theoryId]);
    $maxOrderStmt->execute([$theoryId]);
    $maxOrder = (int) $maxOrderStmt->fetchColumn();

    $insertStmt->execute([$theoryId, 'instruccion', 'Escenario de practica', $extra['scenario'], 'espanol', 1, $maxOrder + 1]);
    $insertStmt->execute([$theoryId, 'instruccion', 'Mision express', $extra['mission'], 'espanol', 1, $maxOrder + 2]);
}

$pdo->commit();

echo json_encode([
    'course_id' => 17,
    'updated_lessons' => count($lessonUpdates),
    'updated_theories' => count($theoryExtras),
    'database' => DB_NAME,
    'host' => DB_HOST,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
