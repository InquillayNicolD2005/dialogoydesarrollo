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

    $host = getenv('REVISTA_DB_HOST') ?: ($config['DB_HOST'] ?? 'localhost  ');
    $name = getenv('REVISTA_DB_NAME') ?: ($config['DB_NAME'] ?? 'molicusc_revista_digital');
    $user = getenv('REVISTA_DB_USER') ?: ($config['DB_USER'] ?? 'molicusc_Nicol');
    $password = getenv('REVISTA_DB_PASSWORD') ?: ($config['DB_PASSWORD'] ?? 'danielN2005');
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

    ensureContentStatusColumns($connection, $name);

    return $connection;
}

function ensureContentStatusColumns(PDO $connection, string $databaseName): void
{
    $tables = ['noticias', 'reportajes', 'boletines', 'podcasts', 'videos'];
    $columnCheck = $connection->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = :table_name AND COLUMN_NAME = \'estado\''
    );

    foreach ($tables as $table) {
        $columnCheck->execute(['database_name' => $databaseName, 'table_name' => $table]);
        if ((int) $columnCheck->fetchColumn() === 0) {
            $connection->exec("ALTER TABLE `{$table}` ADD COLUMN `estado` ENUM('borrador','publicado','archivado') NOT NULL DEFAULT 'publicado' AFTER `fecha_publicacion`");
        }
    }
}
