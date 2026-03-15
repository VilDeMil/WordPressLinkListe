<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wll-wrapper" id="wll-app">

    <!-- ── Kopfbereich ──────────────────────────────────────────── -->
    <div class="wll-header">
        <h2 class="wll-page-title"><?php echo esc_html( get_option( 'wll_links_page_title', 'Unsere Linkliste' ) ); ?></h2>
        <p class="wll-subtitle">
            <?php printf(
                _n( '%s genehmigter Link', '%s genehmigte Links', $total, 'wordpress-linkliste' ),
                number_format_i18n( $total )
            ); ?>
        </p>
    </div>

    <!-- ── Suche & Filter ───────────────────────────────────────── -->
    <div class="wll-controls">
        <div class="wll-search-box">
            <span class="wll-search-icon">🔍</span>
            <input type="text" id="wll-search-input" placeholder="Links durchsuchen…"
                   value="<?php echo esc_attr( $search ); ?>" autocomplete="off">
            <?php if ( $search ) : ?>
                <button class="wll-search-clear" id="wll-clear-search" title="Suche löschen">✕</button>
            <?php endif; ?>
        </div>

        <div class="wll-filter-row">
            <div class="wll-category-filter">
                <button class="wll-cat-btn <?php echo 0 === $category_id ? 'active' : ''; ?>" data-cat="0">
                    Alle
                </button>
                <?php foreach ( $categories as $cat ) : ?>
                    <button class="wll-cat-btn <?php echo (int) $category_id === (int) $cat->id ? 'active' : ''; ?>"
                            data-cat="<?php echo esc_attr( $cat->id ); ?>"
                            style="--cat-color: <?php echo esc_attr( $cat->color ); ?>">
                        <?php if ( $cat->icon ) : ?>
                            <span class="dashicons <?php echo esc_attr( $cat->icon ); ?>"></span>
                        <?php endif; ?>
                        <?php echo esc_html( $cat->name ); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="wll-sort-box">
                <label for="wll-sort">Sortieren:</label>
                <select id="wll-sort">
                    <option value="created_at">Neueste zuerst</option>
                    <option value="name">Name A–Z</option>
                    <option value="views">Meistaufgerufen</option>
                    <option value="rating">Beste Bewertung</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ── Link-Karten ──────────────────────────────────────────── -->
    <div class="wll-grid" id="wll-links-grid">
        <?php WLL_Frontend::load_template( 'link-cards', array( 'links' => $links, 'categories' => $categories ) ); ?>
    </div>

    <!-- ── Lade-Indikator ───────────────────────────────────────── -->
    <div class="wll-spinner" id="wll-spinner" style="display:none">
        <div class="wll-spin-circle"></div>
        <span>Lade Links…</span>
    </div>

    <!-- ── Pagination / "Mehr laden" ────────────────────────────── -->
    <?php
    $total_pages = ceil( $total / $per_page );
    if ( $total_pages > 1 ) :
    ?>
        <div class="wll-pagination" id="wll-pagination"
             data-current="<?php echo esc_attr( $paged ); ?>"
             data-total="<?php echo esc_attr( $total_pages ); ?>">
            <?php if ( $paged < $total_pages ) : ?>
                <button class="wll-btn wll-load-more" id="wll-load-more">
                    Mehr laden (<?php echo esc_html( $total - ( $paged * $per_page ) ); ?> weitere)
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ── Link einreichen ──────────────────────────────────────── -->
    <div class="wll-submit-teaser">
        <p>Fehlt ein Link? <a href="#wll-submit-modal" class="wll-open-submit">Link einreichen</a></p>
    </div>

    <!-- ── Modal: Link einreichen ───────────────────────────────── -->
    <?php WLL_Frontend::load_template( 'submit-form', array( 'categories' => $categories ) ); ?>

</div><!-- .wll-wrapper -->

<!-- Verstecktes Daten-Objekt für JS -->
<script>
window.wllState = {
    categoryId: <?php echo (int) $category_id; ?>,
    search:     <?php echo wp_json_encode( $search ); ?>,
    paged:      <?php echo (int) $paged; ?>,
    perPage:    <?php echo (int) $per_page; ?>,
    totalPages: <?php echo (int) ceil( $total / $per_page ); ?>,
    orderby:    'created_at',
    order:      'DESC'
};
</script>
