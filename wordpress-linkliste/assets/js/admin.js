/**
 * WordPress LinkListe – Admin-JavaScript
 *
 * Behandelt AJAX-Aktionen im Backend:
 *  - Link genehmigen
 *  - Link ablehnen
 *  - Link löschen
 */

/* global jQuery, wllAdmin */
(function ($) {
    'use strict';

    function showNotice(msg, type) {
        const cls = type === 'success' ? 'notice-success' : 'notice-error';
        const $n  = $('<div class="notice ' + cls + ' is-dismissible"><p>' + $('<span>').text(msg).html() + '</p></div>');
        $('.wp-header-end').after($n);
        setTimeout(function () { $n.fadeOut(400, function () { $n.remove(); }); }, 4000);
    }

    function removeRow($btn) {
        $btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
    }

    // Genehmigen
    $(document).on('click', '.wll-approve', function () {
        const $btn = $(this);
        const id   = $btn.data('id');
        $btn.prop('disabled', true).text('…');

        $.post(wllAdmin.ajaxurl, {
            action:  'wll_approve_link',
            nonce:   wllAdmin.nonce,
            link_id: id,
        })
        .done(function (res) {
            if (res.success) {
                showNotice(wllAdmin.i18n.action_approved, 'success');
                removeRow($btn);
            } else {
                showNotice('Fehler', 'error');
                $btn.prop('disabled', false).text('Genehmigen');
            }
        });
    });

    // Ablehnen
    $(document).on('click', '.wll-reject', function () {
        if (!confirm(wllAdmin.i18n.confirm_reject)) { return; }
        const $btn = $(this);
        const id   = $btn.data('id');
        $btn.prop('disabled', true).text('…');

        $.post(wllAdmin.ajaxurl, {
            action:  'wll_reject_link',
            nonce:   wllAdmin.nonce,
            link_id: id,
        })
        .done(function (res) {
            if (res.success) {
                showNotice(wllAdmin.i18n.action_rejected, 'success');
                removeRow($btn);
            } else {
                showNotice('Fehler', 'error');
                $btn.prop('disabled', false).text('Ablehnen');
            }
        });
    });

    // Löschen
    $(document).on('click', '.wll-delete', function () {
        if (!confirm(wllAdmin.i18n.confirm_delete)) { return; }
        const $btn = $(this);
        const id   = $btn.data('id');
        $btn.prop('disabled', true).text('…');

        $.post(wllAdmin.ajaxurl, {
            action:  'wll_delete_link',
            nonce:   wllAdmin.nonce,
            link_id: id,
        })
        .done(function (res) {
            if (res.success) {
                showNotice(wllAdmin.i18n.action_deleted, 'success');
                removeRow($btn);
            } else {
                showNotice('Fehler', 'error');
                $btn.prop('disabled', false).text('Löschen');
            }
        });
    });

}(jQuery));
