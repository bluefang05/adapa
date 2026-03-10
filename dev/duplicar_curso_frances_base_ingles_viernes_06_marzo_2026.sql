START TRANSACTION;

-- ============================================================
-- Duplica curso existente de frances y crea variante base ingles
-- Fuente:  Frances Zero to Hero: Sante & Sourires
-- Destino: Frances Zero to Hero: Sante & Sourires (Base Ingles)
-- ============================================================

SET @instancia_id := 1;
SET @source_title := 'Frances Zero to Hero: Sante & Sourires';
SET @target_title := 'Frances Zero to Hero: Sante & Sourires (Base Ingles)';

SET @source_course_id := NULL;
SET @target_course_id := NULL;

SELECT id INTO @source_course_id
FROM cursos
WHERE instancia_id = @instancia_id
  AND titulo COLLATE utf8mb4_unicode_ci = @source_title COLLATE utf8mb4_unicode_ci
LIMIT 1;

-- Crear curso destino si no existe
SELECT id INTO @target_course_id
FROM cursos
WHERE instancia_id = @instancia_id
  AND titulo COLLATE utf8mb4_unicode_ci = @target_title COLLATE utf8mb4_unicode_ci
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
    c.instancia_id, c.plantilla_pensum_id, c.creado_por,
    @target_title,
    CONCAT(c.descripcion, ' | Variante creada para estudiantes con base en ingles.'),
    c.idioma, c.idioma_objetivo, 'ingles', 'ingles',
    c.portada_media_id,
    c.nivel_cefr_desde, c.nivel_cefr_hasta, c.nivel_cefr,
    c.modalidad, c.fecha_inicio, c.fecha_fin, c.duracion_semanas,
    c.es_publico, c.requiere_codigo, c.codigo_acceso, c.tipo_codigo,
    c.inscripcion_abierta, c.fecha_cierre_inscripcion, c.max_estudiantes,
    c.estado, c.notificar_profesor_completada, c.notificar_profesor_atascado
FROM cursos c
WHERE c.id = @source_course_id
  AND @source_course_id IS NOT NULL
  AND @target_course_id IS NULL;

SET @target_course_id := COALESCE(@target_course_id, LAST_INSERT_ID());

-- ============================================================
-- Lecciones
-- ============================================================
INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado)
SELECT
    @target_course_id,
    l.titulo,
    l.descripcion,
    l.orden,
    l.duracion_minutos,
    l.es_obligatoria,
    l.estado
FROM lecciones l
WHERE l.curso_id = @source_course_id
  AND @source_course_id IS NOT NULL
  AND @target_course_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM lecciones x
      WHERE x.curso_id = @target_course_id
        AND x.orden = l.orden
  );

-- ============================================================
-- Teoria (mapeo por orden de leccion)
-- ============================================================
INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, url_recurso, duracion_minutos, orden, es_interactivo)
SELECT
    lt.id,
    t.titulo,
    t.contenido,
    t.tipo_contenido,
    t.url_recurso,
    t.duracion_minutos,
    t.orden,
    t.es_interactivo
FROM teoria t
JOIN lecciones ls ON ls.id = t.leccion_id
JOIN lecciones lt ON lt.curso_id = @target_course_id AND lt.orden = ls.orden
WHERE ls.curso_id = @source_course_id
  AND @source_course_id IS NOT NULL
  AND @target_course_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM teoria tx
      WHERE tx.leccion_id = lt.id
        AND tx.titulo = t.titulo
  );

-- ============================================================
-- Actividades (mapeo por orden de leccion)
-- ============================================================
INSERT INTO actividades (
    leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
    puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable,
    orden, estado
)
SELECT
    lt.id,
    a.titulo,
    a.descripcion,
    a.tipo_actividad,
    a.instrucciones,
    a.contenido,
    a.puntos_maximos,
    a.tiempo_limite_minutos,
    a.intentos_permitidos,
    a.es_calificable,
    a.orden,
    a.estado
FROM actividades a
JOIN lecciones ls ON ls.id = a.leccion_id
JOIN lecciones lt ON lt.curso_id = @target_course_id AND lt.orden = ls.orden
WHERE ls.curso_id = @source_course_id
  AND @source_course_id IS NOT NULL
  AND @target_course_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM actividades ax
      WHERE ax.leccion_id = lt.id
        AND ax.titulo = a.titulo
  );

COMMIT;

-- ============================================================
-- Validacion
-- ============================================================
SELECT id, titulo, idioma, idioma_base, idioma_ensenanza, estado
FROM cursos
WHERE id IN (@source_course_id, @target_course_id);

SELECT COUNT(*) AS lecciones_destino
FROM lecciones
WHERE curso_id = @target_course_id;

SELECT
    COUNT(DISTINCT t.id) AS teorias_destino,
    COUNT(DISTINCT a.id) AS actividades_destino
FROM lecciones l
LEFT JOIN teoria t ON t.leccion_id = l.id
LEFT JOIN actividades a ON a.leccion_id = l.id
WHERE l.curso_id = @target_course_id;
