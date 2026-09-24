<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

if (adminIsAuthenticated()) {
    header('Location: index.php');
    exit;
}

$error = null;
$notice = isset($_GET['restablecida']) ? 'Tu contraseña fue actualizada. Ya puedes ingresar.' : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $names = trim((string) ($_POST['nombres'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($names === '' || $password === '') {
        $error = 'Completa tu nombre y contraseña.';
    } else {
        try {
            if (authenticateAdmin($names, $password)) {
                header('Location: index.php');
                exit;
            }
            $error = 'Los datos no corresponden a un usuario administrador.';
        } catch (Throwable $exception) {
            $error = 'No se pudo conectar con la base de datos. Verifica las credenciales y el hostname de InfinityFree.';
        }
    }
}

require __DIR__ . '/vistas/login.php';