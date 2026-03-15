<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( empty( $links ) ) : ?>
    <p class="wll-no-results">Noch keine Links vorhanden.</p>
<?php else : ?>
    <ol class="wll-top-links-list">
        <?php foreach ( $links as $i => $link ) : ?>
            <li class="wll-top-link-item">
                <span class="wll-top-rank"><?php echo esc_html( $i + 1 ); ?>.</span>
                <a href="<?php echo esc_url( $link->url ); ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="wll-link-click"
                   data-id="<?php echo esc_attr( $link->id ); ?>">
                    <?php echo esc_html( $link->name ); ?>
                </a>
                <span class="wll-top-views">👁 <?php echo esc_html( number_format_i18n( $link->views ) ); ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
