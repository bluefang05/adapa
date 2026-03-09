<?php

declare(strict_types=1);

require_once __DIR__ . '/german_course_blueprint.php';

function german_expand_lines(array $items): string
{
    $items = array_values(array_filter(array_map(static fn($item): string => trim((string) $item), $items), static fn(string $item): bool => $item !== ''));
    if (empty($items)) {
        return '';
    }

    return "- " . implode("\n- ", $items);
}

function german_expand_restore_umlauts_word(string $word): string
{
    static $map = [
        'abhaengigkeit' => 'abhängigkeit',
        'abhaengigkeiten' => 'abhängigkeiten',
        'abschliessend' => 'abschließend',
        'arbeitsablaeufe' => 'arbeitsabläufe',
        'auffaellig' => 'auffällig',
        'ausserdem' => 'außerdem',
        'baeckerei' => 'bäckerei',
        'brueder' => 'brüder',
        'buero' => 'büro',
        'darueber' => 'darüber',
        'dreissig' => 'dreißig',
        'einfuegen' => 'einfügen',
        'einfuehren' => 'einführen',
        'einfuehrung' => 'einführung',
        'einschraenkung' => 'einschränkung',
        'einschraenkungen' => 'einschränkungen',
        'ergaenzen' => 'ergänzen',
        'erklaeren' => 'erklären',
        'erklaert' => 'erklärt',
        'erklaerte' => 'erklärte',
        'faehigkeit' => 'fähigkeit',
        'faehigkeiten' => 'fähigkeiten',
        'faehrt' => 'fährt',
        'flexibilitaet' => 'flexibilität',
        'frueh' => 'früh',
        'frueher' => 'früher',
        'fuehlte' => 'fühlte',
        'fuehren' => 'führen',
        'fuehrt' => 'führt',
        'fuehrte' => 'führte',
        'fuenfzig' => 'fünfzig',
        'fuer' => 'für',
        'fussball' => 'fußball',
        'gefuehrt' => 'geführt',
        'gegenueber' => 'gegenüber',
        'geloest' => 'gelöst',
        'goethe' => 'goethe',
        'groesser' => 'größer',
        'gross' => 'groß',
        'grosseltern' => 'großeltern',
        'gruende' => 'gründe',
        'gruessen' => 'grüßen',
        'grundsaetzlich' => 'grundsätzlich',
        'guenstiger' => 'günstiger',
        'haette' => 'hätte',
        'haetten' => 'hätten',
        'heisse' => 'heiße',
        'heissen' => 'heißen',
        'heisst' => 'heißt',
        'hoere' => 'höre',
        'hoeren' => 'hören',
        'koennen' => 'können',
        'koennte' => 'könnte',
        'koennten' => 'könnten',
        'koenntest' => 'könntest',
        'kueche' => 'küche',
        'kuenstliche' => 'künstliche',
        'laesst' => 'lässt',
        'lektuere' => 'lektüre',
        'loesung' => 'lösung',
        'loesungen' => 'lösungen',
        'maerkte' => 'märkte',
        'massnahmen' => 'maßnahmen',
        'menue' => 'menü',
        'missverstaendnis' => 'missverständnis',
        'moechte' => 'möchte',
        'moechten' => 'möchten',
        'moechtest' => 'möchtest',
        'moeglich' => 'möglich',
        'moechtest' => 'möchtest',
        'muede' => 'müde',
        'muessen' => 'müssen',
        'musste' => 'musste',
        'naechste' => 'nächste',
        'naechsten' => 'nächsten',
        'naechstes' => 'nächstes',
        'noetig' => 'nötig',
        'nuetzlich' => 'nützlich',
        'oeffnen' => 'öffnen',
        'oeffnungszeiten' => 'öffnungszeiten',
        'oesd' => 'ösd',
        'plaediert' => 'plädiert',
        'plaetze' => 'plätze',
        'praesentation' => 'präsentation',
        'praesentationen' => 'präsentationen',
        'praezise' => 'präzise',
        'praeziser' => 'präziser',
        'privatsphaere' => 'privatsphäre',
        'pruefe' => 'prüfe',
        'pruefen' => 'prüfen',
        'pruefung' => 'prüfung',
        'regelmaessiger' => 'regelmäßiger',
        'ruecken' => 'rücken',
        'rueckgang' => 'rückgang',
        'rueckmeldungen' => 'rückmeldungen',
        'spaet' => 'spät',
        'spaeter' => 'später',
        'staerker' => 'stärker',
        'stueck' => 'stück',
        'tragfaehig' => 'tragfähig',
        'ueber' => 'über',
        'uebersehen' => 'übersehen',
        'umgangssprache' => 'umgangssprache',
        'universitaet' => 'universität',
        'veraendert' => 'verändert',
        'veraenderte' => 'veränderte',
        'veraenderung' => 'veränderung',
        'verbesserung' => 'verbesserung',
        'verspaetung' => 'verspätung',
        'vollstaendig' => 'vollständig',
        'voraussetzung' => 'voraussetzung',
        'waechst' => 'wächst',
        'waere' => 'wäre',
        'waeren' => 'wären',
        'wasser' => 'wasser',
        'wuerde' => 'würde',
        'wuerden' => 'würden',
        'zunaechst' => 'zunächst',
        'zusaetzlich' => 'zusätzlich',
    ];

    $lowerWord = mb_strtolower($word, 'UTF-8');
    if (!isset($map[$lowerWord])) {
        return $word;
    }

    $replacement = $map[$lowerWord];
    if (mb_strtoupper($word, 'UTF-8') === $word) {
        return mb_strtoupper($replacement, 'UTF-8');
    }

    $first = mb_substr($word, 0, 1, 'UTF-8');
    $rest = mb_substr($word, 1, null, 'UTF-8');
    if (mb_strtoupper($first, 'UTF-8') === $first && mb_strtolower($rest, 'UTF-8') === $rest) {
        return mb_strtoupper(mb_substr($replacement, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($replacement, 1, null, 'UTF-8');
    }

    return $replacement;
}

function german_expand_restore_umlauts_text(string $text): string
{
    return preg_replace_callback('/\b[\p{L}]+\b/u', static function(array $matches): string {
        return german_expand_restore_umlauts_word($matches[0]);
    }, $text) ?? $text;
}

function german_expand_restore_umlauts_recursive($value)
{
    if (is_string($value)) {
        return german_expand_restore_umlauts_text($value);
    }

    if (is_array($value)) {
        $normalized = [];
        foreach ($value as $key => $item) {
            $normalizedKey = is_string($key) ? german_expand_restore_umlauts_text($key) : $key;
            $normalized[$normalizedKey] = german_expand_restore_umlauts_recursive($item);
        }
        return $normalized;
    }

    return $value;
}

function german_expand_section(string $title, array $bullets = [], ?string $text = null, ?string $example = null, string $language = 'espanol'): array
{
    return [
        'title' => $title,
        'bullets' => $bullets,
        'text' => $text,
        'example' => $example,
        'language' => $language,
    ];
}

function german_expand_dialogue_block(string $title, array $lines, string $language = 'aleman', int $tts = 1): array
{
    $lines = array_values(array_filter(array_map(static fn($line): string => trim((string) $line), $lines), static fn(string $line): bool => $line !== ''));

    return [
        'tipo_bloque' => 'dialogo',
        'titulo' => $title,
        'contenido' => implode("\n", $lines),
        'idioma_bloque' => $language,
        'tts_habilitado' => $tts,
    ];
}

function german_expand_theory(
    string $title,
    int $duration,
    string $intro,
    array $sections,
    string $tip,
    ?string $scenario = null,
    array $mission = []
): array {
    return [
        'titulo' => $title,
        'duracion' => $duration,
        'intro' => $intro,
        'sections' => $sections,
        'tip' => $tip,
        'scenario' => $scenario,
        'mission' => $mission,
    ];
}

function german_expand_lesson(string $title, string $description, int $duration, array $theories, array $activities): array
{
    return [
        'titulo' => $title,
        'descripcion' => $description,
        'duracion' => $duration,
        'teoria' => $theories,
        'actividades' => $activities,
    ];
}

function german_expand_question(string $text, string $correct, string $wrongA, string $wrongB): array
{
    return [
        'texto' => $text,
        'opciones' => [
            ['texto' => $correct, 'es_correcta' => true],
            ['texto' => $wrongA, 'es_correcta' => false],
            ['texto' => $wrongB, 'es_correcta' => false],
        ],
    ];
}

function german_expand_mcq(string $title, string $description, string $instructions, array $questions, int $points = 18, int $time = 7): array
{
    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'opcion_multiple',
        'instrucciones' => $instructions,
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'pregunta_global' => $instructions,
            'preguntas' => $questions,
        ],
    ];
}

