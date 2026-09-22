<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/modelos/contenido.php';

requireAdmin();

$type = (string) ($_GET['tipo'] ?? $_POST['tipo'] ?? 'noticias');
$definition = contentType($type);
$type = array_search($definition, contentTypes(), true) ?: 'noticias';
$editingId = isset($_GET['editar']) ? (int) $_GET['editar'] : null;
$error = null;
$notice = null;
$editing = $editingId ? contentRow($type, $editingId) : null;
$authors = authors();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['accion'] ?? 'save');
    $postedId = (int) ($_POST['id'] ?? 0);

    try {
        if (!verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
            throw new InvalidArgumentException('La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.');
        }
        if ($action === 'delete' && $postedId > 0) {
            deleteContent($type, $postedId);
            $notice = 'El registro fue eliminado.';
        } else {
            if ($type === 'noticias' && $postedId === 0 && contentCount('noticias') >= 3) {
                throw new InvalidArgumentException('Actualidad ya tiene 3 noticias. Elimina una para poder agregar otra.');
            }
            $data = [];
            foreach (array_keys($definition['db']) as $field) {
                if ($field !== 'id') {
                    $data[$field] = trim((string) ($_POST[$field] ?? ''));
                }
            }
            if ($type === 'reportajes') {
                $data['es_destacado'] = isset($_POST['es_destacado']) ? 1 : 0;
                if ($data['es_destacado'] === 1 && featuredReportageCount($postedId > 0 ? $postedId : null) >= 3) {
                    throw new InvalidArgumentException('Solo puedes seleccionar 3 reportajes para mostrar en la portada.');
                }
            }

            $hasImageFile = false;
            foreach (['foto_portada', 'foto_principal', 'foto', 'imagen', 'archivo_pdf', 'pdf_adjunto'] as $fileField) {
                if (!isset($_FILES[$fileField]) || empty($_FILES[$fileField]['name'])) {
                    if ($postedId > 0) {
                        unset($data[$fileField]);
                    }
                    continue;
                }

                if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
                    throw new InvalidArgumentException('Hubo un problema al subir el archivo. Inténtalo de nuevo.');
                }

                $originalName = basename((string) $_FILES[$fileField]['name']);
                $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $originalName);
                $safeName = $safeName !== '' ? $safeName : 'archivo';
                $targetDir = str_contains($fileField, 'pdf') ? __DIR__ . '/../boletines/' : __DIR__ . '/../assets/images/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $targetPath = $targetDir . $safeName;
                if (file_exists($targetPath)) {
                    $targetPath = $targetDir . time() . '-' . $safeName;
                }
                if (!move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
                    throw new InvalidArgumentException('No se pudo guardar el archivo seleccionado.');
                }

                $storedValue = str_contains($fileField, 'pdf') ? 'boletines/' . basename($targetPath) : 'assets/images/' . basename($targetPath);
                $data[$fileField] = $storedValue;
                $hasImageFile = $fileField === 'foto';
            }

            $imageUrl = trim((string) ($_POST['foto_url'] ?? ''));
            if ($type === 'noticias' && $imageUrl !== '') {
                if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('La URL de la imagen no es válida.');
                }
                if (!$hasImageFile) {
                    $data['foto'] = $imageUrl;
                }
            }

            $videoImageUrl = trim((string) ($_POST['imagen_url'] ?? ''));
            if ($type === 'videos' && $videoImageUrl !== '') {
                if (!filter_var($videoImageUrl, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('La URL de la miniatura del video no es válida.');
                }
                if (!isset($_FILES['imagen']) || empty($_FILES['imagen']['name'])) {
                    $data['imagen'] = $videoImageUrl;
                }
            }

            if (($definition['title'] ?? null) && $data[$definition['title']] === '') {
                throw new InvalidArgumentException('El título es obligatorio.');
            }
            if (($definition['number'] ?? null) && (int) $data[$definition['number']] < 1) {
                throw new InvalidArgumentException('El número del boletín debe ser mayor que cero.');
            }
            if ($type === 'reportajes' && array_key_exists('autor', $_POST)) {
                $authorName = trim((string) ($_POST['autor'] ?? ''));
                if ($authorName === '') {
                    $data['autor_id'] = null;
                } else {
                    $data['autor_id'] = resolveAuthorId($authorName);
                }
            }
            if (($definition['author'] ?? null) && (int) ($data[$definition['author']] ?? 0) < 1) {
                $data[$definition['author']] = null;
            }
            if (($definition['embed'] ?? null) && $data[$definition['embed']] === '') {
                throw new InvalidArgumentException('Agrega el enlace del audio o video.');
            }
            saveContent($type, $data, $postedId > 0 ? $postedId : null);
            $notice = $postedId > 0 ? 'El registro fue actualizado.' : 'El registro fue creado.';
            $editing = null;
            $editingId = null;
        }
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'No se pudo guardar el contenido. Revisa los datos e inténtalo nuevamente.';
    }
}

$rows = contentRows($type);
require __DIR__ . '/vistas/contenido.php';