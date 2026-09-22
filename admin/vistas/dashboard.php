<?php
declare(strict_types=1);

$totalContent = ($counts['Noticias'] ?? 0) + ($counts['Reportajes'] ?? 0) + ($counts['Boletines'] ?? 0) + ($counts['Podcasts'] ?? 0) + ($counts['Videos'] ?? 0);
$chartItems = [
  'Noticias' => (int) ($counts['Noticias'] ?? 0),
  'Reportajes' => (int) ($counts['Reportajes'] ?? 0),
  'Boletines' => (int) ($counts['Boletines'] ?? 0),
  'Podcasts' => (int) ($counts['Podcasts'] ?? 0),
  'Videos' => (int) ($counts['Videos'] ?? 0),
];
$chartMax = max(1, ...array_values($chartItems));
$chartTotal = array_sum($chartItems);
$chartColors = ['#e0020d', '#3b4ef5', '#f79009', '#12b76a', '#7f56d9'];
$chartGradient = [];
$chartStart = 0;
foreach (array_values($chartItems) as $index => $value) {
  $chartEnd = $chartStart + ($chartTotal > 0 ? ($value / $chartTotal) * 360 : 0);
  $chartGradient[] = $chartColors[$index] . ' ' . $chartStart . 'deg ' . $chartEnd . 'deg';
  $chartStart = $chartEnd;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel de control | DDP</title>
  <link rel="stylesheet" href="public/assets/dashboard.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <a class="brand" href="index.php"><img class="brand-logo" src="../assets/images/logo.png" alt="DDP Noticias"><span>DDP Admin</span></a>
    <p class="section-label">Contenido</p>
    <nav class="nav">
      <a class="active" href="index.php">Panel general</a>
      <a href="contenido.php?tipo=noticias">Actualidad</a>
      <a href="contenido.php?tipo=reportajes">Reportajes</a>
      <a href="contenido.php?tipo=boletines">Boletines</a>
      <a href="contenido.php?tipo=podcasts">Podcasts</a>
      <a href="contenido.php?tipo=videos">Videos</a>
      <a href="../index.html">Ver sitio público</a>
    </nav>
  </aside>
  <div class="content">
    <header class="topbar">
      <div><h1>Panel de control</h1><p>Resumen de contenidos de Diálogo y Desarrollo Perú</p></div>
      <div class="topbar-actions"><span class="welcome">Hola, <?= e(adminName()) ?></span><a class="public-link" href="change-password.php">Cambiar contraseña</a><a class="logout-link" href="logout.php">Cerrar sesión</a></div>
    </header>
    <main class="main">
      <?php if ($error): ?><div class="panel empty"><?= e($error) ?></div><?php endif; ?>
      <section class="metrics">
        <article class="metric"><div class="metric-icon">N</div><small>Noticias</small><strong><?= $counts['Noticias'] ?? 0 ?></strong></article>
        <article class="metric"><div class="metric-icon">R</div><small>Reportajes</small><strong><?= $counts['Reportajes'] ?? 0 ?></strong></article>
        <article class="metric"><div class="metric-icon">B</div><small>Boletines</small><strong><?= $counts['Boletines'] ?? 0 ?></strong></article>
        <article class="metric"><div class="metric-icon">+</div><small>Contenidos publicados</small><strong><?= $totalContent ?></strong></article>
      </section>
      <section class="dashboard-grid">
        <article class="panel chart-panel">
          <div class="panel-head"><h2>Contenido por módulo</h2><span>Gráfico de barras</span></div>
          <div class="bar-chart">
          <?php foreach ($chartItems as $label => $value): ?><div class="bar-item"><div class="bar-value"><?= $value ?></div><div class="bar" style="height: <?= max(8, (int) round(($value / $chartMax) * 100)) ?>%;"></div><span><?= e($label) ?></span></div><?php endforeach; ?>
          </div>
        </article>
        <article class="panel line-chart-panel">
          <div class="panel-head"><h2>Distribución de contenidos</h2><span>Gráfico lineal</span></div>
          <svg class="line-chart-svg" viewBox="0 0 600 180" role="img" aria-label="Gráfico lineal de contenidos por módulo">
            <polyline points="30,<?= 160 - (int) round(($chartItems['Noticias'] / $chartMax) * 130) ?> 165,<?= 160 - (int) round(($chartItems['Reportajes'] / $chartMax) * 130) ?> 300,<?= 160 - (int) round(($chartItems['Boletines'] / $chartMax) * 130) ?> 435,<?= 160 - (int) round(($chartItems['Podcasts'] / $chartMax) * 130) ?> 570,<?= 160 - (int) round(($chartItems['Videos'] / $chartMax) * 130) ?>" fill="none" stroke="#3b4ef5" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
            <?php foreach (array_values($chartItems) as $index => $value): ?><circle cx="<?= 30 + ($index * 135) ?>" cy="<?= 160 - (int) round(($value / $chartMax) * 130) ?>" r="6" fill="#fff" stroke="#3b4ef5" stroke-width="4" /><?php endforeach; ?>
          </svg>
          <div class="line-chart-values"><?php foreach ($chartItems as $label => $value): ?><div class="line-chart-item"><i class="chart-dot" style="background:#3b4ef5"></i><?= e($label) ?><strong><?= $value ?></strong></div><?php endforeach; ?></div>
        </article>
        <article class="panel donut-panel">
          <div class="panel-head"><h2>Resumen general</h2><span>Gráfico circular</span></div>
          <div class="donut-wrapper"><div class="donut-chart" style="background: conic-gradient(<?= e(implode(', ', $chartGradient)) ?>);"><div class="donut-inner"><strong><?= $chartTotal ?></strong><span>Total</span></div></div></div>
          <div class="legend"><?php foreach (array_values($chartItems) as $index => $value): ?><div class="legend-item"><i class="legend-dot" style="background:<?= $chartColors[$index] ?>"></i><?= e((string) array_keys($chartItems)[$index]) ?><strong><?= $value ?></strong></div><?php endforeach; ?></div>
        </article>
      </section>
      <section class="grid">
        <article class="panel">
          <div class="panel-head"><h2>Contenido reciente</h2><span>Últimos registros</span></div>
          <?php if ($recent): ?>
          <table><thead><tr><th>Título</th><th>Tipo</th><th>Fecha</th></tr></thead><tbody>
          <?php foreach ($recent as $item): ?><tr><td class="title"><?= e((string) ($item['titulo'] ?? 'Sin título')) ?></td><td><span class="badge"><?= e((string) $item['tipo']) ?></span></td><td><?= e((string) ($item['fecha'] ?? 'Sin fecha')) ?></td></tr><?php endforeach; ?>
          </tbody></table>
          <?php else: ?><div class="empty">Todavía no hay contenido registrado.</div><?php endif; ?>
        </article>
        <article class="panel">
          <div class="panel-head"><h2>Base de datos</h2><span>Registros por módulo</span></div>
          <div class="breakdown">
          <?php foreach ($counts as $label => $count): ?><div class="row"><span class="row-label"><i class="dot"></i><?= e($label) ?></span><strong><?= $count ?></strong></div><?php endforeach; ?>
          </div>
        </article>
      </section>
    </main>
  </div>
</div>
</body>
</html>