function german_expand_true_false(string $title, string $description, string $statement, string $correct = 'Verdadero', int $points = 10, int $time = 4): array
{
    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'verdadero_falso',
        'instrucciones' => 'Elige verdadero o falso.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'pregunta' => $statement,
            'respuesta_correcta' => $correct,
        ],
    ];
}

function german_expand_matching(string $title, string $description, array $pairs, int $points = 12, int $time = 6): array
{
    $normalizedPairs = [];
    foreach ($pairs as $pair) {
        $normalizedPairs[] = [
            'left' => $pair[0],
            'right' => $pair[1],
        ];
    }

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'emparejamiento',
        'instrucciones' => 'Empareja cada elemento con su pareja correcta.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'pares' => $normalizedPairs,
        ],
    ];
}

function german_expand_drag(string $title, string $description, array $solutionMap, int $points = 14, int $time = 6): array
{
    $targets = array_values(array_unique(array_values($solutionMap)));

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'arrastrar_soltar',
        'instrucciones' => 'Arrastra cada elemento al contenedor correcto.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'items' => array_keys($solutionMap),
            'targets' => $targets,
            'solucion' => $solutionMap,
        ],
    ];
}

function german_expand_order(string $title, string $description, array $sentences, int $points = 10, int $time = 5): array
{
    $items = [];
    foreach ($sentences as $index => $sentence) {
        $words = is_array($sentence) ? $sentence : preg_split('/\s+/', trim((string) $sentence));
        $items[] = [
            'id' => 'order_' . ($index + 1),
            'instruction' => 'Ordena la frase.',
            'items' => array_values(array_filter($words, static fn($word): bool => trim((string) $word) !== '')),
        ];
    }

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'ordenar_palabras',
        'instrucciones' => 'Ordena las palabras hasta formar una frase natural.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => $items,
    ];
}

function german_expand_fill(string $title, string $description, array $items, int $points = 12, int $time = 6): array
{
    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'completar_oracion',
        'instrucciones' => 'Escribe solo la palabra correcta en cada espacio.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => $items,
    ];
}

function german_expand_short(string $title, string $description, string $question, array $answers, string $placeholder = 'Escribe una palabra', int $points = 10, int $time = 4): array
{
    $answers = array_values(array_filter(array_map(static fn($item): string => trim((string) $item), $answers), static fn(string $item): bool => $item !== ''));

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'respuesta_corta',
        'instrucciones' => 'Escribe solo una palabra.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'pregunta' => $question,
            'respuesta_correcta' => $answers[0] ?? '',
            'respuestas_correctas' => $answers,
            'placeholder' => $placeholder,
        ],
    ];
}

function german_expand_pronunciation_focuses(string $phrase): array
{
    $normalized = german_expand_restore_umlauts_text(mb_strtolower(trim($phrase), 'UTF-8'));
    if ($normalized === '') {
        return [];
    }

    $focuses = [];
    $rules = [
        'ich' => 'ch suave',
        'ch' => 'ch aleman',
        'sch' => 'sch claro',
        'sp' => 'sp aleman',
        'st' => 'st aleman',
        'eu' => 'diptongo eu/aeu',
        'äu' => 'diptongo eu/aeu',
        'ei' => 'diptongo ei',
        'ie' => 'i larga',
        'ä' => 'vocal umlaut',
        'ö' => 'vocal umlaut',
        'ü' => 'vocal umlaut',
        'ä' => 'vocal umlaut',
        'ö' => 'vocal umlaut',
        'ü' => 'vocal umlaut',
        'r ' => 'r final suave',
        'tion' => 'terminacion -tion',
        'ig' => 'final -ig',
    ];

    foreach ($rules as $needle => $label) {
        if (mb_strpos($normalized, $needle, 0, 'UTF-8') !== false && !in_array($label, $focuses, true)) {
            $focuses[] = $label;
        }
    }

    if (preg_match('/\b(können|koennen|möchte|moechte|würde|wuerde|führt|fuehrt|für|fuer|über|ueber)\b/u', $normalized) === 1 && !in_array('vocales largas', $focuses, true)) {
        $focuses[] = 'vocales largas';
    }

    if (empty($focuses)) {
        $focuses[] = 'ritmo estable';
    }

    return array_slice($focuses, 0, 3);
}

function german_expand_pronunciation_hint(array $focuses): string
{
    if (empty($focuses)) {
        return 'Habla con pausas cortas y mantén cada palabra completa.';
    }

    return 'Pon atencion en ' . implode(', ', $focuses) . ' y evita correr el final de la frase.';
}

function german_expand_pronunciation(string $title, string $description, array $phrases, int $points = 15, int $time = 7): array
{
    $items = [];
    foreach ($phrases as $index => $phrase) {
        $focuses = german_expand_pronunciation_focuses((string) $phrase);
        $items[] = [
            'id' => 'pron_' . ($index + 1),
            'frase' => $phrase,
            'texto_tts' => $phrase,
            'idioma_objetivo' => 'aleman',
            'focos' => $focuses,
            'palabras_clave' => german_expand_extract_keywords((string) $phrase, 4),
            'pista' => german_expand_pronunciation_hint($focuses),
        ];
    }

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'pronunciacion',
        'instrucciones' => 'Activa el microfono y lee las frases con claridad.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => $items,
    ];
}

