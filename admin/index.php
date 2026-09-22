<?php
declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/modelos/dashboard.php';

requireAdmin();

try {
    $data = dashboardData();
    $counts = $data['counts'];
    $recent = $data['recent'];
    $error = null;
} catch (Throwable $exception) {
    $counts = [];
    $recent = [];
    $error = 'No se pudo cargar el panel. Verifica que MySQL esté iniciado y que la base revista_digital esté disponible.';
}

require __DIR__ . '/vistas/dashboard.php';
