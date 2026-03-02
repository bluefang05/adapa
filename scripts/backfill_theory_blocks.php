<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=adapa;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function detect_block_type(string $title, string $text): string
{
    $haystack = mb_strtolower(trim($title . ' ' . $text), 'UTF-8');

    if (str_contains($haystack, 'example:') || str_contains($haystack, 'example')) {
        return 'ejemplo';
    }

    if (str_contains($haystack, 'tip')) {
        return 'instruccion';
    }

    if (str_contains($haystack, 'vocabulary')) {
        return 'vocabulario';
    }

    return 'explicacion';
}

function normalize_text(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function theory_blocks_from_html(string $html, string $idiomaEnsenanza): array
{
    $html = trim($html);
    if ($html === '') {
        return [];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<!DOCTYPE html><html><body><div id="root">' . $html . '</div></body></html>';
    $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $dom->getElementById('root');
    if (!$root) {
        return [];
    }

    $blocks = [];
    $currentTitle = null;

    foreach ($root->childNodes as $node) {
        if (!($node instanceof DOMElement)) {
            continue;
        }

        $tag = strtolower($node->tagName);
        $text = normalize_text($node->textContent ?? '');

        if ($text === '') {
            continue;
        }

        if (in_array($tag, ['h1', 'h2', 'h3', 'h4'], true)) {
            $currentTitle = $text;
            continue;
        }

        if ($tag === 'p') {
            $blocks[] = [
                'tipo_bloque' => detect_block_type((string) $currentTitle, $text),
                'titulo' => $currentTitle,
                'contenido' => $text,
                'idioma_bloque' => $idiomaEnsenanza,
                'tts_habilitado' => 0,
                'media_id' => null,
            ];
            $currentTitle = null;
            continue;
        }

        if ($tag === 'ul' || $tag === 'ol') {
            $items = [];
            foreach ($node->getElementsByTagName('li') as $li) {
                $item = normalize_text($li->textContent ?? '');
                if ($item !== '') {
                    $items[] = '- ' . $item;
                }
            }

            if ($items) {
                $blocks[] = [
                    'tipo_bloque' => detect_block_type((string) $currentTitle, implode(' ', $items)),
                    'titulo' => $currentTitle,
                    'contenido' => implode("\n", $items),
                    'idioma_bloque' => $idiomaEnsenanza,
                    'tts_habilitado' => 0,
                    'media_id' => null,
                ];
            }

            $currentTitle = null;
            continue;
        }

        if (in_array($tag, ['div', 'section', 'article'], true)) {
            $blocks[] = [
                'tipo_bloque' => detect_block_type((string) $currentTitle, $text),
                'titulo' => $currentTitle,
                'contenido' => $text,
                'idioma_bloque' => $idiomaEnsenanza,
                'tts_habilitado' => 0,
                'media_id' => null,
            ];
            $currentTitle = null;
        }
    }

    return array_values(array_filter($blocks, function ($block) {
        return !empty($block['contenido']);
    }));
}

$teorias = $pdo->query("
    SELECT
        t.id,
        t.titulo,
        t.contenido,
        COALESCE(c.idioma_ensenanza, 'espanol') AS idioma_ensenanza
    FROM teoria t
    INNER JOIN lecciones l ON l.id = t.leccion_id
    INNER JOIN cursos c ON c.id = l.curso_id
    WHERE NOT EXISTS (
        SELECT 1
        FROM contenido_bloques cb
        WHERE cb.teoria_id = t.id
    )
    ORDER BY t.id ASC
")->fetchAll();

$insert = $pdo->prepare("
    INSERT INTO contenido_bloques (
        teoria_id,
        tipo_bloque,
        titulo,
        contenido,
        idioma_bloque,
        tts_habilitado,
        media_id,
        orden
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$pdo->beginTransaction();

$summary = [];

foreach ($teorias as $teoria) {
    $blocks = theory_blocks_from_html(
        $teoria['contenido'] ?? '',
        $teoria['idioma_ensenanza'] ?? 'espanol'
    );
    if (!$blocks) {
        continue;
    }

    foreach ($blocks as $index => $block) {
        $insert->execute([
            $teoria['id'],
            $block['tipo_bloque'],
            $block['titulo'],
            $block['contenido'],
            $block['idioma_bloque'],
            $block['tts_habilitado'],
            $block['media_id'],
            $index + 1,
        ]);
    }

    $summary[] = [
        'teoria_id' => (int) $teoria['id'],
        'titulo' => $teoria['titulo'],
        'bloques' => count($blocks),
    ];
}

$pdo->commit();

echo json_encode([
    'teorias_actualizadas' => count($summary),
    'detalle' => $summary,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
