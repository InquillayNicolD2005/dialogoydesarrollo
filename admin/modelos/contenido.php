<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function contentTypes(): array
{
    return [
        'noticias' => ['label' => 'Actualidad', 'table' => 'noticias', 'id' => 'id', 'title' => 'titulo', 'description' => 'descripcion', 'date' => 'fecha_publicacion', 'image' => 'foto', 'link' => 'link_externo', 'db' => ['id' => 'id', 'titulo' => 'titulo', 'descripcion' => 'descripcion', 'fecha_publicacion' => 'fecha_publicacion', 'foto' => 'foto', 'link_externo' => 'link_externo']],
        'reportajes' => ['label' => 'Reportajes', 'table' => 'reportajes', 'id' => 'id', 'title' => 'titulo', 'date' => 'fecha_publicacion', 'summary' => 'resumen_corto', 'description' => 'desarrollo', 'image' => 'foto_principal', 'pdf' => 'pdf_adjunto', 'featured' => 'es_destacado', 'author' => 'autor_id', 'db' => ['id' => 'id', 'titulo' => 'titulo', 'fecha_publicacion' => 'fecha_publicacion', 'resumen_corto' => 'resumen_corto', 'desarrollo' => 'desarrollo', 'foto_principal' => 'foto_principal', 'pdf_adjunto' => 'pdf_adjunto', 'es_destacado' => 'es_destacado', 'autor_id' => 'autor_id']],
        'boletines' => ['label' => 'Boletines', 'table' => 'boletines', 'id' => 'id', 'number' => 'numero_boletin', 'summary' => 'resumen', 'date' => 'fecha_publicacion', 'image' => 'foto_portada', 'pdf' => 'archivo_pdf', 'db' => ['id' => 'id', 'numero_boletin' => 'numero_boletin', 'resumen' => 'resumen', 'fecha_publicacion' => 'fecha_publicacion', 'foto_portada' => 'foto_portada', 'archivo_pdf' => 'archivo_pdf']],
        'podcasts' => ['label' => 'Podcasts', 'table' => 'podcasts', 'id' => 'id', 'title' => 'titulo', 'description' => 'descripcion', 'date' => 'fecha_publicacion', 'embed' => 'url_embed', 'db' => ['id' => 'id', 'titulo' => 'titulo', 'descripcion' => 'descripcion', 'fecha_publicacion' => 'fecha_publicacion', 'url_embed' => 'url_embed']],
        'videos' => ['label' => 'Videos', 'table' => 'videos', 'id' => 'id', 'title' => 'titulo', 'description' => 'descripcion', 'date' => 'fecha_publicacion', 'image' => 'imagen', 'embed' => 'url_embed', 'db' => ['id' => 'id', 'titulo' => 'titulo', 'descripcion' => 'descripcion', 'fecha_publicacion' => 'fecha_publicacion', 'imagen' => 'imagen', 'url_embed' => 'url_embed']],
    ];
}

function contentType(string $type): array
{
    $types = contentTypes();
    return $types[$type] ?? $types['noticias'];
}

function contentSelect(array $definition): string
{
    return implode(', ', array_map(static fn (string $column, string $alias): string => sprintf('`%s` AS `%s`', $column, $alias), $definition['db'], array_keys($definition['db'])));
}

function contentRows(string $type): array
{
    $definition = contentType($type);
    $limit = $type === 'noticias' ? ' LIMIT 3' : '';
    return database()->query(sprintf('SELECT %s FROM `%s` ORDER BY `%s` DESC, `%s` DESC%s', contentSelect($definition), $definition['table'], $definition['date'], $definition['id'], $limit))->fetchAll();
}

function contentCount(string $type): int
{
    $definition = contentType($type);
    return (int) database()->query(sprintf('SELECT COUNT(*) FROM `%s`', $definition['table']))->fetchColumn();
}

