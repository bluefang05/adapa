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
            'returnTo' => trim((string) ($_GET['return_to'] ?? '')),
            'resourceContext' => trim((string) ($_GET['context'] ?? '')),
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

        $externalUrl = trim((string) ($_POST['url_externa'] ?? ''));
        $hasUpload = !empty($_FILES['archivo']) && is_uploaded_file($_FILES['archivo']['tmp_name']);

        if (!$hasUpload && $externalUrl === '') {
            $this->flash('error', 'Selecciona un archivo o pega un enlace valido.');
            $this->redirect('/profesor/recursos');
        }

        $mimeType = null;
        $tipoMedia = null;
        $relativePath = null;
        $metadata = [];
        $originalName = null;

        if ($hasUpload) {
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

            $metadata = [
                'source' => 'upload',
                'original_name' => $archivo['name'],
                'size' => (int) ($archivo['size'] ?? 0),
            ];
            $originalName = pathinfo($archivo['name'], PATHINFO_FILENAME);
        } else {
            $external = $this->resolverRecursoExterno($externalUrl);
            if (!$external) {
                $this->flash('error', 'El enlace externo no es compatible todavia. Usa YouTube o una URL directa a un archivo valido.');
                $this->redirect('/profesor/recursos');
            }

            $tipoMedia = $external['tipo_media'];
            $mimeType = $external['mime_type'];
            $relativePath = $external['ruta_archivo'];
            $metadata = $external['metadata'];
            $originalName = $external['titulo_sugerido'];
        }

        $titulo = trim($_POST['titulo'] ?? '');
        if ($titulo === '') {
            $titulo = $originalName ?: 'Recurso externo';
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
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $this->flash('success', 'Recurso subido correctamente.');
        $this->redirect('/profesor/recursos');
    }

    private function resolverTipoMedia($mimeType, $fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($extension === 'svg' || $mimeType === 'image/svg+xml') {
            return null;
        }

        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $audioMimes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a', 'audio/aac'];
        $videoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'];

        if (in_array($mimeType, $imageMimes, true)) {
            return 'imagen';
        }

        if (in_array($mimeType, $audioMimes, true)) {
            return 'audio';
        }

        if (in_array($mimeType, $videoMimes, true)) {
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

    private function resolverRecursoExterno($externalUrl)
    {
        if (!filter_var($externalUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $youtubeEmbed = app_youtube_embed_url($externalUrl);
        if ($youtubeEmbed) {
            return [
                'tipo_media' => 'video',
                'mime_type' => 'video/external',
                'ruta_archivo' => $externalUrl,
                'titulo_sugerido' => 'Video de YouTube',
                'metadata' => [
                    'source' => 'youtube',
                    'embed_url' => $youtubeEmbed,
                ],
            ];
        }

        $path = strtolower((string) parse_url($externalUrl, PHP_URL_PATH));
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = [
            'jpg' => ['imagen', 'image/external'],
            'jpeg' => ['imagen', 'image/external'],
            'png' => ['imagen', 'image/external'],
            'gif' => ['imagen', 'image/external'],
            'webp' => ['imagen', 'image/external'],
            'mp3' => ['audio', 'audio/external'],
            'wav' => ['audio', 'audio/external'],
            'ogg' => ['audio', 'audio/external'],
            'm4a' => ['audio', 'audio/external'],
            'aac' => ['audio', 'audio/external'],
            'mp4' => ['video', 'video/external'],
            'webm' => ['video', 'video/external'],
            'mov' => ['video', 'video/external'],
            'avi' => ['video', 'video/external'],
            'pdf' => ['pdf', 'application/pdf'],
        ];

        if (!isset($map[$extension])) {
            return null;
        }

        return [
            'tipo_media' => $map[$extension][0],
            'mime_type' => $map[$extension][1],
            'ruta_archivo' => $externalUrl,
            'titulo_sugerido' => 'Recurso externo',
            'metadata' => [
                'source' => 'external_url',
            ],
        ];
    }
}
