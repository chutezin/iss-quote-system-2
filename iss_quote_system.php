<?php
/**
 * Plugin Name: Iss quote System
 * Plugin URI:  https://intrinsicallysafestore.com
 * Description: Plugin to add quote system within woocommerce
 * Version:     1.0
 * Tags: comments, spam
 *  Requires at least:
 * Author:      chutes
 * Author URI:  https://intrinsicallysafestore.com
 * Text Domain: iss_quote_system
 * Domain Path: /languages  *
 *
 * Copyright: © 20019 Intrinsically Safe Store
 *
 * WC requires at least: 2.2
 * WC tested up to: 2.3
 *
 *  License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin paths and URLs
if (!defined('ISS_RQAQ_PLUGIN')) {
    define('ISS_RQAQ_PLUGIN_URL', plugin_dir_url(__FILE__));
}

define('ISS_RQAQ_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!defined('ISS_RQAQ_PLUGIN_INC')) {
    define('ISS_RQAQ_PLUGIN_INC', ISS_RQAQ_PLUGIN_DIR . 'includes/');
}

if (!defined('ISS_RQAQ_PLUGIN_ASSETS')) {
    define('ISS_RQAQ_PLUGIN_ASSETS', ISS_RQAQ_PLUGIN_DIR . 'assets/');
}

if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

function iss_rqaq_constructor()
{
    /**
     * Check if WooCommerce is active
     **/
    if (
        in_array(
            'woocommerce/woocommerce.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        )
    ) {
        if (!class_exists('WC_Session')) {
            include_once WC()->plugin_path() . '/includes/abstracts/abstract-wc-session.php';
        }
        require_once ISS_RQAQ_PLUGIN_INC . 'class.iss_quote_admin.php';
        require_once ISS_RQAQ_PLUGIN_INC . 'class.iss_quote_shortcodes.php';
        require_once ISS_RQAQ_PLUGIN_INC . 'class.iss_quote_session.php';
        require_once ISS_RQAQ_PLUGIN_INC . 'class.iss_quote_request.php';
        require_once ISS_RQAQ_PLUGIN_INC . 'functions.iss-rqaq-functions.php';
    } else {

    }
}
add_action('plugins_loaded', 'iss_rqaq_constructor');