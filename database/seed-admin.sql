-- Ejecutar después de crear la tabla usuarios en la base de InfinityFree.
-- Este usuario puede iniciar sesión como Nicol con la contraseña 123456.
-- Cambia la contraseña inmediatamente desde el panel después del primer acceso.

INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol)
VALUES (
    'Nicol',
    '',
    '',
    'admin@ddp.com',
    '$2y$10$dTg6HSuDmRFs/PdCfNooXuuw7X51KqwEPyThgWLmFXFD4KSYP4H9O',
    'admin'
);