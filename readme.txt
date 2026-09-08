=== YGB Selector ===
Contributors: yosdeny
Donate link: https://example.com/
Tags: popup, cookies, selector, admin, customization
Requires at least: 7.0
Tested up to: 7.1
Stable tag: 1.0.9
Requires PHP: 8.0
Tested PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
YGB Selector is a lightweight WordPress plugin that allows administrators to display a customizable popup selector for users.
It provides cookie-based persistence, AJAX actions to set or clear preferences, and a fully configurable admin panel.
The plugin is designed to be visually intuitive, accessible, and easy to configure without code edits.

Features:
* Customizable popup with title and accent colors.
* Cookie management with AJAX for persistence.
* Admin settings page with intuitive controls.
* Internationalization ready (English/Spanish).
* SEO-optimized assets for WordPress.org repository.

== Installation ==
1. Upload the `ygb-selector` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings > YGB Selector to configure popup options.

== Frequently Asked Questions ==
= Does it work with caching plugins? =
Yes, cookies are managed via AJAX, so caching does not interfere.

= Can I customize the popup colors? =
Yes, you can set title and accent colors directly in the admin panel.

= Is it translation-ready? =
Yes, `.pot`, `.po`, and `.mo` files are included for English and Spanish.

== Screenshots ==
1. Popup selector example.
2. Admin settings page.

== Changelog ==
= 1.0.9 =
* Security hardening: implemented custom capabilities (ygb_manage_stores) following least privilege principle.
* Added explicit HTTP security headers (X-Content-Type-Options, Cache-Control) to AJAX responses and exports.
* Implemented JSON schema validation to prevent property contamination attacks in configuration imports.
* Removed all debugging logs for production environments.
* Updated minimum requirements: WordPress 7.0+, PHP 8.0+.
* Plugin now officially tested up to WordPress 7.1.

= 1.0.8 =
* Updated minimum requirements: WordPress 6.9+, PHP 8.0+.
* Improved security in import/export (file size limit, wp_unslash usage).
* Plugin now officially tested up to WordPress 7.0.

= 1.0.7 =
* Security enhancements: added file size check on import, consistent wp_unslash.

= 1.0.0 =
* Initial release with popup selector, cookie management, and admin settings.

== Upgrade Notice ==
= 1.0.9 =
Requires PHP 8.0 and WordPress 7.0 or higher. Critical security update: custom capabilities, HTTP security headers, JSON schema validation. Highly recommended for all users.

= 1.0.8 =
Requires PHP 8.0 and WordPress 6.9 or higher. Update recommended for security and compatibility.

== License ==
This plugin is licensed under the GPLv2 or later.

=== YGB Selector ===
Contribuidores: yosdeny
Enlace de donación: https://example.com/
Etiquetas: popup, cookies, selector, administración, personalización
Requiere al menos: 7.0
Probado hasta: 7.1
Etiqueta estable: 1.0.9
Requiere PHP: 8.0
PHP probado: 8.2
Licencia: GPLv2 o posterior
Licencia URI: https://www.gnu.org/licenses/gpl-2.0.html

== Descripción ==
YGB Selector es un plugin ligero para WordPress que permite a los administradores mostrar un popup selector personalizable a los usuarios.
Ofrece persistencia basada en cookies, acciones AJAX para fijar o limpiar preferencias y un panel de administración totalmente configurable.
El plugin está diseñado para ser visualmente intuitivo, accesible y fácil de configurar sin necesidad de editar código.

Características:
* Popup personalizable con título y colores de acento.
* Gestión de cookies con AJAX para persistencia.
* Página de ajustes en el admin con controles intuitivos.
* Preparado para internacionalización (inglés/español).
* Recursos optimizados para SEO en el repositorio de WordPress.org.

== Instalación ==
1. Sube la carpeta `ygb-selector` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú 'Plugins' en WordPress.
3. Ve a Ajustes > YGB Selector para configurar las opciones del popup.

== Preguntas Frecuentes ==
= ¿Funciona con plugins de caché? =
Sí, las cookies se gestionan vía AJAX, por lo que la caché no interfiere.

= ¿Puedo personalizar los colores del popup? =
Sí, puedes establecer los colores del título y acento directamente en el panel de administración.

= ¿Está listo para traducción? =
Sí, se incluyen archivos `.pot`, `.po` y `.mo` para inglés y español.

== Capturas ==
1. Ejemplo del popup selector.
2. Página de ajustes en el admin.

== Registro de cambios ==
= 1.0.9 =
* Fortalecimiento de seguridad: implementadas capacidades personalizadas (ygb_manage_stores) siguiendo el principio de menor privilegio.
* Añadidas cabeceras HTTP de seguridad explícitas (X-Content-Type-Options, Cache-Control) en respuestas AJAX y exportaciones.
* Implementada validación de esquema JSON para prevenir ataques de contaminación de propiedades en importaciones de configuración.
* Eliminados todos los logs de depuración para entornos de producción.
* Actualizados los requisitos mínimos: WordPress 7.0+, PHP 8.0+.
* Plugin probado oficialmente hasta WordPress 7.1.

= 1.0.8 =
* Actualizados los requisitos mínimos: WordPress 6.9+, PHP 8.0+.
* Mejoras de seguridad en importación/exportación (límite de tamaño, wp_unslash).
* Plugin probado oficialmente hasta WordPress 7.0.

= 1.0.7 =
* Mejoras de seguridad: añadida verificación de tamaño en importación, uso consistente de wp_unslash.

= 1.0.0 =
* Versión inicial con popup selector, gestión de cookies y ajustes en el admin.

== Aviso de actualización ==
= 1.0.9 =
Requiere PHP 8.0 y WordPress 7.0 o superior. Actualización crítica de seguridad: capacidades personalizadas, cabeceras HTTP de seguridad, validación de esquema JSON. Altamente recomendada para todos los usuarios.

= 1.0.8 =
Requiere PHP 8.0 y WordPress 6.9 o superior. Se recomienda actualizar por seguridad y compatibilidad.

== Licencia ==
Este plugin está licenciado bajo GPLv2 o posterior.