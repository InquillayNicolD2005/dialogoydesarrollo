-- Ejecutar después de crear la tabla usuarios en la base de InfinityFree.
-- Este usuario puede iniciar sesión como Nicol con la contraseña 12345678.
-- Cambia la contraseña inmediatamente desde el panel después del primer acceso.

INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol)
VALUES (
    'Nicol',
    '',
    '',
    'admin@ddp.com',
    '$2y$10$w9YAG5pTSLMWbC2ywn5o4OitRudp4J3UrO6PpocHzshA8xpRmUuYO',
    'admin'
);