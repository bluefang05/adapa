<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';

class IssueReportController extends Controller {
    private $db;

    public function __construct() {
        $this->requireRole(['estudiante', 'profesor', 'admin']);
        $this->db = new Database();
    }

    public function store() {
        $this->requirePost();
        require_csrf();

        $contextType = trim((string) ($_POST['context_type'] ?? 'general'));
        $issueType = trim((string) ($_POST['issue_type'] ?? 'otro'));
        $description = trim((string) ($_POST['description'] ?? ''));
        $returnTo = trim((string) ($_POST['return_to'] ?? '/estudiante'));

        $allowedContext = ['general', 'leccion', 'actividad'];
        $allowedIssue = ['contenido_incorrecto', 'error_visual', 'boton_no_funciona', 'audio_video', 'otro'];

        if (!in_array($contextType, $allowedContext, true)) {
            $contextType = 'general';
        }

        if (!in_array($issueType, $allowedIssue, true)) {
            $issueType = 'otro';
        }

        if (mb_strlen($description) < 12) {
            $this->flash('error', 'Describe el fallo con al menos 12 caracteres para poder revisarlo.');
            $this->redirect($this->safeReturnTo($returnTo));
        }

        $cursoId = $this->nullableInt($_POST['curso_id'] ?? null);
        $leccionId = $this->nullableInt($_POST['leccion_id'] ?? null);
        $actividadId = $this->nullableInt($_POST['actividad_id'] ?? null);
        $instanciaId = Auth::getInstanciaId();
        $usuarioId = Auth::getUserId();
        $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

        try {
            $this->db->query(
                'INSERT INTO lesson_issue_reports
                (instancia_id, usuario_id, curso_id, leccion_id, actividad_id, context_type, issue_type, description, reference_url, user_agent, status)
                VALUES
                (:instancia_id, :usuario_id, :curso_id, :leccion_id, :actividad_id, :context_type, :issue_type, :description, :reference_url, :user_agent, :status)'
            );
            $this->db->bind(':instancia_id', $instanciaId);
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':curso_id', $cursoId);
            $this->db->bind(':leccion_id', $leccionId);
            $this->db->bind(':actividad_id', $actividadId);
            $this->db->bind(':context_type', $contextType);
            $this->db->bind(':issue_type', $issueType);
            $this->db->bind(':description', $description);
            $this->db->bind(':reference_url', $returnTo);
            $this->db->bind(':user_agent', $userAgent);
            $this->db->bind(':status', 'nuevo');
            $this->db->execute();

            $this->flash('success', 'Gracias. Recibimos tu reporte y lo revisaremos pronto.');
        } catch (Exception $e) {
            error_log('Issue report store error: ' . $e->getMessage());
            if ($this->isMissingIssueReportTable($e)) {
                $this->flash('error', 'El modulo de reportes aun no esta habilitado en este entorno.');
            } else {
                $this->flash('error', 'No se pudo enviar el reporte en este momento. Intenta de nuevo.');
            }
        }

        $this->redirect($this->safeReturnTo($returnTo));
    }

    private function nullableInt($value) {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function safeReturnTo($value) {
        $value = (string) $value;
        if ($value === '') {
            return '/estudiante';
        }

        foreach (['/estudiante', '/profesor', '/admin'] as $allowedPrefix) {
            if (strpos($value, $allowedPrefix) === 0) {
                return $value;
            }
        }

        return Auth::isProfesor() ? '/profesor/cursos' : (Auth::isAdmin() ? '/admin' : '/estudiante');
    }

    private function isMissingIssueReportTable(Exception $e) {
        $message = strtolower($e->getMessage());
        return strpos($message, 'lesson_issue_reports') !== false
            && (strpos($message, 'doesn\'t exist') !== false || strpos($message, '1146') !== false);
    }
}
