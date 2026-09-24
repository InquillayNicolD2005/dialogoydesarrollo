<?php
declare(strict_types=1);

$isReportaje = $type === 'reportajes';
$isBoletin = $type === 'boletines';
$isMedia = in_array($type, ['podcasts', 'videos'], true);
$isVideo = $type === 'videos';
$form = $editing ?? [];
$currentId = (int) ($form['id'] ?? 0);
$featuredCount = $isReportaje ? count(array_filter($rows, static fn (array $row): bool => !empty($row['es_destacado']))) : 0;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($definition['label']) ?> | Panel DDP</title>
  <link rel="stylesheet" href="public/assets/dashboard.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar"><a class="brand" href="index.php"><img class="brand-logo" src="../assets/images/logo.png" alt="DDP Noticias"><span>DDP Admin</span></a><p class="section-label">Contenido</p><nav class="nav"><a href="index.php">Panel general</a><a class="<?= $type === 'noticias' ? 'active' : '' ?>" href="contenido.php?tipo=noticias">Actualidad</a><a class="<?= $type === 'reportajes' ? 'active' : '' ?>" href="contenido.php?tipo=reportajes">Reportajes</a><a class="<?= $type === 'boletines' ? 'active' : '' ?>" href="contenido.php?tipo=boletines">Boletines</a><a class="<?= $type === 'podcasts' ? 'active' : '' ?>" href="contenido.php?tipo=podcasts">Podcasts</a><a class="<?= $type === 'videos' ? 'active' : '' ?>" href="contenido.php?tipo=videos">Videos</a></nav></aside>
  <div class="content"><header class="topbar"><div><h1><?= e($definition['label']) ?></h1><p>Completa los datos y guarda los cambios de esta sección.</p></div><div class="topbar-actions"><span class="welcome">Hola, <?= e(adminName()) ?> <span class="role-badge role-<?= e(adminRole()) ?>"><?= e(strtoupper(adminRole())) ?></span></span><a class="public-link" href="change-password.php">Cambiar contraseña</a><a class="logout-link" href="logout.php">Cerrar sesión</a></div></header>
    <main class="main">
      <?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <?php if ($notice): ?><div class="notice success" role="status"><?= e($notice) ?></div><?php endif; ?>
      <section class="content-layout"><article class="panel form-panel"><div class="panel-head"><div><h2><?= $editing ? 'Editar registro' : 'Nuevo registro' ?></h2><span class="panel-hint">Los campos con * son obligatorios</span></div><span><?= e($definition['label']) ?></span></div>
        <form method="post" enctype="multipart/form-data" class="content-form"><input type="hidden" name="tipo" value="<?= e($type) ?>"><input type="hidden" name="id" value="<?= $currentId ?>"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="form-guide">Completa la información, revisa los datos y presiona Guardar.</div>
          <?php if ($isBoletin): ?><label>Número del boletín<input type="number" name="numero_boletin" min="1" required value="<?= e((string) ($form['numero_boletin'] ?? '')) ?>"></label><label>Resumen<textarea name="resumen" rows="3"><?= e((string) ($form['resumen'] ?? '')) ?></textarea></label><label>Fecha de publicación<input type="date" name="fecha_publicacion" required value="<?= e((string) ($form['fecha_publicacion'] ?? date('Y-m-d'))) ?>"></label><label>Imagen de portada<input type="file" name="foto_portada" accept="image/*"></label><label>Archivo PDF<?= !empty($form['archivo_pdf']) ? ' (selecciona otro solo si deseas reemplazarlo)' : '' ?><input type="file" name="archivo_pdf" accept="application/pdf"<?= empty($form['archivo_pdf']) ? ' required' : '' ?>></label>
          <?php elseif ($isMedia): ?><label>Título del contenido<input type="text" name="titulo" maxlength="255" required value="<?= e((string) ($form['titulo'] ?? '')) ?>"></label><label>Fecha de publicación<input type="date" name="fecha_publicacion" required value="<?= e((string) ($form['fecha_publicacion'] ?? date('Y-m-d'))) ?>"></label><?php if ($isVideo): ?><label>Miniatura desde tu PC<input type="file" name="imagen" accept="image/*"></label><label>O enlace de miniatura<input type="url" name="imagen_url" placeholder="https://ejemplo.com/miniatura.jpg" value="<?= filter_var((string) ($form['imagen'] ?? ''), FILTER_VALIDATE_URL) ? e((string) $form['imagen']) : '' ?>"></label><?php endif; ?><label>Descripción del contenido<textarea name="descripcion" rows="4" placeholder="Información adicional que se mostrará al abrir el contenido"><?= e((string) ($form['descripcion'] ?? '')) ?></textarea></label><label>Enlace del video o podcast<input type="url" name="url_embed" placeholder="Pega aquí el enlace de YouTube, Spotify u otra plataforma" required value="<?= e((string) ($form['url_embed'] ?? '')) ?>"></label>
          <?php else: ?><label>Título<input type="text" name="titulo" maxlength="255" required value="<?= e((string) ($form['titulo'] ?? '')) ?>"></label><label>Fecha de publicación<input type="date" name="fecha_publicacion" required value="<?= e((string) ($form['fecha_publicacion'] ?? date('Y-m-d'))) ?>"></label><label>Imagen desde tu PC<input type="file" name="<?= $isReportaje ? 'foto_principal' : 'foto' ?>" accept="image/*"></label><?php if (!$isReportaje): ?><label>O enlace de imagen<input type="url" name="foto_url" placeholder="https://ejemplo.com/imagen.jpg" value="<?= filter_var((string) ($form['foto'] ?? ''), FILTER_VALIDATE_URL) ? e((string) $form['foto']) : '' ?>"></label><label>Descripción<textarea name="descripcion" rows="4" placeholder="Información adicional que se mostrará al abrir la noticia"><?= e((string) ($form['descripcion'] ?? '')) ?></textarea><?php endif; ?><?php if ($isReportaje): ?><label>Resumen<textarea name="resumen_corto" rows="3"><?= e((string) ($form['resumen_corto'] ?? '')) ?></textarea></label><label>Desarrollo<textarea name="desarrollo" rows="5" required><?= e((string) ($form['desarrollo'] ?? '')) ?></textarea></label><label>PDF del reportaje<input type="file" name="pdf_adjunto" accept="application/pdf"></label><?php $defaultAuthor = ''; if (!empty($form['autor_id'])) { foreach ($authors as $author) { if ((int) $author['id'] === (int) $form['autor_id']) { $defaultAuthor = trim((string) ($author['nombres'] ?? '') . ' ' . (string) ($author['ap_paterno'] ?? '') . ' ' . (string) ($author['ap_materno'] ?? '')); break; } } } ?><label>Autor (opcional)<input type="text" name="autor" list="author-list" value="<?= e($defaultAuthor) ?>" placeholder="Escribe el nombre del autor"></label><datalist id="author-list"><?php foreach ($authors as $author): ?><option value="<?= e(trim((string) ($author['nombres'] ?? '') . ' ' . (string) ($author['ap_paterno'] ?? '') . ' ' . (string) ($author['ap_materno'] ?? ''))) ?>"></option><?php endforeach; ?></datalist><label><input type="checkbox" name="es_destacado" value="1"<?= !empty($form['es_destacado']) ? ' checked' : '' ?>> Mostrar en portada (máximo 3)</label><?php else: ?><label>Enlace externo<input type="url" name="link_externo" placeholder="Pega aquí el enlace de la noticia (opcional)" value="<?= e((string) ($form['link_externo'] ?? '')) ?>"></label><?php endif; ?><?php endif; ?>
          <button type="submit"><?= $editing ? 'Guardar cambios' : 'Agregar contenido' ?></button><?php if ($editing): ?><a class="cancel-link" href="contenido.php?tipo=<?= e($type) ?>">Cancelar edición</a><?php endif; ?>
        </form></article>
        <article class="panel"><div class="panel-head"><h2>Contenido registrado</h2><span><?= count($rows) ?> registros<?= $isReportaje ? ' · ' . $featuredCount . '/3 en portada' : '' ?></span></div><?php if ($rows): ?><div class="records"><?php foreach ($rows as $row): ?><?php $recordTitle = $isBoletin ? 'Boletín N.º ' . (string) ($row['numero_boletin'] ?? '') : (string) ($row[$definition['title']] ?? 'Sin título'); $recordId = (int) ($row['id'] ?? 0); ?><div class="record"><div><strong><?= e($recordTitle) ?></strong><small><?= e((string) ($row[$definition['date']] ?? 'Sin fecha')) ?><?php if ($isReportaje && !empty($row['es_destacado'])): ?> · <b>En portada</b><?php endif; ?></small></div><div class="record-actions"><a href="contenido.php?tipo=<?= e($type) ?>&editar=<?= $recordId ?>">Editar</a><?php if (isAdministrator()): ?><form method="post" onsubmit="return confirm('¿Eliminar este contenido?');"><input type="hidden" name="tipo" value="<?= e($type) ?>"><input type="hidden" name="id" value="<?= $recordId ?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button type="submit">Eliminar</button></form><?php endif; ?></div></div><?php endforeach; ?></div><?php else: ?><div class="empty">Todavía no hay registros. Crea el primero desde el formulario.</div><?php endif; ?></article>
      </section>
    </main>
  </div>
</div>
<script>
document.querySelectorAll('textarea[name="desarrollo"]').forEach(function (textarea) {
  var toolbar = document.createElement('div');
  toolbar.className = 'editor-toolbar';
  toolbar.innerHTML = '<button type="button" data-command="bold"><b>B</b></button><button type="button" data-command="italic"><i>I</i></button><button type="button" data-command="underline"><u>U</u></button><button type="button" data-command="insertUnorderedList">Lista</button><button type="button" data-command="insertOrderedList">Numerada</button>';
  var editor = document.createElement('div');
  editor.className = 'rich-editor';
  editor.contentEditable = 'true';
  editor.innerHTML = textarea.value ? '<p>' + textarea.value.replace(/\n/g, '</p><p>') + '</p>' : '<p><br></p>';
  textarea.hidden = true;
  textarea.parentNode.insertBefore(toolbar, textarea);
  textarea.parentNode.insertBefore(editor, textarea);
  toolbar.querySelectorAll('button').forEach(function (button) {
    button.addEventListener('click', function () {
      editor.focus();
      document.execCommand(button.dataset.command, false, null);
    });
  });
  textarea.form.addEventListener('submit', function () {
    textarea.value = editor.innerHTML;
  });
});
</script>
</body>
</html>