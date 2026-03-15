<?php
/**
 * Datenbankinstallation & -verwaltung für die WordPress LinkListe.
 *
 * Tabellen:
 *   {prefix}wll_links      – Haupttabelle mit allen Linkeinträgen
 *   {prefix}wll_categories – Eigene Kategorien (zusätzlich zu WordPress-Taxonomien)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WLL_Database {

    /**
     * Plugin installieren – Tabellen anlegen / aktualisieren.
     */
    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tabelle: Links
        $table_links = $wpdb->prefix . 'wll_links';
        $sql_links = "CREATE TABLE $table_links (
            id           BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
            url          VARCHAR(2048)         NOT NULL,
            name         VARCHAR(255)          NOT NULL,
            category_id  BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
            bemerkung    TEXT,
            image_url    VARCHAR(2048)         DEFAULT NULL,
            status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            views        BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
            rating_sum   BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
            rating_count BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
            submitted_by VARCHAR(255)          DEFAULT NULL,
            created_at   DATETIME              NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at   DATETIME              NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_status      (status),
            KEY idx_category_id (category_id),
            KEY idx_created_at  (created_at),
            FULLTEXT KEY ft_search (name, url, bemerkung)
        ) ENGINE=InnoDB $charset_collate;";

        // Tabelle: Kategorien
        $table_cats = $wpdb->prefix . 'wll_categories';
        $sql_cats = "CREATE TABLE $table_cats (
            id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
            name        VARCHAR(255)          NOT NULL,
            slug        VARCHAR(255)          NOT NULL,
            description TEXT,
            icon        VARCHAR(100)          DEFAULT NULL,
            color       VARCHAR(7)            DEFAULT '#3498db',
            sort_order  INT(11)               NOT NULL DEFAULT 0,
            created_at  DATETIME              NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_slug (slug)
        ) ENGINE=InnoDB $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_links );
        dbDelta( $sql_cats );

        update_option( 'wll_db_version', WLL_VERSION );

        // Standard-Kategorien einfügen, falls noch keine vorhanden
        self::maybe_insert_default_categories();
    }

    /**
     * Legt Standardkategorien an, wenn die Tabelle leer ist.
     */
    private static function maybe_insert_default_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'wll_categories';

        if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) > 0 ) {
            return;
        }

        $defaults = array(
            array( 'name' => 'Technologie',   'slug' => 'technologie',   'icon' => 'dashicons-admin-tools',     'color' => '#2980b9' ),
            array( 'name' => 'Nachrichten',   'slug' => 'nachrichten',   'icon' => 'dashicons-media-document',  'color' => '#27ae60' ),
            array( 'name' => 'Unterhaltung',  'slug' => 'unterhaltung',  'icon' => 'dashicons-video-alt3',      'color' => '#8e44ad' ),
            array( 'name' => 'Bildung',       'slug' => 'bildung',       'icon' => 'dashicons-welcome-learn-more','color' => '#e67e22' ),
            array( 'name' => 'Soziale Medien','slug' => 'soziale-medien','icon' => 'dashicons-share',           'color' => '#e74c3c' ),
            array( 'name' => 'Sonstiges',     'slug' => 'sonstiges',     'icon' => 'dashicons-category',        'color' => '#7f8c8d' ),
        );

        foreach ( $defaults as $i => $cat ) {
            $wpdb->insert(
                $table,
                array(
                    'name'       => $cat['name'],
                    'slug'       => $cat['slug'],
                    'icon'       => $cat['icon'],
                    'color'      => $cat['color'],
                    'sort_order' => $i,
                ),
                array( '%s', '%s', '%s', '%s', '%d' )
            );
        }
    }

    /** Plugin-Deaktivierung (Tabellen bleiben erhalten). */
    public static function deactivate() {
        // Nichts zu tun – Daten bleiben bei Deaktivierung erhalten.
    }

    // ---------------------------------------------------------------
    // Hilfsmethoden für Link-Abfragen
    // ---------------------------------------------------------------

    /**
     * Gibt alle Kategorien zurück.
     *
     * @return array
     */
    public static function get_categories() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wll_categories ORDER BY sort_order ASC, name ASC"
        );
    }

    /**
     * Gibt genehmigte Links zurück, optional gefiltert und durchsucht.
     *
     * @param array $args {
     *   @type int    $category_id  0 = alle Kategorien
     *   @type string $search       Suchbegriff
     *   @type string $orderby      name|views|created_at|rating
     *   @type string $order        ASC|DESC
     *   @type int    $per_page
     *   @type int    $paged
     * }
     * @return array { links: WP_Object[], total: int }
     */
    public static function get_approved_links( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'category_id' => 0,
            'search'      => '',
            'orderby'     => 'created_at',
            'order'       => 'DESC',
            'per_page'    => 12,
            'paged'       => 1,
        );
        $args = wp_parse_args( $args, $defaults );

        $where  = "WHERE l.status = 'approved'";
        $values = array();

        if ( ! empty( $args['category_id'] ) ) {
            $where   .= ' AND l.category_id = %d';
            $values[] = (int) $args['category_id'];
        }

        if ( ! empty( $args['search'] ) ) {
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where   .= ' AND (l.name LIKE %s OR l.url LIKE %s OR l.bemerkung LIKE %s)';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }

        // Sicheres ORDER BY
        $allowed_order   = array( 'name', 'views', 'created_at' );
        $orderby         = in_array( $args['orderby'], $allowed_order, true ) ? $args['orderby'] : 'created_at';
        $order           = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        if ( 'rating' === $args['orderby'] ) {
            $orderby_sql = 'CASE WHEN l.rating_count = 0 THEN 0 ELSE l.rating_sum / l.rating_count END ' . $order;
        } else {
            $orderby_sql = "l.{$orderby} {$order}";
        }

        $offset = ( (int) $args['paged'] - 1 ) * (int) $args['per_page'];

        $table_l = $wpdb->prefix . 'wll_links';
        $table_c = $wpdb->prefix . 'wll_categories';

        $count_sql = "SELECT COUNT(*) FROM $table_l l $where";
        $total     = empty( $values )
            ? (int) $wpdb->get_var( $count_sql )
            : (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $values ) );

        $query_sql = "SELECT l.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
                      FROM $table_l l
                      LEFT JOIN $table_c c ON c.id = l.category_id
                      $where
                      ORDER BY $orderby_sql
                      LIMIT %d OFFSET %d";
        $values[]  = (int) $args['per_page'];
        $values[]  = $offset;

        $links = $wpdb->get_results( $wpdb->prepare( $query_sql, $values ) );

        return array(
            'links' => $links ? $links : array(),
            'total' => $total,
        );
    }

    /**
     * Zählt einen Seitenaufruf für einen Link hoch.
     *
     * @param int $link_id
     */
    public static function increment_views( $link_id ) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}wll_links SET views = views + 1 WHERE id = %d",
                (int) $link_id
            )
        );
    }

    /**
     * Speichert eine Bewertung (1–5 Sterne).
     *
     * @param int $link_id
     * @param int $rating  1–5
     */
    public static function add_rating( $link_id, $rating ) {
        global $wpdb;
        $rating = max( 1, min( 5, (int) $rating ) );
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}wll_links
                 SET rating_sum = rating_sum + %d, rating_count = rating_count + 1
                 WHERE id = %d",
                $rating,
                (int) $link_id
            )
        );
    }
}
