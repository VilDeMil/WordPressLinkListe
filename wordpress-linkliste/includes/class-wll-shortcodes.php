<?php
/**
 * Shortcodes für die WordPress LinkListe.
 *
 * [wll_linkliste]          – Komplette Linkliste mit Suche & Filter
 * [wll_submit_form]        – Einreichungsformular für neue Links
 * [wll_top_links count=5]  – Widget: Top-Links nach Aufrufen
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WLL_Shortcodes {

    public static function init() {
        add_shortcode( 'wll_linkliste',   array( __CLASS__, 'render_linkliste' ) );
        add_shortcode( 'wll_submit_form', array( __CLASS__, 'render_submit_form' ) );
        add_shortcode( 'wll_top_links',   array( __CLASS__, 'render_top_links' ) );
    }

    // ------------------------------------------------------------------
    // [wll_linkliste]
    // ------------------------------------------------------------------

    public static function render_linkliste( $atts ) {
        $atts = shortcode_atts(
            array(
                'per_page'    => 12,
                'category_id' => 0,
                'orderby'     => 'created_at',
                'order'       => 'DESC',
            ),
            $atts,
            'wll_linkliste'
        );

        $paged       = max( 1, (int) get_query_var( 'paged', 1 ) );
        $search      = isset( $_GET['wll_search'] ) ? sanitize_text_field( wp_unslash( $_GET['wll_search'] ) ) : '';
        $category_id = isset( $_GET['wll_cat'] ) ? (int) $_GET['wll_cat'] : (int) $atts['category_id'];

        $result = WLL_Database::get_approved_links( array(
            'category_id' => $category_id,
            'search'      => $search,
            'orderby'     => $atts['orderby'],
            'order'       => $atts['order'],
            'per_page'    => (int) $atts['per_page'],
            'paged'       => $paged,
        ) );

        $categories = WLL_Database::get_categories();

        ob_start();
        WLL_Frontend::load_template( 'linkliste', array(
            'links'       => $result['links'],
            'total'       => $result['total'],
            'per_page'    => (int) $atts['per_page'],
            'paged'       => $paged,
            'search'      => $search,
            'category_id' => $category_id,
            'categories'  => $categories,
        ) );
        return ob_get_clean();
    }

    // ------------------------------------------------------------------
    // [wll_submit_form]
    // ------------------------------------------------------------------

    public static function render_submit_form( $atts ) {
        $categories = WLL_Database::get_categories();
        ob_start();
        WLL_Frontend::load_template( 'submit-form', array(
            'categories' => $categories,
        ) );
        return ob_get_clean();
    }

    // ------------------------------------------------------------------
    // [wll_top_links count=5]
    // ------------------------------------------------------------------

    public static function render_top_links( $atts ) {
        $atts = shortcode_atts( array( 'count' => 5 ), $atts, 'wll_top_links' );

        $result = WLL_Database::get_approved_links( array(
            'orderby'  => 'views',
            'order'    => 'DESC',
            'per_page' => (int) $atts['count'],
            'paged'    => 1,
        ) );

        ob_start();
        WLL_Frontend::load_template( 'top-links', array( 'links' => $result['links'] ) );
        return ob_get_clean();
    }
}
