<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function startAdminSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function adminIsAuthenticated(): bool
{
    startAdminSession();
    return isset($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!adminIsAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

function authenticateAdmin(string $username, string $password): bool
{
    $query = database()->prepare(
        "SELECT id, nombres, ap_paterno, ap_materno, rol, password_hash
         FROM usuarios
         WHERE nombres = :username
             AND rol = 'admin'
         LIMIT 1"
    );
    $query->execute(['username' => $username]);
    $admin = $query->fetch();

    $storedPassword = (string) ($admin['password_hash'] ?? '');
    $validPassword = $storedPassword !== '' && password_verify($password, $storedPassword);
    if (!$admin || !$validPassword) {
        return false;
    }

    startAdminSession();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = trim($admin['nombres'] . ' ' . ($admin['ap_paterno'] ?? '') . ' ' . ($admin['ap_materno'] ?? ''));
    $_SESSION['admin_role'] = (string) $admin['rol'];

    return true;
}

function adminName(): string
{
    startAdminSession();
    return (string) ($_SESSION['admin_name'] ?? 'Administrador');
}

function adminId(): int
{
    startAdminSession();
    return (int) ($_SESSION['admin_id'] ?? 0);
}

function csrfToken(): string
{
    startAdminSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    startAdminSession();
    return $token !== '' && hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}

function changeAdminPassword(int $adminId, string $currentPassword, string $newPassword): bool
{
    $query = database()->prepare('SELECT password_hash FROM usuarios WHERE id = :id AND rol = \'admin\' LIMIT 1');
    $query->execute(['id' => $adminId]);
    $admin = $query->fetch();

    $storedPassword = (string) ($admin['password_hash'] ?? '');
    if (!$admin || $storedPassword === '' || !password_verify($currentPassword, $storedPassword)) {
        return false;
    }

    $update = database()->prepare('UPDATE usuarios SET password_hash = :password_hash WHERE id = :id AND rol = \'admin\'');
    $update->execute([
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        'id' => $adminId,
    ]);

    return $update->rowCount() === 1;
}

function createPasswordReset(string $email): ?string
{
    $connection = database();
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS password_resets (' .
        'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ' .
        'user_id INT UNSIGNED NOT NULL, ' .
        'token_hash CHAR(64) NOT NULL, ' .
        'expires_at DATETIME NOT NULL, ' .
        'used_at DATETIME NULL, ' .
        'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ' .
        'INDEX (token_hash), INDEX (user_id)' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $query = $connection->prepare("SELECT id FROM usuarios WHERE email = :email AND rol = 'admin' LIMIT 1");
    $query->execute(['email' => $email]);
    $admin = $query->fetch();
    if (!$admin) {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    $insert = $connection->prepare(
        'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)'
    );
    $insert->execute([
        'user_id' => (int) $admin['id'],
        'token_hash' => hash('sha256', $token),
        'expires_at' => date('Y-m-d H:i:s', time() + 3600),
    ]);

    return $token;
}

function resetAdminPassword(string $token, string $newPassword): bool
{
    $connection = database();
    $query = $connection->prepare(
        "SELECT id, user_id FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1"
    );
    $query->execute(['token_hash' => hash('sha256', $token)]);
    $reset = $query->fetch();
    if (!$reset) {
        return false;
    }

    $connection->beginTransaction();
    try {
        $update = $connection->prepare("UPDATE usuarios SET password_hash = :password_hash WHERE id = :id AND rol = 'admin'");
        $update->execute([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => (int) $reset['user_id'],
        ]);
        $markUsed = $connection->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $markUsed->execute(['id' => (int) $reset['id']]);
        $connection->commit();
        return $update->rowCount() === 1;
    } catch (Throwable $exception) {
        $connection->rollBack();
        throw $exception;
    }
}