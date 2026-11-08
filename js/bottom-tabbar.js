/**
 * Bottom Tab Bar — libreria condivisa per il pannello admin.
 *
 * Uso in ogni pagina (dopo aver incluso bottom-tabbar.css):
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
 * QUESTA VERSIONE È IDEMPOTENTE: se lo script viene incluso/eseguito più
 * volte (es. anche da header.php), o se PC_TABBAR_CONFIG non era ancora
 * pronto la prima volta, ogni chiamata successiva AGGIORNA la barra già
 * presente invece di ignorare la richiesta. Questo elimina la classe di
 * bug "tab bloccata sui valori di default" causata da doppia inclusione.
 *
 * DEBUG: se PC_TABBAR_CONFIG manca del tutto, viene stampato un
 * console.warn ben visibile invece di fallire in silenzio.
 */
(function () {
    'use strict';

    var TABS = [
        { key: 'dashboard', href: 'dashboard.php', icon: 'bi-speedometer2', label: 'Dashboard' },
        { key: 'news',      href: 'news.php',      icon: 'bi-newspaper',    label: 'News' },
        { key: 'galleria',  href: 'galleria.php',  icon: 'bi-images',       label: 'Galleria' },
        { key: 'glossario', href: 'glossario.php', icon: 'bi-book',         label: 'Glossario' }
    ];

    var SUPERADMIN_TABS = [
        { key: 'users', href: 'manage_users.php', icon: 'bi-people', label: 'Utenti' }
    ];

    function tabsForRole(role) {
        return role === 'superadmin' ? SUPERADMIN_TABS : TABS;
    }

    function readConfig() {
        var dataNode = document.getElementById('pcTabbarData');
        if (dataNode) {
            return {
                activePage: dataNode.getAttribute('data-active-page') || 'dashboard',
                username: dataNode.getAttribute('data-username') || 'Utente',
                role: dataNode.getAttribute('data-role') || 'user',
                syncUrl: dataNode.getAttribute('data-sync-url') || '/allerte/sync.php'
            };
        }

        var legacy = window.PC_TABBAR_CONFIG || {};
        return {
            activePage: legacy.activePage || 'dashboard',
            username: legacy.username || 'Utente',
            role: legacy.role || 'user',
            syncUrl: legacy.syncUrl || '/allerte/sync.php'
        };
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function buildTabbarHtml(cfg) {
        var items = tabsForRole(cfg.role).map(function (tab) {
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
        var roleLabel = role === 'superadmin'
            ? 'Super amministratore'
            : (role === 'admin' ? 'Amministratore' : 'Utente');

        var adminLink = role === 'admin'
            ? '<a href="manage_users.php" class="tabbar-sheet-item" data-role-admin><i class="bi bi-people" aria-hidden="true"></i> Gestione Utenti</a>'
            : '';

        var searchSettingsLink = role === 'admin'
            ? '<a href="site_search_settings.php" class="tabbar-sheet-item" data-role-search-settings><i class="bi bi-search" aria-hidden="true"></i> Impostazioni ricerca</a>'
            : '';

        var superadminLink = role === 'superadmin'
            ? '<a href="manage_users.php" class="tabbar-sheet-item" data-role-superadmin><i class="bi bi-people" aria-hidden="true"></i> Gestione Utenti</a>'
            : '';

        var syncLink = role === 'superadmin' ? '' :
            '<a href="' + escapeHtml(cfg.syncUrl || '/allerte/sync.php') + '" class="tabbar-sheet-item" data-sync-link>' +
            '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Forza sincronizzazione</a>';

        return (
            '<div class="tabbar-sheet-backdrop" id="tabbarSheetBackdrop"></div>' +
            '<div class="tabbar-sheet" id="tabbarSheet" role="dialog" aria-modal="true" ' +
            'aria-label="Altre opzioni" tabindex="-1">' +
            '<div class="tabbar-sheet-handle"></div>' +
            '<div class="tabbar-sheet-user">' +
            '<i class="bi bi-person-circle" aria-hidden="true"></i>' +
            '<div>' +
            '<div class="tabbar-sheet-user-label">Sessione attiva</div>' +
            '<div class="tabbar-sheet-user-name" data-username>' + escapeHtml(cfg.username || 'Utente') + '</div>' +
            '<span class="tabbar-sheet-user-role" data-role-label>' + escapeHtml(roleLabel) + '</span>' +
            '</div>' +
            '</div>' +
            syncLink +
            adminLink +
            searchSettingsLink +
            superadminLink +
            '<a href="logout.php" class="tabbar-sheet-item tabbar-sheet-item--danger">' +
            '<i class="bi bi-box-arrow-right" aria-hidden="true"></i> Esci</a>' +
            '</div>'
        );
    }

    // Applica/aggiorna la config su una barra e uno sheet già presenti nel DOM.
    function applyConfig(cfg) {
        var tabbar = document.getElementById('bottomTabbar');
        if (!tabbar) return false;

        // Tab attivo
        var availableTabs = tabsForRole(cfg.role);
        tabbar.querySelectorAll('.tabbar-item[href]').forEach(function (a) {
            var tab = availableTabs.filter(function (t) { return a.getAttribute('href') === t.href; })[0];
            var active = !!tab && tab.key === cfg.activePage;
            a.classList.toggle('is-active', active);
            if (active) {
                a.setAttribute('aria-current', 'page');
            } else {
                a.removeAttribute('aria-current');
            }
        });

        // Utente / ruolo
        var nameEl = document.querySelector('[data-username]');
        if (nameEl) nameEl.textContent = cfg.username || 'Utente';

        var role = cfg.role || 'user';
        var roleEl = document.querySelector('[data-role-label]');
        if (roleEl) {
            roleEl.textContent = role === 'superadmin'
                ? 'Super amministratore'
                : (role === 'admin' ? 'Amministratore' : 'Utente');
        }

        // Link sync
        var syncEl = document.querySelector('[data-sync-link]');
        if (syncEl && cfg.syncUrl) syncEl.setAttribute('href', cfg.syncUrl);

        // Voce Gestione Utenti visibile solo per admin (aggiunta/rimossa se cambia il ruolo)
        var adminEl = document.querySelector('[data-role-admin]');
        if (role === 'admin' && !adminEl) {
            var sheetBody = document.getElementById('tabbarSheet');
            var syncNode = document.querySelector('[data-sync-link]');
            if (sheetBody && syncNode) {
                var el = document.createElement('a');
                el.href = 'manage_users.php';
                el.className = 'tabbar-sheet-item';
                el.setAttribute('data-role-admin', '');
                el.innerHTML = '<i class="bi bi-people" aria-hidden="true"></i> Gestione Utenti';
                syncNode.insertAdjacentElement('afterend', el);
            }
        } else if (role !== 'admin' && adminEl) {
            adminEl.remove();
        }

        var searchSettingsEl = document.querySelector('[data-role-search-settings]');
        if (role === 'admin' && !searchSettingsEl) {
            var sheetBody = document.getElementById('tabbarSheet');
            var adminLink = document.querySelector('[data-role-admin]');
            if (sheetBody && adminLink) {
                var settingsEl = document.createElement('a');
                settingsEl.href = 'site_search_settings.php';
                settingsEl.className = 'tabbar-sheet-item';
                settingsEl.setAttribute('data-role-search-settings', '');
                settingsEl.innerHTML = '<i class="bi bi-search" aria-hidden="true"></i> Impostazioni ricerca';
                adminLink.insertAdjacentElement('afterend', settingsEl);
            }
        } else if (role !== 'admin' && searchSettingsEl) {
            searchSettingsEl.remove();
        }

        return true;
    }

    function bindSheetInteractions() {
        var moreBtn  = document.getElementById('tabbarMoreBtn');
        var sheet    = document.getElementById('tabbarSheet');
        var backdrop = document.getElementById('tabbarSheetBackdrop');
        if (!moreBtn || !sheet || !backdrop || moreBtn.dataset.bound === '1') return;
        moreBtn.dataset.bound = '1';

        var lastFocused = null;

        function openSheet() {
            lastFocused = document.activeElement;
            backdrop.classList.add('is-visible');
            sheet.classList.add('is-visible');
            void sheet.offsetHeight; // forza reflow per far partire la transizione
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

    function run() {
        var cfg = readConfig();

        if (!document.getElementById('pcTabbarData')) {
            // eslint-disable-next-line no-console
            console.warn(
                '[bottom-tabbar] Elemento #pcTabbarData non trovato. ' +
                'Aggiungi il div data-* subito prima di includere bottom-tabbar.js.'
            );
        }

        if (!applyConfig(cfg)) {
            var wrapper = document.createElement('div');
            wrapper.innerHTML = buildTabbarHtml(cfg) + buildSheetHtml(cfg);
            while (wrapper.firstChild) {
                document.body.appendChild(wrapper.firstChild);
            }
        }

        bindSheetInteractions();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }

    // Espone un hook manuale per rilanciare l'aggiornamento da console,
    // utile in fase di debug: PCBottomTabbar.refresh()
    window.PCBottomTabbar = { refresh: run };
})();