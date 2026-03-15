<?php
/**
 * Backend-Verwaltung für die WordPress LinkListe.
 *
 * Menüstruktur im WordPress-Dashboard:
 *   LinkListe
 *     ├── Alle Links
 *     ├── Ausstehend  (Badge mit Anzahl)
 *     ├── Kategorien
 *     └── Einstellungen
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WLL_Admin {

    public static function init() {
        add_action( 'admin_menu',            array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_init',            array( __CLASS__, 'handle_form_actions' ) );
    }

    // ------------------------------------------------------------------
    // Menüs
    // ------------------------------------------------------------------

    public static function register_menus() {
        $pending_count = self::get_pending_count();
        $badge         = $pending_count
            ? ' <span class="awaiting-mod">' . esc_html( $pending_count ) . '</span>'
            : '';

        add_menu_page(
            'WordPress LinkListe',
            'LinkListe' . $badge,
            'manage_options',
            'wll-links',
            array( __CLASS__, 'page_all_links' ),
            'dashicons-admin-links',
            26
        );

        add_submenu_page( 'wll-links', 'Alle Links', 'Alle Links',       'manage_options', 'wll-links',       array( __CLASS__, 'page_all_links' ) );
        add_submenu_page( 'wll-links', 'Ausstehend', 'Ausstehend' . $badge, 'manage_options', 'wll-pending',  array( __CLASS__, 'page_pending' ) );
        add_submenu_page( 'wll-links', 'Kategorien', 'Kategorien',       'manage_options', 'wll-categories',  array( __CLASS__, 'page_categories' ) );
        add_submenu_page( 'wll-links', 'Einstellungen', 'Einstellungen', 'manage_options', 'wll-settings',    array( __CLASS__, 'page_settings' ) );
    }

    // ------------------------------------------------------------------
    // Assets
    // ------------------------------------------------------------------

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'wll-' ) === false ) {
            return;
        }
        wp_enqueue_style(
            'wll-admin-style',
            WLL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WLL_VERSION
        );
        wp_enqueue_script(
            'wll-admin-script',
            WLL_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            WLL_VERSION,
            true
        );
        wp_localize_script( 'wll-admin-script', 'wllAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wll_admin_nonce' ),
            'i18n'    => array(
                'confirm_delete'  => __( 'Link wirklich löschen?', 'wordpress-linkliste' ),
                'confirm_reject'  => __( 'Link ablehnen?', 'wordpress-linkliste' ),
                'action_approved' => __( 'Genehmigt!', 'wordpress-linkliste' ),
                'action_rejected' => __( 'Abgelehnt.', 'wordpress-linkliste' ),
                'action_deleted'  => __( 'Gelöscht.', 'wordpress-linkliste' ),
            ),
        ) );
    }

    // ------------------------------------------------------------------
    // Formular-Aktionen (nicht-AJAX)
    // ------------------------------------------------------------------

    public static function handle_form_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Kategorie speichern
        if ( isset( $_POST['wll_save_category'] ) && check_admin_referer( 'wll_save_category' ) ) {
            global $wpdb;
            $name  = sanitize_text_field( wp_unslash( $_POST['cat_name'] ?? '' ) );
            $desc  = sanitize_textarea_field( wp_unslash( $_POST['cat_description'] ?? '' ) );
            $color = sanitize_hex_color( $_POST['cat_color'] ?? '#3498db' );
            $icon  = sanitize_text_field( wp_unslash( $_POST['cat_icon'] ?? '' ) );
            $id    = (int) ( $_POST['cat_id'] ?? 0 );

            if ( ! empty( $name ) ) {
                $slug = sanitize_title( $name );
                if ( $id > 0 ) {
                    $wpdb->update(
                        $wpdb->prefix . 'wll_categories',
                        array( 'name' => $name, 'slug' => $slug, 'description' => $desc, 'color' => $color, 'icon' => $icon ),
                        array( 'id' => $id ),
                        array( '%s', '%s', '%s', '%s', '%s' ),
                        array( '%d' )
                    );
                } else {
                    $wpdb->insert(
                        $wpdb->prefix . 'wll_categories',
                        array( 'name' => $name, 'slug' => $slug, 'description' => $desc, 'color' => $color, 'icon' => $icon ),
                        array( '%s', '%s', '%s', '%s', '%s' )
                    );
                }
            }
            wp_safe_redirect( admin_url( 'admin.php?page=wll-categories&saved=1' ) );
            exit;
        }

        // Kategorie löschen
        if ( isset( $_GET['wll_action'], $_GET['cat_id'] ) && 'delete_cat' === $_GET['wll_action']
            && check_admin_referer( 'wll_delete_cat_' . (int) $_GET['cat_id'] ) ) {
            global $wpdb;
            $wpdb->delete( $wpdb->prefix . 'wll_categories', array( 'id' => (int) $_GET['cat_id'] ), array( '%d' ) );
            wp_safe_redirect( admin_url( 'admin.php?page=wll-categories&deleted=1' ) );
            exit;
        }

        // Einstellungen speichern
        if ( isset( $_POST['wll_save_settings'] ) && check_admin_referer( 'wll_save_settings' ) ) {
            $fields = array(
                'wll_per_page'          => 'absint',
                'wll_allow_images'      => 'sanitize_text_field',
                'wll_notify_admin'      => 'sanitize_text_field',
                'wll_links_page_title'  => 'sanitize_text_field',
            );
            foreach ( $fields as $key => $sanitize ) {
                $val = call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ?? '' ) );
                update_option( $key, $val );
            }
            wp_safe_redirect( admin_url( 'admin.php?page=wll-settings&saved=1' ) );
            exit;
        }
    }

    // ------------------------------------------------------------------
    // Admin-Seiten
    // ------------------------------------------------------------------

    public static function page_all_links() {
        global $wpdb;
        $status  = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'approved';
        $search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
        $paged   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
        $per_page = 20;

        $where  = $wpdb->prepare( 'WHERE status = %s', $status );
        if ( $search ) {
            $like   = '%' . $wpdb->esc_like( $search ) . '%';
            $where .= $wpdb->prepare( ' AND (name LIKE %s OR url LIKE %s)', $like, $like );
        }

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wll_links $where" );
        $offset = ( $paged - 1 ) * $per_page;
        $links  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT l.*, c.name AS category_name
                 FROM {$wpdb->prefix}wll_links l
                 LEFT JOIN {$wpdb->prefix}wll_categories c ON c.id = l.category_id
                 $where ORDER BY l.created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        $categories = WLL_Database::get_categories();
        require WLL_PLUGIN_DIR . 'admin/views/page-all-links.php';
    }

    public static function page_pending() {
        global $wpdb;
        $links = $wpdb->get_results(
            "SELECT l.*, c.name AS category_name
             FROM {$wpdb->prefix}wll_links l
             LEFT JOIN {$wpdb->prefix}wll_categories c ON c.id = l.category_id
             WHERE l.status = 'pending'
             ORDER BY l.created_at ASC"
        );
        require WLL_PLUGIN_DIR . 'admin/views/page-pending.php';
    }

    public static function page_categories() {
        global $wpdb;
        $categories = WLL_Database::get_categories();
        $edit_cat   = null;
        if ( isset( $_GET['edit'] ) ) {
            $edit_cat = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wll_categories WHERE id = %d", (int) $_GET['edit'] )
            );
        }
        require WLL_PLUGIN_DIR . 'admin/views/page-categories.php';
    }

    public static function page_settings() {
        require WLL_PLUGIN_DIR . 'admin/views/page-settings.php';
    }

    // ------------------------------------------------------------------
    // Hilfsmethoden
    // ------------------------------------------------------------------

    private static function get_pending_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wll_links WHERE status = 'pending'" );
    }
}
