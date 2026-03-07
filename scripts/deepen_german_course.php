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
    'id' => 17,
    'descripcion' => 'Ruta completa de aleman para hispanohablantes desde supervivencia A1 hasta precision C1. Combina pronunciacion, gramatica funcional, escucha, escritura, debate y misiones practicas con situaciones memorables para que el idioma se sienta util desde la primera leccion.',
];

$lessonUpdates = [
    45 => 'Arranque con pronunciacion esencial, saludo, presentacion personal, preguntas basicas y primeras respuestas solidas para ganar seguridad desde el dia uno.',
    48 => 'Modulo puente entre A1 y A2 para pedir cosas, moverte por la ciudad, usar modalverben y resolver escenas cotidianas sin salto brusco.',
    46 => 'Perfekt, dativo, vivienda, salud y comparativos para contar lo que paso, pedir ayuda y describir mejor tu entorno inmediato.',
    47 => 'Conecta ideas, justifica opiniones, escribe con mas tacto y habla de trabajo, estudio y medios sin quedarte en frases aisladas.',
    49 => 'Hipotesis, pasiva, densidad expresiva y debate guiado en temas academicos, sociales y culturales con mas control de matices.',
    50 => 'Registro, discurso referido, lectura academica y estrategia de consolidacion para sostener un C1 usable y medible.',
];

$theorySupportBlocks = [];
$activityUpdates = [];

