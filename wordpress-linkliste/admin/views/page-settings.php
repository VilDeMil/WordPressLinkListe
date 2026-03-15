<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wll-admin-wrap">
    <h1>
        <span class="dashicons dashicons-admin-settings"></span>
        LinkListe – Einstellungen
    </h1>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Einstellungen gespeichert!</p></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'wll_save_settings' ); ?>

        <table class="form-table">
            <tr>
                <th><label for="wll_per_page">Links pro Seite</label></th>
                <td>
                    <input type="number" id="wll_per_page" name="wll_per_page" class="small-text"
                           value="<?php echo esc_attr( get_option( 'wll_per_page', 12 ) ); ?>" min="1" max="100">
                    <p class="description">Anzahl der Links, die im Frontend auf einer Seite angezeigt werden.</p>
                </td>
            </tr>
            <tr>
                <th><label for="wll_allow_images">Bilder erlauben</label></th>
                <td>
                    <input type="checkbox" id="wll_allow_images" name="wll_allow_images" value="1"
                        <?php checked( '1', get_option( 'wll_allow_images', '1' ) ); ?>>
                    <label for="wll_allow_images">Einreicher können ein Bild-URL angeben</label>
                </td>
            </tr>
            <tr>
                <th><label for="wll_notify_admin">Admin-E-Mail-Benachrichtigungen</label></th>
                <td>
                    <input type="checkbox" id="wll_notify_admin" name="wll_notify_admin" value="1"
                        <?php checked( '1', get_option( 'wll_notify_admin', '1' ) ); ?>>
                    <label for="wll_notify_admin">Bei neuen Einreichungen eine E-Mail senden</label>
                </td>
            </tr>
            <tr>
                <th><label for="wll_links_page_title">Seitentitel der Linkliste</label></th>
                <td>
                    <input type="text" id="wll_links_page_title" name="wll_links_page_title" class="regular-text"
                           value="<?php echo esc_attr( get_option( 'wll_links_page_title', 'Unsere Linkliste' ) ); ?>">
                </td>
            </tr>
        </table>

        <h2>Shortcode-Übersicht</h2>
        <table class="wp-list-table widefat fixed">
            <thead>
                <tr><th>Shortcode</th><th>Beschreibung</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[wll_linkliste]</code></td>
                    <td>Zeigt die komplette Linkliste mit Suche und Kategoriefilter.</td>
                </tr>
                <tr>
                    <td><code>[wll_linkliste per_page="6" category_id="2"]</code></td>
                    <td>Linkliste mit individuellen Parametern (Anzahl, Startkategorie).</td>
                </tr>
                <tr>
                    <td><code>[wll_submit_form]</code></td>
                    <td>Einreichungsformular für Besucher.</td>
                </tr>
                <tr>
                    <td><code>[wll_top_links count="5"]</code></td>
                    <td>Die meistaufgerufenen Links als Widget-Liste.</td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" name="wll_save_settings" class="button button-primary">Einstellungen speichern</button>
        </p>
    </form>
</div>
