CREATE TABLE IF NOT EXISTS lesson_issue_reports (
    id INT(11) NOT NULL AUTO_INCREMENT,
    instancia_id INT(11) DEFAULT NULL,
    usuario_id INT(11) NOT NULL,
    curso_id INT(11) DEFAULT NULL,
    leccion_id INT(11) DEFAULT NULL,
    actividad_id INT(11) DEFAULT NULL,
    context_type ENUM('general', 'leccion', 'actividad') NOT NULL DEFAULT 'general',
    issue_type VARCHAR(64) NOT NULL DEFAULT 'otro',
    description TEXT NOT NULL,
    reference_url VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status ENUM('nuevo', 'en_revision', 'resuelto') NOT NULL DEFAULT 'nuevo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_issue_reports_status (status),
    KEY idx_issue_reports_created_at (created_at),
    KEY idx_issue_reports_leccion_id (leccion_id),
    KEY idx_issue_reports_actividad_id (actividad_id),
    KEY idx_issue_reports_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS lesson_issue_report_notes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    issue_report_id INT(11) NOT NULL,
    admin_user_id INT(11) NOT NULL,
    note TEXT NOT NULL,
    is_private TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_issue_report_notes_report (issue_report_id),
    KEY idx_issue_report_notes_admin (admin_user_id),
    KEY idx_issue_report_notes_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT(11) NOT NULL AUTO_INCREMENT,
    instancia_id INT(11) DEFAULT NULL,
    admin_user_id INT(11) NOT NULL,
    action_type VARCHAR(64) NOT NULL,
    target_type VARCHAR(64) NOT NULL,
    target_id INT(11) DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    metadata_json TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_activity_instancia (instancia_id),
    KEY idx_admin_activity_admin (admin_user_id),
    KEY idx_admin_activity_action (action_type),
    KEY idx_admin_activity_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
