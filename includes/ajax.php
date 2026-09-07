<?php
if (!defined('ABSPATH')) exit;

function ygb_selector_set_cookie_ajax() {
    check_ajax_referer('ygb_selector_nonce', 'nonce');

    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
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
    $cookie_domain = ygb_selector_get_cookie_domain() ?: ($_SERVER['HTTP_HOST'] ?? '');
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
        setcookie($cookie_name . 'Expira', (string)$expires_ts, $opts);
    } else {
        setcookie($cookie_name, $url, $expires_ts, '/', $cookie_domain, $secure);
        setcookie($cookie_name . 'Expira', (string)$expires_ts, $expires_ts, '/', $cookie_domain, $secure);
    }

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
    wp_send_json_success(array('message' => __('Cookie eliminada', 'ygb-selector')));
}
add_action('wp_ajax_ygb_selector_clear_cookie','ygb_selector_clear_cookie_ajax');
add_action('wp_ajax_nopriv_ygb_selector_clear_cookie','ygb_selector_clear_cookie_ajax');