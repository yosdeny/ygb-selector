<?php
if (!defined('ABSPATH')) exit;

function ygb_selector_popup_html() {
    if (!ygb_selector_popup_enabled()) return;

    $cookie_name = ygb_selector_get_cookie_name();
    $stores = ygb_selector_get_stores();
    
    if (empty($stores)) return;
    
    $show = true;
    
    if (!empty($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name . 'Expira'])) {
        $exp = intval($_COOKIE[$cookie_name . 'Expira']);
        $stored_url = sanitize_text_field($_COOKIE[$cookie_name]);
        
        if (time() < $exp) {
            $tienda_existe = false;
            foreach ($stores as $store) {
                if (isset($store['url']) && $store['url'] === $stored_url) {
                    $tienda_existe = true;
                    break;
                }
            }
            if ($tienda_existe) $show = false;
        }
    }
    
    if (!$show) return;

    $bg     = get_option('ygb_selector_popup_bg_color', '#fff');
    $text   = get_option('ygb_selector_popup_text_color', '#333');
    $title  = get_option('ygb_selector_popup_title_color', '#E26143');
    $accent = get_option('ygb_selector_popup_accent_color', '#0073aa');

    $title_text  = get_option('ygb_selector_popup_title_text', __('Selecciona tu tienda', 'ygb-selector'));
    $select_text = get_option('ygb_selector_popup_select_placeholder', __('Elige una tienda', 'ygb-selector'));
    ?>
    <div id="ygb-selector-popup" role="dialog" aria-modal="true" aria-labelledby="ygb-selector-popup-title">
      <div id="ygb-selector-popup-content"
           data-bg="<?php echo esc_attr($bg); ?>"
           data-text="<?php echo esc_attr($text); ?>"
           data-title="<?php echo esc_attr($title); ?>"
           data-accent="<?php echo esc_attr($accent); ?>">
        <h3 id="ygb-selector-popup-title"><?php echo esc_html($title_text); ?></h3>
        <select id="ygb-selector-menu" aria-label="<?php echo esc_attr(__('Selector de tiendas', 'ygb-selector')); ?>">
          <option value="" selected>-- <?php echo esc_html($select_text); ?> --</option>
          <?php foreach ($stores as $store): ?>
            <?php 
              $name = isset($store['nombre']) ? $store['nombre'] : '';
              $url  = isset($store['url']) ? $store['url'] : '';
              if (!$name || !$url) continue;
            ?>
            <option value="<?php echo esc_url($url); ?>">
              <?php echo esc_html($name); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div id="ygb-selector-redirect-msg" aria-live="polite" style="display:none;"></div>
      </div>
    </div>
    <?php
}
add_action('wp_footer', 'ygb_selector_popup_html');