<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

requireAdmin();

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'La sesión del formulario expiró. Inténtalo nuevamente.';
    } elseif ($currentPassword === '' || strlen($newPassword) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($newPassword !== $confirmation) {
        $error = 'La confirmación no coincide con la nueva contraseña.';
    } elseif (!changeAdminPassword(adminId(), $currentPassword, $newPassword)) {
        $error = 'La contraseña actual no es correcta.';
    } else {
        $success = 'Contraseña actualizada correctamente.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cambiar contraseña | DDP</title>
  <link rel="stylesheet" href="public/assets/dashboard.css">
</head>
<body class="login-page">
  <main class="login-card">
    <img class="login-logo" src="../assets/images/logo.png" alt="DDP Noticias">
    <h1>Cambiar contraseña</h1>
    <p class="login-intro">Actualiza tu clave de acceso administrativo</p>
    <?php if ($error): ?><div class="login-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="login-notice" role="status"><?= e($success) ?></div><?php endif; ?>
    <form method="post" class="login-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <label class="sr-only" for="current_password">Contraseña actual</label>
      <input id="current_password" name="current_password" type="password" placeholder="Contraseña actual" autocomplete="current-password" required>
      <label class="sr-only" for="new_password">Nueva contraseña</label>
      <input id="new_password" name="new_password" type="password" placeholder="Nueva contraseña (mínimo 8 caracteres)" autocomplete="new-password" minlength="8" required>
      <label class="sr-only" for="password_confirmation">Confirmar contraseña</label>
      <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirmar nueva contraseña" autocomplete="new-password" minlength="8" required>
      <button type="submit">Guardar contraseña</button>
    </form>
    <a class="back-link" href="index.php">Volver al panel</a>
  </main>
</body>
</html>