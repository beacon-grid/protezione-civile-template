/**
 * sw-mappa.js
 * ──────────────────────────────────────────────────────────────────
 * SERVICE WORKER — Cache offline per i tile della mappa di Protezione Civile
 * ──────────────────────────────────────────────────────────────────
 *
 * A COSA SERVE:
 * Questo file intercetta le richieste di rete fatte dalla pagina mappa.php
 * (tramite Leaflet) verso i server di OpenStreetMap (tile.openstreetmap.org),
 * cioè le "tessere" grafiche che compongono la mappa.
 *
 * Una volta che un tile è stato scaricato una prima volta, viene salvato
 * in una cache locale del browser. Le volte successive (anche senza
 * connessione internet) il tile viene servito direttamente dalla cache,
 * permettendo di consultare le aree di mappa già visitate anche offline
 * o con connessione assente/instabile — utile in scenari di emergenza
 * di protezione civile dove la rete potrebbe non essere affidabile.
 *
 * COSA NON FA:
 * - Non mette in cache nient'altro (HTML, CSS, JS, immagini del sito):
 *   si occupa SOLO dei tile della mappa.
 * - Non funziona per zone mai visitate prima (non è una mappa
 *   "scaricabile" in anticipo, solo una cache di ciò che è già stato visto).
 *
 * DOVE VA MESSO:
 * Il file deve stare nella ROOT del sito (es. https://protezionecivile.comune.esempio.provincia.it/sw-mappa.js),
 * o comunque in una cartella di pari livello o superiore rispetto a
 * mappa.php. Il "service worker scope" coincide infatti con la cartella
 * in cui risiede questo file: se lo metti dentro una sottocartella
 * (es. /assets/js/sw-mappa.js), il suo raggio d'azione si limiterà a
 * quella sottocartella e NON intercetterà le richieste fatte da mappa.php
 * se questa si trova fuori da quel percorso.
 *
 * REGISTRAZIONE (già presente in mappa.php):
 *   if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
 *       navigator.serviceWorker.register('sw-mappa.js');
 *   }
 * I service worker richiedono HTTPS (eccetto in sviluppo su localhost).
 *
 * VERSIONING DELLA CACHE:
 * Il nome della cache include un numero di versione (v2). Se in futuro
 * modifichi questo file in modo sostanziale e vuoi forzare la pulizia
 * della cache vecchia sui dispositivi degli utenti, incrementa il numero
 * (es. 'mappa-tiles-v3'): l'evento "activate" qui sotto si occuperà
 * automaticamente di eliminare le cache con nomi diversi da quello attuale.
 * ──────────────────────────────────────────────────────────────────
 */

const CACHE = 'mappa-tiles-v2';

// ── INSTALL ───────────────────────────────────────────────────────
// Forza il nuovo service worker ad attivarsi subito, senza aspettare
// che tutte le tab/finestre con la vecchia versione vengano chiuse.
self.addEventListener('install', () => {
    self.skipWaiting();
});

// ── ACTIVATE ──────────────────────────────────────────────────────
// Alla attivazione, ripulisce eventuali cache di versioni precedenti
// (con nome diverso da CACHE), per non lasciare dati orfani sul
// dispositivo dell'utente. Poi "rivendica" subito il controllo delle
// pagine già aperte, senza bisogno di un refresh.
self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// ── FETCH ─────────────────────────────────────────────────────────
// Intercetta ogni richiesta di rete della pagina. Se NON riguarda un
// tile di OpenStreetMap, esce subito e lascia che il browser gestisca
// la richiesta normalmente (nessuna interferenza con altre risorse).
self.addEventListener('fetch', function (e) {
    const url = e.request.url;

    if (!url.includes('tile.openstreetmap.org')) {
        return; // richiesta non interessata: gestione standard del browser
    }

    e.respondWith(
        caches.open(CACHE).then(cache =>
            cache.match(e.request).then(cached => {
                // 1. Se il tile è già in cache, restituiscilo subito
                //    (strategia "cache-first": niente attesa di rete).
                if (cached) return cached;

                // 2. Altrimenti scaricalo dalla rete...
                return fetch(e.request).then(response => {
                    // ...e salvalo in cache per le prossime volte,
                    // solo se la risposta è valida (status 2xx).
                    if (response.ok) {
                        cache.put(e.request, response.clone());
                    }
                    return response;
                }).catch(() => {
                    // 3. Fallback estremo: rete assente e tile non in cache.
                    // La richiesta fallisce silenziosamente (il tile resterà
                    // vuoto/grigio sulla mappa). Si potrebbe in futuro
                    // restituire un'immagine placeholder locale.
                    console.log('Tile non trovato in cache e sei offline:', url);
                });
            })
        )
    );
});