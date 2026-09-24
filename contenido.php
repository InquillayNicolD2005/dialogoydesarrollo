<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/config/database.php';

$type = (string) ($_GET['tipo'] ?? 'noticias');
$definitions = [
    'noticias' => ['title' => 'Actualidad', 'table' => 'noticias', 'id' => 'id', 'name' => 'titulo', 'date' => 'fecha_publicacion', 'image' => 'foto', 'link' => 'link_externo'],
    'reportajes' => ['title' => 'Reportajes', 'table' => 'reportajes', 'id' => 'id', 'name' => 'titulo', 'date' => 'fecha_publicacion', 'image' => 'foto_principal', 'summary' => 'resumen_corto'],
    'boletines' => ['title' => 'Boletines NTEP', 'table' => 'boletines', 'id' => 'id', 'name' => 'numero_boletin', 'date' => 'fecha_publicacion', 'image' => 'foto_portada', 'pdf' => 'archivo_pdf'],
    'podcasts' => ['title' => 'Podcasts', 'table' => 'podcasts', 'id' => 'id', 'name' => 'titulo', 'date' => 'fecha_publicacion', 'link' => 'url_embed'],
    'videos' => ['title' => 'Videos', 'table' => 'videos', 'id' => 'id', 'name' => 'titulo', 'date' => 'fecha_publicacion', 'link' => 'url_embed'],
];
$definition = $definitions[$type] ?? $definitions['noticias'];
$limit = $type === 'noticias' ? ' LIMIT 3' : '';
$rows = database()->query(sprintf("SELECT * FROM `%s` WHERE estado = 'publicado' ORDER BY `%s` DESC, `%s` DESC%s", $definition['table'], $definition['date'], $definition['id'], $limit))->fetchAll();

function publicValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function publicDate(?string $value): string
{
    return $value ? date('d/m/Y', strtotime($value)) : 'Sin fecha';
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= publicValue($definition['title']) ?> | DDP Noticias</title><link rel="stylesheet" href="css/base.css"><link rel="stylesheet" href="css/main.css"></head>
<body>
<header id="site-header" class="fixed-top"><div class="container"><nav class="navbar navbar-expand-lg stroke"><a class="navbar-brand" href="index.html"><img src="assets/images/logo.png" alt="DDP Noticias" style="height:75px;"></a><div class="navbar-collapse"><ul class="navbar-nav ml-auto"><li class="nav-item"><a class="nav-link" href="index.html">Inicio</a></li><li class="nav-item<?= $type === 'noticias' ? ' active' : '' ?>"><a class="nav-link" href="contenido.php?tipo=noticias">Actualidad</a></li><li class="nav-item<?= $type === 'reportajes' ? ' active' : '' ?>"><a class="nav-link" href="contenido.php?tipo=reportajes">Reportajes</a></li><li class="nav-item<?= $type === 'boletines' ? ' active' : '' ?>"><a class="nav-link" href="contenido.php?tipo=boletines">Boletín NTEP</a></li><li class="nav-item<?= $type === 'podcasts' ? ' active' : '' ?>"><a class="nav-link" href="contenido.php?tipo=podcasts">Podcast</a></li><li class="nav-item<?= $type === 'videos' ? ' active' : '' ?>"><a class="nav-link" href="contenido.php?tipo=videos">Videos</a></li><li class="nav-item"><a class="nav-link" href="sobreD&D.html">Sobre D&amp;D</a></li></ul></div></nav></div></header>
<section class="breadcrumb-area py-sm-5 py-4"><div class="container"><div class="breadcrumb-contents"><h2 class="title-big"><?= publicValue($definition['title']) ?></h2><div class="breadcrumb"><ul><li><a href="index.html">Inicio</a></li><li class="active"><?= publicValue($definition['title']) ?></li></ul></div></div></div></section>
<div class="grids-block-5 py-5"><section class="py-lg-4 py-md-3"><div class="container"><div class="row"><?php if ($rows): foreach ($rows as $row): ?>
<?php
    $isMedia = in_array($type, ['podcasts', 'videos'], true);
    $target = $definition['link'] ?? ($definition['pdf'] ?? '');
    $url = (string) ($target !== '' ? ($row[$target] ?? '') : '');
    $image = $isMedia ? '' : (string) ($row[$definition['image']] ?? '');
    $title = $type === 'boletines' ? 'Boletín N.º ' . (string) ($row[$definition['name']] ?? '') : (string) ($row[$definition['name']] ?? '');
    $detailUrl = 'detalle.php?tipo=' . rawurlencode($type) . '&id=' . (int) ($row['id'] ?? 0);
?><div class="col-lg-4 col-md-6 grids5-info mb-5"><a href="<?= publicValue($detailUrl) ?>" class="d-block"><?php if ($image !== ''): ?><img src="<?= publicValue($image) ?>" alt="<?= publicValue($title) ?>" class="img-fluid"><?php endif; ?></a><div class="blog-info"><h5><?= publicDate($row[$definition['date']] ?? null) ?></h5><h4><a href="<?= publicValue($detailUrl) ?>"><?= publicValue($title) ?></a></h4><?php if ($type === 'reportajes' && ($row[$definition['summary']] ?? '') !== ''): ?><p><?= publicValue((string) $row[$definition['summary']]) ?></p><?php endif; ?><a class="btn mt-4 p-0" href="<?= publicValue($detailUrl) ?>"><?= $type === 'boletines' ? 'Ver boletín' : 'Leer' ?> <span class="fa fa-arrow-right"></span></a></div></div>
<?php endforeach; else: ?><div class="col-12"><p>Aún no hay contenido publicado en esta sección.</p></div><?php endif; ?></div></div></section></div>
</body>
</html>
