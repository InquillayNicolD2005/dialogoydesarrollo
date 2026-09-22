<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

$error = null;
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } else {
        try {
            $token = createPasswordReset($email);
            if ($token !== null) {
                $resetUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/reset-password.php?token=' . urlencode($token);
                $message = "Solicitaste restablecer tu contraseña de DDP Noticias.\n\nAbre este enlace dentro de una hora:\n{$resetUrl}\n\nSi no fuiste tú, ignora este mensaje.";
                @mail($email, 'Restablecer contraseña | DDP Noticias', $message, "Content-Type: text/plain; charset=UTF-8\r\n");
            }
            $sent = true;
        } catch (Throwable $exception) {
            $error = 'No se pudo procesar la solicitud. Verifica la conexión con la base de datos.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar contraseña | DDP</title>
  <link rel="stylesheet" href="public/assets/dashboard.css">
</head>
<body class="login-page">
  <main class="login-card">
    <img class="login-logo" src="../assets/images/logo.png" alt="DDP Noticias">
    <h1>¿Olvidaste tu contraseña?</h1>
    <p class="login-intro">Escribe tu correo y te enviaremos un enlace seguro para restablecerla.</p>
    <?php if ($error): ?><div class="login-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($sent): ?><div class="login-notice" role="status">Si el correo corresponde a un administrador, recibirás instrucciones para restablecer tu contraseña.</div><?php endif; ?>
    <?php if (!$sent): ?>
    <form method="post" class="login-form">
      <label class="sr-only" for="email">Correo electrónico</label>
      <input id="email" name="email" type="email" placeholder="Correo electrónico" autocomplete="email" required value="<?= e((string) ($_POST['email'] ?? '')) ?>">
      <button type="submit">Enviar enlace</button>
    </form>
    <?php endif; ?>
    <a class="back-link" href="login.php">Volver al acceso</a>
  </main>
</body>
</html>