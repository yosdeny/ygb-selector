<?php
/**
 * Desinstalador del plugin YGB Store Selector
 *
 * Se ejecuta cuando el plugin es eliminado definitivamente desde el panel de administración.
 * Limpia todas las opciones guardadas en la base de datos.
 *
 * @package YGB_Selector
 * @version 1.0.6
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Claves a eliminar (las mismas que usa el plugin)
$keys = array(
	'ygb_selector_stores',
	'ygb_selector_cookie_days',
	'ygb_selector_cookie_domain',
	'ygb_selector_cookie_name',
	'ygb_selector_popup_enabled',
	'ygb_selector_popup_bg_color',
	'ygb_selector_popup_text_color',
	'ygb_selector_popup_title_color',
	'ygb_selector_popup_accent_color',
	'ygb_selector_popup_title_text',
	'ygb_selector_popup_select_placeholder',
);

foreach ( $keys as $key ) {
	delete_option( $key );
}