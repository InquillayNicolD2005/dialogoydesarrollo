<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/config/database.php';

$type = (string) ($_GET['tipo'] ?? 'noticias');
$id = (int) ($_GET['id'] ?? 0);
$definitions = [
    'noticias' => ['title' => 'Actualidad', 'table' => 'noticias', 'image' => 'foto', 'description' => 'descripcion', 'link' => 'link_externo'],
    'reportajes' => ['title' => 'Reportaje', 'table' => 'reportajes', 'image' => 'foto_principal', 'description' => 'desarrollo'],
    'boletines' => ['title' => 'Boletín NTEP', 'table' => 'boletines', 'image' => 'foto_portada', 'description' => 'resumen', 'link' => 'archivo_pdf'],
    'podcasts' => ['title' => 'Podcast', 'table' => 'podcasts', 'description' => 'descripcion', 'link' => 'url_embed'],
    'videos' => ['title' => 'Video', 'table' => 'videos', 'image' => 'imagen', 'description' => 'descripcion', 'link' => 'url_embed'],
];
$definition = $definitions[$type] ?? $definitions['noticias'];
$query = database()->prepare(sprintf('SELECT * FROM `%s` WHERE id = :id LIMIT 1', $definition['table']));
$query->execute(['id' => $id]);
$row = $query->fetch();

if (!$row) {
    http_response_code(404);
    $row = ['titulo' => 'Contenido no encontrado', 'fecha_publicacion' => null];
}

function detailValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$title = $type === 'boletines' ? 'Boletín N.º ' . (string) ($row['numero_boletin'] ?? '') : (string) ($row['titulo'] ?? 'Sin título');
$image = (string) ($row[$definition['image'] ?? ''] ?? '');
$description = (string) ($row[$definition['description'] ?? ''] ?? '');
$descriptionHtml = $type === 'reportajes'
    ? strip_tags($description, '<p><br><strong><em><u><ul><ol><li><h3><h4>')
    : nl2br(detailValue($description));
$link = (string) ($row[$definition['link'] ?? ''] ?? '');
$author = '';
if ($type === 'reportajes' && !empty($row['autor_id'])) {
    $authorQuery = database()->prepare('SELECT CONCAT(nombres, " ", ap_paterno) FROM autores WHERE id = :id LIMIT 1');
    $authorQuery->execute(['id' => (int) $row['autor_id']]);
    $author = (string) ($authorQuery->fetchColumn() ?: '');
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= detailValue($title) ?> | DDP Noticias</title><link rel="stylesheet" href="css/base.css"><link rel="stylesheet" href="css/main.css"></head>
<body>
<header id="site-header" class="fixed-top"><div class="container"><nav class="navbar navbar-expand-lg stroke"><a class="navbar-brand" href="index.html"><img src="assets/images/logo.png" alt="DDP Noticias" style="height:75px;"></a><div class="navbar-collapse"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link" href="index.html">Inicio</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=noticias">Actualidad</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=reportajes">Reportajes</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=podcasts">Podcast</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=boletines">Boletín NTEP</a></li><li class="nav-item"><a class="nav-link" href="sobreD&D.html">Sobre D&amp;D</a></li></ul></div></nav></div></header>
<section class="breadcrumb-area py-sm-5 py-4"><div class="container"><div class="breadcrumb-contents"><h2 class="title-big"><?= detailValue($definition['title']) ?></h2><div class="breadcrumb"><ul><li><a href="index.html">Inicio</a></li><li class="active"><?= detailValue($definition['title']) ?></li></ul></div></div></div></section>
<main class="py-5"><div class="container"><article class="blog-info" style="max-width:900px;margin:0 auto"><h5><?= !empty($row['fecha_publicacion']) ? detailValue(date('d/m/Y', strtotime((string) $row['fecha_publicacion']))): '' ?></h5><h1 class="title-big"><?= detailValue($title) ?></h1><?php if ($author !== ''): ?><p>Por <?= detailValue($author) ?></p><?php endif; ?><?php if ($image !== ''): ?><img src="<?= detailValue($image) ?>" alt="<?= detailValue($title) ?>" class="img-fluid radius-image mb-4"><?php endif; ?><?php if ($description !== ''): ?><div class="mt-4 report-content"><?= $descriptionHtml ?></div><?php endif; ?><?php if ($link !== ''): ?><p class="mt-4"><a class="btn btn-style btn-primary" target="_blank" rel="noopener noreferrer" href="<?= detailValue($link) ?>"><?= in_array($type, ['podcasts', 'videos'], true) ? 'Abrir contenido' : ($type === 'boletines' ? 'Ver boletín' : 'Ver enlace original') ?></a></p><?php endif; ?><p class="mt-4"><a href="contenido.php?tipo=<?= detailValue($type) ?>">Volver a <?= detailValue($definition['title']) ?></a></p></article></div></main>
</body>
</html>
