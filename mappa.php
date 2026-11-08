<?php
/**
 * Template sito Protezione Civile
 * Copyright (C) 2026 beacon-grid
 *
 * Questo programma è software libero: puoi ridistribuirlo e/o modificarlo
 * secondo i termini della GNU Affero General Public License v3, come
 * pubblicata dalla Free Software Foundation.
 *
 * Distribuito SENZA ALCUNA GARANZIA. Testo completo della licenza nel
 * file LICENSE incluso in questo repository, o su:
 * https://www.gnu.org/licenses/agpl-3.0.html
 */

include('includes/header.php'); 
?>

<script src="/js/leaflet.js"></script>
<script src="/js/qrcode.min.js"></script>
<script src="/js/html2canvas.min.js"></script>
<link rel="stylesheet" href="/css/mappa.css">
<link rel="stylesheet" href="/css/leaflet.css">
<main id="main-content" tabindex="-1">
    <section id="mappa-territorio" class="py-5 bg-light">
        <div class="container-xl">

            <div class="row mb-4 text-center">
                <div class="col-12">
                    <h1 class="fw-bold h2" style="color: var(--pc-blue);">
                        <i class="bi bi-map-fill me-2"  aria-hidden="true"></i>Mappa di Protezione Civile
                    </h1>
                    <h2 class="text-muted mb-0 h3">Aree di attesa, aree di ricovero, sede operativa e Polizia Locale.</h2>
                </div>
            </div>

            <div class="row g-4 align-items-start">

                <div class="col-lg-8">
                    <div class="map-toolbar">
                        <span class="text-white-50 small fw-bold me-1"><i class="bi bi-funnel-fill me-1" aria-hidden="true"></i>Mostra:</span>
                        <button class="map-filter-btn active" data-layer="all" aria-label="Mostra tutti i punti sulla mappa" aria-pressed="true">
                            <span class="dot" style="background:#fff" aria-hidden="true"></span>Tutti
                        </button>
                        <button class="map-filter-btn" data-layer="sedi" aria-label="Mostra solo le sedi operative" aria-pressed="false">
                            <span class="dot" style="background:#D62828" aria-hidden="true"></span>Sedi
                        </button>
                        <button class="map-filter-btn" data-layer="attesa" aria-label="Mostra solo le aree di attesa" aria-pressed="false">
                            <span class="dot" style="background:#FFD200" aria-hidden="true"></span>Aree Attesa
                        </button>
                        <button class="map-filter-btn" data-layer="accoglienza" aria-label="Mostra solo le aree di accoglienza" aria-pressed="false">
                            <span class="dot" style="background:#1A7A3C" aria-hidden="true"></span>Accoglienza
                        </button>
                        <button class="map-filter-btn geo" id="btn-geo" aria-label="Trova la mia posizione e l'area piu' vicina" aria-pressed="false">
                            <i class="bi bi-crosshair me-1" aria-hidden="true"></i>Dove sono io
                        </button>
                    </div>
                    <div class="shadow-sm border border-top-0" style="border-radius:0 0 12px 12px; overflow:hidden;">
                        <div id="map-pc"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="map-legend">
                        <div class="map-legend-header">
                            <i class="bi bi-list-stars me-2"></i>Punti Chiave
                        </div>
                        <div class="map-legend-body">

                            <div class="legend-section-title">Sedi Operative</div>

                            <?php foreach ($site['map_points']['sedi'] as $i => $sede): ?>
                            <div class="legend-row">
                                <a class="legend-item" role="button" aria-label="Centra mappa su <?= htmlspecialchars($sede['titolo']) ?>, <?= htmlspecialchars($sede['indirizzo']) ?>" onclick="centraMappa([<?= $sede['coords'][0] ?>,<?= $sede['coords'][1] ?>], 18); return false;" href="#">
                                    <span class="legend-dot" style="background:<?= htmlspecialchars($sede['colore']) ?>" aria-hidden="true"></span>
                                    <div class="legend-item-text">
                                        <strong><?= htmlspecialchars($sede['titolo']) ?></strong>
                                        <small><?= htmlspecialchars($sede['indirizzo']) ?></small>
                                    </div>
                                </a>
                                <button type="button" class="legend-print-btn" data-action="print"
                                        data-title="<?= htmlspecialchars($sede['titolo']) ?>"
                                        data-address="<?= htmlspecialchars($sede['indirizzo']) ?>"
                                        data-type="Sede operativa"
                                        data-lat="<?= $sede['coords'][0] ?>" data-lng="<?= $sede['coords'][1] ?>"
                                        aria-label="Stampa scheda informativa di <?= htmlspecialchars($sede['titolo']) ?>">
                                    <i class="bi bi-printer-fill" aria-hidden="true"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>

                            <div class="legend-section-title mt-2">Aree di Attesa</div>

                            <?php foreach ($site['map_points']['attesa'] as $i => $att): ?>
                            <div class="legend-row">
                                <a class="legend-item" role="button" aria-label="Centra mappa su <?= htmlspecialchars($att['loc']) ?> (<?= htmlspecialchars($att['cod']) ?>)" onclick="centraMappa([<?= $att['coords'][0] ?>,<?= $att['coords'][1] ?>], 18); return false;" href="#">
                                    <span class="legend-dot" style="background:#C9A600;" aria-hidden="true"></span>
                                    <div class="legend-item-text">
                                        <strong>Area <?= $i+1 ?> <span class="fw-normal text-muted">(<?= htmlspecialchars($att['cod']) ?>)</span></strong>
                                        <small><?= htmlspecialchars($att['loc']) ?></small>
                                    </div>
                                </a>
                                <button type="button" class="legend-print-btn" data-action="print"
                                        data-title="Area di Attesa <?= $i+1 ?> — <?= htmlspecialchars($att['cod']) ?>"
                                        data-address="<?= htmlspecialchars($att['loc']) ?>"
                                        data-type="Area di attesa"
                                        data-lat="<?= $att['coords'][0] ?>" data-lng="<?= $att['coords'][1] ?>"
                                        aria-label="Stampa scheda informativa di Area di Attesa <?= $i+1 ?>">
                                    <i class="bi bi-printer-fill" aria-hidden="true"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>

                            <div class="legend-section-title mt-2">Aree di Accoglienza e Ricovero</div>

                            <?php foreach ($site['map_points']['accoglienza'] as $i => $acc): ?>
                            <div class="legend-row">
                                <a class="legend-item" role="button" aria-label="Centra mappa su <?= htmlspecialchars($acc['loc']) ?> (<?= htmlspecialchars($acc['cod']) ?>)" onclick="centraMappa([<?= $acc['coords'][0] ?>,<?= $acc['coords'][1] ?>], 18); return false;" href="#">
                                    <span class="legend-dot" style="background:#1A7A3C;" aria-hidden="true"></span>
                                    <div class="legend-item-text">
                                        <strong>Accoglienza <?= $i+1 ?> <span class="fw-normal text-muted">(<?= htmlspecialchars($acc['cod']) ?>)</span></strong>
                                        <small><?= htmlspecialchars($acc['loc']) ?></small>
                                    </div>
                                </a>
                                <button type="button" class="legend-print-btn" data-action="print"
                                        data-title="Area di Accoglienza <?= $i+1 ?> — <?= htmlspecialchars($acc['cod']) ?>"
                                        data-address="<?= htmlspecialchars($acc['loc']) ?>"
                                        data-type="Area di accoglienza"
                                        data-lat="<?= $acc['coords'][0] ?>" data-lng="<?= $acc['coords'][1] ?>"
                                        aria-label="Stampa scheda informativa di Accoglienza <?= $i+1 ?>">
                                    <i class="bi bi-printer-fill" aria-hidden="true"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>

                        </div>
                        <div class="map-tip">
                            <i class="bi bi-hand-index-thumb me-1"></i>
                            Clicca un punto per centrare la mappa
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <div class="offline-indicator" id="offline-indicator" role="status" aria-live="polite">
        <i class="bi bi-wifi-off me-1" aria-hidden="true"></i>Modalità offline — mappa in cache
    </div>
    <div id="sr-announce" class="sr-only-announce" role="status" aria-live="polite"></div>

    <!-- Scheda stampabile (popolata dinamicamente, visibile solo in stampa) -->
    <div class="print-sheet" id="print-sheet">
        <div class="ps-header">
            <img src="/assets/img/logo.png" alt="Logo Protezione Civile" class="pc-logo">
            <div class="ps-header-text">
                <p class="ps-eyebrow">
                    Scheda informativa — Protezione Civile <?php echo htmlspecialchars($site['nome_comune']); ?>
                </p>                
                <h2 id="ps-title">Scheda informativa — Protezione Civile</h2>
            </div>
            <span class="ps-badge" id="ps-type"></span>
        </div>

        <div class="ps-content">
            <div class="ps-map">
                <div class="ps-map-frame">
                    <img id="ps-map-img" src="/" alt="Mappa con posizione del punto">
                    <div class="ps-map-legend">
                        <div class="ps-map-legend-item">
                            <span class="ps-legend-icon ps-legend-star">★</span>
                            <span>Sede operativa</span>
                        </div>
                        <div class="ps-map-legend-item">
                            <span class="ps-legend-icon ps-legend-star-pl">★</span>
                            <span>Comando Polizia Locale</span>
                        </div>
                        <div class="ps-map-legend-item">
                            <span class="ps-legend-icon ps-legend-dot" style="background:#C9A600;">N</span>
                            <span>Area di attesa</span>
                        </div>
                        <div class="ps-map-legend-item">
                            <span class="ps-legend-icon ps-legend-dot" style="background:#1A7A3C;">N</span>
                            <span>Area di accoglienza</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ps-info-grid">
                <div class="ps-info-card full">
                    <p class="ps-info-label">Indirizzo / Localizzazione</p>
                    <p class="ps-info-value" id="ps-address"></p>
                </div>
                <div class="ps-info-card full">
                    <p class="ps-info-label">Coordinate GPS</p>
                    <p class="ps-info-value" id="ps-coords"></p>
                </div>
            </div>

            <div class="ps-qr-section">
                <div class="ps-qr" id="ps-qr"></div>
                <div class="ps-qr-text">
                    <strong>Naviga verso questo punto</strong>
                    Scansiona il codice QR con la fotocamera del telefono per avviare la navigazione GPS verso questa posizione.
                </div>
            </div>

            <div class="ps-footer">
                Documento generato da Protezione Civile <?php echo htmlspecialchars($site['nome_comune']); ?> — <span id="ps-date"></span>
            </div>
        </div>
    </div>
