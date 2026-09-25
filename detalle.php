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
$query = database()->prepare(sprintf("SELECT * FROM `%s` WHERE id = :id AND estado = 'publicado' LIMIT 1", $definition['table']));
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
$latestReportages = [];
if ($type === 'reportajes' && !empty($row['autor_id'])) {
    $authorQuery = database()->prepare('SELECT CONCAT(nombres, " ", ap_paterno) FROM autores WHERE id = :id LIMIT 1');
    $authorQuery->execute(['id' => (int) $row['autor_id']]);
    $author = (string) ($authorQuery->fetchColumn() ?: '');
}
if ($type === 'reportajes' && !empty($row['id'])) {
    $latestQuery = database()->prepare("SELECT id, titulo, fecha_publicacion FROM reportajes WHERE estado = 'publicado' AND id <> :id ORDER BY fecha_publicacion DESC, id DESC LIMIT 3");
    $latestQuery->execute(['id' => (int) $row['id']]);
    $latestReportages = $latestQuery->fetchAll();
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= detailValue($title) ?> | DDP Noticias</title><link rel="stylesheet" href="css/base.css"><link rel="stylesheet" href="css/main.css"></head>
<body>
<header id="site-header" class="fixed-top"><div class="container"><nav class="navbar navbar-expand-lg stroke"><a class="navbar-brand" href="index.html"><img src="assets/images/logo.png" alt="DDP Noticias" style="height:75px;"></a><div class="navbar-collapse"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link" href="index.html">Inicio</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=noticias">Actualidad</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=reportajes">Reportajes</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=podcasts">Podcast</a></li><li class="nav-item"><a class="nav-link" href="contenido.php?tipo=boletines">Boletín NTEP</a></li><li class="nav-item"><a class="nav-link" href="sobreD&D.html">Sobre D&amp;D</a></li></ul></div></nav></div></header>
<section class="breadcrumb-area py-sm-5 py-4"><div class="container"><div class="breadcrumb-contents"><h2 class="title-big"><?= detailValue($definition['title']) ?></h2><div class="breadcrumb"><ul><li><a href="index.html">Inicio</a></li><li class="active"><?php if ($type === 'reportajes'): ?><a href="contenido.php?tipo=reportajes">Reportajes</a><?php else: ?><?= detailValue($definition['title']) ?><?php endif; ?></li></ul></div></div></div></section>
<?php if ($type === 'reportajes'): ?>
<section class="w3l-blog mt-lg-5">
    <div class="text-element-9 py-5 mt-lg-5">
        <div class="container py-lg-3">
            <div class="row grid-text-9">
                <div class="col-lg-8">
                    <article class="blog-single-post">
                        <div class="post-content">
                            <h1 class="title-single mb-3"><?= detailValue($title) ?></h1>
                            <p class="mb-3">
                                <?php if (!empty($row['fecha_publicacion'])): ?><?= detailValue(date('d/m/Y', strtotime((string) $row['fecha_publicacion']))) ?><?php endif; ?>
                                <?php if ($author !== ''): ?><?= !empty($row['fecha_publicacion']) ? ' | ' : '' ?>Por <?= detailValue($author) ?><?php endif; ?>
                            </p>
                        </div>
                        <?php if (!empty($row['resumen_corto'])): ?>
                        <blockquote class="blockquote my-4">
                            <p class="mb-0"><?= detailValue((string) $row['resumen_corto']) ?></p>
                        </blockquote>
                        <?php endif; ?>
                        <?php if ($image !== ''): ?>
                        <div class="single-post-image mb-4 text-center">
                            <img src="<?= detailValue($image) ?>" alt="<?= detailValue($title) ?>" class="img-fluid w-100 radius-image">
                        </div>
                        <?php endif; ?>
                        <?php if ($description !== ''): ?>
                        <div class="single-post-content mt-4 mb-5 report-content"><?= $descriptionHtml ?></div>
                        <?php endif; ?>
                        <?php if (!empty($row['pdf_adjunto'])): ?>
                        <p class="mb-4"><a class="btn btn-style btn-primary" target="_blank" rel="noopener noreferrer" href="<?= detailValue((string) $row['pdf_adjunto']) ?>">Ver documento adjunto</a></p>
                        <?php endif; ?>
                        <nav class="post-navigation row mb-5 py-4">
                            <div class="post-prev col-md-6 pr-sm-5">
                                <span class="nav-title"><span class="fa fa-arrow-left mr-2"></span><a href="contenido.php?tipo=reportajes">Volver a reportajes</a></span>
                            </div>
                        </nav>
                    </article>
                </div>
                <aside class="col-lg-4 left-text-9 mt-lg-0 mt-5 pl-lg-4">
                    <div class="left-top-9 mt-5 pt-sm-3">
                        <h2 class="heading-small-text-9 mb-3">Últimos reportajes</h2>
                        <?php foreach ($latestReportages as $latest): ?>
                        <a href="detalle.php?tipo=reportajes&amp;id=<?= (int) $latest['id'] ?>" class="p-post d-block py-2">
                            <h3 class="text-left-inner-9"><?= detailValue((string) $latest['titulo']) ?></h3>
                            <?php if (!empty($latest['fecha_publicacion'])): ?><span class="sub-inner-text-9"><?= detailValue(date('d/m/Y', strtotime((string) $latest['fecha_publicacion']))) ?></span><?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="categories mt-5 pt-sm-3">
                        <h2 class="heading-small-text-9">Secciones</h2>
                        <ul><li><a href="contenido.php?tipo=reportajes">Todos los reportajes</a></li><li><a href="index.html">Inicio</a></li></ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<main class="py-5"><div class="container"><article class="blog-info" style="max-width:900px;margin:0 auto"><h5><?= !empty($row['fecha_publicacion']) ? detailValue(date('d/m/Y', strtotime((string) $row['fecha_publicacion']))): '' ?></h5><h1 class="title-big"><?= detailValue($title) ?></h1><?php if ($author !== ''): ?><p>Por <?= detailValue($author) ?></p><?php endif; ?><?php if ($image !== ''): ?><img src="<?= detailValue($image) ?>" alt="<?= detailValue($title) ?>" class="img-fluid radius-image mb-4"><?php endif; ?><?php if ($description !== ''): ?><div class="mt-4 report-content"><?= $descriptionHtml ?></div><?php endif; ?><?php if ($link !== ''): ?><p class="mt-4"><a class="btn btn-style btn-primary" target="_blank" rel="noopener noreferrer" href="<?= detailValue($link) ?>"><?= in_array($type, ['podcasts', 'videos'], true) ? 'Abrir contenido' : ($type === 'boletines' ? 'Ver boletín' : 'Ver enlace original') ?></a></p><?php endif; ?><p class="mt-4"><a href="contenido.php?tipo=<?= detailValue($type) ?>">Volver a <?= detailValue($definition['title']) ?></a></p></article></div></main>
<?php endif; ?>
<?php if ($type === 'reportajes'): ?>
<section class="w3l-footer-29-main py-5" id="footer">
    <div class="footer-29 py-md-3">
        <div class="container">
            <div class="row footer-top-29">
                <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
                    <h2 class="footer-title-29">Quiénes Somos</h2>
                    <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                </div>
                <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
                    <h2 class="footer-title-29">Contenido</h2>
                    <ul><li><a href="contenido.php?tipo=noticias">Actualidad</a></li><li><a href="contenido.php?tipo=reportajes">Reportajes</a></li><li><a href="contenido.php?tipo=podcasts">Podcast</a></li></ul>
                </div>
                <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
                    <h2 class="footer-title-29">Contacto</h2>
                    <ul><li><a href="mailto:info@dialogoydesarrollo.com.pe">info@dialogoydesarrollo.com.pe</a></li></ul>
                </div>
            </div>
            <div class="bottom-copies text-center"><p class="copy-footer-29">© 2026 Diálogo y Desarrollo Perú.</p></div>
        </div>
    </div>
</section>
<?php endif; ?>
</body>
</html>
