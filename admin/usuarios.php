<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';
requireAdministrator();

$connection = database();
$error = null;
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireAdministrator();
        if (!verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
            throw new InvalidArgumentException('La sesión del formulario expiró.');
        }
        $action = (string) ($_POST['accion'] ?? 'save');
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id === adminId()) {
                throw new InvalidArgumentException('No puedes eliminar tu propio usuario.');
            }
            $query = $connection->prepare('DELETE FROM usuarios WHERE id = :id');
            $query->execute(['id' => $id]);
            $notice = 'Usuario eliminado.';
        } else {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['nombres'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $role = (string) ($_POST['rol'] ?? 'redactor');
            $password = (string) ($_POST['password'] ?? '');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'editor', 'redactor'], true)) {
                throw new InvalidArgumentException('Completa usuario, correo y rol correctamente.');
            }
            if ($id > 0) {
                $query = $connection->prepare('UPDATE usuarios SET nombres = :nombres, email = :email, rol = :rol' . ($password !== '' ? ', password_hash = :password_hash' : '') . ' WHERE id = :id');
                $values = ['nombres' => $name, 'email' => $email, 'rol' => $role, 'id' => $id];
                if ($password !== '') {
                    $values['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                }
            } else {
                if (strlen($password) < 8) {
                    throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
                }
                $query = $connection->prepare('INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol) VALUES (:nombres, \'\', \'\', :email, :password_hash, :rol)');
                $values = ['nombres' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'rol' => $role];
            }
            $query->execute($values);
            $notice = $id > 0 ? 'Usuario actualizado.' : 'Usuario creado.';
        }
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'No se pudo guardar el usuario. Verifica que el correo no esté repetido.';
    }
}

$users = $connection->query('SELECT id, nombres, email, rol, created_at FROM usuarios ORDER BY nombres')->fetchAll();
$editing = null;
if (isset($_GET['editar'])) {
    $query = $connection->prepare('SELECT id, nombres, email, rol FROM usuarios WHERE id = :id LIMIT 1');
    $query->execute(['id' => (int) $_GET['editar']]);
    $editing = $query->fetch() ?: null;
}
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Usuarios y roles | DDP</title><link rel="stylesheet" href="public/assets/dashboard.css"></head>
<body><div class="shell"><aside class="sidebar"><a class="brand" href="index.php"><img class="brand-logo" src="../assets/images/logo.png" alt="DDP Noticias"><span>DDP Admin</span></a><p class="section-label">Administración</p><nav class="nav"><a href="index.php">Panel general</a><a class="active" href="usuarios.php">Usuarios y roles</a><a href="autores.php">Autores</a><a href="contenido.php?tipo=reportajes">Contenido</a><a href="logout.php">Cerrar sesión</a></nav></aside><div class="content"><header class="topbar"><div><h1>Usuarios y roles</h1><p>Controla quién puede publicar y administrar contenido.</p></div><span class="role-badge role-admin">ADMIN</span></header><main class="main"><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?><section class="content-layout"><article class="panel form-panel"><div class="panel-head"><h2><?= $editing ? 'Editar usuario' : 'Nuevo usuario' ?></h2><span>Roles con permisos</span></div><form method="post" class="content-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>"><label>Nombre de usuario<input name="nombres" required value="<?= e((string) ($editing['nombres'] ?? '')) ?>"></label><label>Correo electrónico<input type="email" name="email" required value="<?= e((string) ($editing['email'] ?? '')) ?>"></label><label>Rol<select name="rol"><option value="admin"<?= ($editing['rol'] ?? '') === 'admin' ? ' selected' : '' ?>>Administrador</option><option value="editor"<?= ($editing['rol'] ?? '') === 'editor' ? ' selected' : '' ?>>Editor</option><option value="redactor"<?= ($editing['rol'] ?? 'redactor') === 'redactor' ? ' selected' : '' ?>>Redactor</option></select></label><label>Contraseña<?= $editing ? ' (déjala vacía para conservarla)' : '' ?><input type="password" name="password" minlength="8"<?= $editing ? '' : ' required' ?>></label><button type="submit">Guardar usuario</button></form></article><article class="panel"><div class="panel-head"><h2>Usuarios registrados</h2><span><?= count($users) ?> usuarios</span></div><div class="records"><?php foreach ($users as $user): ?><div class="record"><div><strong><?= e((string) $user['nombres']) ?></strong><small><?= e((string) $user['email']) ?> · <span class="role-badge role-<?= e((string) $user['rol']) ?>"><?= e(strtoupper((string) $user['rol'])) ?></span></small></div><div class="record-actions"><a href="usuarios.php?editar=<?= (int) $user['id'] ?>">Editar</a><?php if ((int) $user['id'] !== adminId()): ?><form method="post" onsubmit="return confirm('¿Eliminar este usuario?');"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button type="submit">Eliminar</button></form><?php endif; ?></div></div><?php endforeach; ?></div></article></section></main></div></div></body></html>