function german_expand_extract_keywords(string $text, int $limit = 5): array
{
    $text = german_expand_restore_umlauts_text(mb_strtolower(trim($text), 'UTF-8'));
    if ($text === '') {
        return [];
    }

    preg_match_all('/[\p{L}][\p{L}\p{N}-]*/u', $text, $matches);
    $stopwords = [
        'aber', 'auch', 'auch', 'dann', 'dass', 'dein', 'deine', 'dem', 'den', 'der',
        'die', 'ein', 'eine', 'einen', 'einer', 'einem', 'eines', 'erst', 'fuer', 'für', 'gut',
        'habe', 'haben', 'heute', 'ich', 'ihr', 'ihre', 'ihren', 'ihrem', 'ihres',
        'ist', 'mit', 'nicht', 'noch', 'nur', 'oder', 'sehr', 'sich', 'sie', 'sind',
        'spaeter', 'später', 'und', 'uns', 'von', 'weil', 'wir',
    ];

    $keywords = [];
    foreach ($matches[0] as $token) {
        $token = trim((string) $token);
        if ($token === '' || strlen($token) < 4 || in_array($token, $stopwords, true)) {
            continue;
        }
        if (!in_array($token, $keywords, true)) {
            $keywords[] = $token;
        }
        if (count($keywords) >= $limit) {
            break;
        }
    }

    return $keywords;
}

function german_expand_listening(string $title, string $description, string $transcription, int $points = 18, int $time = 7, array $evaluation = []): array
{
    $keywords = array_values(array_filter(array_map('strval', $evaluation['palabras_clave'] ?? german_expand_extract_keywords($transcription, 5)), static fn($item) => trim($item) !== ''));

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'escucha',
        'instrucciones' => 'Escucha el audio y escribe exactamente lo que oyes.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'texto_tts' => $transcription,
            'transcripcion' => $transcription,
            'idioma_objetivo' => 'aleman',
            'acepta_variantes_menores' => 1,
            'palabras_clave' => $keywords,
        ],
    ];
}

function german_expand_writing(string $title, string $description, string $topic, int $minWords, int $points = 22, int $time = 14, array $evaluation = []): array
{
    $defaultMinSentences = max(3, (int) ceil(max(1, $minWords) / 25));
    $criterios = array_values(array_filter(array_map('strval', $evaluation['criterios'] ?? []), static fn($item) => trim($item) !== ''));
    $keywords = array_values(array_filter(array_map('strval', $evaluation['palabras_clave'] ?? []), static fn($item) => trim($item) !== ''));
    $connectors = array_values(array_filter(array_map('strval', $evaluation['conectores_sugeridos'] ?? []), static fn($item) => trim($item) !== ''));
    $structure = array_values(array_filter(array_map('strval', $evaluation['estructura_sugerida'] ?? []), static fn($item) => trim($item) !== ''));

    return [
        'titulo' => $title,
        'descripcion' => $description,
        'tipo' => 'escritura',
        'instrucciones' => 'Escribe con frases completas y evita listas sueltas.',
        'puntos' => $points,
        'tiempo' => $time,
        'contenido' => [
            'tema' => $topic,
            'min_palabras' => $minWords,
            'idioma_objetivo' => 'aleman',
            'min_oraciones' => (int) ($evaluation['min_oraciones'] ?? $defaultMinSentences),
            'registro' => (string) ($evaluation['registro'] ?? 'neutral'),
            'criterios' => $criterios,
            'palabras_clave' => $keywords,
            'conectores_sugeridos' => $connectors,
            'estructura_sugerida' => $structure,
            'modelo_inicio' => trim((string) ($evaluation['modelo_inicio'] ?? '')),
        ],
    ];
}

function german_expand_language_hits(string $text, array $needles): int
{
    $hits = 0;
    foreach ($needles as $needle) {
        if (preg_match('/(^|\\s)' . preg_quote($needle, '/') . '(\\s|$)/u', $text) === 1) {
            $hits++;
        }
    }

    return $hits;
}

function german_expand_detect_language(string $content, string $fallback = 'espanol'): string
{
    $normalized = mb_strtolower(trim($content), 'UTF-8');
    if ($normalized === '') {
        return $fallback;
    }

    $normalized = ' ' . preg_replace('/[^\\p{L}\\p{N}\\s]+/u', ' ', $normalized) . ' ';

    $germanNeedles = [
        'ich', 'du', 'er', 'sie', 'wir', 'ihr', 'nicht', 'bitte', 'guten', 'morgen',
        'wie', 'woher', 'wo', 'wann', 'was', 'habe', 'hast', 'hat', 'haben', 'bin',
        'bist', 'ist', 'sind', 'komme', 'komme', 'heisse', 'heiße', 'zeit', 'danke', 'bitte',
        'moechte', 'möchte', 'wuerde', 'würde', 'koennen', 'können', 'kann', 'weil', 'dass', 'obwohl', 'rechnung',
        'termin', 'bahnhof', 'wohnung', 'familie', 'lernen', 'deutsch', 'artikel',
        'beitrag', 'zusammenfassend', 'folglich', 'zunaechst', 'abschliessend',
    ];
    $spanishNeedles = [
        'el', 'la', 'los', 'las', 'un', 'una', 'unas', 'unos', 'que', 'como', 'cuando',
        'donde', 'explica', 'explicacion', 'practica', 'consejo', 'errores', 'pregunta',
        'preguntas', 'responde', 'escribe', 'elige', 'cierra', 'usa', 'meta', 'salida',
        'claro', 'mejor', 'porque', 'antes', 'despues', 'deberias', 'puedes', 'frases',
    ];

    $germanScore = german_expand_language_hits($normalized, $germanNeedles);
    $spanishScore = german_expand_language_hits($normalized, $spanishNeedles);

    if (preg_match('/\\b(ich|wie|woher|hast|guten|bitte|nicht|dass|weil|koennen|können|zusammenfassend|heiße|möchte)\\b/u', $normalized) === 1) {
        $germanScore += 2;
    }

    if (preg_match('/\\b(explica|pregunta|escribe|elige|deberias|puedes|consejo)\\b/u', $normalized) === 1) {
        $spanishScore += 2;
    }

    if ($germanScore > $spanishScore) {
        return 'aleman';
    }

    if ($spanishScore > $germanScore) {
        return 'espanol';
    }

    return $fallback;
}

