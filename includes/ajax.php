<?php
if (!defined('ABSPATH')) exit;

function ygb_selector_set_cookie_ajax() {
    // Rate limiting: max 5 requests per minute per IP
    $rate_limit_key = 'ygb_selector_rate_limit_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $rate_limit_data = get_transient($rate_limit_key);
    if ($rate_limit_data === false) {
        $rate_limit_data = array('count' => 0, 'reset_time' => time() + 60);
    }
    if (time() > $rate_limit_data['reset_time']) {
        $rate_limit_data = array('count' => 0, 'reset_time' => time() + 60);
    }
    if ($rate_limit_data['count'] >= 5) {
        wp_send_json_error(array('message' => __('Demasiadas solicitudes. Intente de nuevo en un minuto.', 'ygb-selector')), 429);
        return;
    }
    $rate_limit_data['count']++;
    set_transient($rate_limit_key, $rate_limit_data, 60);

    check_ajax_referer('ygb_selector_nonce', 'nonce');

    $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
    if (!$url) {
        wp_send_json_error(array('message' => __('URL inválida', 'ygb-selector')));
        return;
    }

    $stores = ygb_selector_get_stores();
    $allowed_urls = array();
    foreach ($stores as $store) {
        if (isset($store['url'])) {
            $allowed_urls[] = $store['url'];
        }
    }
    
    if (!in_array($url, $allowed_urls, true)) {
        wp_send_json_error(array('message' => __('URL no permitida', 'ygb-selector')));
        return;
    }

    $cookie_name   = ygb_selector_get_cookie_name();
    $cookie_days   = ygb_selector_get_cookie_days();
    $configured_domain = ygb_selector_get_cookie_domain();
    $cookie_domain = $configured_domain ?: sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'] ?? ''));
    $secure        = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $expires_ts    = time() + ($cookie_days * DAY_IN_SECONDS);

    if (PHP_VERSION_ID >= 70300) {
        $opts = array(
            'expires'  => $expires_ts,
            'path'     => '/',
            'domain'   => $cookie_domain,
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        );
        setcookie($cookie_name, $url, $opts);
        // Also set expiration cookie for validation
        setcookie($cookie_name . 'Expira', $expires_ts, $opts);
    } else {
        setcookie($cookie_name, $url, $expires_ts, '/', $cookie_domain, $secure, true);
        setcookie($cookie_name . 'Expira', $expires_ts, '/', $cookie_domain, $secure);
    }

    // Security headers for AJAX response
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    wp_send_json_success(array(
        'message' => __('Cookie fijada', 'ygb-selector'),
        'url'     => $url,
        'expira'  => $expires_ts,
        'days'    => $cookie_days
    ));
}
add_action('wp_ajax_ygb_selector_set_cookie','ygb_selector_set_cookie_ajax');
add_action('wp_ajax_nopriv_ygb_selector_set_cookie','ygb_selector_set_cookie_ajax');

function ygb_selector_clear_cookie_ajax() {
    check_ajax_referer('ygb_selector_nonce', 'nonce');
    ygb_selector_delete_store_cookie();
    
    // Security headers for AJAX response
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    
    wp_send_json_success(array('message' => __('Cookie eliminada', 'ygb-selector')));
}
add_action('wp_ajax_ygb_selector_clear_cookie','ygb_selector_clear_cookie_ajax');
add_action('wp_ajax_nopriv_ygb_selector_clear_cookie','ygb_selector_clear_cookie_ajax');
