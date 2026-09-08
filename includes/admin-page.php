<?php
if (!defined('ABSPATH')) exit;

function ygb_selector_admin_page_html() {
    if (!ygb_selector_can_manage_stores()) return;

    // Current data
    $stores       = ygb_selector_get_stores();
    $cookie_days  = ygb_selector_get_cookie_days();
    $cookie_domain= ygb_selector_get_cookie_domain();
    $popup_enabled= ygb_selector_popup_enabled();
    $cookie_name  = ygb_selector_get_cookie_name();

    // Cookie state - Validated access to avoid XSS
    $raw_cookie_val = '';
    $raw_cookie_exp = 0;
    if (isset($_COOKIE[$cookie_name]) && is_string($_COOKIE[$cookie_name])) {
        $raw_cookie_val = sanitize_text_field($_COOKIE[$cookie_name]);
    }
    if (isset($_COOKIE[$cookie_name . 'Expira']) && is_numeric($_COOKIE[$cookie_name . 'Expira'])) {
        $raw_cookie_exp = intval($_COOKIE[$cookie_name . 'Expira']);
    }
    
    $cookie_actual  = __('(no existe)', 'ygb-selector');
    $restante_str   = __('Expirada o inexistente', 'ygb-selector');
    $exp_date_str   = __('N/A', 'ygb-selector');
    $cookie_dom_str = $cookie_domain ? esc_html($cookie_domain) : esc_html(wp_unslash($_SERVER['HTTP_HOST'] ?? ''));

    if (!empty($raw_cookie_val) && !empty($raw_cookie_exp)) {
        $cookie_expira   = intval($raw_cookie_exp);
        $tiempo_restante = $cookie_expira > time() ? ($cookie_expira - time()) : 0;
        if ($tiempo_restante > 0) {
            $cookie_actual = sanitize_text_field($raw_cookie_val);
            $dias    = floor($tiempo_restante / 86400);
            $horas   = floor(($tiempo_restante % 86400) / 3600);
            $minutos = floor(($tiempo_restante % 3600) / 60);
            $restante_str = "{$dias}d {$horas}h {$minutos}m";
            $exp_date_str = date('Y-m-d H:i:s', $cookie_expira);
        }
    }
    ?>
    
    <div class="wrap">
        <h1><?php echo esc_html(__('Selector de Tienda', 'ygb-selector')); ?></h1>
        <p class="description"><?php _e('Gestiona el popup selector de tiendas con cookies.', 'ygb-selector'); ?></p>
        
        <?php settings_errors('ygb_selector'); ?>
        
        <!-- ==================== -->
        <!-- SECCIÓN 1: TIENDAS Y CONFIGURACIÓN VISUAL -->
        <!-- ==================== -->
        <div class="ygb-admin-section">
            <h2 class="ygb-section-title">
                <span class="dashicons dashicons-store"></span>
                <?php echo esc_html(__('Tiendas y Configuración Visual', 'ygb-selector')); ?>
            </h2>
            
            <div class="ygb-section-content">
                <!-- FORMULARIO 1: TIENDAS + CONFIGURACIÓN VISUAL DEL POPUP -->
                <form method="post">
                    <?php wp_nonce_field('ygb_selector_save_settings_nonce'); ?>
                    
                    <h3><?php echo esc_html(__('Tiendas Configuradas', 'ygb-selector')); ?></h3>
                    <table class="form-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(__('Nombre', 'ygb-selector')); ?></th>
                                <th><?php echo esc_html(__('URL', 'ygb-selector')); ?></th>
                                <th><?php echo esc_html(__('Acciones', 'ygb-selector')); ?></th>
                            </tr>
                        </thead>
                        <tbody id="ygb-selector-stores-rows">
                            <?php foreach ($stores as $store): ?>
                                <tr>
                                    <td><input type="text" name="store_name[]" value="<?php echo esc_attr($store['nombre']); ?>" aria-label="<?php echo esc_attr(__('Nombre de la tienda', 'ygb-selector')); ?>" /></td>
                                    <td><input type="url" name="store_url[]" value="<?php echo esc_url($store['url']); ?>" aria-label="<?php echo esc_attr(__('URL de la tienda', 'ygb-selector')); ?>" /></td>
                                    <td><button type="button" class="button ygb-delete-row" aria-label="<?php echo esc_attr(__('Eliminar fila', 'ygb-selector')); ?>"><?php echo esc_html(__('Eliminar', 'ygb-selector')); ?></button></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><input type="text" name="store_name[]" placeholder="<?php echo esc_attr(__('Nombre tienda', 'ygb-selector')); ?>" aria-label="<?php echo esc_attr(__('Nombre de la tienda', 'ygb-selector')); ?>" /></td>
                                <td><input type="url" name="store_url[]" placeholder="https://ejemplo.com" aria-label="<?php echo esc_attr(__('URL de la tienda', 'ygb-selector')); ?>" /></td>
                                <td><button type="button" class="button ygb-delete-row" aria-label="<?php echo esc_attr(__('Eliminar fila', 'ygb-selector')); ?>"><?php echo esc_html(__('Eliminar', 'ygb-selector')); ?></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <p><button type="button" class="button" id="ygb-add-row" aria-label="<?php echo esc_attr(__('Añadir fila', 'ygb-selector')); ?>">
                        <?php echo esc_html(__('Añadir tienda', 'ygb-selector')); ?>
                    </button></p>
                    
                    <h3><?php echo esc_html(__('Configuración del Popup', 'ygb-selector')); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><?php echo esc_html(__('Mostrar popup', 'ygb-selector')); ?></th>
                            <td><input type="checkbox" name="ygb_selector_popup_enabled" value="1" <?php checked($popup_enabled); ?> /></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html(__('Título del popup', 'ygb-selector')); ?></th>
                            <td><input type="text" name="ygb_selector_popup_title_text"
                                value="<?php echo esc_attr(get_option('ygb_selector_popup_title_text', __('Selecciona tu tienda', 'ygb-selector'))); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html(__('Texto inicial del selector', 'ygb-selector')); ?></th>
                            <td><input type="text" name="ygb_selector_popup_select_placeholder"
                                value="<?php echo esc_attr(get_option('ygb_selector_popup_select_placeholder', __('Elige una tienda', 'ygb-selector'))); ?>" /></td>
                        </tr>

                        <!-- COLOR PICKERS -->
                        <tr>
                            <th><?php echo esc_html(__('Color de fondo popup', 'ygb-selector')); ?></th>
                            <td><input type="text" name="ygb_selector_popup_bg_color" value="<?php echo esc_attr(get_option('ygb_selector_popup_bg_color', '#ffffff')); ?>" class="ygb-color-field" data-default-color="#ffffff" aria-label="<?php echo esc_attr(__('Color de fondo', 'ygb-selector')); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html(__('Color de texto popup', 'ygb-selector')); ?></th>
                            <td><input type="text" name="ygb_selector_popup_text_color" value="<?php echo esc_attr(get_option('ygb_selector_popup_text_color', '#333333')); ?>" class="ygb-color-field" data-default-color="#333333" aria-label="<?php echo esc_attr(__('Color de texto', 'ygb-selector')); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html(__('Color del título popup', 'ygb-selector')); ?></th>
                            <td><input type="text" name="ygb_selector_popup_title_color" value="<?php echo esc_attr(get_option('ygb_selector_popup_title_color', '#E26143')); ?>" class="ygb-color-field" data-default-color="#E26143" aria-label="<?php echo esc_attr(__('Color del título', 'ygb-selector')); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html(__('Color de acento popup', 'ygb-selector')); ?></th>
                            <td><input type="text" name="ygb_selector_popup_accent_color" value="<?php echo esc_attr(get_option('ygb_selector_popup_accent_color', '#0073aa')); ?>" class="ygb-color-field" data-default-color="#0073aa" aria-label="<?php echo esc_attr(__('Color de acento', 'ygb-selector')); ?>" /></td>
                        </tr>
                    </table>
                    
                    <input type="hidden" name="ygb_selector_save_settings" value="1" />
                    <p>
                        <button class="button button-primary" type="submit">
                            <span class="dashicons dashicons-saved"></span> <?php echo esc_html(__('Guardar Tiendas y Configuración Visual', 'ygb-selector')); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- ==================== -->
        <!-- SECCIÓN 2: CONFIGURACIÓN DE COOKIES -->
        <!-- ==================== -->
        <div class="ygb-admin-section">
            <h2 class="ygb-section-title">
                <span class="dashicons dashicons-shield"></span>
                <?php echo esc_html(__('Configuración de Cookies', 'ygb-selector')); ?>
            </h2>
            
            <div class="ygb-section-content">
                <div class="ygb-cookie-info">
                    <h3><?php echo esc_html(__('Estado Actual de la Cookie', 'ygb-selector')); ?></h3>
                    <table class="widefat fixed" cellspacing="0">
                        <tbody>
                            <tr>
                                <th width="200"><?php echo esc_html(__('Cookie actual:', 'ygb-selector')); ?></th>
                                <td><code><?php echo esc_html($cookie_actual); ?></code></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html(__('Tiempo restante:', 'ygb-selector')); ?></th>
                                <td><code><?php echo esc_html($restante_str); ?></code></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html(__('Expira:', 'ygb-selector')); ?></th>
                                <td><code><?php echo esc_html($exp_date_str); ?></code></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html(__('Dominio:', 'ygb-selector')); ?></th>
                                <td><code><?php echo esc_html($cookie_dom_str); ?></code></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html(__('Nombre cookie:', 'ygb-selector')); ?></th>
                                <td><code><?php echo esc_html($cookie_name); ?></code></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h3 style="margin-top: 20px;"><?php echo esc_html(__('Configuración de Cookies', 'ygb-selector')); ?></h3>
                    <!-- FORMULARIO 2: SOLO CONFIGURACIÓN DE COOKIES -->
                    <form method="post">
                        <?php wp_nonce_field('ygb_selector_save_cookies_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><?php echo esc_html(__('Nombre de la cookie', 'ygb-selector')); ?></th>
                                <td><input type="text" name="ygb_selector_cookie_name" value="<?php echo esc_attr($cookie_name); ?>" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html(__('Duración (días)', 'ygb-selector')); ?></th>
                                <td><input type="number" min="1" max="365" name="ygb_selector_cookie_days" value="<?php echo esc_attr($cookie_days); ?>" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html(__('Dominio de cookie', 'ygb-selector')); ?></th>
                                <td><input type="text" name="ygb_selector_cookie_domain" value="<?php echo esc_attr($cookie_domain); ?>" placeholder="<?php echo esc_attr('.dominio.com'); ?>" />
                                <p class="description"><?php _e('Dejar vacío para dominio actual. Para subdominios usa .dominio.com', 'ygb-selector'); ?></p></td>
                            </tr>
                        </table>
                        <input type="hidden" name="ygb_selector_save_cookies" value="1" />
                        <p><button class="button" type="submit">
                            <span class="dashicons dashicons-update"></span> 
                            <?php echo esc_html(__('Actualizar configuración de cookies', 'ygb-selector')); ?>
                        </button></p>
                    </form>
                    
                    <h3 style="margin-top: 20px;"><?php echo esc_html(__('Acciones de Cookie', 'ygb-selector')); ?></h3>
                    <div class="ygb-cookie-actions">
                        <form method="post" style="display: inline-block; margin-right: 10px;">
                            <?php wp_nonce_field('ygb_selector_clear_cookie_nonce'); ?>
                            <input type="hidden" name="ygb_selector_clear_cookie" value="1" />
                            <button class="button" type="submit">
                                <span class="dashicons dashicons-trash"></span> <?php echo esc_html(__('Limpiar cookie actual', 'ygb-selector')); ?>
                            </button>
                        </form>
                        
                        <button class="button" onclick="location.reload()">
                            <span class="dashicons dashicons-update"></span> <?php echo esc_html(__('Actualizar estado', 'ygb-selector')); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ======================= -->
        <!-- SECCIÓN 3: HERRAMIENTAS -->
        <!-- ======================= -->
        <div class="ygb-admin-section">
            <h2 class="ygb-section-title">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php echo esc_html(__('Herramientas', 'ygb-selector')); ?>
            </h2>
            
            <div class="ygb-section-content">
                <div class="ygb-tools-grid">
                    <!-- Exportar -->
                    <div class="ygb-tool-card">
                        <h3><span class="dashicons dashicons-download"></span> <?php echo esc_html(__('Exportar Configuración', 'ygb-selector')); ?></h3>
                        <p><?php _e('Exporta toda la configuración actual a un archivo JSON.', 'ygb-selector'); ?></p>
                        <form method="post">
                            <?php wp_nonce_field('ygb_selector_export_settings_nonce'); ?>
                            <input type="hidden" name="ygb_selector_export_settings" value="1" />
                            <button class="button button-secondary" type="submit">
                                <?php echo esc_html(__('Descargar JSON', 'ygb-selector')); ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Importar -->
                    <div class="ygb-tool-card">
                        <h3><span class="dashicons dashicons-upload"></span> <?php echo esc_html(__('Importar Configuración', 'ygb-selector')); ?></h3>
                        <p><?php _e('Importa configuración desde un archivo JSON.', 'ygb-selector'); ?></p>
                        <form method="post" enctype="multipart/form-data">
                            <?php wp_nonce_field('ygb_selector_import_settings_nonce'); ?>
                            <input type="file" name="settings_file" accept=".json" required aria-label="<?php echo esc_attr(__('Archivo JSON', 'ygb-selector')); ?>" />
                            <input type="hidden" name="ygb_selector_import_settings" value="1" />
                            <p style="margin-top: 10px;">
                                <button class="button button-secondary" type="submit">
                                    <?php echo esc_html(__('Importar JSON', 'ygb-selector')); ?>
                                </button>
                            </p>
                        </form>
                    </div>
                    
                    <!-- Restablecer Tiendas -->
                    <div class="ygb-tool-card">
                        <h3><span class="dashicons dashicons-remove"></span> <?php echo esc_html(__('Restablecer Tiendas', 'ygb-selector')); ?></h3>
                        <p><?php _e('Elimina solo la lista de tiendas, manteniendo colores y textos.', 'ygb-selector'); ?></p>
                        <form method="post" onsubmit="return confirm('<?php echo esc_js(__('¿Seguro que quieres borrar todas las tiendas? Esta acción no se puede deshacer.', 'ygb-selector')); ?>');">
                            <?php wp_nonce_field('ygb_selector_reset_stores_nonce'); ?>
                            <input type="hidden" name="ygb_selector_reset_stores" value="1" />
                            <button class="button" type="submit" style="background-color: #f0a849; border-color: #d18e2a; color: #fff;">
                                <?php echo esc_html(__('Borrar Solo Tiendas', 'ygb-selector')); ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Restablecer Todo -->
                    <div class="ygb-tool-card">
                        <h3><span class="dashicons dashicons-warning"></span> <?php echo esc_html(__('Restablecer Todo', 'ygb-selector')); ?></h3>
                        <p><?php _e('Elimina TODAS las opciones del plugin. Deja el plugin como nuevo.', 'ygb-selector'); ?></p>
                        <form method="post" onsubmit="return confirm('<?php echo esc_js(__('¿Seguro que quieres borrar TODAS las opciones? Esta acción no se puede deshacer.', 'ygb-selector')); ?>');">
                            <?php wp_nonce_field('ygb_selector_reset_settings_nonce'); ?>
                            <input type="hidden" name="ygb_selector_reset_settings" value="1" />
                            <button class="button" type="submit" style="background-color: #dc3232; border-color: #b22222; color: #fff;">
                                <?php echo esc_html(__('Borrar Configuración Completa', 'ygb-selector')); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<script>
// Añadir/eliminar filas de tiendas
document.getElementById('ygb-add-row')?.addEventListener('click', function () {
    const tbody = document.getElementById('ygb-selector-stores-rows');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="store_name[]" placeholder="<?php echo esc_attr(__('Nombre tienda', 'ygb-selector')); ?>" aria-label="<?php echo esc_attr(__('Nombre de la tienda', 'ygb-selector')); ?>" /></td>
        <td><input type="url" name="store_url[]" placeholder="https://ejemplo.com" aria-label="<?php echo esc_attr(__('URL de la tienda', 'ygb-selector')); ?>" /></td>
        <td><button type="button" class="button ygb-delete-row" aria-label="<?php echo esc_attr(__('Eliminar fila', 'ygb-selector')); ?>"><?php echo esc_html(__('Eliminar', 'ygb-selector')); ?></button></td>
    `;
    tbody.appendChild(tr);
});
document.getElementById('ygb-selector-stores-rows')?.addEventListener('click', function(e){
    if(e.target.classList.contains('ygb-delete-row')){
        e.target.closest('tr').remove();
    }
});

// INICIALIZAR COLOR PICKERS
jQuery(document).ready(function($){
    setTimeout(function() {
        $('.ygb-color-field').wpColorPicker();
    }, 100);
});
</script>
    <?php
}