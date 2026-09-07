<?php
if (!defined('ABSPATH')) exit;

function ygb_selector_get_stores() {
    $stores = get_option('ygb_selector_stores', array());
    return is_array($stores) ? $stores : array();
}

function ygb_selector_get_cookie_days() {
    $days = intval(get_option('ygb_selector_cookie_days', 30));
    return $days > 0 ? $days : 30;
}

function ygb_selector_get_cookie_domain() {
    $domain = sanitize_text_field(trim((string)get_option('ygb_selector_cookie_domain', '')));
    return strtolower($domain);
}

function ygb_selector_get_cookie_name() {
    return get_option('ygb_selector_cookie_name', 'tiendaSeleccionada');
}

function ygb_selector_popup_enabled() {
    return (bool) get_option('ygb_selector_popup_enabled', 1);
}

function ygb_selector_delete_store_cookie() {
    $cookie_name = ygb_selector_get_cookie_name();
    $secure      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $host        = $_SERVER['HTTP_HOST'] ?? '';
    $configured  = ygb_selector_get_cookie_domain();
    $expired     = time() - 3600;

    $domains = array_unique(array_filter(array(
        $host,
        $configured,
        ($configured && $configured[0] === '.') ? substr($configured, 1) : '',
        ($configured && $configured[0] !== '.') ? ('.' . $configured) : '',
    )));

    foreach ($domains as $domain) {
        if (PHP_VERSION_ID >= 70300) {
            $opts = array(
                'expires'  => $expired,
                'path'     => '/',
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            );
            setcookie($cookie_name, '', $opts);
            setcookie($cookie_name . 'Expira', '', $opts);
        } else {
            setcookie($cookie_name, '', $expired, '/', $domain, $secure);
            setcookie($cookie_name . 'Expira', '', $expired, '/', $domain, $secure);
        }
    }

    unset($_COOKIE[$cookie_name], $_COOKIE[$cookie_name . 'Expira']);
}