function german_expand_render_theory_html(array $theory): string
{
    $html = '<div class="theory-rich">';
    $html .= '<p>' . htmlspecialchars($theory['intro'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';

    foreach ((array) ($theory['sections'] ?? []) as $section) {
        $html .= '<h3>' . htmlspecialchars($section['title'] ?? '', ENT_QUOTES, 'UTF-8') . '</h3>';

        if (!empty($section['text'])) {
            $html .= '<p>' . htmlspecialchars((string) $section['text'], ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if (!empty($section['bullets'])) {
            $html .= '<ul>';
            foreach ((array) $section['bullets'] as $bullet) {
                $html .= '<li>' . htmlspecialchars((string) $bullet, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($section['example'])) {
            $html .= '<p><strong>Ejemplo:</strong> ' . htmlspecialchars((string) $section['example'], ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }

    if (!empty($theory['scenario'])) {
        $html .= '<div class="alert alert-light border mt-3"><strong>Escenario:</strong> ' . htmlspecialchars((string) $theory['scenario'], ENT_QUOTES, 'UTF-8') . '</div>';
    }

    if (!empty($theory['mission'])) {
        $html .= '<div class="alert alert-light border mt-3"><strong>Mision express:</strong><br>' . nl2br(htmlspecialchars(german_expand_lines((array) $theory['mission']), ENT_QUOTES, 'UTF-8')) . '</div>';
    }

    if (!empty($theory['tip'])) {
        $html .= '<div class="alert alert-light border mt-3"><strong>Coach tip:</strong> ' . htmlspecialchars((string) $theory['tip'], ENT_QUOTES, 'UTF-8') . '</div>';
    }

    $html .= '</div>';

    return $html;
}

function german_expand_theory_blocks(array $theory): array
{
    $blocks = [[
        'tipo_bloque' => 'explicacion',
        'titulo' => 'Panorama',
        'contenido' => $theory['intro'] ?? '',
        'idioma_bloque' => 'espanol',
        'tts_habilitado' => 1,
    ]];

    foreach ((array) ($theory['sections'] ?? []) as $section) {
        if (!empty($section['text'])) {
            $blocks[] = [
                'tipo_bloque' => 'explicacion',
                'titulo' => $section['title'] ?? 'Explicacion',
                'contenido' => (string) $section['text'],
                'idioma_bloque' => $section['language'] ?? german_expand_detect_language((string) $section['text'], 'espanol'),
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['bullets'])) {
            $title = (string) ($section['title'] ?? 'Bloques utiles');
            $blocks[] = [
                'tipo_bloque' => stripos($title, 'vocabulario') !== false ? 'vocabulario' : 'explicacion',
                'titulo' => $title,
                'contenido' => german_expand_lines((array) $section['bullets']),
                'idioma_bloque' => $section['language'] ?? german_expand_detect_language(implode(' ', (array) $section['bullets']), 'espanol'),
                'tts_habilitado' => 1,
            ];
        }

        if (!empty($section['example'])) {
            $blocks[] = [
                'tipo_bloque' => 'ejemplo',
                'titulo' => $section['title'] ?? 'Modelo',
                'contenido' => (string) $section['example'],
                'idioma_bloque' => $section['language'] ?? german_expand_detect_language((string) $section['example'], 'aleman'),
                'tts_habilitado' => 1,
            ];
        }
    }

    if (!empty($theory['scenario'])) {
        $blocks[] = [
            'tipo_bloque' => 'instruccion',
            'titulo' => 'Escenario de practica',
            'contenido' => (string) $theory['scenario'],
            'idioma_bloque' => 'espanol',
            'tts_habilitado' => 1,
        ];
    }

    if (!empty($theory['mission'])) {
        $blocks[] = [
            'tipo_bloque' => 'instruccion',
            'titulo' => 'Mision express',
            'contenido' => german_expand_lines((array) $theory['mission']),
            'idioma_bloque' => 'espanol',
            'tts_habilitado' => 1,
        ];
    }

    $blocks[] = [
        'tipo_bloque' => 'instruccion',
        'titulo' => 'Coach tip',
        'contenido' => $theory['tip'] ?? '',
        'idioma_bloque' => 'espanol',
        'tts_habilitado' => 1,
    ];

    return $blocks;
}

function german_expand_support_pack(string $scenario, array $mission, array $errors = [], array $checks = []): array
{
    $blocks = [
        [
            'tipo_bloque' => 'instruccion',
            'titulo' => 'Escenario de practica',
            'contenido' => $scenario,
            'idioma_bloque' => 'espanol',
            'tts_habilitado' => 1,
        ],
        [
            'tipo_bloque' => 'instruccion',
            'titulo' => 'Mision express',
            'contenido' => german_expand_lines($mission),
            'idioma_bloque' => 'espanol',
            'tts_habilitado' => 1,
        ],
    ];

    if (!empty($errors)) {
        $blocks[] = [
            'tipo_bloque' => 'explicacion',
            'titulo' => 'Error frecuente',
            'contenido' => german_expand_lines($errors),
            'idioma_bloque' => 'espanol',
            'tts_habilitado' => 1,
        ];
    }

    if (!empty($checks)) {
        $blocks[] = [
            'tipo_bloque' => 'instruccion',
            'titulo' => 'Chequeo rapido',
            'contenido' => german_expand_lines($checks),
            'idioma_bloque' => 'espanol',
            'tts_habilitado' => 1,
        ];
    }

    return $blocks;
}

function german_expand_course_profile(): array
{
    return [
        'titulo' => 'Aleman de Cero a Heroe: Ruta completa A1-C1',
        'descripcion' => 'Ruta extensa de aleman para hispanohablantes desde supervivencia A1 hasta precision C1. Suma pronunciacion, gramatica funcional, vida real, escucha, produccion escrita, expresion oral guiada, lectura academica y un plan visible de consolidacion.',
        'idioma' => 'aleman',
        'idioma_objetivo' => 'aleman',
        'idioma_base' => 'espanol',
        'idioma_ensenanza' => 'espanol',
        'nivel_cefr' => 'A1',
        'nivel_cefr_desde' => 'A1',
        'nivel_cefr_hasta' => 'C1',
        'modalidad' => 'perpetuo',
        'duracion_semanas' => 104,
        'es_publico' => 1,
        'requiere_codigo' => 0,
        'codigo_acceso' => null,
        'tipo_codigo' => null,
        'inscripcion_abierta' => 1,
        'fecha_cierre_inscripcion' => null,
        'max_estudiantes' => 1000,
        'estado' => 'activo',
        'estado_editorial' => 'publicado',
        'notificar_profesor_completada' => 1,
        'notificar_profesor_atascado' => 1,
    ];
}

function german_expand_existing_lesson_reframes(): array
{
    return [
        [
            'aliases' => ['Nivel A1: sonidos, presentaciones y primer contacto', 'Nivel A1.1: sonidos, saludos y primer contacto'],
            'titulo' => 'Nivel A1.1: sonidos, saludos y primer contacto',
            'descripcion' => 'Pronunciacion base, saludo, presentacion personal y primeras respuestas solidas para que el aleman no se sienta hostil desde el dia uno.',
            'orden' => 1,
        ],
        [
            'aliases' => ['Nivel A1-A2: compras, ciudad y verbos de accion', 'Nivel A1.4: compras, ciudad y verbos de accion'],
            'titulo' => 'Nivel A1.4: compras, ciudad y verbos de accion',
            'descripcion' => 'Acusativo, modalverben, ciudad y verbos de accion para pedir, pagar, orientarte y resolver escenas practicas con autonomia.',
            'orden' => 4,
        ],
        [
            'aliases' => ['Nivel A2: pasado, dativo y vida cotidiana', 'Nivel A2.1: pasado, dativo y vida cotidiana'],
            'titulo' => 'Nivel A2.1: pasado, dativo y vida cotidiana',
            'descripcion' => 'Perfekt, dativo, vivienda, salud y comparativos para contar lo que paso, pedir ayuda y describir mejor tu entorno.',
            'orden' => 5,
        ],
        [
            'aliases' => ['Nivel B1: opinion, subordinadas y mundo real', 'Nivel B1.1: opinion, subordinadas y mundo real'],
            'titulo' => 'Nivel B1.1: opinion, subordinadas y mundo real',
            'descripcion' => 'Conecta ideas, justifica opiniones y habla de trabajo, estudio y medios con estructuras que ya suenan a usuario intermedio.',
            'orden' => 9,
        ],
        [
            'aliases' => ['Nivel B2: debate, matices y lenguaje abstracto', 'Nivel B2.1: debate, matices y lenguaje abstracto'],
            'titulo' => 'Nivel B2.1: debate, matices y lenguaje abstracto',
            'descripcion' => 'Hipotesis, pasiva, nominalizacion y argumentacion para debatir con mas control, mas densidad y menos frases escolares.',
            'orden' => 13,
        ],
        [
            'aliases' => ['Nivel C1: precision, registro y ruta de consolidacion', 'Nivel C1.1: precision, registro y ruta de consolidacion'],
            'titulo' => 'Nivel C1.1: precision, registro y ruta de consolidacion',
            'descripcion' => 'Registro, discurso referido, lectura academica y consolidacion para sostener un C1 usable en contextos reales y exigentes.',
            'orden' => 16,
        ],
    ];
}

function german_expand_existing_theory_support(): array
{
    return [
        [
            'lesson_aliases' => ['Nivel A1: sonidos, presentaciones y primer contacto', 'Nivel A1.1: sonidos, saludos y primer contacto'],
            'theory_aliases' => ['Pronunciacion base y sonidos que mas bloquean'],
            'blocks' => german_expand_support_pack(
                'Escenario: te presentas a una clase donde todos detectan al instante si confundes ich y ach.',
                ['Distingue ich y ach.', 'Di Guten Morgen sin correr.', 'Presentate en una sola linea.'],
                ['Decir ik o ish en lugar de ich.', 'Leer sp y st como en espanol.', 'Ignorar la diferencia entre umlaut y vocal simple.'],
                ['Pronuncia Ich heisse ...', 'Pronuncia Ich komme aus ...', 'Cierra con Freut mich.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1: sonidos, presentaciones y primer contacto', 'Nivel A1.1: sonidos, saludos y primer contacto'],
            'theory_aliases' => ['Articulos, pronombres y el verbo sein'],
            'blocks' => german_expand_support_pack(
                'Escenario: una libreria solo te entrega el libro correcto si aciertas articulo y forma de sein.',
                ['Memoriza der Lehrer.', 'Memoriza die Stadt.', 'Crea una frase con ich bin.'],
                ['Aprender el sustantivo sin articulo.', 'Usar kein y nicht como si fueran lo mismo.', 'Mezclar ich bin con ich habe.'],
                ['Nombra tres articulos.', 'Haz una frase negativa.', 'Haz una frase con Sie sind.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1: sonidos, presentaciones y primer contacto', 'Nivel A1.1: sonidos, saludos y primer contacto'],
            'theory_aliases' => ['Preguntas personales, haben y microdialogos'],
            'blocks' => german_expand_support_pack(
                'Escenario: una persona te hace cinco preguntas seguidas y no puedes esconderte detras del espanol.',
                ['Responde Wie heisst du?', 'Responde Woher kommst du?', 'Responde Hast du Zeit?'],
                ['Quitar el verbo en la respuesta corta.', 'Responder la edad con haben.', 'Traducir palabra por palabra desde el espanol.'],
                ['Da tu nombre.', 'Da tu origen.', 'Di si tienes tiempo o no.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1-A2: compras, ciudad y verbos de accion', 'Nivel A1.4: compras, ciudad y verbos de accion'],
            'theory_aliases' => ['Acusativo funcional y objetos cotidianos'],
            'blocks' => german_expand_support_pack(
                'Escenario: compras cafe, mapa y ticket antes de perder el tranvia.',
                ['Pide un objeto con articulo.', 'Formula una compra con ich brauche.', 'Pregunta Hast du einen ...?'],
                ['Dejar el articulo en nominativo.', 'Pedir objetos sin articulo.', 'Memorizar tablas sin escena real.'],
                ['Pide un cafe.', 'Pide una tarjeta.', 'Pregunta por un boligrafo.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1-A2: compras, ciudad y verbos de accion', 'Nivel A1.4: compras, ciudad y verbos de accion'],
            'theory_aliases' => ['Modalverben y peticiones educadas'],
            'blocks' => german_expand_support_pack(
                'Escenario: pagas, preguntas y pides ayuda en una ciudad donde la cortesia importa.',
                ['Haz una pregunta con kann.', 'Expresa una necesidad con muss.', 'Pide algo con moechte.'],
                ['Usar moechten mal conjugado.', 'Confundir kann con muss.', 'Sonar brusco al pedir.'],
                ['Pregunta si puedes pagar con tarjeta.', 'Di que necesitas ayuda.', 'Pide algo con bitte.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A1-A2: compras, ciudad y verbos de accion', 'Nivel A1.4: compras, ciudad y verbos de accion'],
            'theory_aliases' => ['Ciudad, direcciones y verbos separables'],
            'blocks' => german_expand_support_pack(
                'Escenario: debes dar una direccion y una hora exacta antes de perder el ultimo tren.',
                ['Da una direccion con links o rechts.', 'Usa gegenueber o neben.', 'Di a que hora sale el tren.'],
                ['Olvidar la particula del verbo separable.', 'Confundir links y rechts.', 'Dar direcciones sin referencia.'],
                ['Di donde esta el Bahnhof.', 'Usa abfahren.', 'Menciona una hora.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2: pasado, dativo y vida cotidiana', 'Nivel A2.1: pasado, dativo y vida cotidiana'],
            'theory_aliases' => ['Perfekt para contar lo que hiciste'],
            'blocks' => german_expand_support_pack(
                'Escenario: cuentas un fin de semana movido y alguien detecta al instante si fallas el auxiliar.',
                ['Di una frase con bin gegangen.', 'Di otra con habe gearbeitet.', 'Anade gestern o am Wochenende.'],
                ['Elegir siempre habe.', 'Poner el participio en medio.', 'Olvidar el marcador temporal.'],
                ['Narra una accion de movimiento.', 'Narra una accion comun.', 'Usa un marcador temporal.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2: pasado, dativo y vida cotidiana', 'Nivel A2.1: pasado, dativo y vida cotidiana'],
            'theory_aliases' => ['Dativo funcional y preposiciones de alta frecuencia'],
            'blocks' => german_expand_support_pack(
                'Escenario: ayudas a alguien, vas con alguien y sales de algun lugar sin perder el caso.',
                ['Haz una frase con helfen.', 'Usa mit o bei.', 'Incluye una persona en dativo.'],
                ['Poner acusativo por costumbre.', 'Separar verbo y caso en la memoria.', 'Olvidar el posesivo correcto.'],
                ['Usa meiner o meinem.', 'Usa mit dem Bus.', 'Haz una frase con gefallen.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel A2: pasado, dativo y vida cotidiana', 'Nivel A2.1: pasado, dativo y vida cotidiana'],
            'theory_aliases' => ['Casa, salud y comparativos que si sirven'],
            'blocks' => german_expand_support_pack(
                'Escenario: visitas dos apartamentos, te duele algo y aun asi debes compararlos con calma.',
                ['Di que te duele algo.', 'Compara dos habitaciones.', 'Describe una vivienda con dos adjetivos.'],
                ['Decir mas grande como mas + adjetivo.', 'No usar la estructura de dolor fija.', 'Describir sin detalles concretos.'],
                ['Usa groesser.', 'Usa billiger.', 'Usa Mir tut ... weh.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1: opinion, subordinadas y mundo real', 'Nivel B1.1: opinion, subordinadas y mundo real'],
            'theory_aliases' => ['Subordinadas que organizan el pensamiento'],
            'blocks' => german_expand_support_pack(
                'Escenario: justificas por que estudias aleman mientras alguien solo acepta razones bien conectadas.',
                ['Usa weil.', 'Usa dass.', 'Haz un contraste con obwohl.'],
                ['Mantener el orden de una principal dentro de subordinada.', 'Encadenar conectores sin cerrar la idea.', 'Usar conectores solo para sonar avanzado.'],
                ['Formula una razon.', 'Formula una opinion.', 'Formula un contraste.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1: opinion, subordinadas y mundo real', 'Nivel B1.1: opinion, subordinadas y mundo real'],
            'theory_aliases' => ['Relativas, cortesia y estructuras de trabajo'],
            'blocks' => german_expand_support_pack(
                'Escenario: escribes a una oficina y necesitas sonar amable, claro y competente.',
                ['Formula Koennten Sie ...?', 'Describe una persona con der o die.', 'Pide informacion extra.'],
                ['Pedir cosas de forma demasiado directa.', 'Romper la relativa a mitad de frase.', 'Confundir formalidad con rigidez vacia.'],
                ['Haz una pregunta formal.', 'Usa una relativa.', 'Cierra con tacto.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B1: opinion, subordinadas y mundo real', 'Nivel B1.1: opinion, subordinadas y mundo real'],
            'theory_aliases' => ['Trabajo, estudio, medios y sociedad'],
            'blocks' => german_expand_support_pack(
                'Escenario: opinas sobre trabajo, estudio y redes sin quedarte en frases planas.',
                ['Usa Meiner Meinung nach.', 'Usa einerseits ... andererseits.', 'Cierra con una conclusion.'],
                ['Dar opinion sin justificar.', 'Repetir ich finde en cada frase.', 'No matizar una postura.'],
                ['Da tu postura.', 'Da una razon.', 'Introduce un matiz.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2: debate, matices y lenguaje abstracto', 'Nivel B2.1: debate, matices y lenguaje abstracto'],
            'theory_aliases' => ['Condicionales irreales y pasiva con funcion argumentativa'],
            'blocks' => german_expand_support_pack(
                'Escenario: moderas un debate y necesitas sonar B2 sin perder orden ni sentido.',
                ['Formula una hipotesis irreal.', 'Usa wuerde.', 'Haz una frase en pasiva.'],
                ['Mezclar condicion real e irreal.', 'Usar pasiva sin funcion comunicativa.', 'Llenar todo de conectores sin estructura.'],
                ['Usa wenn ich ... haette.', 'Usa einerseits ... andererseits.', 'Usa werden + participio.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2: debate, matices y lenguaje abstracto', 'Nivel B2.1: debate, matices y lenguaje abstracto'],
            'theory_aliases' => ['Nominalisierung, participios y densidad expresiva'],
            'blocks' => german_expand_support_pack(
                'Escenario: te exigen compactar ideas sin convertir el texto en un muro ilegible.',
                ['Forma un sustantivo con -ung.', 'Usa un participio atributivo.', 'Reescribe una frase con mas precision.'],
                ['Creer que densidad equivale a oscuridad.', 'Nominalizar todo sin criterio.', 'Copiar formulas abstractas sin entenderlas.'],
                ['Crea una nominalizacion.', 'Describe algo con participio.', 'Reformula una frase simple.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel B2: debate, matices y lenguaje abstracto', 'Nivel B2.1: debate, matices y lenguaje abstracto'],
            'theory_aliases' => ['Lexico de academia, negocios y cultura'],
            'blocks' => german_expand_support_pack(
                'Escenario: discutes tecnologia, cultura y politica con vocabulario menos domestico y mas preciso.',
                ['Usa tres palabras del campo academico.', 'Formula una postura.', 'Introduce una reserva o matiz.'],
                ['Usar lexico abstracto sin posicion clara.', 'Saltar entre temas sin hilo.', 'Cerrar sin conclusion.'],
                ['Nombra un concepto academico.', 'Nombra un concepto social.', 'Haz una conclusion breve.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1: precision, registro y ruta de consolidacion', 'Nivel C1.1: precision, registro y ruta de consolidacion'],
            'theory_aliases' => ['Registro, Konjunktiv y precision discursiva'],
            'blocks' => german_expand_support_pack(
                'Escenario: resumes la postura de otra persona sin sonar coloquial ni exagerado.',
                ['Reformula una cita con sei.', 'Usa folglich o demnach.', 'Mantiene tono formal en dos lineas.'],
                ['Confundir registro formal con frialdad vacia.', 'Usar Konjunktiv I de forma mecanica.', 'Mezclar cita directa e indirecta.'],
                ['Haz una reformulacion.', 'Usa un conector formal.', 'Evita coloquialismos.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1: precision, registro y ruta de consolidacion', 'Nivel C1.1: precision, registro y ruta de consolidacion'],
            'theory_aliases' => ['Word formation y lectura academica'],
            'blocks' => german_expand_support_pack(
                'Escenario: te lanzan un texto denso y solo sobrevives si detectas estructura y formacion de palabras.',
                ['Detecta un prefijo.', 'Detecta un sufijo.', 'Resume la tesis de un texto.'],
                ['Traducir palabra por palabra.', 'No detectar piezas morfologicas clave.', 'Leer sin mapear estructura.'],
                ['Marca un prefijo.', 'Marca un sufijo.', 'Ubica una tesis.']
            ),
        ],
        [
            'lesson_aliases' => ['Nivel C1: precision, registro y ruta de consolidacion', 'Nivel C1.1: precision, registro y ruta de consolidacion'],
            'theory_aliases' => ['Certificaciones, ritmo semanal y plan de 90 dias'],
            'blocks' => german_expand_support_pack(
                'Escenario: planificas 90 dias de estudio con objetivos que deban sobrevivir a la vida real.',
                ['Define un objetivo semanal.', 'Elige una forma de medirlo.', 'Reserva un bloque fijo de repaso.'],
                ['Hacer planes heroicos imposibles de sostener.', 'Medir avance solo por horas.', 'Dejar fuera escucha o escritura.'],
                ['Escribe un objetivo.', 'Escribe una metrica.', 'Escribe una rutina minima.']
            ),
        ],
    ];
}

function german_expand_existing_lesson_additions(): array
{
    return [
        [
            'lesson_aliases' => ['Nivel A1: sonidos, presentaciones y primer contacto', 'Nivel A1.1: sonidos, saludos y primer contacto'],
            'extra_theories' => [
                german_expand_theory(
                    'Alphabet, spelling y supervivencia en clase',
                    16,
                    'Antes de hablar mucho, el alumno necesita deletrear su nombre, entender instrucciones basicas y reconocer el alfabeto funcional.',
                    [
                        german_expand_section('Claves utiles', ['A, E, I, O, U', 'Wie schreibt man das?', 'Buchstabe', 'langsam bitte'], null, null, 'aleman'),
                        german_expand_section('Escenas de clase', ['Konnen Sie das wiederholen?', 'Wie schreibt man Ihren Namen?', 'Ich verstehe nicht ganz.'], null, null, 'aleman'),
                        german_expand_section('Modelo funcional', [], null, 'Mein Name ist Julia, J-U-L-I-A.', 'aleman'),
                    ],
                    'La supervivencia inicial mejora cuando el alumno sabe pedir repeticion y deletrear sin vergueenza.',
                    'Escenario: llenas un formulario y tienes que deletrear tu nombre y pedir que repitan una instruccion.',
                    ['Deletrea tu nombre.', 'Pide repeticion.', 'Di que no entendiste del todo.']
                ),
            ],
            'extra_activities' => [
                german_expand_matching(
                    'Empareja saludo y respuesta natural',
                    'Relaciona saludos frecuentes con la respuesta que mejor encaja.',
                    [
                        ['Guten Morgen', 'Morgen'],
                        ['Wie geht es dir?', 'Gut, danke'],
                        ['Freut mich', 'Mich auch'],
                        ['Bis spaeter', 'Bis spaeter'],
                    ]
                ),
                german_expand_pronunciation(
                    'Pronuncia tu primera mini presentacion',
                    'Lee frases cortas de saludo, nombre y origen con ritmo claro.',
                    [
                        'Guten Tag, ich heisse Laura.',
                        'Ich komme aus Chile.',
                        'Freut mich, Sie kennenzulernen.',
                    ]
                ),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel A1-A2: compras, ciudad y verbos de accion', 'Nivel A1.4: compras, ciudad y verbos de accion'],
            'extra_theories' => [
                german_expand_theory(
                    'Precio, cantidad y preguntas de mostrador',
                    15,
                    'Muchas escenas cotidianas se destraban cuando el alumno pregunta cuanto cuesta algo, cuantas unidades quiere y si hay una opcion disponible.',
                    [
                        german_expand_section('Preguntas utiles', ['Wie viel kostet das?', 'Wie viele brauchen Sie?', 'Haben Sie noch ...?'], null, null, 'aleman'),
                        german_expand_section('Cantidad minima', ['ein Kilo', 'zwei Flaschen', 'ein Stueck', 'eine Packung'], null, null, 'aleman'),
                        german_expand_section('Modelo de mostrador', [], null, 'Ich nehme zwei Brote und eine Flasche Wasser, bitte.', 'aleman'),
                    ],
                    'Cantidad y precio se fijan mejor dentro de un dialogo corto que dentro de una lista abstracta.',
                    'Escenario: compras rapido antes de que cierre la tienda y necesitas preguntar precio, cantidad y disponibilidad.',
                    ['Pregunta un precio.', 'Pide una cantidad.', 'Pregunta si queda una unidad.']
                ),
            ],
            'extra_activities' => [
                german_expand_drag(
                    'Lleva cada compra al lugar correcto',
                    'Coloca cada palabra en el espacio donde tiene mas sentido encontrarla.',
                    [
                        'Brot' => 'Baeckerei',
                        'Medikament' => 'Apotheke',
                        'Ticket' => 'Bahnhof',
                        'Kaffee' => 'Cafe',
                    ]
                ),
                german_expand_pronunciation(
                    'Pedir, pagar y preguntar con buena entonacion',
                    'Lee frases cortas de servicio y pago sin sonar cortado.',
                    [
                        'Ich moechte einen Tee, bitte.',
                        'Kann ich mit Karte zahlen?',
                        'Wo ist der Bahnhof?',
                    ]
                ),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel A2: pasado, dativo y vida cotidiana', 'Nivel A2.1: pasado, dativo y vida cotidiana'],
            'extra_theories' => [
                german_expand_theory(
                    'Citas, transporte y pequenas gestiones',
                    16,
                    'A2 se vuelve mucho mas util cuando el alumno puede fijar una cita, cambiar un plan y moverse por la ciudad con menos friccion.',
                    [
                        german_expand_section('Bloques de agenda', ['einen Termin haben', 'den Termin verschieben', 'um zehn Uhr', 'am Freitag'], null, null, 'aleman'),
                        german_expand_section('Frases de gestion', ['Ich komme spaeter.', 'Konnen wir den Termin verschieben?', 'Der Zug ist ausgefallen.'], null, null, 'aleman'),
                        german_expand_section('Modelo funcional', [], null, 'Ich habe morgen einen Termin, aber ich komme zehn Minuten spaeter.', 'aleman'),
                    ],
                    'Las gestiones cotidianas entrenan tiempo, cortes a y flexibilidad al mismo tiempo.',
                    'Escenario: tienes una cita, un retraso y la necesidad de avisar con claridad.',
                    ['Di que tienes una cita.', 'Di que llegas tarde.', 'Pide mover el horario.']
                ),
            ],
            'extra_activities' => [
                german_expand_matching(
                    'Preposicion y caso que toca',
                    'Relaciona cada bloque de alta frecuencia con la estructura correcta.',
                    [
                        ['mit', 'dem Freund'],
                        ['bei', 'der Arbeit'],
                        ['nach', 'dem Kurs'],
                        ['zu', 'meiner Mutter'],
                    ]
                ),
                german_expand_listening(
                    'Escucha: el fin de semana y el retraso',
                    'Escucha una micro historia sobre visita, retraso y llegada.',
                    'Am Wochenende habe ich meine Eltern besucht, aber der Zug war spaet und ich bin erst um neun Uhr angekommen.'
                ),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel B1: opinion, subordinadas y mundo real', 'Nivel B1.1: opinion, subordinadas y mundo real'],
            'extra_theories' => [
                german_expand_theory(
                    'Resumen oral y presentacion corta',
                    17,
                    'B1 ya pide sostener una mini presentacion: abrir, explicar una idea central y cerrar sin derrumbar la estructura.',
                    [
                        german_expand_section('Estructura util', ['Thema nennen', 'zwei Gruende geben', 'Beispiel einfuegen', 'kurz abschliessen'], null, null, 'aleman'),
                        german_expand_section('Frases de apoyo', ['Ich moechte kurz erklaeren...', 'Ein wichtiger Punkt ist...', 'Zum Schluss denke ich...'], null, null, 'aleman'),
                        german_expand_section('Modelo breve', [], null, 'Ich moechte kurz erklaeren, warum Onlinekurse praktisch sind. Sie sind flexibel und sparen Zeit.', 'aleman'),
                    ],
                    'Una mini presentacion clara empuja al alumno a ordenar mejor lo que ya sabe.',
                    'Escenario: debes hablar un minuto delante de un grupo pequeno sin leer un guion completo.',
                    ['Abre el tema.', 'Da dos ideas.', 'Cierra con una frase breve.']
                ),
            ],
            'extra_activities' => [
                german_expand_drag(
                    'Conector segun funcion',
                    'Arrastra cada conector a la funcion que mejor describe su uso principal.',
                    [
                        'weil' => 'causa',
                        'obwohl' => 'contraste',
                        'dass' => 'opinion o declaracion',
                        'wenn' => 'condicion o repeticion',
                    ]
                ),
                german_expand_pronunciation(
                    'Opinion breve con contraste',
                    'Lee frases cortas de opinion donde el contraste debe sonar natural.',
                    [
                        'Ich finde, dass Onlinekurse praktisch sind.',
                        'Obwohl ich muede bin, lerne ich weiter.',
                        'Meiner Meinung nach braucht man klare Ziele.',
                    ]
                ),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel B2: debate, matices y lenguaje abstracto', 'Nivel B2.1: debate, matices y lenguaje abstracto'],
            'extra_theories' => [
                german_expand_theory(
                    'Concesion, contraargumento y cierre elegante',
                    17,
                    'B2 suena mas serio cuando el alumno no solo afirma, sino que reconoce limites, concede algo y vuelve a su tesis.',
                    [
                        german_expand_section('Movimientos utiles', ['zwar ... aber', 'einerseits ... andererseits', 'dennoch', 'allerdings'], null, null, 'aleman'),
                        german_expand_section('Contraargumento limpio', ['Es gibt zwar Vorteile, aber...', 'Dennoch sollte man beachten, dass...'], null, null, 'aleman'),
                        german_expand_section('Modelo B2', [], null, 'Zwar spart digitale Arbeit Zeit, dennoch darf man ihre sozialen Kosten nicht ignorieren.', 'aleman'),
                    ],
                    'El contraargumento de calidad evita que el texto suene propagandistico o infantil.',
                    'Escenario: defiendes una postura y aun asi debes mostrar que entiendes la posicion contraria.',
                    ['Concede un punto.', 'Recupera tu tesis.', 'Cierra con equilibrio.']
                ),
            ],
            'extra_activities' => [
                german_expand_matching(
                    'Idea abstracta y ejemplo concreto',
                    'Relaciona conceptos B2 con el ejemplo que mejor los aterriza.',
                    [
                        ['Digitalisierung', 'veraendert den Arbeitsmarkt'],
                        ['Verantwortung', 'bedeutet Folgen mitzudenken'],
                        ['Globalisierung', 'verbindet Maerkte und Krisen'],
                        ['Analyse', 'ordnet Daten und Argumente'],
                    ],
                    12,
                    6
                ),
                german_expand_drag(
                    'Tesis, evidencia y matiz',
                    'Lleva cada frase al lugar que cumple en un argumento B2.',
                    [
                        'Digitale Bildung ist flexibel.' => 'tesis',
                        'Viele Studierende sparen Wegezeit.' => 'evidencia',
                        'Sie verlangt jedoch mehr Selbstdisziplin.' => 'matiz',
                        'Deshalb braucht sie gute acompanamiento.' => 'conclusion',
                    ],
                    14,
                    6
                ),
            ],
        ],
        [
            'lesson_aliases' => ['Nivel C1: precision, registro y ruta de consolidacion', 'Nivel C1.1: precision, registro y ruta de consolidacion'],
            'extra_theories' => [
                german_expand_theory(
                    'Resumen academico y toma de postura',
                    18,
                    'C1 no termina en entender textos: exige resumir con fidelidad, marcar distancia cuando hace falta y luego tomar una postura propia.',
                    [
                        german_expand_section('Pasos utiles', ['tesis', 'argumentos centrales', 'limites', 'posicion personal'], null, null, 'espanol'),
                        german_expand_section('Frases de sintesis', ['Der Text legt nahe, dass...', 'Zusammenfassend laesst sich sagen...', 'Ich wuerde allerdings ergaenzen, dass...'], null, null, 'aleman'),
                        german_expand_section('Modelo C1', [], null, 'Der Beitrag legt nahe, dass Innovation nur dann nachhaltig ist, wenn sie gesellschaftlich eingeordnet wird.', 'aleman'),
                    ],
                    'La sintesis fuerte conserva la estructura del autor sin copiarle la voz.',
                    'Escenario: resumes un texto academico y luego anades tu postura sin perder precision.',
                    ['Resume una tesis.', 'Resume dos argumentos.', 'Anade una posicion breve.']
                ),
            ],
            'extra_activities' => [
                german_expand_matching(
                    'Registro segun contexto',
                    'Relaciona el contexto con el tono o formula mas adecuada.',
                    [
                        ['correo academico', 'Sehr geehrte Damen und Herren'],
                        ['comentario informal', 'Ehrlich gesagt'],
                        ['resumen de una fuente', 'Der Autor betont, ...'],
                        ['conclusion formal', 'Folglich laesst sich sagen, dass ...'],
                    ],
                    12,
                    6
                ),
                german_expand_pronunciation(
                    'Declaracion formal con distancia',
                    'Lee frases de registro alto y manten la articulacion estable.',
                    [
                        'Der Autor betont, die Reform sei notwendig.',
                        'Folglich sollte die Debatte sachlicher gefuehrt werden.',
                        'Zusammenfassend laesst sich sagen, dass die Lage komplex ist.',
                    ],
                    16,
                    8
                ),
            ],
        ],
    ];
}
