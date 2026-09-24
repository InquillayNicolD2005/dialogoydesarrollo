<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';
requireAdmin();
$connection = database();
$error = null;
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
            throw new InvalidArgumentException('La sesión del formulario expiró.');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $values = ['nombres' => trim((string) ($_POST['nombres'] ?? '')), 'ap_paterno' => trim((string) ($_POST['ap_paterno'] ?? '')), 'ap_materno' => trim((string) ($_POST['ap_materno'] ?? ''))];
        if ($values['nombres'] === '' || $values['ap_paterno'] === '') {
            throw new InvalidArgumentException('El nombre y apellido paterno son obligatorios.');
        }
        if ($id > 0) {
            $query = $connection->prepare('UPDATE autores SET nombres = :nombres, ap_paterno = :ap_paterno, ap_materno = :ap_materno WHERE id = :id');
            $values['id'] = $id;
        } else {
            $query = $connection->prepare('INSERT INTO autores (nombres, ap_paterno, ap_materno) VALUES (:nombres, :ap_paterno, :ap_materno)');
        }
        $query->execute($values);
        $notice = $id > 0 ? 'Autor actualizado.' : 'Autor creado.';
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'No se pudo guardar el autor.';
    }
}
$authors = $connection->query('SELECT id, nombres, ap_paterno, ap_materno FROM autores ORDER BY nombres, ap_paterno')->fetchAll();
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Autores | DDP</title><link rel="stylesheet" href="public/assets/dashboard.css"></head>
<body><div class="shell"><aside class="sidebar"><a class="brand" href="index.php"><img class="brand-logo" src="../assets/images/logo.png" alt="DDP Noticias"><span>DDP Admin</span></a><p class="section-label">Administración</p><nav class="nav"><a href="index.php">Panel general</a><a href="usuarios.php">Usuarios y roles</a><a class="active" href="autores.php">Autores</a><a href="contenido.php?tipo=reportajes">Contenido</a><a href="logout.php">Cerrar sesión</a></nav></aside><div class="content"><header class="topbar"><div><h1>Autores</h1><p>Gestiona las firmas que aparecerán en reportajes.</p></div><a class="public-link" href="contenido.php?tipo=reportajes">Ir a reportajes</a></header><main class="main"><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?><section class="content-layout"><article class="panel form-panel"><div class="panel-head"><h2>Nuevo autor</h2><span>Se guarda en MySQL</span></div><form method="post" class="content-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><label>Nombres<input name="nombres" required></label><label>Apellido paterno<input name="ap_paterno" required></label><label>Apellido materno<input name="ap_materno"></label><button type="submit">Guardar autor</button></form></article><article class="panel"><div class="panel-head"><h2>Autores registrados</h2><span><?= count($authors) ?> autores</span></div><div class="records"><?php foreach ($authors as $author): ?><div class="record"><div><strong><?= e(trim((string) $author['nombres'] . ' ' . (string) $author['ap_paterno'] . ' ' . (string) $author['ap_materno'])) ?></strong><small>ID <?= (int) $author['id'] ?></small></div></div><?php endforeach; ?></div></article></section></main></div></div></body></html>
