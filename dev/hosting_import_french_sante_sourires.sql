START TRANSACTION;

-- ============================================================
-- ADAPA import: Frances Zero to Hero: Sante & Sourires
-- Compatible with current schema (cursos/lecciones/teoria/actividades)
-- ============================================================

SET @instancia_id := 1;
SET @creado_por := 13;
SET @curso_titulo := 'Frances Zero to Hero: Sante & Sourires';

SET @curso_id := NULL;
SELECT id INTO @curso_id
FROM cursos
WHERE instancia_id = @instancia_id
  AND titulo COLLATE utf8mb4_unicode_ci = @curso_titulo COLLATE utf8mb4_unicode_ci
LIMIT 1;

INSERT INTO cursos (
    instancia_id, plantilla_pensum_id, creado_por,
    titulo, descripcion,
    idioma, idioma_objetivo, idioma_base, idioma_ensenanza,
    portada_media_id,
    nivel_cefr_desde, nivel_cefr_hasta, nivel_cefr,
    modalidad, fecha_inicio, fecha_fin, duracion_semanas,
    es_publico, requiere_codigo, codigo_acceso, tipo_codigo,
    inscripcion_abierta, fecha_cierre_inscripcion, max_estudiantes,
    estado, notificar_profesor_completada, notificar_profesor_atascado
)
SELECT
    @instancia_id, NULL, @creado_por,
    @curso_titulo,
    'Ruta completa para hispanohablantes de A1 a B1 con enfoque practico, humor ligero y modulos de salud: alimentacion, ejercicio, estres, consulta medica y bienestar.',
    'frances', 'frances', 'espanol', 'espanol',
    NULL,
    'A1', 'B1', 'A1',
    'perpetuo', CURDATE(), NULL, 24,
    1, 0, NULL, NULL,
    1, NULL, 500,
    'activo', 1, 1
WHERE @curso_id IS NULL;

SET @curso_id := COALESCE(@curso_id, LAST_INSERT_ID());

-- ============================================================
-- 1) LECCIONES (12)
-- ============================================================

INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado)
SELECT @curso_id, x.titulo, x.descripcion, x.orden, x.duracion_minutos, 1, 'publicada'
FROM (
    SELECT 1 AS orden, 'Lecon 1: Bonjour et les Sons' AS titulo, 'Saludos basicos y sonidos iniciales del frances.' AS descripcion, 75 AS duracion_minutos
    UNION ALL SELECT 2, 'Lecon 2: Qui suis-je?', 'Presentaciones personales, origen y nacionalidad.', 80
    UNION ALL SELECT 3, 'Lecon 3: Manger et Sante', 'Comida, partitivos y decisiones saludables.', 85
    UNION ALL SELECT 4, 'Lecon 4: Les Nombres et le Marche', 'Numeros, precios y compras basicas.', 80
    UNION ALL SELECT 5, 'Lecon 5: Ma Routine Quotidienne', 'Rutina diaria y verbos reflexivos.', 85
    UNION ALL SELECT 6, 'Lecon 6: Le Corps et le Sport', 'Cuerpo, ejercicio y expresion de dolor.', 85
    UNION ALL SELECT 7, 'Lecon 7: Dans la Ville', 'Direcciones, movilidad y transporte.', 80
    UNION ALL SELECT 8, 'Lecon 8: Le Passe Compose', 'Pasado reciente con avoir/etre y participios.', 90
    UNION ALL SELECT 9, 'Lecon 9: Chez le Medecin', 'Sintomas, farmacia e interaccion medica.', 90
    UNION ALL SELECT 10, 'Lecon 10: Emotions et Stress', 'Estados emocionales y bienestar mental.', 85
    UNION ALL SELECT 11, 'Lecon 11: Conseils et Obligations', 'Il faut, devoir y consejos saludables.', 85
    UNION ALL SELECT 12, 'Lecon 12: Mon Projet Sante', 'Proyecto integrador final de vida saludable.', 95
) AS x
WHERE NOT EXISTS (
    SELECT 1
    FROM lecciones l
    WHERE l.curso_id = @curso_id
      AND l.orden = x.orden
);

-- ============================================================
-- 2) TEORIA (2 por leccion)
-- ============================================================

INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, url_recurso, duracion_minutos, orden, es_interactivo)
SELECT
    l.id,
    'Marco conceptual',
    CONCAT(
        '<div class=\"theory-rich\"><p>',
        'Objetivo de la leccion: ', l.titulo, '.',
        '</p><h3>Bloques clave</h3><ul>',
        '<li>Vocabulario funcional</li><li>Estructura gramatical minima</li><li>Uso en situacion real</li>',
        '</ul><div class=\"alert alert-light border mt-3\"><strong>Coach tip:</strong> Menos teoria abstracta y mas frase util.</div></div>'
    ),
    'texto', NULL, 12, 1, 0
FROM lecciones l
WHERE l.curso_id = @curso_id
  AND NOT EXISTS (
      SELECT 1 FROM teoria t
      WHERE t.leccion_id = l.id
        AND t.titulo = 'Marco conceptual'
  );

INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, url_recurso, duracion_minutos, orden, es_interactivo)
SELECT
    l.id,
    'Aplicacion practica',
    CONCAT(
        '<div class=\"theory-rich\"><p>',
        'Esta parte conecta la leccion con tareas reales del dia a dia.',
        '</p><h3>Aplicacion</h3><ul>',
        '<li>Hablar con claridad</li><li>Tomar decisiones simples</li><li>Expresar salud y rutina</li>',
        '</ul><p><strong>Micro reto:</strong> Crea dos frases propias usando el foco de ', l.titulo, '.</p></div>'
    ),
    'texto', NULL, 12, 2, 0
FROM lecciones l
WHERE l.curso_id = @curso_id
  AND NOT EXISTS (
      SELECT 1 FROM teoria t
      WHERE t.leccion_id = l.id
        AND t.titulo = 'Aplicacion practica'
  );

-- ============================================================
-- 3) ACTIVIDADES (4 por leccion = 48 total)
-- ============================================================

INSERT INTO actividades (
    leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
    puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado
)
SELECT
    l.id,
    CONCAT('L', LPAD(l.orden, 2, '0'), ' / Opcion multiple'),
    'Chequeo de decision rapida.',
    'opcion_multiple',
    'Selecciona la mejor respuesta en cada caso.',
    '{"pregunta_global":"Elige la opcion correcta.","preguntas":[{"texto":"Selecciona la frase mas natural.","opciones":[{"texto":"Opcion A correcta","es_correcta":true},{"texto":"Opcion B","es_correcta":false},{"texto":"Opcion C","es_correcta":false}]},{"texto":"Selecciona la expresion con mejor uso.","opciones":[{"texto":"Uso recomendado","es_correcta":true},{"texto":"Uso incorrecto","es_correcta":false},{"texto":"Uso forzado","es_correcta":false}]}]}',
    15, 6, 3, 1, 1, 'activa'
FROM lecciones l
WHERE l.curso_id = @curso_id
  AND NOT EXISTS (
      SELECT 1 FROM actividades a
      WHERE a.leccion_id = l.id
        AND a.titulo = CONCAT('L', LPAD(l.orden, 2, '0'), ' / Opcion multiple')
  );

INSERT INTO actividades (
    leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
    puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado
)
SELECT
    l.id,
    CONCAT('L', LPAD(l.orden, 2, '0'), ' / Completar oracion'),
    'Completa estructura clave de la leccion.',
    'completar_oracion',
    'Escribe la palabra o expresion faltante.',
    CONCAT(
        '[{"id":"fr_l', l.orden, '_g1","oracion":"Frase 1 de practica ____ .","respuesta_correcta":"correcta"},',
        '{"id":"fr_l', l.orden, '_g2","oracion":"Frase 2 de practica ____ .","respuesta_correcta":"correcta"}]'
    ),
    12, 5, 3, 1, 2, 'activa'
FROM lecciones l
WHERE l.curso_id = @curso_id
  AND NOT EXISTS (
      SELECT 1 FROM actividades a
      WHERE a.leccion_id = l.id
        AND a.titulo = CONCAT('L', LPAD(l.orden, 2, '0'), ' / Completar oracion')
  );