function featuredReportageCount(?int $exceptId = null): int
{
    $query = 'SELECT COUNT(*) FROM reportajes WHERE es_destacado = 1';
    $parameters = [];
    if ($exceptId !== null) {
        $query .= ' AND id <> :id';
        $parameters['id'] = $exceptId;
    }
    $statement = database()->prepare($query);
    $statement->execute($parameters);
    return (int) $statement->fetchColumn();
}

function authors(): array
{
    return database()->query('SELECT id, nombres, ap_paterno, ap_materno FROM autores ORDER BY nombres, ap_paterno')->fetchAll();
}

function resolveAuthorId(string $name): ?int
{
    $trimmedName = trim($name);
    if ($trimmedName === '') {
        return null;
    }

    $normalizedName = preg_replace('/\s+/', ' ', $trimmedName);
    $nameParts = preg_split('/\s+/', $normalizedName ?? '', -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $nombres = [];
    $apPaterno = '';
    $apMaterno = '';

    if (count($nameParts) === 1) {
        $nombres = [$nameParts[0]];
    } elseif (count($nameParts) === 2) {
        $nombres = [$nameParts[0]];
        $apPaterno = $nameParts[1];
    } else {
        $nombres = array_slice($nameParts, 0, -2);
        $apPaterno = $nameParts[count($nameParts) - 2];
        $apMaterno = $nameParts[count($nameParts) - 1];
    }

    $connection = database();
    $fullName = trim(implode(' ', $nombres) . ' ' . $apPaterno . ' ' . $apMaterno);
    $existing = $connection->prepare('SELECT id FROM autores WHERE TRIM(CONCAT(nombres, " ", ap_paterno, " ", ap_materno)) = :full_name LIMIT 1');
    $existing->execute(['full_name' => $fullName]);
    $existingId = $existing->fetchColumn();
    if ($existingId !== false && $existingId !== null) {
        return (int) $existingId;
    }

    $insert = $connection->prepare('INSERT INTO autores (nombres, ap_paterno, ap_materno) VALUES (:nombres, :ap_paterno, :ap_materno)');
    $insert->execute([
        'nombres' => implode(' ', $nombres),
        'ap_paterno' => $apPaterno,
        'ap_materno' => $apMaterno,
    ]);

    return (int) $connection->lastInsertId();
}

function contentRow(string $type, int $id): ?array
{
    $definition = contentType($type);
    $query = database()->prepare(sprintf('SELECT %s FROM `%s` WHERE `id` = :id LIMIT 1', contentSelect($definition), $definition['table']));
    $query->execute(['id' => $id]);
    $row = $query->fetch();
    return $row ?: null;
}

function saveContent(string $type, array $data, ?int $id = null): void
{
    $definition = contentType($type);
    $fields = array_values(array_filter(array_keys($definition['db']), static fn (string $field): bool => $field !== 'id' && array_key_exists($field, $data)));
    $values = array_intersect_key($data, array_flip($fields));
    $connection = database();

    if ($id !== null) {
        $assignments = implode(', ', array_map(fn (string $field): string => sprintf('`%s` = :%s', $definition['db'][$field], $field), $fields));
        $values['id'] = $id;
        $query = $connection->prepare(sprintf('UPDATE `%s` SET %s WHERE id = :id', $definition['table'], $assignments));
    } else {
        $fields[] = 'usuario_id';
        $values['usuario_id'] = (int) ($_SESSION['admin_id'] ?? 0);
        $columns = implode(', ', array_map(fn (string $field): string => sprintf('`%s`', $field === 'usuario_id' ? 'usuario_id' : $definition['db'][$field]), $fields));
        $parameters = implode(', ', array_map(static fn (string $field): string => ':' . $field, $fields));
        $query = $connection->prepare(sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $definition['table'], $columns, $parameters));
    }

    $query->execute($values);
}

function deleteContent(string $type, int $id): void
{
    $definition = contentType($type);
    $query = database()->prepare(sprintf('DELETE FROM `%s` WHERE id = :id', $definition['table']));
    $query->execute(['id' => $id]);
}