$theorySupportBlocks = [
    71 => [
        'error' => "Error frecuente:\n- Pronunciar ich como ik o ish.\n- Leer sp y st como en espanol.\n- Olvidar que sch suele sonar como sh.",
        'check' => "Chequeo rapido:\n- Distingue ich de ach.\n- Lee Guten Morgen sin correr.\n- Di una mini presentacion de una linea.",
    ],
    72 => [
        'error' => "Error frecuente:\n- Memorizar sustantivos sin articulo.\n- Usar kein para todo.\n- Mezclar ich bin con ich habe sin criterio.",
        'check' => "Chequeo rapido:\n- Di un sustantivo con der.\n- Di otro con die.\n- Niega una frase con nicht y otra con kein.",
    ],
    73 => [
        'error' => "Error frecuente:\n- Responder con frases calcadas del espanol.\n- Saltarte el verbo en respuestas cortas.\n- Confundir edad con tener en lugar de sein.",
        'check' => "Chequeo rapido:\n- Responde Wie heisst du?\n- Responde Woher kommst du?\n- Responde Wie alt bist du?",
    ],
    80 => [
        'error' => "Error frecuente:\n- Repetir siempre der sin mirar el caso.\n- Usar nominativo despues de brauchen.\n- Pedir objetos sin articulo.",
        'check' => "Chequeo rapido:\n- Pide un cafe.\n- Pide una tarjeta.\n- Pregunta si alguien tiene un boligrafo.",
    ],
    81 => [
        'error' => "Error frecuente:\n- Usar moechten mal conjugado.\n- Mezclar kann con muss cuando la idea es distinta.\n- Sonar brusco al pedir algo.",
        'check' => "Chequeo rapido:\n- Formula una pregunta con kann.\n- Expresa una necesidad con muss.\n- Pide algo con moechte.",
    ],
    82 => [
        'error' => "Error frecuente:\n- Olvidar la particula del verbo separable.\n- Confundir links y rechts bajo presion.\n- Dar direcciones sin punto de referencia.",
        'check' => "Chequeo rapido:\n- Da una direccion corta.\n- Usa gegenueber o neben.\n- Di a que hora sale un bus o tren.",
    ],
    74 => [
        'error' => "Error frecuente:\n- Elegir siempre habe como auxiliar.\n- Poner el participio en medio de la frase.\n- Omitir el marcador de tiempo.",
        'check' => "Chequeo rapido:\n- Di una frase con bin gegangen.\n- Di otra con habe gearbeitet.\n- Anade gestern o letzte Woche.",
    ],
    75 => [
        'error' => "Error frecuente:\n- Usar acusativo por costumbre donde va dativo.\n- Aprender preposiciones sueltas sin contexto.\n- Olvidar la terminacion del posesivo.",
        'check' => "Chequeo rapido:\n- Construye una frase con helfen.\n- Usa mit o bei.\n- Menciona una persona en dativo.",
    ],
    76 => [
        'error' => "Error frecuente:\n- Comparar con mas + adjetivo como en espanol.\n- Hablar de dolor sin estructura fija.\n- Describir vivienda con vocabulario muy corto.",
        'check' => "Chequeo rapido:\n- Di que te duele algo.\n- Compara dos habitaciones.\n- Describe una vivienda con dos adjetivos.",
    ],
    77 => [
        'error' => "Error frecuente:\n- Mantener el orden del verbo como en principal dentro de subordinadas.\n- Encadenar weil y dass sin cerrar bien la idea.\n- Usar conectores solo para sonar avanzado.",
        'check' => "Chequeo rapido:\n- Da una razon con weil.\n- Expresa una opinion con dass.\n- Haz un contraste con obwohl.",
    ],
    78 => [
        'error' => "Error frecuente:\n- Pedir cosas de forma demasiado directa.\n- Romper la relativa a mitad de frase.\n- Sonar formal solo por usar palabras largas.",
        'check' => "Chequeo rapido:\n- Formula una peticion cortesa.\n- Describe una persona con der o die.\n- Pide informacion extra.",
    ],
    79 => [
        'error' => "Error frecuente:\n- Dar opinion sin justificar.\n- Repetir ich finde todo el tiempo.\n- No matizar una postura.",
        'check' => "Chequeo rapido:\n- Usa meiner Meinung nach.\n- Da dos lados de un tema.\n- Cierra con una conclusion breve.",
    ],
    83 => [
        'error' => "Error frecuente:\n- Mezclar condicion real e irreal.\n- Usar pasiva sin agente ni funcion comunicativa.\n- Abusar de conectores sin estructura clara.",
        'check' => "Chequeo rapido:\n- Formula una hipotesis irreal.\n- Usa wuerde.\n- Escribe una frase en pasiva.",
    ],
    84 => [
        'error' => "Error frecuente:\n- Confundir densidad con oscuridad.\n- Nominalizar en exceso y perder claridad.\n- Copiar palabras abstractas sin entenderlas.",
        'check' => "Chequeo rapido:\n- Forma un sustantivo con -ung.\n- Describe algo con un participio.\n- Reescribe una frase simple con mas precision.",
    ],
    85 => [
        'error' => "Error frecuente:\n- Usar lexico abstracto sin posicion clara.\n- Hacer un debate sin matices.\n- Saltar de idea en idea sin hilo.",
        'check' => "Chequeo rapido:\n- Usa tres palabras del campo academico o social.\n- Formula una postura.\n- Introduce una reserva o matiz.",
    ],
    86 => [
        'error' => "Error frecuente:\n- Confundir registro formal con frialdad total.\n- Usar Konjunktiv I de forma mecanica.\n- Mezclar cita directa e indirecta.",
        'check' => "Chequeo rapido:\n- Reformula una declaracion con sei.\n- Usa folglich o demnach.\n- Mantiene tono formal en dos lineas.",
    ],
    87 => [
        'error' => "Error frecuente:\n- Traducir palabra por palabra textos densos.\n- No detectar prefijos que cambian el sentido.\n- Leer sin buscar tesis ni estructura.",
        'check' => "Chequeo rapido:\n- Detecta un prefijo.\n- Detecta un sufijo.\n- Resume una idea central en una frase.",
    ],
    88 => [
        'error' => "Error frecuente:\n- Hacer planes heroicos imposibles de sostener.\n- Medir avance solo por horas y no por resultados.\n- Dejar fuera escucha y escritura.",
        'check' => "Chequeo rapido:\n- Define un objetivo semanal.\n- Elige una forma de medirlo.\n- Deja un bloque minimo para repaso.",
    ],
];

