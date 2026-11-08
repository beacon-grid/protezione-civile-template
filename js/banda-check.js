/**
 * banda-check.js — Rilevamento banda debole con redirect automatico
 * alla pagina di emergenza ultra-leggera.
 *
 * FUNZIONAMENTO:
 *  1. Controlla prima la Network Information API (Chrome/Android/Edge).
 *     Se la connessione è 2G/slow-2G o "Save-Data" è attivo → redirect.
 *     Se la connessione è ok → skip (nessun test immagine).
 *     Se l'API non è disponibile (Safari/iOS/Firefox) → test immagine.
 *  2. Il redirect automatico avviene SOLO sulla Home Page per evitare
 *     loop infiniti sulle pagine interne.
 *  3. L'utente può disattivare il redirect con:
 *     - ?banda_bypass=1  nell'URL (bypass temporaneo, solo questa sessione)
 *     - localStorage['banda_check_optout'] = '1'  (bypass permanente)
 *     - window.bandaCheckOptOut(url)  (da chiamare al click "vai al sito completo")
 *
 * UTILIZZO — includi nell'<head> prima degli altri script:
 *   <script src="/js/banda-check.js"></script>
 *
 * CONFIGURAZIONE OPZIONALE (blocco <script> prima dell'include):
 *   window.BANDA_CHECK = {
 *     url:         '/emergency.php',   // pagina di destinazione
 *     soglia2g:    true,               // redirect su 2G/slow-2G
 *     saveData:    true,               // rispetta "risparmio dati" del browser
 *     sogliaMsImg: 3000,               // ms massimi per caricare l'immagine test
 *     imgTest:     '/assets/img/logo.png',
 *     debug:       false,
 *   };
 */

(function () {
    'use strict';

    var OPT_OUT_KEY = 'banda_check_optout';

    // ── Configurazione con defaults ──────────────────────────────────
    var cfg = Object.assign({
        url:         '/emergency.php',
        soglia2g:    true,
        saveData:    true,
        sogliaMsImg: 3000,
        imgTest:     '/assets/img/logo.png',
        debug:       false,
    }, window.BANDA_CHECK || {});

    var log = cfg.debug
        ? function (msg) { console.log('[banda-check] ' + msg); }
        : function () {};

    // ── API pubblica — definita SEMPRE, prima di qualsiasi return ────

    /**
     * bandaCheckOptOut(destinazione)
     * L'utente sceglie esplicitamente di usare il sito normale.
     * Imposta il flag in localStorage e naviga aggiungendo ?banda_bypass=1
     * così da non essere reindirizzato di nuovo se dovesse tornare alla Home
     * prima che la pagina si ricarichi.
     *
     * @param {string} [destinazione='/'] URL di destinazione
     */
    window.bandaCheckOptOut = function (destinazione) {
        try {
            sessionStorage.setItem(OPT_OUT_KEY, '1');
            log('Opt-out impostato in localStorage.');
        } catch (e) {
            log('Impossibile scrivere in localStorage: ' + e.message);
        }

        var url = destinazione || '/';

        // Gestione ancore: il parametro deve stare prima del '#'
        if (url.indexOf('#') !== -1) {
            var parti = url.split('#');
            var sep   = parti[0].indexOf('?') === -1 ? '?' : '&';
            window.location.href = parti[0] + sep + 'banda_bypass=1#' + parti[1];
        } else {
            var separatore = url.indexOf('?') === -1 ? '?' : '&';
            window.location.href = url + separatore + 'banda_bypass=1';
        }
    };

    /**
     * bandaCheckReset()
     * Rimuove l'opt-out: alla prossima visita alla Home il controllo torna attivo.
     */
    window.bandaCheckReset = function () {
        try {
            sessionStorage.removeItem(OPT_OUT_KEY);
            log('Opt-out rimosso.');
        } catch (e) {
            log('Impossibile scrivere in localStorage: ' + e.message);
        }
    };

    // ── 1. Bypass tramite URL (?banda_bypass=1) ───────────────────────
    if (window.location.search.indexOf('banda_bypass=1') !== -1) {
        log('Bypass forzato via URL (?banda_bypass=1). Navigazione consentita.');
        return;
    }

    // ── 2. Opt-out permanente dell'utente ─────────────────────────────
    try {
        if (sessionStorage.getItem(OPT_OUT_KEY) === '1') {
            log('Opt-out attivo: utente ha scelto il sito normale, skip.');
            return;
        }
    } catch (e) {
        log('localStorage non disponibile: ' + e.message);
    }

    // ── 3. Già sulla pagina di emergenza → evita loop ─────────────────
    var currentPath   = window.location.pathname;
    var emergenzaPath = cfg.url.replace(/\/$/, '');
    if (currentPath.indexOf(emergenzaPath) === 0) {
        log('Già sulla pagina di emergenza, skip.');
        return;
    }

    // ── 4. Solo sulla Home Page → evita loop su pagine interne ───────
    var isHomePage = (
        currentPath === '/' ||
        currentPath === '/index.html' ||
        currentPath === '/index.php'
    );
    if (!isHomePage) {
        log('Pagina interna (' + currentPath + '): controllo saltato.');
        return;
    }

    // ── Redirect ──────────────────────────────────────────────────────
    function redirect(motivo) {
        log('Redirect → ' + cfg.url + ' (motivo: ' + motivo + ')');
        window.location.replace(cfg.url);
    }

    // ── 5. Network Information API (Chrome / Android / Edge) ─────────
    // Ritorna true se l'API ha dato una risposta definitiva (buona o cattiva).
    // Ritorna false solo se l'API non è disponibile: in tal caso si esegue
    // il test immagine di fallback.
    function checkNetworkInfo() {
        var conn = navigator.connection
                || navigator.mozConnection
                || navigator.webkitConnection;

        if (!conn) {
            log('Network Information API non disponibile: avvio test immagine.');
            return false;
        }

        log('effectiveType: ' + conn.effectiveType + ' | saveData: ' + conn.saveData);

        if (cfg.saveData && conn.saveData) {
            redirect('saveData attivo');
            return true;
        }

        if (cfg.soglia2g &&
                (conn.effectiveType === '2g' ||
                 conn.effectiveType === 'slow-2g')) {
            redirect('connessione 2G/slow-2G');
            return true;
        }

        log('Network Information API: connessione ok, test immagine non necessario.');
        return true;
    }

    // ── 6. Misura tempo caricamento immagine (universale / Safari/iOS) ─
    function checkImgSpeed() {
        var start   = Date.now();
        var img     = new Image();
        var timeout = null;
        var done    = false;

        function conclude(lento, motivo) {
            if (done) return;
            done = true;
            clearTimeout(timeout);
            if (lento) {
                redirect(motivo);
            } else {
                log('Test immagine ok (' + (Date.now() - start) + 'ms).');
            }
        }

        timeout = setTimeout(function () {
            img.src = '';
            conclude(true, 'Timeout immagine (' + cfg.sogliaMsImg + 'ms)');
        }, cfg.sogliaMsImg);

        img.onload  = function () { conclude(false, null); };
        img.onerror = function () { conclude(true, 'Errore caricamento immagine test'); };

        // Cache-busting per avere una misura reale
        img.src = cfg.imgTest + '?_bc=' + Date.now();
        log('Test immagine avviato → ' + img.src);
    }

    // ── Esecuzione ────────────────────────────────────────────────────
    var gestito = checkNetworkInfo();
    if (!gestito) {
        checkImgSpeed();
    }

})();