</main>
<script>
// ── Configurazione punti ──────────────────────────────
const PUNTI = <?= json_encode($site['map_points'], JSON_UNESCAPED_UNICODE) ?>;

// ── Helpers di navigazione esterna ───────────────────
function gmapsUrl(lat, lng) {
    return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
}
function appleMapsUrl(lat, lng) {
    return `https://maps.apple.com/?daddr=${lat},${lng}`;
}
function isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

// ── Annunci per screen reader ────────────────────────
function announce(msg) {
    const el = document.getElementById('sr-announce');
    el.textContent = '';
    setTimeout(() => { el.textContent = msg; }, 50);
}

// ── Distanza in metri (formula haversine) ────────────
function distanzaMetri(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const toRad = d => d * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2) ** 2 +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function formatDistanza(m) {
    return m < 1000 ? `${Math.round(m)} m` : `${(m/1000).toFixed(1)} km`;
}

// ── Crea icona marker colorata ────────────────────────
function makeIcon(colore, label) {
    const size = 34;
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size+8}" viewBox="0 0 ${size} ${size+8}">
          <filter id="sh" x="-30%" y="-30%" width="160%" height="160%">
            <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="rgba(0,0,0,.35)"/>
          </filter>
          <g filter="url(#sh)">
            <circle cx="${size/2}" cy="${size/2}" r="${size/2 - 2}" fill="${colore}"/>
            <polygon points="${size/2-6},${size-2} ${size/2},${size+6} ${size/2+6},${size-2}" fill="${colore}"/>
          </g>
          <text x="${size/2}" y="${size/2 + 5}" text-anchor="middle"
                font-family="Arial,sans-serif" font-size="13" font-weight="bold"
                fill="white">${label}</text>
        </svg>`;

    return L.divIcon({
        html: svg,
        className: '',
        iconSize:   [size, size + 8],
        iconAnchor: [size/2, size + 8],
        popupAnchor:[0, -(size + 6)]
    });
}

// ── Crea barra azioni minimal (Naviga + Condividi + QR) ──
function makeActions(lat, lng, titolo, indirizzo, pointId) {
    const navUrl = isIOS() ? appleMapsUrl(lat, lng) : gmapsUrl(lat, lng);
    const navLabel = isIOS() ? 'Apple Maps' : 'Google Maps';

    return `
    <div class="popup-actions">
        <a class="popup-btn-primary" href="${navUrl}" target="_blank" rel="noopener"
           aria-label="Naviga verso ${titolo} con ${navLabel}">
            <i class="bi bi-signpost-2-fill" aria-hidden="true"></i>Naviga qui
        </a>
        <button type="button" class="popup-btn-icon" data-action="share"
                data-title="${titolo}" data-address="${indirizzo}" data-url="${navUrl}"
                aria-label="Condividi posizione di ${titolo}">
            <i class="bi bi-share-fill" aria-hidden="true"></i>
        </button>
        <button type="button" class="popup-btn-icon" data-action="qr" data-target="qr-${pointId}"
                data-url="${navUrl}" aria-expanded="false" aria-controls="qr-${pointId}"
                aria-label="Mostra codice QR per navigare verso ${titolo}">
            <i class="bi bi-qr-code" aria-hidden="true"></i>
        </button>
    </div>
    <div class="popup-qr" id="qr-${pointId}" aria-hidden="true"></div>`;
}

// ── Crea contenuto popup ──────────────────────────────
function makePopup(colore, titolo, corpo, lat, lng, indirizzo, pointId) {
    return `<div class="popup-header" style="background:${colore}">${titolo}</div>
            <div class="popup-body">
                <p>${corpo}</p>
                ${makeActions(lat, lng, titolo, indirizzo, pointId)}
            </div>`;
}

// ── Init mappa ────────────────────────────────────────
const map = L.map('map-pc', { zoomControl: false }).setView([<?= floatval($site['address']['lat']) ?>, <?= floatval($site['address']['lng']) ?>], 14);
L.control.zoom({ position: 'bottomright' }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    crossOrigin: 'anonymous',
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

// ── Layer groups ──────────────────────────────────────
const layers = { sedi: L.layerGroup(), attesa: L.layerGroup(), accoglienza: L.layerGroup() };

// Lista flat di tutti i punti per calcolo distanze
const tuttiIPunti = [];

// Sedi operative
PUNTI.sedi.forEach((p, i) => {
    const desc = p.phone
        ? `${p.desc}<br><b>${p.indirizzo}</b> — <a href="tel:${p.phone.replace(/[^+0-9]/g, '')}">${p.phone}</a>`
        : `${p.desc}<br><b>${p.indirizzo}</b>`;

    const pointId = `sede-${i}`;
    const marker = L.marker(p.coords, { icon: makeIcon(p.colore, '★') })
     .bindPopup(makePopup(p.colore, p.titolo, desc, p.coords[0], p.coords[1], p.indirizzo, pointId), { maxWidth: 260 })
     .addTo(layers.sedi);

    tuttiIPunti.push({ marker, coords: p.coords, titolo: p.titolo, tipo: 'Sede operativa', layer: 'sedi' });
});

// Aree di attesa (giallo/ambra)
PUNTI.attesa.forEach((p, i) => {
    const pointId = `attesa-${i}`;
    const titolo = `Area di Attesa ${p.num} — ${p.cod}`;
    const marker = L.marker(p.coords, { icon: makeIcon('#C9A600', p.num) })
     .bindPopup(makePopup('#C9A600', titolo,
        `Punto di ammassamento popolazione.<br><b>${p.loc}</b>`,
        p.coords[0], p.coords[1], p.loc, pointId
     ), { maxWidth: 260 })
     .addTo(layers.attesa);

    tuttiIPunti.push({ marker, coords: p.coords, titolo, tipo: 'Area di attesa', layer: 'attesa' });
});

// Aree di accoglienza (verde)
PUNTI.accoglienza.forEach((p, i) => {
    const pointId = `accoglienza-${i}`;
    const titolo = `Area di Accoglienza ${p.num} — ${p.cod}`;
    const marker = L.marker(p.coords, { icon: makeIcon('#1A7A3C', p.num) })
     .bindPopup(makePopup('#1A7A3C', titolo,
        `Struttura di accoglienza e ricovero.<br><b>${p.loc}</b>`,
        p.coords[0], p.coords[1], p.loc, pointId
     ), { maxWidth: 260 })
     .addTo(layers.accoglienza);

    tuttiIPunti.push({ marker, coords: p.coords, titolo, tipo: 'Area di accoglienza', layer: 'accoglienza' });
});

// Aggiungi tutti i layer alla mappa
Object.values(layers).forEach(l => l.addTo(map));

// ── Filtri toolbar ────────────────────────────────────
document.querySelectorAll('.map-filter-btn[data-layer]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.map-filter-btn[data-layer]').forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-pressed', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-pressed', 'true');
        const target = this.dataset.layer;

        if (target === 'all') {
            Object.values(layers).forEach(l => map.addLayer(l));
        } else {
            Object.entries(layers).forEach(([key, l]) => {
                target === key ? map.addLayer(l) : map.removeLayer(l);
            });
        }
        announce(`Filtro applicato: ${this.textContent.trim()}`);
    });
});

// ── Funzione centra da legenda (Resa Globale per Window) ──
window.centraMappa = function(coords, zoom) {
    map.flyTo(coords, zoom, { duration: .8 });
};

// ──────────────────────────────────────────────────────
// GEOLOCALIZZAZIONE UTENTE
// ──────────────────────────────────────────────────────
let userMarker = null;
let nearestBanner = null;

document.getElementById('btn-geo').addEventListener('click', function() {
    if (!navigator.geolocation) {
        announce('Geolocalizzazione non supportata da questo browser.');
        alert('Il tuo browser non supporta la geolocalizzazione.');
        return;
    }

    this.setAttribute('aria-pressed', 'true');
    this.disabled = true;
    const originalHtml = this.innerHTML;
    this.innerHTML = '<i class="bi bi-hourglass-split me-1" aria-hidden="true"></i>Ricerca posizione...';
    announce('Ricerca della tua posizione in corso.');

    navigator.geolocation.getCurrentPosition(
        pos => {
            const { latitude, longitude } = pos.coords;
            this.disabled = false;
            this.innerHTML = originalHtml;
            this.setAttribute('aria-pressed', 'false');

            // Rimuovi marker precedente
            if (userMarker) map.removeLayer(userMarker);

            // Aggiungi marker posizione utente
            userMarker = L.marker([latitude, longitude], {
                icon: L.divIcon({
                    html: '<div class="user-location-marker" role="img" aria-label="La tua posizione"></div>',
                    className: '',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                }),
                keyboard: false
            }).bindPopup('La tua posizione attuale').addTo(map);

            // Calcola punto più vicino
            let nearest = null;
            let minDist = Infinity;
            tuttiIPunti.forEach(pt => {
                const d = distanzaMetri(latitude, longitude, pt.coords[0], pt.coords[1]);
                if (d < minDist) {
                    minDist = d;
                    nearest = pt;
                }
            });

            map.flyTo([latitude, longitude], 15, { duration: .8 });

            if (nearest) {
                showNearestBanner(nearest, minDist);
                announce(`Posizione trovata. Il punto piu' vicino e' ${nearest.titolo}, a ${formatDistanza(minDist)}.`);
            }
        },
        err => {
            this.disabled = false;
            this.innerHTML = originalHtml;
            this.setAttribute('aria-pressed', 'false');
            let msg = 'Impossibile ottenere la posizione.';
            if (err.code === err.PERMISSION_DENIED) {
                msg = 'Permesso di geolocalizzazione negato. Abilitalo nelle impostazioni del browser per usare questa funzione.';
            } else if (err.code === err.TIMEOUT) {
                msg = 'Tempo scaduto durante la ricerca della posizione. Riprova in un punto con migliore copertura GPS.';
            }
            announce(msg);
            alert(msg);
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
    );
});

function showNearestBanner(nearest, dist) {
    // Rimuovi banner esistente
    if (nearestBanner) nearestBanner.remove();

    const navUrl = isIOS() ? appleMapsUrl(nearest.coords[0], nearest.coords[1]) : gmapsUrl(nearest.coords[0], nearest.coords[1]);

    nearestBanner = document.createElement('div');
    nearestBanner.className = 'nearest-point-banner';
    nearestBanner.setAttribute('role', 'status');
    nearestBanner.innerHTML = `
        <span>
            <i class="bi bi-geo-alt-fill me-1" aria-hidden="true"></i>
            Punto piu' vicino: <strong>${nearest.titolo}</strong> (${nearest.tipo}) — circa ${formatDistanza(dist)}.
            <a href="${navUrl}" target="_blank" rel="noopener">Naviga ora</a>
        </span>
        <button type="button" class="close-banner" aria-label="Chiudi avviso punto piu' vicino">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    `;

    const mapCol = document.querySelector('.col-lg-8 .map-toolbar');
    mapCol.parentNode.insertBefore(nearestBanner, mapCol);

    nearestBanner.querySelector('.close-banner').addEventListener('click', () => {
        nearestBanner.remove();
        nearestBanner = null;
        announce('Avviso chiuso.');
    });

    // Apri popup del punto più vicino e centra (solo se il layer è visibile)
    setTimeout(() => {
        map.flyTo(nearest.coords, 17, { duration: .8 });
        if (map.hasLayer(layers[nearest.layer])) {
            nearest.marker.openPopup();
        }
    }, 1200);
}

// ──────────────────────────────────────────────────────
// AZIONI: CONDIVIDI, QR CODE, STAMPA (popup + legenda)
// ──────────────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;

    // ── Condividi ──
    if (action === 'share') {
        const titolo = btn.dataset.title;
        const address = btn.dataset.address;
        const url = btn.dataset.url;
        const shareData = {
            title: titolo,
            text: `${titolo} — ${address}`,
            url: url
        };

        if (navigator.share) {
            navigator.share(shareData).catch(() => {});
        } else {
            // Fallback: copia negli appunti
            navigator.clipboard.writeText(`${titolo} — ${address}\n${url}`)
                .then(() => {
                    announce('Link copiato negli appunti.');
                    alert('Link copiato negli appunti.');
                })
                .catch(() => {
                    alert(`${titolo}\n${address}\n${url}`);
                });
        }
    }

    // ── QR Code ──
    if (action === 'qr') {
        const targetId = btn.dataset.target;
        const url = btn.dataset.url;
        const container = document.getElementById(targetId);
        const expanded = btn.getAttribute('aria-expanded') === 'true';

        if (expanded) {
            container.classList.remove('show');
            container.setAttribute('aria-hidden', 'true');
            container.innerHTML = '';
            btn.setAttribute('aria-expanded', 'false');
        } else {
            container.innerHTML = '';
            new QRCode(container, {
                text: url,
                width: 140,
                height: 140,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            // Aggiungi testo alternativo per accessibilità
            setTimeout(() => {
                const img = container.querySelector('img, canvas');
                if (img) img.setAttribute('alt', 'Codice QR per avviare la navigazione verso questo punto');
            }, 50);
            container.classList.add('show');
            container.setAttribute('aria-hidden', 'false');
            btn.setAttribute('aria-expanded', 'true');
            announce('Codice QR generato. Scansionalo con la fotocamera del telefono per avviare la navigazione.');
        }
    }

    // ── Stampa scheda (da legenda) ──
    if (action === 'print') {
        stampaSchedaPunto(btn.dataset);
    }
});

// ──────────────────────────────────────────────────────
// GENERA SCHEDA STAMPABILE CON SCREENSHOT MAPPA
// ──────────────────────────────────────────────────────
// ──────────────────────────────────────────────────────
// GENERA SCHEDA STAMPABILE CON SCREENSHOT MAPPA
// ──────────────────────────────────────────────────────
async function stampaSchedaPunto({ title, address, type, lat, lng }) {
    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);
    const url = isIOS() ? appleMapsUrl(latNum, lngNum) : gmapsUrl(latNum, lngNum);

    // Popola testo
    document.getElementById('ps-title').textContent = title;
    document.getElementById('ps-address').textContent = address;
    document.getElementById('ps-coords').textContent = `${latNum.toFixed(6)}, ${lngNum.toFixed(6)}`;
    document.getElementById('ps-type').textContent = type;
    document.getElementById('ps-date').textContent = new Date().toLocaleDateString('it-IT', {
        year: 'numeric', month: 'long', day: 'numeric'
    });

    // QR code
    const qrContainer = document.getElementById('ps-qr');
    qrContainer.innerHTML = '';
    new QRCode(qrContainer, {
        text: url,
        width: 180,
        height: 180,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });

    announce('Generazione anteprima mappa in corso, attendere.');

    // Salva stato attuale mappa
    const originalCenter = map.getCenter();
    const originalZoom = map.getZoom();
    const imgEl = document.getElementById('ps-map-img');
    const mapEl = document.getElementById('map-pc');

    // Salva dimensioni inline originali del contenitore mappa
    const originalInlineWidth = mapEl.style.width;
    const originalInlineHeight = mapEl.style.height;

    // Dimensioni fisse per la cattura, indipendenti dal viewport corrente
    // (così lo screenshot è identico da mobile e da desktop)
    const CAPTURE_W = 800;
    const CAPTURE_H = 600;

    let screenshotOk = false;

    try {
        // Forza dimensioni fisse e forza Leaflet a ridisegnare i tile
        mapEl.style.width = `${CAPTURE_W}px`;
        mapEl.style.height = `${CAPTURE_H}px`;
        map.invalidateSize({ animate: false });

        // Attendi che TUTTI i tile della vista finiscano di caricare,
        // con timeout di sicurezza di 8s
        await waitForTilesLoaded([latNum, lngNum], 17, 8000);

        // Piccolo margine extra per il repaint del browser
        await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

        // Cattura screenshot della mappa a dimensioni fisse
        const canvas = await html2canvas(mapEl, {
            useCORS: true,
            logging: false,
            width: CAPTURE_W,
            height: CAPTURE_H
        });

        imgEl.src = canvas.toDataURL('image/jpeg', 0.85);
        imgEl.style.display = 'block';
        screenshotOk = true;
    } catch (err) {
        console.warn('Screenshot mappa non disponibile:', err);
        imgEl.style.display = 'none';
    } finally {
        // Ripristina dimensioni originali del contenitore mappa
        mapEl.style.width = originalInlineWidth;
        mapEl.style.height = originalInlineHeight;
        map.invalidateSize({ animate: false });

        // Ripristina vista originale mappa
        map.setView(originalCenter, originalZoom, { animate: false });
    }

    if (!screenshotOk) {
        announce('Anteprima mappa non disponibile. Apertura finestra di stampa senza mappa.');
    } else {
        announce('Apertura finestra di stampa scheda punto.');
    }

    // Attendi il repaint dopo il ripristino vista prima di stampare
    await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
    setTimeout(() => window.print(), 200);
}

// ── Attende il caricamento completo dei tile per una vista ──
// ── Attende il caricamento completo dei tile per una vista ──
function waitForTilesLoaded(center, zoom, timeoutMs) {
    return new Promise(resolve => {
        let settled = false;
        const finish = () => {
            if (settled) return;
            settled = true;
            resolve();
        };

        const timeoutId = setTimeout(finish, timeoutMs);

        map.once('moveend', () => {
            // Aspetta un frame perché Leaflet crei/posizioni gli <img> dei nuovi tile
            requestAnimationFrame(() => {
                pollTilesComplete();
            });
        });

        map.setView(center, zoom, { animate: false });

        function pollTilesComplete() {
            if (settled) return;

            const container = map.getContainer();
            const tiles = container.querySelectorAll('.leaflet-tile-pane img.leaflet-tile');

            if (tiles.length === 0) {
                // Nessun tile trovato ancora, riprova a breve
                setTimeout(pollTilesComplete, 50);
                return;
            }

            const allComplete = Array.from(tiles).every(img => img.complete && img.naturalWidth > 0);

            if (allComplete) {
                clearTimeout(timeoutId);
                // margine extra per il compositing finale del browser
                setTimeout(finish, 150);
            } else {
                setTimeout(pollTilesComplete, 100);
            }
        }
    });
}

// ──────────────────────────────────────────────────────
// MODALITA' OFFLINE / CACHE TILE
// ──────────────────────────────────────────────────────
const offlineIndicator = document.getElementById('offline-indicator');

function updateOnlineStatus() {
    if (navigator.onLine) {
        offlineIndicator.classList.remove('show');
    } else {
        offlineIndicator.classList.add('show');
        announce('Connessione assente. Le mappe già visualizzate restano disponibili dalla cache.');
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
updateOnlineStatus();

// Registra service worker per cache delle tile (solo se supportato e in contesto sicuro)
const isSecureContext = window.location.protocol === 'https:'
    || window.location.hostname === 'localhost'
    || window.location.hostname === '127.0.0.1';

if ('serviceWorker' in navigator && isSecureContext) {
    navigator.serviceWorker.register('/sw-mappa.js', { scope: '/' })
        .catch(err => console.warn('Service worker non registrato:', err));
}
</script>

<?php include('includes/footer.php'); ?>