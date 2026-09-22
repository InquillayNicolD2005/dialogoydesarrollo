<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$queries = [
    ["SELECT id, titulo, fecha_publicacion AS fecha, foto AS imagen, link_externo AS enlace, 'Actualidad' AS tipo FROM noticias ORDER BY fecha_publicacion DESC, id DESC LIMIT 3"],
    ["SELECT r.id, r.titulo, r.fecha_publicacion AS fecha, r.foto_principal AS imagen, '' AS enlace, 'Reportajes' AS tipo, r.es_destacado AS destacado, COALESCE(CONCAT(a.nombres, ' ', a.ap_paterno), '') AS autor FROM reportajes r LEFT JOIN autores a ON a.id = r.autor_id ORDER BY r.fecha_publicacion DESC, r.id DESC"],
    ["SELECT id, CONCAT('Boletín N.º ', numero_boletin) AS titulo, fecha_publicacion AS fecha, foto_portada AS imagen, archivo_pdf AS enlace, 'Boletines' AS tipo FROM boletines ORDER BY fecha_publicacion DESC, id DESC LIMIT 6"],
    ["SELECT id, titulo, fecha_publicacion AS fecha, '' AS imagen, url_embed AS enlace, 'Podcasts' AS tipo FROM podcasts ORDER BY fecha_publicacion DESC, id DESC LIMIT 6"],
    ["SELECT id, titulo, fecha_publicacion AS fecha, imagen, url_embed AS enlace, 'Videos' AS tipo FROM videos ORDER BY fecha_publicacion DESC, id DESC LIMIT 6"],
];

try {
    $items = [];
    foreach ($queries as [$sql]) {
        foreach (database()->query($sql)->fetchAll() as $item) {
            $items[] = [
                'id' => (int) ($item['id'] ?? 0),
                'titulo' => (string) ($item['titulo'] ?? ''),
                'fecha' => (string) ($item['fecha'] ?? ''),
                'imagen' => (string) ($item['imagen'] ?? ''),
                'enlace' => (string) ($item['enlace'] ?? ''),
                'tipo' => (string) ($item['tipo'] ?? ''),
                'autor' => (string) ($item['autor'] ?? ''),
                'destacado' => (int) ($item['destacado'] ?? 0),
            ];
        }
    }
    $typeSlugs = ['Actualidad' => 'noticias', 'Reportajes' => 'reportajes', 'Boletines' => 'boletines', 'Podcasts' => 'podcasts', 'Videos' => 'videos'];
    foreach ($items as &$item) {
        $item['detalle'] = 'detalle.php?tipo=' . ($typeSlugs[$item['tipo']] ?? 'noticias') . '&id=' . $item['id'];
    }
    unset($item);
    usort($items, static fn (array $first, array $second): int => strcmp($second['fecha'], $first['fecha']));
    echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo cargar el contenido publicado.'], JSON_UNESCAPED_UNICODE);
}
