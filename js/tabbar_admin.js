/**
 * Bottom Tab Bar — libreria condivisa per il pannello admin.
 *
 * Uso in ogni pagina (dopo aver incluso bottom-tabbar.css e questo file):
 *
 *   <script>
 *     window.PC_TABBAR_CONFIG = {
 *       activePage: 'dashboard',        // 'dashboard' | 'news' | 'galleria' | 'glossario'
 *       username:   <?= json_encode($username) ?>,
 *       role:       <?= json_encode($role) ?>,
 *       syncUrl:    <?= json_encode('/allerte/sync.php?token=' . urlencode($tokenAttuale)) ?>
 *     };
 *   </script>
 *   <script src="../js/bottom-tabbar.js"></script>
 *
 * La libreria inietta da sola markup, backdrop e bottom sheet: non serve
 * scrivere HTML nelle pagine.
 */
(function () {
    'use strict';

    var TABS = [
        { key: 'dashboard', href: 'dashboard.php', icon: 'bi-speedometer2', label: 'Dashboard' },
        { key: 'news',      href: 'news.php',      icon: 'bi-newspaper',    label: 'News' },
        { key: 'galleria',  href: 'galleria.php',  icon: 'bi-images',       label: 'Galleria' },
        { key: 'glossario', href: 'glossario.php', icon: 'bi-book',         label: 'Glossario' }
    ];

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function buildTabbarHtml(cfg) {
        var items = TABS.map(function (tab) {
            var active = tab.key === cfg.activePage;
            return (
                '<a href="' + tab.href + '" class="tabbar-item' + (active ? ' is-active' : '') + '"' +
                (active ? ' aria-current="page"' : '') + '>' +
                '<i class="bi ' + tab.icon + '" aria-hidden="true"></i>' +
                '<span>' + tab.label + '</span>' +
                '</a>'
            );
        }).join('');

        items += (
            '<button type="button" class="tabbar-item tabbar-more" id="tabbarMoreBtn" ' +
            'aria-haspopup="dialog" aria-expanded="false" aria-controls="tabbarSheet">' +
            '<i class="bi bi-three-dots" aria-hidden="true"></i>' +
            '<span>Altro</span>' +
            '</button>'
        );

        return '<nav class="bottom-tabbar" id="bottomTabbar" aria-label="Menu principale">' + items + '</nav>';
    }

    function buildSheetHtml(cfg) {
        var role = cfg.role || 'user';
        var roleLabel = role === 'admin' ? 'Amministratore' : 'Utente';

        var adminLink = role === 'admin'
            ? '<a href="manage_users.php" class="tabbar-sheet-item"><i class="bi bi-people" aria-hidden="true"></i> Gestione Utenti</a>'
            : '';

        var syncLink = cfg.syncUrl
            ? '<a href="' + escapeHtml(cfg.syncUrl) + '" class="tabbar-sheet-item"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Forza sincronizzazione</a>'
            : '';

        return (
            '<div class="tabbar-sheet-backdrop" id="tabbarSheetBackdrop"></div>' +
            '<div class="tabbar-sheet" id="tabbarSheet" role="dialog" aria-modal="true" ' +
            'aria-label="Altre opzioni" tabindex="-1">' +
            '<div class="tabbar-sheet-handle"></div>' +
            '<div class="tabbar-sheet-user">' +
            '<i class="bi bi-person-circle" aria-hidden="true"></i>' +
            '<div>' +
            '<div class="tabbar-sheet-user-label">Sessione attiva</div>' +
            '<div class="tabbar-sheet-user-name">' + escapeHtml(cfg.username || 'Utente') + '</div>' +
            '<span class="tabbar-sheet-user-role">' + escapeHtml(roleLabel) + '</span>' +
            '</div>' +
            '</div>' +
            syncLink +
            adminLink +
            '<a href="logout.php" class="tabbar-sheet-item tabbar-sheet-item--danger">' +
            '<i class="bi bi-box-arrow-right" aria-hidden="true"></i> Esci</a>' +
            '</div>'
        );
    }

    function init(cfg) {
        cfg = cfg || {};

        // Evita doppie inizializzazioni se lo script viene incluso più volte.
        if (document.getElementById('bottomTabbar')) return;

        var wrapper = document.createElement('div');
        wrapper.innerHTML = buildTabbarHtml(cfg) + buildSheetHtml(cfg);
        while (wrapper.firstChild) {
            document.body.appendChild(wrapper.firstChild);
        }

        var moreBtn  = document.getElementById('tabbarMoreBtn');
        var sheet    = document.getElementById('tabbarSheet');
        var backdrop = document.getElementById('tabbarSheetBackdrop');
        var lastFocused = null;

        function openSheet() {
            lastFocused = document.activeElement;
            backdrop.classList.add('is-visible');
            sheet.classList.add('is-visible');
            // forza un reflow prima di aggiungere is-open, per far partire la transizione
            void sheet.offsetHeight;
            sheet.classList.add('is-open');
            moreBtn.classList.add('is-open');
            moreBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            sheet.focus();
        }

        function closeSheet() {
            sheet.classList.remove('is-open');
            moreBtn.classList.remove('is-open');
            moreBtn.setAttribute('aria-expanded', 'false');
            backdrop.classList.remove('is-visible');
            document.body.style.overflow = '';
            // rimuove is-visible solo a transizione finita, per far scorrere via lo sheet
            var onEnd = function () {
                sheet.classList.remove('is-visible');
                sheet.removeEventListener('transitionend', onEnd);
            };
            sheet.addEventListener('transitionend', onEnd);
            if (lastFocused) lastFocused.focus();
        }

        function isOpen() {
            return sheet.classList.contains('is-open');
        }

        moreBtn.addEventListener('click', function () {
            isOpen() ? closeSheet() : openSheet();
        });
        backdrop.addEventListener('click', closeSheet);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen()) closeSheet();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        init(window.PC_TABBAR_CONFIG);
    });
})();