$activityUpdates = [
    129 => [
        'titulo' => 'Checkpoint A1: presentarte sin temblar',
        'descripcion' => 'Resuelve escenas basicas de saludo, origen y verbo sein con respuestas que suenan naturales.',
        'instrucciones' => 'Marca la mejor opcion en cada mini escena.',
        'contenido' => [
            'pregunta_global' => 'Selecciona la respuesta mas natural para cada situacion.',
            'preguntas' => [
                ['texto' => 'Frase correcta para presentarte en una clase:', 'opciones' => [['texto' => 'Ich heisse Marta.', 'es_correcta' => true], ['texto' => 'Ich heisst Marta.', 'es_correcta' => false], ['texto' => 'Ich name Marta.', 'es_correcta' => false]]],
                ['texto' => 'Respuesta natural a "Woher kommst du?"', 'opciones' => [['texto' => 'Ich komme aus Chile.', 'es_correcta' => true], ['texto' => 'Ich bin Chile.', 'es_correcta' => false], ['texto' => 'Aus komme ich Chile.', 'es_correcta' => false]]],
                ['texto' => 'Forma correcta de sein con ich:', 'opciones' => [['texto' => 'bin', 'es_correcta' => true], ['texto' => 'bist', 'es_correcta' => false], ['texto' => 'seid', 'es_correcta' => false]]],
                ['texto' => 'Respuesta breve a "Hast du Zeit?"', 'opciones' => [['texto' => 'Ja, ich habe Zeit.', 'es_correcta' => true], ['texto' => 'Ja, ich bin Zeit.', 'es_correcta' => false], ['texto' => 'Ja, Zeit ich habe.', 'es_correcta' => false]]],
            ],
        ],
    ],
    130 => [
        'titulo' => 'Ordena tu mini dialogo de ascensor',
        'descripcion' => 'Reconstruye frases cortas de presentacion con orden aleman estable.',
        'instrucciones' => 'Ordena todas las palabras hasta formar frases naturales.',
        'contenido' => [
            ['id' => 'a1_intro_1', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'heisse', 'Lucia.']],
            ['id' => 'a1_intro_2', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'komme', 'aus', 'Kolumbien.']],
            ['id' => 'a1_intro_3', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'bin', 'Studentin.']],
        ],
    ],
    131 => [
        'titulo' => 'Sein y haben bajo presion amable',
        'descripcion' => 'Completa estructuras frecuentes sin mezclar edad, origen y posesion.',
        'instrucciones' => 'Escribe solo la palabra correcta en cada espacio.',
        'contenido' => [
            ['id' => 'a1_gap_1', 'oracion' => 'Ich ____ 19 Jahre alt.', 'respuesta_correcta' => 'bin'],
            ['id' => 'a1_gap_2', 'oracion' => 'Wir ____ einen Deutschkurs.', 'respuesta_correcta' => 'haben'],
            ['id' => 'a1_gap_3', 'oracion' => 'Sie ____ aus Peru.', 'respuesta_correcta' => 'ist'],
            ['id' => 'a1_gap_4', 'oracion' => 'Du ____ heute Zeit.', 'respuesta_correcta' => 'hast'],
        ],
    ],
    132 => [
        'titulo' => 'Escucha: presentacion del alumno nuevo',
        'descripcion' => 'Escucha una mini presentacion y transcribela con cuidado.',
        'instrucciones' => 'Reproduce el audio y escribe la frase exacta.',
        'contenido' => [
            'texto_tts' => 'Guten Tag, ich heisse Daniel, ich komme aus Chile und ich habe heute meinen ersten Deutschkurs.',
            'transcripcion' => 'Guten Tag, ich heisse Daniel, ich komme aus Chile und ich habe heute meinen ersten Deutschkurs.',
        ],
    ],
    141 => [
        'titulo' => 'Cafe, ticket y tarjeta: sobrevivir en la ciudad',
        'descripcion' => 'Elige la opcion mas natural para pedir, comprar y pagar en situaciones de ciudad.',
        'instrucciones' => 'Marca la mejor opcion en cada escena.',
        'contenido' => [
            'pregunta_global' => 'Elige la respuesta mas natural.',
            'preguntas' => [
                ['texto' => 'Forma correcta de pedir algo en una cafeteria:', 'opciones' => [['texto' => 'Ich moechte einen Tee, bitte.', 'es_correcta' => true], ['texto' => 'Ich moechten Tee bitte.', 'es_correcta' => false], ['texto' => 'Tee ich bitte moechte.', 'es_correcta' => false]]],
                ['texto' => 'Pregunta correcta para pagar con tarjeta:', 'opciones' => [['texto' => 'Kann ich mit Karte zahlen?', 'es_correcta' => true], ['texto' => 'Kann ich Karte zahlen mit?', 'es_correcta' => false], ['texto' => 'Ich Karte kann zahlen?', 'es_correcta' => false]]],
                ['texto' => 'Articulo correcto en acusativo masculino:', 'opciones' => [['texto' => 'einen', 'es_correcta' => true], ['texto' => 'einem', 'es_correcta' => false], ['texto' => 'einer', 'es_correcta' => false]]],
                ['texto' => 'Pregunta natural para buscar un ticket:', 'opciones' => [['texto' => 'Wo kann ich ein Ticket kaufen?', 'es_correcta' => true], ['texto' => 'Wo ich kann ein Ticket kaufen?', 'es_correcta' => false], ['texto' => 'Ich wo Ticket kaufen kann?', 'es_correcta' => false]]],
            ],
        ],
    ],
    142 => [
        'titulo' => 'Respuesta corta con modal y plan inmediato',
        'descripcion' => 'Completa frases breves con el modal que mejor encaja.',
        'instrucciones' => 'Escribe una sola palabra por respuesta.',
        'contenido' => [
            'pregunta' => 'Completa: Ich ____ heute in die Stadt gehen.',
            'respuesta_correcta' => 'kann',
            'respuestas_correctas' => ['kann'],
            'placeholder' => 'Escribe una palabra',
        ],
    ],
    143 => [
        'titulo' => 'Ordena la direccion sin perderte',
        'descripcion' => 'Reconstruye instrucciones cortas de ciudad y transporte.',
        'instrucciones' => 'Ordena las palabras para formar frases utiles.',
        'contenido' => [
            ['id' => 'city_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Der', 'Bahnhof', 'ist', 'gegenueber', 'dem', 'Museum.']],
            ['id' => 'city_order_2', 'instruction' => 'Ordena la frase.', 'items' => ['Der', 'Bus', 'faehrt', 'um', 'acht', 'Uhr', 'ab.']],
        ],
    ],
    144 => [
        'titulo' => 'Mision escrita: una manana util en aleman',
        'descripcion' => 'Escribe una micro rutina donde aparezcan ciudad, compra o transporte de forma natural.',
        'instrucciones' => 'Escribe 70 a 100 palabras con al menos un modal y una accion de ciudad.',
        'contenido' => [
            'tema' => 'Describe una manana en la que sales, compras algo, tomas transporte o preguntas una direccion. Incluye al menos un modalverbo.',
            'min_palabras' => 70,
        ],
    ],
    133 => [
        'titulo' => 'Perfekt y dativo sin derrape',
        'descripcion' => 'Elige la forma correcta en escenas de pasado, ayuda y comparacion.',
        'instrucciones' => 'Selecciona la mejor opcion en cada frase.',
        'contenido' => [
            'pregunta_global' => 'Elige la opcion correcta.',
            'preguntas' => [
                ['texto' => 'Gestern ____ ich sehr frueh aufgestanden.', 'opciones' => [['texto' => 'bin', 'es_correcta' => true], ['texto' => 'habe', 'es_correcta' => false], ['texto' => 'war', 'es_correcta' => false]]],
                ['texto' => 'Ich helfe ____ Mutter.', 'opciones' => [['texto' => 'meiner', 'es_correcta' => true], ['texto' => 'meine', 'es_correcta' => false], ['texto' => 'meinen', 'es_correcta' => false]]],
                ['texto' => 'Comparativo correcto:', 'opciones' => [['texto' => 'groesser', 'es_correcta' => true], ['texto' => 'groess', 'es_correcta' => false], ['texto' => 'mehr gross', 'es_correcta' => false]]],
                ['texto' => 'Auxiliar natural con ankommen:', 'opciones' => [['texto' => 'ist', 'es_correcta' => true], ['texto' => 'hat', 'es_correcta' => false], ['texto' => 'war', 'es_correcta' => false]]],
            ],
        ],
    ],
    134 => [
        'titulo' => 'Completa el relato del fin de semana',
        'descripcion' => 'Rellena huecos de Perfekt, dativo y comparativos en un relato corto.',
        'instrucciones' => 'Escribe solo una palabra por espacio.',
        'contenido' => [
            ['id' => 'a2_gap_1', 'oracion' => 'Ich ____ gestern im Kino gewesen.', 'respuesta_correcta' => 'bin'],
            ['id' => 'a2_gap_2', 'oracion' => 'Ich habe ____ Freundin geholfen.', 'respuesta_correcta' => 'meiner'],
            ['id' => 'a2_gap_3', 'oracion' => 'Mein Zimmer ist ____ als frueher.', 'respuesta_correcta' => 'groesser'],
            ['id' => 'a2_gap_4', 'oracion' => 'Am Wochenende habe ich meine Eltern ____ .', 'respuesta_correcta' => 'besucht'],
        ],
    ],
    135 => [
        'titulo' => 'Sintomas rapidos y utiles',
        'descripcion' => 'Completa una frase tipica cuando te duele algo.',
        'instrucciones' => 'Escribe una sola palabra.',
        'contenido' => [
            'pregunta' => 'Completa: Mir tut der ____ weh.',
            'respuesta_correcta' => 'Kopf',
            'respuestas_correctas' => ['Kopf'],
            'placeholder' => 'Escribe una palabra',
        ],
    ],
    136 => [
        'titulo' => 'Email A2: fin de semana, casa y pequeno drama',
        'descripcion' => 'Redacta un email informal con pasado, comparacion o una molestia de salud.',
        'instrucciones' => 'Escribe 100 a 140 palabras y conecta al menos dos ideas.',
        'contenido' => [
            'tema' => 'Escribe un email informal contando que hiciste el fin de semana, como es tu vivienda actual o que pequeno problema de salud tuviste. Usa al menos una frase en Perfekt.',
            'min_palabras' => 100,
        ],
    ],
    137 => [
        'titulo' => 'B1 real: conecta ideas y suena natural',
        'descripcion' => 'Elige conectores, cortesia y estructuras que sostienen una opinion bien armada.',
        'instrucciones' => 'Marca la mejor opcion en cada escena.',
        'contenido' => [
            'pregunta_global' => 'Elige la mejor opcion.',
            'preguntas' => [
                ['texto' => 'Ich glaube, ____ er heute nicht kommt.', 'opciones' => [['texto' => 'dass', 'es_correcta' => true], ['texto' => 'denn', 'es_correcta' => false], ['texto' => 'wie', 'es_correcta' => false]]],
                ['texto' => 'Peticion formal correcta:', 'opciones' => [['texto' => 'Koennten Sie mir helfen?', 'es_correcta' => true], ['texto' => 'Kann mir Sie helfen?', 'es_correcta' => false], ['texto' => 'Hilfst du mir koennten?', 'es_correcta' => false]]],
                ['texto' => 'Frase con contraste bien formada:', 'opciones' => [['texto' => 'Obwohl ich muede bin, lerne ich weiter.', 'es_correcta' => true], ['texto' => 'Obwohl ich bin muede, lerne ich weiter.', 'es_correcta' => false], ['texto' => 'Ich obwohl muede bin, lerne.', 'es_correcta' => false]]],
                ['texto' => 'Relative natural para describir a una persona:', 'opciones' => [['texto' => 'Der Mann, der dort arbeitet, ist freundlich.', 'es_correcta' => true], ['texto' => 'Der Mann, dort arbeitet, ist freundlich.', 'es_correcta' => false], ['texto' => 'Der Mann, der dort arbeiten, ist freundlich.', 'es_correcta' => false]]],
            ],
        ],
    ],
    138 => [
        'titulo' => 'Ordena el correo formal con tacto',
        'descripcion' => 'Reconstruye preguntas de oficina y consulta sin romper el orden.',
        'instrucciones' => 'Ordena las palabras para formar frases formales completas.',
        'contenido' => [
            ['id' => 'b1_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Koennten', 'Sie', 'mir', 'bitte', 'weiterhelfen?']],
            ['id' => 'b1_order_2', 'instruction' => 'Ordena la frase.', 'items' => ['Ich', 'wuerde', 'gern', 'mehr', 'Informationen', 'erhalten.']],
        ],
    ],
    139 => [
        'titulo' => 'Huecos clave de B1',
        'descripcion' => 'Completa conectores, relativas y expresiones de deseo utiles en trabajo y estudio.',
        'instrucciones' => 'Escribe solo una palabra por espacio.',
        'contenido' => [
            ['id' => 'b1_gap_1', 'oracion' => 'Ich finde, ____ Onlinekurse praktisch sind.', 'respuesta_correcta' => 'dass'],
            ['id' => 'b1_gap_2', 'oracion' => 'Der Mann, ____ dort steht, ist mein Lehrer.', 'respuesta_correcta' => 'der'],
            ['id' => 'b1_gap_3', 'oracion' => 'Ich ____ gern im Ausland studieren.', 'respuesta_correcta' => 'wuerde'],
            ['id' => 'b1_gap_4', 'oracion' => 'Ich lerne Deutsch, ____ ich in Berlin arbeiten moechte.', 'respuesta_correcta' => 'weil'],
        ],
    ],
    140 => [
        'titulo' => 'Opinion guiada B1 con dos lados',
        'descripcion' => 'Redacta una opinion personal con razones, contraste y cierre claro.',
        'instrucciones' => 'Escribe 130 a 170 palabras y usa al menos dos conectores distintos.',
        'contenido' => [
            'tema' => 'Da tu opinion sobre redes sociales, trabajo remoto o educacion online. Incluye una razon, un contraste y una conclusion corta.',
            'min_palabras' => 130,
        ],
    ],
    145 => [
        'titulo' => 'B2: matiza sin sonar mecanico',
        'descripcion' => 'Escoge estructuras de hipotesis, contraste y argumentacion que suenan naturales en un debate B2.',
        'instrucciones' => 'Marca la mejor opcion en cada caso.',
        'contenido' => [
            'pregunta_global' => 'Selecciona la opcion correcta.',
            'preguntas' => [
                ['texto' => '____ mehr man liest, desto besser schreibt man.', 'opciones' => [['texto' => 'Je', 'es_correcta' => true], ['texto' => 'Als', 'es_correcta' => false], ['texto' => 'Doch', 'es_correcta' => false]]],
                ['texto' => 'Estructura irreal correcta:', 'opciones' => [['texto' => 'Wenn ich Zeit haette, wuerde ich mehr reisen.', 'es_correcta' => true], ['texto' => 'Wenn ich Zeit habe, wuerde ich mehr reiste.', 'es_correcta' => false], ['texto' => 'Wenn ich haette Zeit, ich wuerde reisen.', 'es_correcta' => false]]],
                ['texto' => 'Conector de contraste equilibrado:', 'opciones' => [['texto' => 'einerseits... andererseits', 'es_correcta' => true], ['texto' => 'weil... deshalb', 'es_correcta' => false], ['texto' => 'ob... oder', 'es_correcta' => false]]],
                ['texto' => 'Pasiva funcional bien formada:', 'opciones' => [['texto' => 'Das Problem kann schnell geloest werden.', 'es_correcta' => true], ['texto' => 'Das Problem kann geloest schnell werden.', 'es_correcta' => false], ['texto' => 'Das Problem werden schnell geloest kann.', 'es_correcta' => false]]],
            ],
        ],
    ],
    146 => [
        'titulo' => 'Ordena el argumento con contraste',
        'descripcion' => 'Reconstruye frases mas densas sin perder claridad sintactica.',
        'instrucciones' => 'Ordena todas las palabras para formar frases completas.',
        'contenido' => [
            ['id' => 'b2_order_1', 'instruction' => 'Ordena la frase.', 'items' => ['Einerseits', 'ist', 'digitale', 'Bildung', 'flexibel,', 'andererseits', 'braucht', 'sie', 'mehr', 'Selbstdisziplin.']],
            ['id' => 'b2_order_2', 'instruction' => 'Ordena la frase.', 'items' => ['Wenn', 'ich', 'mehr', 'Zeit', 'haette,', 'wuerde', 'ich', 'taeglich', 'deutsche', 'Artikel', 'lesen.']],
        ],
    ],
    147 => [
        'titulo' => 'Escucha academica: IA y preguntas eticas',
        'descripcion' => 'Escucha una frase argumentativa B2 y transcribela con precision.',
        'instrucciones' => 'Reproduce el audio y escribe la frase exacta.',
        'contenido' => [
            'texto_tts' => 'Einerseits beschleunigt kuenstliche Intelligenz viele Prozesse, andererseits wirft sie ernste ethische Fragen auf.',
            'transcripcion' => 'Einerseits beschleunigt kuenstliche Intelligenz viele Prozesse, andererseits wirft sie ernste ethische Fragen auf.',
        ],
    ],
    148 => [
        'titulo' => 'Ensayo B2 con postura y matiz',
        'descripcion' => 'Redacta una opinion argumentada donde no solo afirmes, sino que tambien limites o equilibres tu postura.',
        'instrucciones' => 'Escribe 190 a 230 palabras con al menos un contraste claro y una conclusion matizada.',
        'contenido' => [
            'tema' => 'Escribe una opinion argumentada sobre inteligencia artificial, globalizacion o democracia digital. Presenta una postura, un contraargumento y una conclusion equilibrada.',
            'min_palabras' => 190,
        ],
    ],
    149 => [
        'titulo' => 'C1: elegir la formulacion precisa',
        'descripcion' => 'Distingue registro formal, distancia discursiva y formacion de palabras con criterio fino.',
        'instrucciones' => 'Selecciona la opcion mas adecuada en cada caso.',
        'contenido' => [
            'pregunta_global' => 'Selecciona la opcion mas precisa.',
            'preguntas' => [
                ['texto' => 'Conector formal de consecuencia:', 'opciones' => [['texto' => 'folglich', 'es_correcta' => true], ['texto' => 'y luego', 'es_correcta' => false], ['texto' => 'naja', 'es_correcta' => false]]],
                ['texto' => 'Frase con distancia discursiva correcta:', 'opciones' => [['texto' => 'Er erklaerte, er sei mit der Entscheidung nicht einverstanden.', 'es_correcta' => true], ['texto' => 'Er sagt, nein.', 'es_correcta' => false], ['texto' => 'Er war so nein.', 'es_correcta' => false]]],
                ['texto' => 'Sufijo que forma sustantivos abstractos frecuentes:', 'opciones' => [['texto' => '-keit', 'es_correcta' => true], ['texto' => '-los', 'es_correcta' => false], ['texto' => '-bar', 'es_correcta' => false]]],
                ['texto' => 'Registro mas adecuado para un informe:', 'opciones' => [['texto' => 'Demnach laesst sich feststellen, dass weitere Massnahmen noetig sind.', 'es_correcta' => true], ['texto' => 'Also, das ist halt problematisch.', 'es_correcta' => false], ['texto' => 'Naja, irgendwie geht es schon.', 'es_correcta' => false]]],
            ],
        ],
    ],
    150 => [
        'titulo' => 'Escucha avanzada C1: lectura academica breve',
        'descripcion' => 'Escucha una frase academica y registrala con precision casi editorial.',
        'instrucciones' => 'Reproduce el audio y escribe la frase exacta.',
        'contenido' => [
            'texto_tts' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Faehigkeit, unterschiedliche Perspektiven praezise einzuordnen.',
            'transcripcion' => 'Interkulturelle Kommunikation erfordert nicht nur Sprachkenntnisse, sondern auch die Faehigkeit, unterschiedliche Perspektiven praezise einzuordnen.',
        ],
    ],
    151 => [
        'titulo' => 'Tu plan realista de 90 dias',
        'descripcion' => 'Escribe un plan de consolidacion con objetivos medibles y una rutina que realmente podrias sostener.',
        'instrucciones' => 'Escribe 150 a 190 palabras con metas, recursos y una forma concreta de medir tu avance.',
        'contenido' => [
            'tema' => 'Describe tu plan de estudio de 90 dias con objetivos de escucha, lectura, escritura y conversacion. Incluye como vas a medir el progreso cada semana.',
            'min_palabras' => 150,
        ],
    ],
    152 => [
        'titulo' => 'Chequeo final: certificaciones y ruta universitaria',
        'descripcion' => 'Decide si la afirmacion es correcta segun el panorama de certificaciones visto en la leccion.',
        'instrucciones' => 'Elige verdadero o falso.',
        'contenido' => [
            'pregunta' => 'TestDaF y DSH aparecen con frecuencia en rutas vinculadas a estudios universitarios en Alemania.',
            'respuesta_correcta' => 'Verdadero',
        ],
    ],
];

