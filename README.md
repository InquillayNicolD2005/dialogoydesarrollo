# Diálogo y Desarrollo Perú

Sitio web estático de DDP Noticias.

## Estructura

- `index.html`: página principal.
- `reportaje.html`: listado de reportajes.
- `boletin.html`: boletines NTEP.
- `administrador/`: espacio reservado para administración.
- `assets/images/`, `assets/icons/`, `assets/fonts/`: recursos estáticos.
- `css/main.css`: hoja de estilos principal existente.
- `css/base.css`: variables y estilos base del proyecto.
- `css/components/`, `css/layout/`: estilos organizados por responsabilidad.
- `js/main.js`: punto de entrada de JavaScript.
- `js/modules/`, `js/utils/`: módulos y utilidades.
- `vendor/`: librerías externas locales.
- `boletines/`: archivos PDF de los boletines.

El sitio está preparado para publicarse en InfinityFree mediante el workflow de GitHub Actions. Configura las credenciales MySQL y FTP siguiendo `admin/README.md`. Los recursos visuales y librerías referenciados por las páginas deben colocarse en las carpetas correspondientes de `assets/` y `vendor/`.
