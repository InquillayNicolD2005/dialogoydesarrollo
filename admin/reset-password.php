<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $error = 'El enlace no es válido o ya venció.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($newPassword !== $confirmation) {
        $error = 'La confirmación no coincide con la nueva contraseña.';
    } else {
        try {
            $success = resetAdminPassword($token, $newPassword);
            if (!$success) {
                $error = 'El enlace no es válido o ya venció.';
            }
        } catch (Throwable $exception) {
            $error = 'No se pudo actualizar la contraseña.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nueva contraseña | DDP</title>
  <link rel="stylesheet" href="public/assets/dashboard.css">
</head>
<body class="login-page">
  <main class="login-card">
    <img class="login-logo" src="../assets/images/logo.png" alt="DDP Noticias">
    <h1>Nueva contraseña</h1>
    <?php if ($error): ?><div class="login-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="login-notice" role="status">Contraseña actualizada correctamente.</div>
      <a class="back-link" href="login.php?restablecida=1">Volver al acceso</a>
    <?php else: ?>
    <form method="post" class="login-form">
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <label class="sr-only" for="new_password">Nueva contraseña</label>
      <input id="new_password" name="new_password" type="password" placeholder="Nueva contraseña (mínimo 8 caracteres)" autocomplete="new-password" minlength="8" required>
      <label class="sr-only" for="password_confirmation">Confirmar contraseña</label>
      <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirmar nueva contraseña" autocomplete="new-password" minlength="8" required>
      <button type="submit">Guardar contraseña</button>
    </form>
    <a class="back-link" href="login.php">Volver al acceso</a>
    <?php endif; ?>
  </main>
</body>
</html>