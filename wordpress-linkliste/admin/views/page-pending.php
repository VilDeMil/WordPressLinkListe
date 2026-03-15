<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wll-admin-wrap">
    <h1>
        <span class="dashicons dashicons-clock"></span>
        Ausstehende Links (<?php echo count( $links ); ?>)
    </h1>
    <hr class="wp-header-end">

    <?php if ( empty( $links ) ) : ?>
        <div class="notice notice-success">
            <p>Keine ausstehenden Links – alles erledigt!</p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped wll-links-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Kategorie</th>
                    <th>Bemerkung</th>
                    <th>Eingereicht von</th>
                    <th>Datum</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $links as $link ) : ?>
                    <tr data-id="<?php echo esc_attr( $link->id ); ?>">
                        <td><strong><?php echo esc_html( $link->name ); ?></strong></td>
                        <td>
                            <a href="<?php echo esc_url( $link->url ); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html( $link->url ); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html( $link->category_name ?: '—' ); ?></td>
                        <td><?php echo esc_html( $link->bemerkung ?: '—' ); ?></td>
                        <td><?php echo esc_html( $link->submitted_by ?: 'Anonym' ); ?></td>
                        <td><?php echo esc_html( date_i18n( 'd.m.Y H:i', strtotime( $link->created_at ) ) ); ?></td>
                        <td class="wll-row-actions">
                            <button class="button button-primary wll-approve" data-id="<?php echo esc_attr( $link->id ); ?>">
                                ✔ Genehmigen
                            </button>
                            <button class="button wll-reject" data-id="<?php echo esc_attr( $link->id ); ?>">
                                ✖ Ablehnen
                            </button>
                            <button class="button wll-delete" data-id="<?php echo esc_attr( $link->id ); ?>">
                                🗑 Löschen
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
