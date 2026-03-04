<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/MediaRecurso.php';

class MediaController extends Controller
{
    private $mediaModel;

    public function __construct()
    {
        $this->requireRole('profesor');
        $this->mediaModel = new MediaRecurso();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->subir();
            return;
        }

        $this->view('profesor/recursos/index', [
            'recursos' => $this->mediaModel->obtenerRecursosPorProfesor(Auth::getUserId(), Auth::getInstanciaId()),
        ]);
    }

    public function delete($id)
    {
        $this->requirePost();
        require_csrf();

        $profesorId = Auth::getUserId();
        $instanciaId = Auth::getInstanciaId();
        $recurso = $this->mediaModel->obtenerRecursoPorId($id, $profesorId, $instanciaId);

        if (!$recurso) {
            $this->flash('error', 'Recurso no encontrado.');
            $this->redirect('/profesor/recursos');
        }

        $uso = $this->mediaModel->obtenerResumenUso($id, $profesorId, $instanciaId);
        if (($uso->total_usos ?? 0) > 0) {
            $partes = [];
            if (!empty($uso->cursos_portada)) {
                $partes[] = $uso->cursos_portada . ' portada(s) de curso';
            }
            if (!empty($uso->bloques_contenido)) {
                $partes[] = $uso->bloques_contenido . ' bloque(s) de contenido';
            }

            $this->flash('error', 'No puedes eliminar este recurso porque esta en uso en ' . implode(' y ', $partes) . '.');
            $this->redirect('/profesor/recursos');
        }

        $absolutePath = dirname(__DIR__, 2) . '/' . ltrim($recurso->ruta_archivo, '/');

        if (!$this->mediaModel->eliminarRecurso($id, $profesorId, $instanciaId)) {
            $this->flash('error', 'No se pudo eliminar el recurso.');
            $this->redirect('/profesor/recursos');
        }

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }

        $this->flash('success', 'Recurso eliminado.');
        $this->redirect('/profesor/recursos');
    }

    private function subir()
    {
        require_csrf();

        if (empty($_FILES['archivo']) || !is_uploaded_file($_FILES['archivo']['tmp_name'])) {
            $this->flash('error', 'Selecciona un archivo valido.');
            $this->redirect('/profesor/recursos');
        }

        $archivo = $_FILES['archivo'];
        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $this->flash('error', 'No se pudo subir el archivo.');
            $this->redirect('/profesor/recursos');
        }

        $mimeType = mime_content_type($archivo['tmp_name']) ?: ($archivo['type'] ?? 'application/octet-stream');
        $tipoMedia = $this->resolverTipoMedia($mimeType, $archivo['name']);

        if (!$tipoMedia) {
            $this->flash('error', 'Tipo de archivo no permitido.');
            $this->redirect('/profesor/recursos');
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($archivo['name'], PATHINFO_FILENAME));
        $safeBaseName = trim($safeBaseName, '-');
        if ($safeBaseName === '') {
            $safeBaseName = 'recurso';
        }

        $relativeDir = 'assets/uploads/media';
        $absoluteDir = dirname(__DIR__, 2) . '/' . $relativeDir;
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $fileName = $safeBaseName . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
        $absolutePath = $absoluteDir . '/' . $fileName;
        $relativePath = $relativeDir . '/' . $fileName;

        if (!move_uploaded_file($archivo['tmp_name'], $absolutePath)) {
            $this->flash('error', 'No se pudo guardar el archivo subido.');
            $this->redirect('/profesor/recursos');
        }

        $titulo = trim($_POST['titulo'] ?? '');
        if ($titulo === '') {
            $titulo = pathinfo($archivo['name'], PATHINFO_FILENAME);
        }

        $this->mediaModel->crearRecurso([
            'instancia_id' => Auth::getInstanciaId(),
            'profesor_id' => Auth::getUserId(),
            'titulo' => $titulo,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'tipo_media' => $tipoMedia,
            'ruta_archivo' => $relativePath,
            'mime_type' => $mimeType,
            'idioma' => trim($_POST['idioma'] ?? ''),
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'metadata' => json_encode([
                'original_name' => $archivo['name'],
                'size' => (int) ($archivo['size'] ?? 0),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $this->flash('success', 'Recurso subido correctamente.');
        $this->redirect('/profesor/recursos');
    }

    private function resolverTipoMedia($mimeType, $fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (str_starts_with($mimeType, 'image/')) {
            return 'imagen';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        $documentExtensions = ['doc', 'docx', 'txt', 'ppt', 'pptx', 'xls', 'xlsx'];
        if (in_array($extension, $documentExtensions, true)) {
            return 'documento';
        }

        return null;
    }
}
