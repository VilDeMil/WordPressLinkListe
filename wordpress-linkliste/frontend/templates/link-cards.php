<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( empty( $links ) ) : ?>
    <div class="wll-no-results">
        <span class="wll-no-results-icon">🔍</span>
        <p>Keine Links gefunden. <a href="#wll-submit-modal" class="wll-open-submit">Ersten Link einreichen?</a></p>
    </div>
<?php else : ?>
    <?php foreach ( $links as $link ) :
        $rating_avg = $link->rating_count > 0
            ? round( $link->rating_sum / $link->rating_count, 1 )
            : 0;
        $stars_full  = (int) floor( $rating_avg );
        $has_half    = ( $rating_avg - $stars_full ) >= 0.5;
        $domain      = wp_parse_url( $link->url, PHP_URL_HOST );
        $favicon_url = 'https://www.google.com/s2/favicons?domain=' . urlencode( $domain ) . '&sz=32';
    ?>
        <article class="wll-card" data-id="<?php echo esc_attr( $link->id ); ?>">

            <!-- Kategorie-Badge -->
            <?php if ( $link->category_name ) : ?>
                <span class="wll-badge" style="background:<?php echo esc_attr( $link->category_color ?? '#3498db' ); ?>">
                    <?php if ( $link->category_icon ) : ?>
                        <span class="dashicons <?php echo esc_attr( $link->category_icon ); ?>"></span>
                    <?php endif; ?>
                    <?php echo esc_html( $link->category_name ); ?>
                </span>
            <?php endif; ?>

            <!-- Vorschaubild / Favicon -->
            <div class="wll-card-image">
                <?php if ( ! empty( $link->image_url ) ) : ?>
                    <img src="<?php echo esc_url( $link->image_url ); ?>"
                         alt="<?php echo esc_attr( $link->name ); ?>"
                         loading="lazy"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="wll-favicon-fallback" style="display:none">
                        <img src="<?php echo esc_url( $favicon_url ); ?>" alt="" width="32" height="32">
                    </div>
                <?php else : ?>
                    <div class="wll-favicon-fallback">
                        <img src="<?php echo esc_url( $favicon_url ); ?>" alt="" width="32" height="32">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Inhalt -->
            <div class="wll-card-body">
                <h3 class="wll-card-title">
                    <a href="<?php echo esc_url( $link->url ); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="wll-link-click"
                       data-id="<?php echo esc_attr( $link->id ); ?>">
                        <?php echo esc_html( $link->name ); ?>
                    </a>
                </h3>

                <p class="wll-card-domain"><?php echo esc_html( $domain ); ?></p>

                <?php if ( $link->bemerkung ) : ?>
                    <p class="wll-card-bemerkung"><?php echo esc_html( $link->bemerkung ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="wll-card-footer">
                <!-- Sternebewertung -->
                <div class="wll-stars" data-id="<?php echo esc_attr( $link->id ); ?>" title="Bewertung: <?php echo esc_attr( $rating_avg ); ?>/5">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <span class="wll-star <?php echo $i <= $stars_full ? 'filled' : ( $i === $stars_full + 1 && $has_half ? 'half' : 'empty' ); ?>"
                              data-value="<?php echo esc_attr( $i ); ?>">★</span>
                    <?php endfor; ?>
                    <?php if ( $link->rating_count > 0 ) : ?>
                        <span class="wll-rating-text"><?php echo esc_html( $rating_avg ); ?> (<?php echo esc_html( $link->rating_count ); ?>)</span>
                    <?php endif; ?>
                </div>

                <!-- Meta-Infos -->
                <div class="wll-card-meta">
                    <span class="wll-views" title="Aufrufe">👁 <?php echo esc_html( number_format_i18n( $link->views ) ); ?></span>
                    <span class="wll-date" title="Hinzugefügt am"><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $link->created_at ) ) ); ?></span>
                </div>

                <!-- Aktions-Buttons -->
                <div class="wll-card-actions">
                    <a href="<?php echo esc_url( $link->url ); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="wll-btn wll-btn-primary wll-link-click"
                       data-id="<?php echo esc_attr( $link->id ); ?>">
                        Besuchen →
                    </a>
                    <button class="wll-btn wll-btn-copy" data-url="<?php echo esc_attr( $link->url ); ?>" title="Link kopieren">
                        📋
                    </button>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
