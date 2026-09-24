<?php
declare(strict_types=1);

function database(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = [];
    $localConfig = __DIR__ . '/database.local.php';
    if (is_file($localConfig)) {
        $config = require $localConfig;
        if (!is_array($config)) {
            throw new RuntimeException('La configuración local de la base de datos no es válida.');
        }
    }

    $envFile = dirname(__DIR__, 2) . '/.env';
    if (is_file($envFile)) {
        $envConfig = parse_ini_file($envFile, false, INI_SCANNER_RAW);
        if (is_array($envConfig)) {
            $config = array_merge($envConfig, $config);
        }
    }

    $host = getenv('REVISTA_DB_HOST') ?: ($config['DB_HOST'] ?? 'sql313.infinityfree.com');
    $name = getenv('REVISTA_DB_NAME') ?: ($config['DB_NAME'] ?? 'if0_42985270_revista_digital');
    $user = getenv('REVISTA_DB_USER') ?: ($config['DB_USER'] ?? 'if0_42985270');
    $password = getenv('REVISTA_DB_PASSWORD') ?: ($config['DB_PASSWORD'] ?? '');
    $port = getenv('REVISTA_DB_PORT') ?: ($config['DB_PORT'] ?? '3306');

    if ($host === '' || $name === '' || $user === '' || $password === '') {
        throw new RuntimeException('Falta configurar la contraseña de la base MySQL de InfinityFree.');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
