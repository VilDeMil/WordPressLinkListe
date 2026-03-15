<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wll-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-links"></span>
        WordPress LinkListe – Alle Links
    </h1>
    <hr class="wp-header-end">

    <!-- Status-Tabs -->
    <ul class="subsubsub">
        <?php
        global $wpdb;
        $statuses = array( 'approved' => 'Genehmigt', 'pending' => 'Ausstehend', 'rejected' => 'Abgelehnt' );
        foreach ( $statuses as $s_key => $s_label ) :
            $count  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wll_links WHERE status = %s", $s_key ) );
            $active = ( $status === $s_key ) ? ' class="current"' : '';
        ?>
            <li>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wll-links&status=' . $s_key ) ); ?>"<?php echo $active; ?>>
                    <?php echo esc_html( $s_label ); ?> <span class="count">(<?php echo esc_html( $count ); ?>)</span>
                </a> |
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Suchformular -->
    <form method="get" action="">
        <input type="hidden" name="page" value="wll-links">
        <input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Links durchsuchen…">
            <button type="submit" class="button">Suchen</button>
        </p>
    </form>

    <table class="wp-list-table widefat fixed striped wll-links-table">
        <thead>
            <tr>
                <th width="30">ID</th>
                <th>Name</th>
                <th>URL</th>
                <th>Kategorie</th>
                <th>Aufrufe</th>
                <th>Eingereicht am</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $links ) ) : ?>
                <tr><td colspan="7">Keine Links gefunden.</td></tr>
            <?php else : ?>
                <?php foreach ( $links as $link ) : ?>
                    <tr data-id="<?php echo esc_attr( $link->id ); ?>">
                        <td><?php echo esc_html( $link->id ); ?></td>
                        <td>
                            <strong><?php echo esc_html( $link->name ); ?></strong>
                            <?php if ( $link->bemerkung ) : ?>
                                <br><small><?php echo esc_html( mb_substr( $link->bemerkung, 0, 80 ) ); ?>…</small>
                            <?php endif; ?>
                        </td>
                        <td><a href="<?php echo esc_url( $link->url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link->url ); ?></a></td>
                        <td><?php echo esc_html( $link->category_name ?: '—' ); ?></td>
                        <td><?php echo esc_html( number_format_i18n( $link->views ) ); ?></td>
                        <td><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $link->created_at ) ) ); ?></td>
                        <td class="wll-row-actions">
                            <?php if ( 'pending' === $link->status ) : ?>
                                <button class="button button-primary wll-approve" data-id="<?php echo esc_attr( $link->id ); ?>">Genehmigen</button>
                            <?php endif; ?>
                            <?php if ( 'approved' === $link->status ) : ?>
                                <button class="button wll-reject" data-id="<?php echo esc_attr( $link->id ); ?>">Ablehnen</button>
                            <?php endif; ?>
                            <button class="button wll-delete" data-id="<?php echo esc_attr( $link->id ); ?>">Löschen</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php
    $total_pages = ceil( $total / $per_page );
    if ( $total_pages > 1 ) :
        echo paginate_links( array(
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ) );
    endif;
    ?>
</div>