INSERT INTO actividades (
    leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
    puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado
)
SELECT
    l.id,
    CONCAT('L', LPAD(l.orden, 2, '0'), ' / Verdadero o falso'),
    'Validacion de comprension clave.',
    'verdadero_falso',
    'Marca verdadero o falso.',
    CONCAT(
        '{"pregunta":"En ', REPLACE(l.titulo, '\"', ''), ' se prioriza claridad y uso real.","respuesta_correcta":"Verdadero"}'
    ),
    10, 3, 3, 1, 3, 'activa'
FROM lecciones l
WHERE l.curso_id = @curso_id
  AND NOT EXISTS (
      SELECT 1 FROM actividades a
      WHERE a.leccion_id = l.id
        AND a.titulo = CONCAT('L', LPAD(l.orden, 2, '0'), ' / Verdadero o falso')
  );

INSERT INTO actividades (
    leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
    puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado
)
SELECT
    l.id,
    CONCAT('L', LPAD(l.orden, 2, '0'), ' / Escritura guiada'),
    'Produccion corta orientada a comunicacion real.',
    'escritura',
    'Escribe un texto breve usando el foco de la leccion.',
    CONCAT(
        '{"tema":"Escribe un texto de 70 a 120 palabras aplicando ', REPLACE(l.titulo, '\"', ''), ' con enfoque practico y tono natural.","min_palabras":70}'
    ),
    20, 12, 3, 1, 4, 'activa'
FROM lecciones l
WHERE l.curso_id = @curso_id
  AND NOT EXISTS (
      SELECT 1 FROM actividades a
      WHERE a.leccion_id = l.id
        AND a.titulo = CONCAT('L', LPAD(l.orden, 2, '0'), ' / Escritura guiada')
  );

COMMIT;

-- ============================================================
-- VALIDACION POST-IMPORT
-- ============================================================

-- 1) Curso
SELECT id, titulo, idioma, idioma_objetivo, estado, modalidad, es_publico, max_estudiantes
FROM cursos
WHERE titulo COLLATE utf8mb4_unicode_ci = 'Frances Zero to Hero: Sante & Sourires' COLLATE utf8mb4_unicode_ci
  AND instancia_id = 1;

-- 2) Lecciones (debe ser 12)
SELECT COUNT(*) AS total_lecciones
FROM lecciones
WHERE curso_id = (
    SELECT id FROM cursos
    WHERE titulo COLLATE utf8mb4_unicode_ci = 'Frances Zero to Hero: Sante & Sourires' COLLATE utf8mb4_unicode_ci
      AND instancia_id = 1
    LIMIT 1
);

-- 3) Teoria por leccion (debe ser 2 por leccion)
SELECT l.orden, l.titulo, COUNT(t.id) AS teorias
FROM lecciones l
LEFT JOIN teoria t ON t.leccion_id = l.id
WHERE l.curso_id = (
    SELECT id FROM cursos
    WHERE titulo COLLATE utf8mb4_unicode_ci = 'Frances Zero to Hero: Sante & Sourires' COLLATE utf8mb4_unicode_ci
      AND instancia_id = 1
    LIMIT 1
)
GROUP BY l.id, l.orden, l.titulo
ORDER BY l.orden;

-- 4) Actividades por leccion (debe ser 4 por leccion)
SELECT l.orden, l.titulo, COUNT(a.id) AS actividades
FROM lecciones l
LEFT JOIN actividades a ON a.leccion_id = l.id
WHERE l.curso_id = (
    SELECT id FROM cursos
    WHERE titulo COLLATE utf8mb4_unicode_ci = 'Frances Zero to Hero: Sante & Sourires' COLLATE utf8mb4_unicode_ci
      AND instancia_id = 1
    LIMIT 1
)
GROUP BY l.id, l.orden, l.titulo
ORDER BY l.orden;

-- 5) JSON invalido en actividades (debe devolver 0 filas)
SELECT id, titulo, tipo_actividad
FROM actividades
WHERE leccion_id IN (
    SELECT id FROM lecciones
    WHERE curso_id = (
        SELECT id FROM cursos
        WHERE titulo COLLATE utf8mb4_unicode_ci = 'Frances Zero to Hero: Sante & Sourires' COLLATE utf8mb4_unicode_ci
          AND instancia_id = 1
        LIMIT 1
    )
)
  AND JSON_VALID(contenido) = 0;
