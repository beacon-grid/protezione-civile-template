<?php
require_once __DIR__ . '/php/emergency_helper.php';

if (($_GET['emergency_bypass'] ?? '') === '1') {
    emergency_register_bypass();
}

$status = emergency_load_status();
ob_start();
include __DIR__ . '/emergency.html';
$page = ob_get_clean();

if ($status['active']) {
    $type = htmlspecialchars($status['type'] ?: 'Emergenza', ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($status['title'] ?: 'Stato di emergenza attivo', ENT_QUOTES, 'UTF-8');
    $description = nl2br(htmlspecialchars($status['description'], ENT_QUOTES, 'UTF-8'));
    $updated = htmlspecialchars((string) ($status['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8');
    $popupStyles = <<<HTML
<style>
.site-emergency-popup{position:fixed;inset:0;z-index:10000;display:grid;place-items:center;padding:1rem;background:rgba(12,18,28,.72)}
.site-emergency-popup__box{width:min(560px,100%);padding:1.4rem;background:#fff;color:#1a1a2e;border-top:7px solid #c1121f;border-radius:12px;box-shadow:0 16px 42px rgba(0,0,0,.28)}
.site-emergency-popup__box h2{margin:0 0 .35rem;color:#c1121f;font-size:1.35rem}
.site-emergency-popup__type{display:inline-block;margin-bottom:.8rem;padding:.2rem .55rem;background:#fde8e8;color:#9b1c1c;border-radius:4px;font-size:.78rem;font-weight:700;text-transform:uppercase}
.site-emergency-popup__description{margin:0 0 1rem;line-height:1.55;white-space:normal;overflow-wrap:anywhere}
.site-emergency-popup__meta{margin:0;color:#5f6b78;font-size:.78rem}
.site-emergency-popup__close{display:block;width:100%;margin-top:1rem;padding:.7rem 1rem;border:0;border-radius:8px;background:#c1121f;color:#fff;font:inherit;font-weight:700;cursor:pointer}
</style>
HTML;
    $popup = <<<HTML
<div class="site-emergency-popup" role="alertdialog" aria-modal="true" aria-labelledby="site-emergency-title" aria-describedby="site-emergency-description site-emergency-meta" tabindex="-1">
    <div class="site-emergency-popup__box">
        <span class="site-emergency-popup__type">$type</span>
        <h2 id="site-emergency-title">$title</h2>
        <p class="site-emergency-popup__description" id="site-emergency-description">$description</p>
        <p class="site-emergency-popup__meta" id="site-emergency-meta">Aggiornato: $updated</p>
        <button type="button" class="site-emergency-popup__close">Ho letto</button>
    </div>
</div>
<script>
(function () {
    var dialog = document.querySelector('.site-emergency-popup');
    var closeButton = dialog.querySelector('.site-emergency-popup__close');
    var main = document.getElementById('main');
    var background = Array.prototype.filter.call(document.body.children, function (element) {
        return element !== dialog && element.tagName !== 'SCRIPT';
    }).map(function (element) {
        return {
            element: element,
            inert: element.inert,
            ariaHidden: element.getAttribute('aria-hidden')
        };
    });

    background.forEach(function (item) {
        item.element.inert = true;
        item.element.setAttribute('aria-hidden', 'true');
    });

    function getFocusableElements() {
        return Array.prototype.filter.call(
            dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'),
            function (element) {
                return !element.hidden && element.getAttribute('aria-hidden') !== 'true' && element.getClientRects().length > 0;
            }
        );
    }

    function closeDialog() {
        document.removeEventListener('focusin', keepFocusInDialog);
        dialog.removeEventListener('keydown', handleDialogKeydown);

        background.forEach(function (item) {
            item.element.inert = item.inert;
            if (item.ariaHidden === null) {
                item.element.removeAttribute('aria-hidden');
            } else {
                item.element.setAttribute('aria-hidden', item.ariaHidden);
            }
        });

        dialog.remove();
        if (main) main.focus();
    }

    function keepFocusInDialog(event) {
        if (!dialog.contains(event.target)) dialog.focus();
    }

    function handleDialogKeydown(event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDialog();
            return;
        }

        if (event.key !== 'Tab') return;

        var focusable = getFocusableElements();
        if (!focusable.length) {
            event.preventDefault();
            dialog.focus();
            return;
        }

        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    dialog.addEventListener('keydown', handleDialogKeydown);
    document.addEventListener('focusin', keepFocusInDialog);
    closeButton.addEventListener('click', closeDialog);
    dialog.focus();
}());
</script>
HTML;
    $page = str_ireplace('</head>', $popupStyles . '</head>', $page);
    $page = str_ireplace('</body>', $popup . '</body>', $page);
}

echo $page;
