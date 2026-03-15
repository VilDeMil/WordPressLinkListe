/**
 * WordPress LinkListe – Frontend-JavaScript
 *
 * Funktionen:
 *  - Echtzeit-Suche (debounced)
 *  - Kategorie-Filter (AJAX)
 *  - Sortierung (AJAX)
 *  - "Mehr laden" (append)
 *  - Klick-Tracking
 *  - 1-5 Sterne-Bewertung
 *  - Link in Zwischenablage kopieren
 *  - Einreichungsformular (Modal, Validierung, AJAX)
 *  - Neue Kategorie vorschlagen
 *  - Bildvorschau
 *  - Toast-Benachrichtigungen
 */

/* global jQuery, wllConfig, wllState */
(function ($) {
    'use strict';

    /* ── Toast ───────────────────────────────────────────────── */
    const toast = (function () {
        let $container;

        function init() {
            $container = $('<div id="wll-toast-container"></div>').appendTo('body');
        }

        function show(msg, type, duration) {
            type     = type     || 'info';
            duration = duration || 3000;
            const $t = $('<div class="wll-toast ' + type + '">' + $('<span>').text(msg).html() + '</div>');
            $container.append($t);
            setTimeout(function () { $t.fadeOut(300, function () { $t.remove(); }); }, duration);
        }

        return { init: init, show: show };
    }());

    /* ── Zustandsvariablen ───────────────────────────────────── */
    const state = window.wllState || {
        categoryId: 0,
        search:     '',
        paged:      1,
        perPage:    12,
        totalPages: 1,
        orderby:    'created_at',
        order:      'DESC',
    };
    let isLoading = false;

    /* ── AJAX-Links laden ────────────────────────────────────── */
    function loadLinks(append) {
        if (isLoading) { return; }
        isLoading = true;

        const $grid    = $('#wll-links-grid');
        const $spinner = $('#wll-spinner');
        const $loadBtn = $('#wll-load-more');

        $spinner.show();
        if (!append) { $grid.css('opacity', '.5'); }

        $.post(wllConfig.ajaxurl, {
            action:      'wll_load_links',
            nonce:       wllConfig.publicNonce,
            category_id: state.categoryId,
            search:      state.search,
            orderby:     state.orderby,
            order:       state.order,
            per_page:    state.perPage,
            paged:       state.paged,
        })
        .done(function (res) {
            if (!res.success) { return; }

            if (append) {
                $grid.append(res.data.html);
            } else {
                $grid.html(res.data.html);
                $grid.css('opacity', '1');
            }

            const total      = res.data.total;
            const totalPages = Math.ceil(total / state.perPage);

            if ($loadBtn.length) {
                if (state.paged >= totalPages) {
                    $loadBtn.hide();
                } else {
                    const remaining = total - (state.paged * state.perPage);
                    $loadBtn.text('Mehr laden (' + remaining + ' weitere)').show();
                }
            }

            // Sterne-Events neu binden
            bindStarEvents();
        })
        .fail(function () {
            toast.show(wllConfig.i18n.submit_error, 'error');
        })
        .always(function () {
            $spinner.hide();
            $grid.css('opacity', '1');
            isLoading = false;
        });
    }

    /* ── Echtzeit-Suche (300 ms Debounce) ───────────────────── */
    let searchTimer;
    $(document).on('input', '#wll-search-input', function () {
        clearTimeout(searchTimer);
        const val = $(this).val().trim();
        searchTimer = setTimeout(function () {
            state.search = val;
            state.paged  = 1;
            loadLinks(false);
        }, 300);
    });

    $(document).on('click', '#wll-clear-search', function () {
        $('#wll-search-input').val('').trigger('input');
    });

    /* ── Kategorie-Filter ────────────────────────────────────── */
    $(document).on('click', '.wll-cat-btn', function () {
        $('.wll-cat-btn').removeClass('active');
        $(this).addClass('active');
        state.categoryId = parseInt($(this).data('cat'), 10) || 0;
        state.paged      = 1;
        loadLinks(false);
    });

    /* ── Sortierung ──────────────────────────────────────────── */
    $(document).on('change', '#wll-sort', function () {
        state.orderby = $(this).val();
        state.paged   = 1;
        loadLinks(false);
    });

    /* ── Mehr laden ──────────────────────────────────────────── */
    $(document).on('click', '#wll-load-more', function () {
        state.paged++;
        loadLinks(true);
    });

    /* ── Klick-Tracking ──────────────────────────────────────── */
    $(document).on('click', '.wll-link-click', function () {
        const id = $(this).data('id');
        if (id) {
            $.post(wllConfig.ajaxurl, {
                action:  'wll_track_click',
                nonce:   wllConfig.publicNonce,
                link_id: id,
            });
        }
    });

    /* ── Sterne-Bewertung ────────────────────────────────────── */
    function bindStarEvents() {
        $(document).off('mouseenter.wll mouseleave.wll click.wll', '.wll-star');

        $(document).on('mouseenter.wll', '.wll-star', function () {
            const val    = parseInt($(this).data('value'), 10);
            const $stars = $(this).closest('.wll-stars').find('.wll-star');
            $stars.each(function () {
                const sv = parseInt($(this).data('value'), 10);
                $(this).css('color', sv <= val ? '#f39c12' : '#ddd');
            });
        });

        $(document).on('mouseleave.wll', '.wll-stars', function () {
            $(this).find('.wll-star').each(function () {
                const $s = $(this);
                if ($s.hasClass('filled') || $s.hasClass('half')) {
                    $s.css('color', '');
                } else {
                    $s.css('color', '#ddd');
                }
            });
        });

        $(document).on('click.wll', '.wll-star', function () {
            const $starsEl = $(this).closest('.wll-stars');
            const linkId   = parseInt($starsEl.data('id'), 10);
            const rating   = parseInt($(this).data('value'), 10);

            $.post(wllConfig.ajaxurl, {
                action:  'wll_rate_link',
                nonce:   wllConfig.publicNonce,
                link_id: linkId,
                rating:  rating,
            })
            .done(function (res) {
                if (res.success) {
                    toast.show(wllConfig.i18n.rating_saved, 'success');
                    // Sterne optisch "einfrieren"
                    $starsEl.find('.wll-star').each(function () {
                        const sv = parseInt($(this).data('value'), 10);
                        $(this).removeClass('filled half empty')
                               .addClass(sv <= rating ? 'filled' : 'empty')
                               .css('color', '');
                    });
                    $starsEl.css('pointer-events', 'none');
                } else {
                    toast.show(res.data ? res.data.message : 'Fehler', 'error');
                }
            });
        });
    }

    /* ── Link kopieren ───────────────────────────────────────── */
    $(document).on('click', '.wll-btn-copy', function () {
        const url = $(this).data('url');
        if (navigator.clipboard && url) {
            navigator.clipboard.writeText(url)
                .then(function () { toast.show(wllConfig.i18n.copy_success, 'success'); })
                .catch(function () { toast.show('Kopieren fehlgeschlagen.', 'error'); });
        }
    });

    /* ── Modal öffnen / schließen ────────────────────────────── */
    $(document).on('click', '.wll-open-submit', function (e) {
        e.preventDefault();
        $('#wll-submit-modal').fadeIn(200);
        $('body').css('overflow', 'hidden');
    });

    function closeModal() {
        $('#wll-submit-modal').fadeOut(200);
        $('body').css('overflow', '');
    }

    $(document).on('click', '.wll-modal-close', closeModal);
    $(document).on('click', '.wll-modal-overlay', function (e) {
        if ($(e.target).is('.wll-modal-overlay')) { closeModal(); }
    });
    $(document).on('keyup', function (e) {
        if (e.key === 'Escape') { closeModal(); }
    });

    /* ── Neue Kategorie (Formular) ───────────────────────────── */
    $(document).on('click', '#wll-btn-add-cat', function () {
        $('#wll-new-cat-input').slideDown(200);
        $('#wll-new-cat-name').focus();
    });
    $(document).on('click', '#wll-cancel-cat', function () {
        $('#wll-new-cat-input').slideUp(200);
        $('#wll-new-cat-name').val('');
    });

    $(document).on('click', '#wll-save-cat', function () {
        const name = $('#wll-new-cat-name').val().trim();
        if (!name) { toast.show('Bitte einen Kategorienamen eingeben.', 'error'); return; }

        $.post(wllConfig.ajaxurl, {
            action: 'wll_add_category',
            nonce:  wllConfig.submitNonce,
            name:   name,
        })
        .done(function (res) {
            if (res.success) {
                const d = res.data;
                const $sel = $('#wll-field-category');
                $sel.append('<option value="' + d.id + '">' + $('<span>').text(d.name).html() + '</option>');
                $sel.val(d.id);
                $('#wll-new-cat-input').slideUp(200);
                $('#wll-new-cat-name').val('');
                toast.show('Kategorie "' + d.name + '" erstellt!', 'success');
            } else {
                toast.show(res.data ? res.data.message : 'Fehler', 'error');
            }
        });
    });

    /* ── Zeichen-Counter Bemerkung ───────────────────────────── */
    $(document).on('input', '#wll-field-bemerkung', function () {
        $('#wll-bemerkung-count').text($(this).val().length);
    });

    /* ── Bildvorschau ────────────────────────────────────────── */
    let imgTimer;
    $(document).on('input', '#wll-field-image', function () {
        clearTimeout(imgTimer);
        const url = $(this).val().trim();
        imgTimer = setTimeout(function () {
            if (url) {
                const $prev = $('#wll-image-preview');
                const $img  = $('#wll-preview-img');
                $img.attr('src', url).off('load error').on('load', function () {
                    $prev.slideDown(200);
                }).on('error', function () {
                    $prev.slideUp(200);
                });
            } else {
                $('#wll-image-preview').slideUp(200);
            }
        }, 400);
    });

    /* ── Einreichungsformular ────────────────────────────────── */
    $(document).on('submit', '#wll-submit-form', function (e) {
        e.preventDefault();

        const $form     = $(this);
        const $feedback = $('#wll-form-feedback');
        const $btnSubmit = $('#wll-btn-submit');

        // Einfache Validierung
        let valid = true;

        $form.find('[required]').each(function () {
            const $f = $(this);
            const $err = $f.siblings('.wll-field-error');
            if (!$f.val().trim()) {
                $f.addClass('wll-invalid');
                $err.text('Dieses Feld ist erforderlich.');
                valid = false;
            } else {
                $f.removeClass('wll-invalid');
                $err.text('');
            }
        });

        // URL-Validierung
        const $urlField = $('#wll-field-url');
        const urlVal    = $urlField.val().trim();
        if (urlVal && !/^https?:\/\/.+\..+/i.test(urlVal)) {
            $urlField.addClass('wll-invalid');
            $urlField.siblings('.wll-field-error').text('Bitte gib eine gültige URL ein (https://…).');
            valid = false;
        }

        if (!valid) { return; }

        $btnSubmit.prop('disabled', true).text('Wird eingereicht…');

        $.post(wllConfig.ajaxurl, {
            action:       'wll_submit_link',
            nonce:        wllConfig.submitNonce,
            url:          $('#wll-field-url').val().trim(),
            name:         $('#wll-field-name').val().trim(),
            category_id:  $('#wll-field-category').val(),
            bemerkung:    $('#wll-field-bemerkung').val().trim(),
            image_url:    $('#wll-field-image').val().trim(),
            submitted_by: $('#wll-field-submitter').val().trim(),
        })
        .done(function (res) {
            if (res.success) {
                $feedback.removeClass('error').addClass('success')
                         .text(res.data.message).show();
                $form[0].reset();
                $('#wll-image-preview').hide();
                toast.show(wllConfig.i18n.submit_success, 'success', 5000);
                setTimeout(closeModal, 3000);
            } else {
                $feedback.removeClass('success').addClass('error')
                         .text(res.data ? res.data.message : wllConfig.i18n.submit_error).show();
            }
        })
        .fail(function () {
            $feedback.removeClass('success').addClass('error')
                     .text(wllConfig.i18n.submit_error).show();
        })
        .always(function () {
            $btnSubmit.prop('disabled', false).text('Link einreichen');
        });
    });

    /* ── Init ────────────────────────────────────────────────── */
    $(function () {
        toast.init();
        bindStarEvents();
    });

}(jQuery));
