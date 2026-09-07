<?php
/**
 * Plugin Name: YGB Store Selector
 * Plugin URI: https://github.com/yosdeny
 * Description: Muestra una ventana emergente para seleccionar una tienda o web, guarda la selección en una cookie y redirige al usuario a su url.
 * Version:     1.0.8
 * Requires at least: 7.0
 * Tested up to: 7.1
 * Requires PHP: 8.0
 * Tested PHP: 8.2
 * Author: YGB
 * Author URI: https://github.com/yosdeny
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ygb-selector
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YGB_SELECTOR_VERSION', '1.0.8' );
define( 'YGB_SELECTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'YGB_SELECTOR_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once YGB_SELECTOR_PATH . 'includes/utils.php';
require_once YGB_SELECTOR_PATH . 'includes/popup.php';
require_once YGB_SELECTOR_PATH . 'includes/ajax.php';
require_once YGB_SELECTOR_PATH . 'includes/admin-page.php';

// ------------------------------------------
// Menú principal directo (reemplaza el fallback)
// ------------------------------------------
function ygb_selector_admin_menu() {
	add_menu_page(
		'YGB Store Selector',            // page title
		'YGB Selector',                  // menu title
		'manage_options',                // capability
		'ygb-selector',                  // menu slug
		'ygb_selector_admin_page_html',  // callback
		'dashicons-store',               // icon
		30                               // position
	);
}
add_action( 'admin_menu', 'ygb_selector_admin_menu' );

// ------------------------------------------
// Activación del plugin
// ------------------------------------------
function ygb_selector_activate() {
	$defaults = array(
		'ygb_selector_stores'                    => array(),
		'ygb_selector_cookie_days'               => 30,
		'ygb_selector_cookie_domain'             => '',
		'ygb_selector_cookie_name'               => 'tiendaSeleccionada',
		'ygb_selector_popup_enabled'             => 1,
		'ygb_selector_popup_bg_color'            => '#ffffff',
		'ygb_selector_popup_text_color'          => '#333333',
		'ygb_selector_popup_title_color'         => '#E26143',
		'ygb_selector_popup_accent_color'        => '#0073aa',
		'ygb_selector_popup_title_text'          => __( 'Selecciona tu tienda', 'ygb-selector' ),
		'ygb_selector_popup_select_placeholder'  => __( 'Elige una tienda', 'ygb-selector' ),
	);

	foreach ( $defaults as $key => $value ) {
		if ( false === get_option( $key, false ) ) {
			add_option( $key, $value );
		}
	}
}
register_activation_hook( __FILE__, 'ygb_selector_activate' );

// ------------------------------------------
// Traducciones
// ------------------------------------------
function ygb_selector_load_textdomain() {
	load_plugin_textdomain( 'ygb-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'ygb_selector_load_textdomain' );

// ------------------------------------------
// Assets del frontend
// ------------------------------------------
function ygb_selector_enqueue_frontend() {
	if ( ! ygb_selector_popup_enabled() ) {
		return;
	}

	wp_enqueue_style( 'ygb-selector-popup', YGB_SELECTOR_URL . 'assets/popup.css', array(), YGB_SELECTOR_VERSION );
	wp_enqueue_script( 'ygb-selector-popup', YGB_SELECTOR_URL . 'assets/popup.js', array(), YGB_SELECTOR_VERSION, true );

	wp_localize_script( 'ygb-selector-popup', 'ygbSelectorAjax', array(
		'url'       => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'ygb_selector_nonce' ),
		'setAction' => 'ygb_selector_set_cookie',
		'labels'    => array(
			'redirecting' => __( 'Redirigiendo a tu tienda…', 'ygb-selector' ),
			'errorSet'    => __( 'Error al fijar la cookie. Redirigiendo…', 'ygb-selector' ),
			'errorConn'   => __( 'Error de conexión. Redirigiendo…', 'ygb-selector' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'ygb_selector_enqueue_frontend' );

// ------------------------------------------
// Assets del admin
// ------------------------------------------
function ygb_selector_enqueue_admin( $hook ) {
	if ( isset( $_GET['page'] ) && 'ygb-selector' === $_GET['page'] ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'ygb-selector-admin', YGB_SELECTOR_URL . 'assets/admin.css', array(), YGB_SELECTOR_VERSION );

		// Inicialización segura del color picker – se mantiene junto a la que ya existe en admin-page.php
		wp_add_inline_script( 'wp-color-picker', '
			jQuery(document).ready(function($) {
				setTimeout(function() {
					$(".ygb-color-field").wpColorPicker();
				}, 100);
			});
		' );
	}
}
add_action( 'admin_enqueue_scripts', 'ygb_selector_enqueue_admin' );

// ------------------------------------------
// Enlaces en la página de plugins
// ------------------------------------------
function ygb_selector_add_action_links( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=ygb-selector' ) . '">' . __( 'Configuración', 'ygb-selector' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ygb_selector_add_action_links' );

// ------------------------------------------
// Funciones de seguridad (sanitización)
// ------------------------------------------
function ygb_selector_sanitize_store_url( $url ) {
	$url = esc_url_raw( $url );
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return false;
	}
	$protocol = parse_url( $url, PHP_URL_SCHEME );
	if ( ! in_array( $protocol, array( 'http', 'https' ), true ) ) {
		return false;
	}
	return $url;
}

function ygb_selector_sanitize_store_name( $name ) {
	$name = sanitize_text_field( $name );
	$name = wp_strip_all_tags( $name );
	$name = trim( $name );
	if ( strlen( $name ) > 255 ) {
		$name = substr( $name, 0, 255 );
	}
	return $name;
}

// ------------------------------------------
// Guardado de cookies (formulario separado)
// ------------------------------------------
function ygb_selector_admin_save_cookies() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_save_cookies'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_save_cookies_nonce' );

	// Aplicar wp_unslash a todas las entradas antes de sanitizar
	if ( isset( $_POST['ygb_selector_cookie_days'] ) ) {
		$days = intval( wp_unslash( $_POST['ygb_selector_cookie_days'] ) );
		update_option( 'ygb_selector_cookie_days', max( 1, $days ) );
	}
	if ( isset( $_POST['ygb_selector_cookie_domain'] ) ) {
		$domain = sanitize_text_field( wp_unslash( $_POST['ygb_selector_cookie_domain'] ) );
		update_option( 'ygb_selector_cookie_domain', $domain );
	}
	if ( isset( $_POST['ygb_selector_cookie_name'] ) ) {
		$name = sanitize_text_field( wp_unslash( $_POST['ygb_selector_cookie_name'] ) );
		update_option( 'ygb_selector_cookie_name', $name );
	}

	add_settings_error( 'ygb_selector', 'cookies_saved', __( 'Configuración de cookies actualizada.', 'ygb-selector' ), 'updated' );
}
add_action( 'admin_init', 'ygb_selector_admin_save_cookies' );

// ------------------------------------------
// Guardado principal (tiendas + visual)
// ------------------------------------------
function ygb_selector_admin_save_main_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_save_settings'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_save_settings_nonce' );

	// Tiendas
	$stores        = array();
	$names         = (array) ( isset( $_POST['store_name'] ) ? wp_unslash( $_POST['store_name'] ) : array() );
	$urls          = (array) ( isset( $_POST['store_url'] ) ? wp_unslash( $_POST['store_url'] ) : array() );
	$existing_urls = array();

	foreach ( $names as $i => $name ) {
		$name = ygb_selector_sanitize_store_name( $name );
		$url  = ygb_selector_sanitize_store_url( $urls[ $i ] ?? '' );

		if ( in_array( $url, $existing_urls, true ) ) {
			continue;
		}
		if ( $name && false !== $url ) {
			$stores[]        = array( 'nombre' => $name, 'url' => $url );
			$existing_urls[] = $url;
		}
	}
	update_option( 'ygb_selector_stores', $stores );

	// Popup habilitado
	update_option( 'ygb_selector_popup_enabled', isset( $_POST['ygb_selector_popup_enabled'] ) ? 1 : 0 );

	// Colores
	$color_fields = array(
		'ygb_selector_popup_bg_color',
		'ygb_selector_popup_text_color',
		'ygb_selector_popup_title_color',
		'ygb_selector_popup_accent_color',
	);
	foreach ( $color_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_option( $field, sanitize_hex_color( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	// Textos
	$text_fields = array(
		'ygb_selector_popup_title_text',
		'ygb_selector_popup_select_placeholder',
	);
	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_option( $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	add_settings_error( 'ygb_selector', 'settings_saved', __( 'Configuración guardada correctamente.', 'ygb-selector' ), 'updated' );
}
add_action( 'admin_init', 'ygb_selector_admin_save_main_settings' );

// ------------------------------------------
// Limpiar cookie manualmente
// ------------------------------------------
function ygb_selector_admin_clear_cookie() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_clear_cookie'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_clear_cookie_nonce' );

	ygb_selector_delete_store_cookie();
	add_settings_error( 'ygb_selector', 'cookie_cleared', __( 'Cookie eliminada correctamente.', 'ygb-selector' ), 'updated' );
}
add_action( 'admin_init', 'ygb_selector_admin_clear_cookie' );

// ------------------------------------------
// Lista blanca de opciones (para export/import/reset)
// ------------------------------------------
function ygb_selector_allowed_keys() {
	return array(
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
}

// ------------------------------------------
// Exportar configuración
// ------------------------------------------
function ygb_selector_export_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_export_settings'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_export_settings_nonce' );

	$options = array();
	foreach ( ygb_selector_allowed_keys() as $key ) {
		$options[ $key ] = get_option( $key, '' );
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="ygb-selector-settings.json"' );
	echo wp_json_encode( $options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	exit;
}
add_action( 'admin_init', 'ygb_selector_export_settings' );

// ------------------------------------------
// Importar configuración
// ------------------------------------------
function ygb_selector_import_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_import_settings'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_import_settings_nonce' );

	if ( empty( $_FILES['settings_file']['tmp_name'] ) ) {
		add_settings_error( 'ygb_selector', 'import_fail', __( 'No se subió ningún archivo.', 'ygb-selector' ), 'error' );
		return;
	}

	// Verificar tamaño máximo (1 MB)
	if ( isset( $_FILES['settings_file']['size'] ) && $_FILES['settings_file']['size'] > 1048576 ) {
		add_settings_error( 'ygb_selector', 'import_fail', __( 'El archivo es demasiado grande (máximo 1 MB).', 'ygb-selector' ), 'error' );
		return;
	}

	if ( ! is_uploaded_file( $_FILES['settings_file']['tmp_name'] ) ) {
		add_settings_error( 'ygb_selector', 'import_fail', __( 'Error con el archivo subido.', 'ygb-selector' ), 'error' );
		return;
	}

	// Verificación adicional del tipo de archivo
	$filetype = wp_check_filetype( $_FILES['settings_file']['name'] );
	if ( 'json' !== $filetype['ext'] || 'application/json' !== $filetype['type'] ) {
		add_settings_error( 'ygb_selector', 'import_fail', __( 'El archivo debe ser JSON.', 'ygb-selector' ), 'error' );
		return;
	}

	// Usar WP_Filesystem para leer el archivo
	global $wp_filesystem;
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();
	$json = $wp_filesystem->get_contents( $_FILES['settings_file']['tmp_name'] );
	if ( false === $json ) {
		add_settings_error( 'ygb_selector', 'import_fail', __( 'No se pudo leer el archivo.', 'ygb-selector' ), 'error' );
		return;
	}

	$data = json_decode( $json, true );
	if ( JSON_ERROR_NONE !== json_last_error() ) {
		add_settings_error( 'ygb_selector', 'import_fail', __( 'Archivo JSON inválido.', 'ygb-selector' ), 'error' );
		return;
	}

	$allowed = ygb_selector_allowed_keys();
	foreach ( $data as $key => $value ) {
		if ( in_array( $key, $allowed, true ) ) {
			// Sanitizar según el tipo de opción para mayor seguridad
			if ( in_array( $key, array( 'ygb_selector_stores' ), true ) ) {
				// Las tiendas ya se sanitizan al guardar, pero al importar podemos validar
				$stores = array();
				if ( is_array( $value ) ) {
					foreach ( $value as $store ) {
						if ( isset( $store['nombre'], $store['url'] ) ) {
							$name = ygb_selector_sanitize_store_name( $store['nombre'] );
							$url  = ygb_selector_sanitize_store_url( $store['url'] );
							if ( $name && false !== $url ) {
								$stores[] = array( 'nombre' => $name, 'url' => $url );
							}
						}
					}
				}
				update_option( $key, $stores );
			} elseif ( in_array( $key, array( 'ygb_selector_cookie_days' ), true ) ) {
				update_option( $key, max( 1, intval( $value ) ) );
			} elseif ( in_array( $key, array( 'ygb_selector_popup_enabled' ), true ) ) {
				update_option( $key, $value ? 1 : 0 );
			} elseif ( in_array( $key, array( 'ygb_selector_popup_bg_color', 'ygb_selector_popup_text_color', 'ygb_selector_popup_title_color', 'ygb_selector_popup_accent_color' ), true ) ) {
				update_option( $key, sanitize_hex_color( $value ) );
			} else {
				// Text fields o dominio
				update_option( $key, sanitize_text_field( $value ) );
			}
		}
	}

	add_settings_error( 'ygb_selector', 'import_ok', __( 'Configuración importada correctamente.', 'ygb-selector' ), 'updated' );
}
add_action( 'admin_init', 'ygb_selector_import_settings' );

// ------------------------------------------
// Resetear todas las opciones
// ------------------------------------------
function ygb_selector_admin_reset_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_reset_settings'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_reset_settings_nonce' );

	foreach ( ygb_selector_allowed_keys() as $key ) {
		delete_option( $key );
	}

	add_settings_error( 'ygb_selector', 'reset_ok', __( 'Todas las opciones eliminadas.', 'ygb-selector' ), 'updated' );
}
add_action( 'admin_init', 'ygb_selector_admin_reset_settings' );

// ------------------------------------------
// Resetear solo las tiendas
// ------------------------------------------
function ygb_selector_admin_reset_stores() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['ygb_selector_reset_stores'] ) ) {
		return;
	}
	check_admin_referer( 'ygb_selector_reset_stores_nonce' );

	delete_option( 'ygb_selector_stores' );
	add_settings_error( 'ygb_selector', 'reset_stores_ok', __( 'Tiendas eliminadas.', 'ygb-selector' ), 'updated' );
}
add_action( 'admin_init', 'ygb_selector_admin_reset_stores' );