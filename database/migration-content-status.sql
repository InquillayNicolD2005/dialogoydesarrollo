-- Ejecutar una sola vez en phpMyAdmin de InfinityFree.
-- Los contenidos existentes quedan visibles como publicados.

ALTER TABLE noticias ADD COLUMN estado ENUM('borrador', 'publicado', 'archivado') NOT NULL DEFAULT 'publicado' AFTER fecha_publicacion;
ALTER TABLE reportajes ADD COLUMN estado ENUM('borrador', 'publicado', 'archivado') NOT NULL DEFAULT 'publicado' AFTER fecha_publicacion;
ALTER TABLE boletines ADD COLUMN estado ENUM('borrador', 'publicado', 'archivado') NOT NULL DEFAULT 'publicado' AFTER fecha_publicacion;
ALTER TABLE podcasts ADD COLUMN estado ENUM('borrador', 'publicado', 'archivado') NOT NULL DEFAULT 'publicado' AFTER fecha_publicacion;
ALTER TABLE videos ADD COLUMN estado ENUM('borrador', 'publicado', 'archivado') NOT NULL DEFAULT 'publicado' AFTER fecha_publicacion;
