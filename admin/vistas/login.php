<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso administrativo | DDP</title>
  <link rel="stylesheet" href="public/assets/dashboard.css">
</head>
<body class="login-page">
  <main class="login-card">
    <img class="login-logo" src="../assets/images/logo.png" alt="DDP Noticias">
    <h1>ADMIN DIALOGO Y<br>DESARROLLO</h1>
    <p class="login-intro">Ingrese sus datos de acceso</p>
    <?php if ($notice): ?><div class="login-notice" role="status"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="login-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php" class="login-form" autocomplete="off">
      <label class="sr-only" for="nombres">Nombre</label>
      <input id="nombres" name="nombres" type="text" placeholder="Nombre" autocomplete="off" spellcheck="false" required>
      <label class="sr-only" for="password">Contraseña</label>
      <input id="password" name="password" type="password" placeholder="Contraseña" autocomplete="current-password" required>
      <button type="submit">Ingresar</button>
    </form>
    <div class="login-actions">
      <a href="forgot-password.php">¿Olvidaste tu contraseña?</a>
    </div>
    <a class="back-link" href="../index.html">Volver al sitio público</a>
  </main>
</body>
</html>