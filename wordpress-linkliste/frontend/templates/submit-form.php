<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!-- Modal Overlay -->
<div class="wll-modal-overlay" id="wll-submit-modal" role="dialog" aria-modal="true" aria-labelledby="wll-modal-title" style="display:none">
    <div class="wll-modal">
        <div class="wll-modal-header">
            <h2 id="wll-modal-title">🔗 Neuen Link einreichen</h2>
            <button class="wll-modal-close" aria-label="Schließen">✕</button>
        </div>

        <div class="wll-modal-body">
            <p class="wll-modal-info">
                Dein Link wird nach einer kurzen Prüfung durch unser Team freigeschaltet.
            </p>

            <form id="wll-submit-form" novalidate>
                <div class="wll-form-row wll-form-row--required">
                    <label for="wll-field-url">URL (Link) *</label>
                    <input type="url" id="wll-field-url" name="url"
                           placeholder="https://beispiel.de" required autocomplete="url">
                    <span class="wll-field-error"></span>
                </div>

                <div class="wll-form-row wll-form-row--required">
                    <label for="wll-field-name">Name / Titel *</label>
                    <input type="text" id="wll-field-name" name="name"
                           placeholder="Kurzer, beschreibender Name" required maxlength="255">
                    <span class="wll-field-error"></span>
                </div>

                <div class="wll-form-row">
                    <label for="wll-field-category">Kategorie</label>
                    <div class="wll-category-select-wrap">
                        <select id="wll-field-category" name="category_id">
                            <option value="0">— Bitte wählen —</option>
                            <?php foreach ( $categories as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->id ); ?>">
                                    <?php echo esc_html( $cat->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="wll-btn-add-cat" id="wll-btn-add-cat" title="Neue Kategorie vorschlagen">
                            + Neue Kategorie
                        </button>
                    </div>
                    <!-- Neue Kategorie -->
                    <div class="wll-new-cat-input" id="wll-new-cat-input" style="display:none">
                        <input type="text" id="wll-new-cat-name" placeholder="Kategoriename">
                        <button type="button" class="wll-btn wll-btn-primary" id="wll-save-cat">Speichern</button>
                        <button type="button" class="wll-btn" id="wll-cancel-cat">Abbrechen</button>
                    </div>
                </div>

                <div class="wll-form-row">
                    <label for="wll-field-bemerkung">Bemerkung / Beschreibung</label>
                    <textarea id="wll-field-bemerkung" name="bemerkung"
                              placeholder="Worum geht es auf dieser Seite?" rows="3" maxlength="1000"></textarea>
                    <span class="wll-char-count"><span id="wll-bemerkung-count">0</span>/1000</span>
                </div>

                <?php if ( get_option( 'wll_allow_images', '1' ) ) : ?>
                    <div class="wll-form-row">
                        <label for="wll-field-image">Bild-URL (optional)</label>
                        <input type="url" id="wll-field-image" name="image_url"
                               placeholder="https://beispiel.de/bild.jpg">
                        <div class="wll-image-preview" id="wll-image-preview" style="display:none">
                            <img id="wll-preview-img" src="" alt="Vorschau">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="wll-form-row">
                    <label for="wll-field-submitter">Dein Name (optional)</label>
                    <input type="text" id="wll-field-submitter" name="submitted_by"
                           placeholder="Anonym" maxlength="255">
                </div>

                <!-- Feedback-Bereich -->
                <div class="wll-form-feedback" id="wll-form-feedback" style="display:none"></div>

                <div class="wll-form-actions">
                    <button type="submit" class="wll-btn wll-btn-primary wll-btn-submit" id="wll-btn-submit">
                        Link einreichen
                    </button>
                    <button type="button" class="wll-btn wll-modal-close">Abbrechen</button>
                </div>
            </form>
        </div>
    </div>
</div>