$updateCourseStmt = $pdo->prepare('UPDATE cursos SET descripcion = :descripcion WHERE id = :id');
$updateLessonStmt = $pdo->prepare('UPDATE lecciones SET descripcion = :descripcion WHERE id = :id');
$updateActivityStmt = $pdo->prepare('
    UPDATE actividades
    SET titulo = :titulo,
        descripcion = :descripcion,
        instrucciones = :instrucciones,
        contenido = :contenido
    WHERE id = :id
');
$deleteSupportStmt = $pdo->prepare("DELETE FROM contenido_bloques WHERE teoria_id = ? AND titulo IN ('Error frecuente', 'Chequeo rapido')");
$maxOrderStmt = $pdo->prepare('SELECT COALESCE(MAX(orden), 0) FROM contenido_bloques WHERE teoria_id = ?');
$insertSupportStmt = $pdo->prepare('
    INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden)
    VALUES (?, ?, ?, ?, ?, ?, NULL, ?)
');

$pdo->beginTransaction();

$updateCourseStmt->execute($courseUpdate);

foreach ($lessonUpdates as $lessonId => $description) {
    $updateLessonStmt->execute([
        'id' => $lessonId,
        'descripcion' => $description,
    ]);
}

foreach ($activityUpdates as $activityId => $activity) {
    $updateActivityStmt->execute([
        'id' => $activityId,
        'titulo' => $activity['titulo'],
        'descripcion' => $activity['descripcion'],
        'instrucciones' => $activity['instrucciones'],
        'contenido' => json_encode($activity['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

foreach ($theorySupportBlocks as $theoryId => $blocks) {
    $deleteSupportStmt->execute([$theoryId]);
    $maxOrderStmt->execute([$theoryId]);
    $maxOrder = (int) $maxOrderStmt->fetchColumn();

    $insertSupportStmt->execute([$theoryId, 'instruccion', 'Error frecuente', $blocks['error'], 'espanol', 1, $maxOrder + 1]);
    $insertSupportStmt->execute([$theoryId, 'instruccion', 'Chequeo rapido', $blocks['check'], 'espanol', 1, $maxOrder + 2]);
}

$pdo->commit();

echo json_encode([
    'course_id' => 17,
    'updated_lessons' => count($lessonUpdates),
    'updated_activities' => count($activityUpdates),
    'updated_theories' => count($theorySupportBlocks),
    'database' => DB_NAME,
    'host' => DB_HOST,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
