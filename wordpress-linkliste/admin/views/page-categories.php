<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wll-admin-wrap">
    <h1>
        <span class="dashicons dashicons-category"></span>
        Kategorien verwalten
    </h1>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Kategorie gespeichert!</p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-info is-dismissible"><p>Kategorie gelöscht.</p></div>
    <?php endif; ?>

    <div class="wll-two-col">
        <!-- Formular: neue / vorhandene Kategorie -->
        <div class="wll-card">
            <h2><?php echo $edit_cat ? 'Kategorie bearbeiten' : 'Neue Kategorie anlegen'; ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'wll_save_category' ); ?>
                <?php if ( $edit_cat ) : ?>
                    <input type="hidden" name="cat_id" value="<?php echo esc_attr( $edit_cat->id ); ?>">
                <?php endif; ?>

                <table class="form-table">
                    <tr>
                        <th><label for="cat_name">Name *</label></th>
                        <td><input type="text" id="cat_name" name="cat_name" class="regular-text"
                                   value="<?php echo esc_attr( $edit_cat->name ?? '' ); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="cat_description">Beschreibung</label></th>
                        <td><textarea id="cat_description" name="cat_description" rows="3" class="large-text"><?php
                            echo esc_textarea( $edit_cat->description ?? '' );
                        ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="cat_color">Farbe</label></th>
                        <td><input type="color" id="cat_color" name="cat_color"
                                   value="<?php echo esc_attr( $edit_cat->color ?? '#3498db' ); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="cat_icon">Dashicon-Klasse</label></th>
                        <td>
                            <input type="text" id="cat_icon" name="cat_icon" class="regular-text"
                                   placeholder="z.B. dashicons-admin-links"
                                   value="<?php echo esc_attr( $edit_cat->icon ?? '' ); ?>">
                            <p class="description">
                                Alle Icons: <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons Übersicht</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="wll_save_category" class="button button-primary">
                        <?php echo $edit_cat ? 'Aktualisieren' : 'Kategorie speichern'; ?>
                    </button>
                    <?php if ( $edit_cat ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wll-categories' ) ); ?>" class="button">Abbrechen</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>

        <!-- Kategorien-Liste -->
        <div class="wll-card">
            <h2>Vorhandene Kategorien (<?php echo count( $categories ); ?>)</h2>
            <?php if ( empty( $categories ) ) : ?>
                <p>Noch keine Kategorien vorhanden.</p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Farbe</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $categories as $cat ) : ?>
                            <tr>
                                <td>
                                    <span class="wll-color-dot" style="background:<?php echo esc_attr( $cat->color ); ?>"></span>
                                </td>
                                <td>
                                    <?php if ( $cat->icon ) : ?>
                                        <span class="dashicons <?php echo esc_attr( $cat->icon ); ?>"></span>
                                    <?php endif; ?>
                                    <?php echo esc_html( $cat->name ); ?>
                                </td>
                                <td><code><?php echo esc_html( $cat->slug ); ?></code></td>
                                <td>
                                    <a class="button button-small"
                                       href="<?php echo esc_url( admin_url( 'admin.php?page=wll-categories&edit=' . $cat->id ) ); ?>">
                                        Bearbeiten
                                    </a>
                                    <a class="button button-small wll-btn-danger"
                                       href="<?php echo esc_url( wp_nonce_url(
                                           admin_url( 'admin.php?page=wll-categories&wll_action=delete_cat&cat_id=' . $cat->id ),
                                           'wll_delete_cat_' . $cat->id
                                       ) ); ?>"
                                       onclick="return confirm('Kategorie wirklich löschen?');">
                                        Löschen
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
