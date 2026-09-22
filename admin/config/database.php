<?php
declare(strict_types=1);

function database(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('REVISTA_DB_HOST') ?: 'sql105.infinityfree.com'; // Reemplaza "sql105" por el Hostname de la sección MySQL Databases de InfinityFree
    $name = getenv('REVISTA_DB_NAME') ?: 'if0_42985270_revista';  // El nombre completo de la BD creada
    $user = getenv('REVISTA_DB_USER') ?: 'if0_42985270';
    $password = getenv('REVISTA_DB_PASSWORD') ?: 'f93DNTQ5Sa';
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
