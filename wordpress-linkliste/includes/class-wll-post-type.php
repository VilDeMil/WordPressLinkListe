<?php
/**
 * Registriert den Custom Post Type "wll_link" sowie die Taxonomie "wll_category"
 * als optionale WordPress-Strukturen (für zukünftige Erweiterungen).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WLL_Post_Type {

    public static function init() {
        // Taxonomie für Kategorien (optional, eigene DB-Tabelle wird bevorzugt)
        add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
    }

    public static function register_taxonomy() {
        // Leer – Plugin nutzt eigene DB-Tabelle für Kategorien.
        // Dieser Hook bleibt als Erweiterungspunkt bestehen.
    }
}
