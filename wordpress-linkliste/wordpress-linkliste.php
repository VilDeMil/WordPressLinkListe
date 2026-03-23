<?php
/**
 * Plugin Name: WordPress LinkListe
 * Plugin URI:  https://github.com/VilDeMil/WordPressLinkListe
 * Description: Verwalte eine kuratierte Linkliste mit Frontend-Einreichung, Kategorien, Bildern und Suche. Neue Einträge müssen zuerst genehmigt werden.
 * Version:     1.1.0
 * Author:      VilDeMil
 * License:     GPL-2.0+
 * Text Domain: wordpress-linkliste
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WLL_VERSION',    '1.1.0' );
define( 'WLL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once WLL_PLUGIN_DIR . 'includes/class-wll-database.php';
require_once WLL_PLUGIN_DIR . 'includes/class-wll-post-type.php';
require_once WLL_PLUGIN_DIR . 'includes/class-wll-shortcodes.php';
require_once WLL_PLUGIN_DIR . 'includes/class-wll-ajax.php';
require_once WLL_PLUGIN_DIR . 'admin/class-wll-admin.php';
require_once WLL_PLUGIN_DIR . 'frontend/class-wll-frontend.php';

// Activation / Deactivation
register_activation_hook( __FILE__, array( 'WLL_Database', 'install' ) );
register_deactivation_hook( __FILE__, array( 'WLL_Database', 'deactivate' ) );

/**
 * Plugin bootstrap – initialise all components.
 */
function wll_init() {
    WLL_Post_Type::init();
    WLL_Shortcodes::init();
    WLL_Ajax::init();
    WLL_Admin::init();
    WLL_Frontend::init();
}
add_action( 'plugins_loaded', 'wll_init' );
