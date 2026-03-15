<?php
/**
 * Frontend-Steuerung: Assets laden und Template-Helfer bereitstellen.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WLL_Frontend {

    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_head',            array( __CLASS__, 'maybe_start_session' ), 1 );
    }

    public static function maybe_start_session() {
        if ( ! session_id() && ! headers_sent() ) {
            session_start();
        }
    }

    public static function enqueue_assets() {
        wp_enqueue_style(
            'wll-frontend-style',
            WLL_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WLL_VERSION
        );

        wp_enqueue_script(
            'wll-frontend-script',
            WLL_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            WLL_VERSION,
            true
        );

        wp_localize_script( 'wll-frontend-script', 'wllConfig', array(
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'publicNonce' => wp_create_nonce( 'wll_public_nonce' ),
            'submitNonce' => wp_create_nonce( 'wll_submit_nonce' ),
            'i18n'        => array(
                'loading'         => __( 'Lade…', 'wordpress-linkliste' ),
                'no_results'      => __( 'Keine Links gefunden.', 'wordpress-linkliste' ),
                'submit_success'  => __( 'Dein Link wurde eingereicht!', 'wordpress-linkliste' ),
                'submit_error'    => __( 'Fehler beim Einreichen.', 'wordpress-linkliste' ),
                'rating_saved'    => __( 'Bewertung gespeichert!', 'wordpress-linkliste' ),
                'copy_success'    => __( 'Link kopiert!', 'wordpress-linkliste' ),
            ),
        ) );
    }

    /**
     * Lädt ein Template aus frontend/templates/.
     *
     * Reihenfolge der Template-Suche:
     *   1. Aktives Theme: {theme}/wll-templates/{name}.php
     *   2. Plugin: frontend/templates/{name}.php
     *
     * @param string $name  Dateiname ohne .php
     * @param array  $data  Variablen, die im Template verfügbar sein sollen
     */
    public static function load_template( string $name, array $data = array() ) {
        $theme_file  = get_stylesheet_directory() . '/wll-templates/' . $name . '.php';
        $plugin_file = WLL_PLUGIN_DIR . 'frontend/templates/' . $name . '.php';

        $file = file_exists( $theme_file ) ? $theme_file : $plugin_file;

        if ( ! file_exists( $file ) ) {
            echo '<p class="wll-error">Template nicht gefunden: ' . esc_html( $name ) . '</p>';
            return;
        }

        // Variablen im Template-Scope verfügbar machen
        extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
        require $file;
    }
}
