<?php
/**
 * AJAX-Handler für die WordPress LinkListe.
 *
 * Öffentliche Aktionen (nopriv):
 *   wll_submit_link   – Neuen Link einreichen
 *   wll_track_click   – Klick auf einen Link tracken
 *   wll_rate_link     – Link bewerten (1–5 Sterne)
 *   wll_load_links    – Links per AJAX nachladen (Filter/Suche)
 *   wll_add_category  – Neue Kategorie vorschlagen
 *
 * Admin-Aktionen (nur angemeldet):
 *   wll_approve_link  – Link genehmigen
 *   wll_reject_link   – Link ablehnen
 *   wll_delete_link   – Link löschen
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WLL_Ajax {

    public static function init() {
        $public_actions = array(
            'wll_submit_link',
            'wll_track_click',
            'wll_rate_link',
            'wll_load_links',
            'wll_add_category',
        );

        foreach ( $public_actions as $action ) {
            add_action( 'wp_ajax_' . $action,        array( __CLASS__, str_replace( 'wll_', 'handle_', $action ) ) );
            add_action( 'wp_ajax_nopriv_' . $action, array( __CLASS__, str_replace( 'wll_', 'handle_', $action ) ) );
        }

        $admin_actions = array( 'wll_approve_link', 'wll_reject_link', 'wll_delete_link' );
        foreach ( $admin_actions as $action ) {
            add_action( 'wp_ajax_' . $action, array( __CLASS__, str_replace( 'wll_', 'handle_', $action ) ) );
        }
    }

    // ------------------------------------------------------------------
    // Öffentliche Handler
    // ------------------------------------------------------------------

    /** Neuen Link einreichen */
    public static function handle_submit_link() {
        check_ajax_referer( 'wll_submit_nonce', 'nonce' );

        global $wpdb;

        $url        = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
        $name       = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        $cat_id     = (int) ( $_POST['category_id'] ?? 0 );
        $bemerkung  = sanitize_textarea_field( wp_unslash( $_POST['bemerkung'] ?? '' ) );
        $image_url  = esc_url_raw( wp_unslash( $_POST['image_url'] ?? '' ) );
        $submitter  = sanitize_text_field( wp_unslash( $_POST['submitted_by'] ?? '' ) );

        if ( empty( $url ) || empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'URL und Name sind Pflichtfelder.', 'wordpress-linkliste' ) ) );
        }

        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( array( 'message' => __( 'Bitte gib eine gültige URL ein.', 'wordpress-linkliste' ) ) );
        }

        // Duplikat-Check
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wll_links WHERE url = %s",
                $url
            )
        );
        if ( $existing ) {
            wp_send_json_error( array( 'message' => __( 'Diese URL wurde bereits eingereicht.', 'wordpress-linkliste' ) ) );
        }

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'wll_links',
            array(
                'url'          => $url,
                'name'         => $name,
                'category_id'  => $cat_id,
                'bemerkung'    => $bemerkung,
                'image_url'    => $image_url ?: null,
                'submitted_by' => $submitter ?: null,
                'status'       => 'pending',
            ),
            array( '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
        );

        if ( false === $inserted ) {
            wp_send_json_error( array( 'message' => __( 'Fehler beim Speichern des Links.', 'wordpress-linkliste' ) ) );
        }

        // Admin-Benachrichtigungs-E-Mail (nur wenn Einstellung aktiv)
        if ( '1' === get_option( 'wll_notify_admin', '1' ) ) {
            self::notify_admin_new_link( $name, $url );
        }

        wp_send_json_success( array(
            'message' => __( 'Dein Link wurde eingereicht und wird nach Prüfung freigeschaltet. Danke!', 'wordpress-linkliste' ),
        ) );
    }

    /** Klick tracken */
    public static function handle_track_click() {
        check_ajax_referer( 'wll_public_nonce', 'nonce' );
        $link_id = (int) ( $_POST['link_id'] ?? 0 );
        if ( $link_id > 0 ) {
            WLL_Database::increment_views( $link_id );
        }
        wp_send_json_success();
    }

    /** Link bewerten */
    public static function handle_rate_link() {
        check_ajax_referer( 'wll_public_nonce', 'nonce' );
        $link_id = (int) ( $_POST['link_id'] ?? 0 );
        $rating  = (int) ( $_POST['rating'] ?? 0 );

        if ( $link_id < 1 || $rating < 1 || $rating > 5 ) {
            wp_send_json_error( array( 'message' => 'Ungültige Bewertung.' ) );
        }

        // Session zuerst starten (AJAX-Requests durchlaufen wp_head nicht)
        if ( ! session_id() ) {
            session_start();
        }

        // Verhindere doppelte Bewertungen per Session
        $rated_key = 'wll_rated_' . $link_id;
        if ( isset( $_SESSION[ $rated_key ] ) ) {
            wp_send_json_error( array( 'message' => __( 'Du hast diesen Link bereits bewertet.', 'wordpress-linkliste' ) ) );
        }

        WLL_Database::add_rating( $link_id, $rating );
        $_SESSION[ $rated_key ] = true;

        wp_send_json_success( array( 'message' => __( 'Bewertung gespeichert!', 'wordpress-linkliste' ) ) );
    }

    /** Links per AJAX laden (Filter / Suche) */
    public static function handle_load_links() {
        check_ajax_referer( 'wll_public_nonce', 'nonce' );

        $result     = WLL_Database::get_approved_links( array(
            'category_id' => (int) ( $_POST['category_id'] ?? 0 ),
            'search'      => sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) ),
            'orderby'     => sanitize_key( $_POST['orderby'] ?? 'created_at' ),
            'order'       => 'DESC',
            'per_page'    => (int) ( $_POST['per_page'] ?? 12 ),
            'paged'       => (int) ( $_POST['paged'] ?? 1 ),
        ) );
        $categories = WLL_Database::get_categories();

        ob_start();
        WLL_Frontend::load_template( 'link-cards', array(
            'links'      => $result['links'],
            'categories' => $categories,
        ) );
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html'  => $html,
            'total' => $result['total'],
        ) );
    }

    /** Neue Kategorie vorschlagen (wird direkt erstellt, wenn Admin; sonst pending) */
    public static function handle_add_category() {
        check_ajax_referer( 'wll_submit_nonce', 'nonce' );

        global $wpdb;

        $name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'Kategoriename darf nicht leer sein.', 'wordpress-linkliste' ) ) );
        }

        $slug = sanitize_title( $name );

        // Bereits vorhanden?
        $existing = $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wll_categories WHERE slug = %s", $slug )
        );
        if ( $existing ) {
            wp_send_json_error( array( 'message' => __( 'Diese Kategorie existiert bereits.', 'wordpress-linkliste' ) ) );
        }

        $wpdb->insert(
            $wpdb->prefix . 'wll_categories',
            array( 'name' => $name, 'slug' => $slug, 'color' => '#3498db' ),
            array( '%s', '%s', '%s' )
        );

        $new_id = (int) $wpdb->insert_id;
        wp_send_json_success( array(
            'id'   => $new_id,
            'name' => $name,
            'slug' => $slug,
        ) );
    }

    // ------------------------------------------------------------------
    // Admin-Handler
    // ------------------------------------------------------------------

    public static function handle_approve_link() {
        self::require_admin();
        check_ajax_referer( 'wll_admin_nonce', 'nonce' );
        self::update_link_status( (int) ( $_POST['link_id'] ?? 0 ), 'approved' );
    }

    public static function handle_reject_link() {
        self::require_admin();
        check_ajax_referer( 'wll_admin_nonce', 'nonce' );
        self::update_link_status( (int) ( $_POST['link_id'] ?? 0 ), 'rejected' );
    }

    public static function handle_delete_link() {
        self::require_admin();
        check_ajax_referer( 'wll_admin_nonce', 'nonce' );
        global $wpdb;
        $link_id = (int) ( $_POST['link_id'] ?? 0 );
        $wpdb->delete( $wpdb->prefix . 'wll_links', array( 'id' => $link_id ), array( '%d' ) );
        wp_send_json_success( array( 'message' => 'Link gelöscht.' ) );
    }

    // ------------------------------------------------------------------
    // Hilfsmethoden
    // ------------------------------------------------------------------

    private static function require_admin() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Keine Berechtigung.' ), 403 );
        }
    }

    private static function update_link_status( int $link_id, string $status ) {
        global $wpdb;
        if ( $link_id < 1 ) {
            wp_send_json_error( array( 'message' => 'Ungültige ID.' ) );
        }
        $wpdb->update(
            $wpdb->prefix . 'wll_links',
            array( 'status' => $status ),
            array( 'id'     => $link_id ),
            array( '%s' ),
            array( '%d' )
        );
        wp_send_json_success( array( 'message' => 'Status aktualisiert.' ) );
    }

    private static function notify_admin_new_link( string $name, string $url ) {
        $admin_email = get_option( 'admin_email' );
        $subject     = sprintf( '[%s] Neuer Link eingereicht: %s', get_bloginfo( 'name' ), $name );
        $message     = sprintf(
            "Ein neuer Link wurde eingereicht und wartet auf Genehmigung:\n\nName: %s\nURL: %s\n\nZum Genehmigen: %s",
            $name,
            $url,
            admin_url( 'admin.php?page=wll-pending' )
        );
        wp_mail( $admin_email, $subject, $message );
    }
}
