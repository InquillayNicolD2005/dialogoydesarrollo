<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function dashboardData(): array
{
    $db = database();
    $tables = [
        'Noticias' => 'noticias',
        'Reportajes' => 'reportajes',
        'Boletines' => 'boletines',
        'Podcasts' => 'podcasts',
        'Videos' => 'videos',
        'Autores' => 'autores',
        'Administradores' => 'usuarios',
    ];
    $counts = [];

    foreach ($tables as $label => $table) {
        $counts[$label] = (int) $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    }

    $recent = $db->query(
        "SELECT titulo AS titulo, fecha_publicacion AS fecha, 'Noticia' AS tipo FROM noticias
         UNION ALL
         SELECT titulo, fecha_publicacion, 'Reportaje' FROM reportajes
         UNION ALL
         SELECT titulo, fecha_publicacion, 'Podcast' FROM podcasts
         ORDER BY fecha DESC, titulo ASC LIMIT 8"
    )->fetchAll();

    return ['counts' => $counts, 'recent' => $recent];
}
