<?php
include('../includes/header.php');
?>

<main id="main-content" tabindex="-1" aria-label="Bollettino allerte meteo">
<div class="pc-bollettino-container my-5">
    <div class="row" style="--bs-gutter-x: 0;">
        <div class="col-lg-11 col-xl-10 mx-auto">

            <?php
            // Se disponibili, passa le coordinate del sito. In assenza, il widget
            // meteo effettuerà una geocodifica automatica del nome del comune.
            $site_lat = $site['address']['lat'] ?? '';
            $site_lng = $site['address']['lng'] ?? '';
            ?>
            
            <div class="row text-center mb-5">
                <div class="col-12 text-center my-4">
                    <h1 class="display-4 fw-bold" style="color: var(--pc-blue);">
                        Bollettino Allerte Meteo
                    </h1>
                    <p class="lead text-secondary">
                        Criticità meteo-idrogeologiche ufficiali per il comune di <?= htmlspecialchars($site['address']['city']) ?>.
                    </p>
                    <hr class="w-25 mx-auto" style="border-top: 3px solid var(--pc-yellow-text); opacity: 1;" aria-hidden="true">
                </div>
            </div>
            <div class="row g-4 mb-4">

                <div class="col-12 col-lg-6">
                    <div class="pc-card h-100"
                         id="meteo-widget"
                         data-lat="<?= htmlspecialchars($site_lat) ?>"
                         data-lng="<?= htmlspecialchars($site_lng) ?>"
                         data-city="<?= htmlspecialchars($site['address']['city']) ?>">

                        <div class="pc-card-header pc-card-header-meteo text-white p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="pc-icon-badge">
                                    <i class="bi bi-cloud-sun-fill fs-3" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h2 class="pc-title h4 mb-0">Meteo del Territorio</h2>
                                    <p class="pc-subtitle mb-0">Condizioni attuali, prossime ore e riepilogo giornata</p>
                                </div>
                            </div>
                        </div>
                        <div id="meteo-body" class="p-4">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status" style="color: var(--pc-blue);">
                                    <span class="visually-hidden">Caricamento meteo...</span>
                                </div>
                                Recupero dati meteo in corso...
                            </div>
                        </div>
                        <div class="px-4 pb-3">
                            <span id="meteo-fonte" class="pc-fonte">Previsioni: <a href="https://open-meteo.com" target="_blank" rel="noopener">Open-Meteo.com</a></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="pc-card h-100">

                        <div class="pc-card-header text-white p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="pc-icon-badge">
                                    <i class="bi bi-shield-fill-exclamation fs-3" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h2 class="pc-title h4 mb-0">Bollettino Criticità</h2>
                                    <p class="pc-subtitle mb-0">Scenari di rischio attuali e previsionali</p>
                                </div>
                            </div>
                        </div>

                        <div class="pc-card-status p-4 border-bottom">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <span class="pc-label d-block mb-1">STATO DI ALLERTA GLOBALE</span>
                                    <div id="bollettino-stato-container" class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill fw-bold text-uppercase">
                                        <i id="bollettino-stato-icona" class="bi"></i>
                                        <span id="bollettino-stato-testo"><span class="spinner-border spinner-border-sm me-2" role="status" style="color: var(--pc-blue);"></span>Verifica...</span>
                                    </div>
                                </div>
                                <div id="bollettino-aggiornamento" class="text-lg-end text-muted">
                                    </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="pc-table table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="ps-4 py-3">Tipologia di Rischio</th>
                                        <th scope="col" class="text-center py-3" style="width: 25%;">Oggi</th>
                                        <th scope="col" class="text-center py-3" style="width: 25%;">Domani</th>
                                    </tr>
                                </thead>
                                <tbody id="tabella-rischi-body">
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <div class="spinner-border spinner-border-sm me-2" role="status" style="color: var(--pc-blue);">
                                                <span class="visually-hidden">Caricamento in corso...</span>
                                            </div>
                                            Sincronizzazione dei dati regionali in corso...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pc-legenda p-4">
                            <h2 class="pc-title h6 mb-3 text-uppercase tracking-wider">Legenda Scenari di Rischio</h2>
                            <div class="row g-2">
                                <div class="col-sm-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded border bg-light">
                                        <span class="pc-dot" style="background-color: var(--alert-verde);"></span>
                                        <span class="small fw-semibold">VERDE (Nessuna)</span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded border bg-light">
                                        <span class="pc-dot" style="background-color: var(--alert-giallo);"></span>
                                        <span class="small fw-semibold">GIALLO (Ordinaria)</span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded border bg-light">
                                        <span class="pc-dot" style="background-color: var(--alert-arancio);"></span>
                                        <span class="small fw-semibold">ARANCIO (Moderata)</span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded border bg-light">
                                        <span class="pc-dot" style="background-color: var(--alert-rosso);"></span>
                                        <span class="small fw-semibold">ROSSO (Elevata)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="pc-card">
                        <button type="button" id="radar-toggle" class="w-100 border-0 bg-transparent text-start p-0" aria-expanded="false" aria-controls="radar-container">
                            <div class="pc-card-header text-white p-4 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="pc-icon-badge">
                                        <i class="bi bi-broadcast fs-3" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h2 class="pc-title h4 mb-0">Radar Precipitazioni</h2>
                                        <p class="pc-subtitle mb-0">Mostra la mappa radar in tempo reale (Radar-DPC)</p>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down fs-4" id="radar-toggle-icona" aria-hidden="true"></i>
                            </div>
                        </button>
                        <div id="radar-container" class="p-4" style="display:none;"
                            data-lat="<?= htmlspecialchars($site_lat) ?>"
                            data-lng="<?= htmlspecialchars($site_lng) ?>"
                            data-city="<?= htmlspecialchars($site['address']['city']) ?>">
                            <div id="radar-map" style="height:400px; border-radius: var(--radius-sm); background: var(--pc-light);"></div>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <button type="button" id="radar-play" class="btn btn-sm" style="background: var(--pc-blue); color: white;">
                                    <i class="bi bi-play-fill" aria-hidden="true"></i> Riproduci ultima ora
                                </button>
                                <span id="radar-orario" class="text-muted small"></span>
                            </div>
                            <span class="pc-fonte d-block mt-2">Fonte: <a href="https://librewxr.net" target="_blank" rel="noopener">LibreWXR</a> — dati radar Italia: <a href="https://www.protezionecivile.gov.it" target="_blank" rel="noopener">Radar-DPC</a>, CC-BY-SA 4.0</span>
                        </div>
                    </div>
                </div>
            </div>
            

        </div>
    </div>
</div>
</main>
<script>
(function() {
    const css = `
        .pc-bollettino-container {
            font-family: var(--font-body);
            color: var(--pc-dark);
        }
        .pc-title {
            font-family: var(--font-display);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pc-subtitle {
            font-size: 0.9rem;
            opacity: 0.85;
        }
        .pc-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.05);
            overflow: hidden;
            transition: var(--transition);
        }
        .pc-card:hover {
            box-shadow: var(--shadow-md), 0 8px 24px rgba(0,0,0,0.06);
        }
        .pc-card-header {
            background: linear-gradient(135deg, var(--pc-blue) 0%, var(--pc-blue-mid) 100%);
            border-bottom: 3px solid var(--pc-yellow);
        }
        .pc-card-header-meteo {
            background: linear-gradient(135deg, var(--pc-blue-mid) 0%, var(--pc-blue) 100%);
            border-bottom: 3px solid var(--pc-yellow);
        }
        .pc-icon-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .pc-card-status {
            background-color: var(--pc-light);
        }
        .pc-label {
            font-family: var(--font-display);
            font-size: 0.85rem;
            letter-spacing: 1px;
            color: var(--pc-blue-mid);
        }
        .pc-table th {
            font-family: var(--font-display);
            background-color: var(--pc-light);
            color: var(--pc-blue-mid);
            border-bottom: 2px solid rgba(0,0,0,0.06);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .pc-table td {
            padding: 14px 10px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 0.95rem;
        }
        .pc-table tbody tr:hover {
            background-color: rgba(0, 59, 122, 0.02);
        }
        .pc-badge {
            font-family: var(--font-display);
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 6px 16px;
            border-radius: var(--radius-sm);
            display: inline-block;
            min-width: 100px;
            text-align: center;
            font-weight: bold;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .pc-badge:hover {
            transform: translateY(-1px);
        }
        .pc-legenda {
            background: #ffffff;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .pc-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        #bollettino-aggiornamento {
            font-size: 0.85rem;
            line-height: 1.4;
        }

        /* --- Widget Meteo --- */
        .meteo-avviso {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(255, 193, 7, 0.12);
            border: 1px solid rgba(255, 193, 7, 0.4);
            color: #7a5c00;
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 14px;
        }
        .meteo-avviso i {
            font-size: 1.1rem;
            color: var(--pc-yellow);
            flex-shrink: 0;
        }
        .meteo-headline {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }
        .meteo-headline i.meteo-icona-principale {
            font-size: 3rem;
            color: var(--pc-blue-mid);
        }
        .meteo-headline .meteo-temp-principale {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--pc-dark);
            line-height: 1;
        }
        .meteo-headline .meteo-desc {
            color: var(--pc-blue-mid);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .meteo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        @media (min-width: 576px) {
            .meteo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .meteo-item {
            background-color: var(--pc-light);
            border-radius: var(--radius-sm);
            padding: 14px 10px;
            text-align: center;
            transition: var(--transition);
        }
        .meteo-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        .meteo-item i {
            font-size: 1.4rem;
            color: var(--pc-blue-mid);
            margin-bottom: 6px;
            display: inline-block;
        }
        .meteo-item .meteo-valore {
            font-family: var(--font-display);
            font-size: 1.15rem;
            font-weight: bold;
            color: var(--pc-dark);
            line-height: 1.1;
        }
        .meteo-item .meteo-valore-secondario {
            font-size: 0.72rem;
            color: #6b7a8d;
            margin-top: 1px;
        }
        .meteo-item .meteo-etichetta {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--pc-blue-mid);
            margin-top: 2px;
        }
        .meteo-sezione-titolo {
            font-family: var(--font-display);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--pc-blue-mid);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .meteo-hourly-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 24px;
            scrollbar-width: thin;
        }
        .meteo-hourly-item {
            flex: 0 0 auto;
            min-width: 74px;
            background-color: var(--pc-light);
            border-radius: var(--radius-sm);
            padding: 10px 8px;
            text-align: center;
        }
        .meteo-hourly-item .meteo-hourly-ora {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--pc-dark);
        }
        .meteo-hourly-item i {
            font-size: 1.2rem;
            color: var(--pc-blue-mid);
            display: block;
            margin: 6px 0;
        }
        .meteo-hourly-item .meteo-hourly-temp {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--pc-dark);
        }
        .meteo-hourly-item .meteo-hourly-pioggia {
            font-size: 0.68rem;
            color: var(--pc-blue-mid);
            margin-top: 2px;
        }
        .meteo-daily-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        @media (min-width: 576px) {
            .meteo-daily-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .meteo-daily-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: var(--pc-light);
            border-radius: var(--radius-sm);
            padding: 12px;
        }
        .meteo-daily-item i {
            font-size: 1.3rem;
            color: var(--pc-blue-mid);
            flex-shrink: 0;
        }
        .meteo-daily-item .meteo-valore {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: bold;
            color: var(--pc-dark);
            line-height: 1.1;
        }
        .meteo-daily-item .meteo-etichetta {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7a8d;
        }
        .pc-fonte {
            font-size: 0.78rem;
            color: #94a3b8;
        }
        .pc-fonte a {
            color: var(--pc-blue-mid);
        }
    `;
    const style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);
})();
</script>

<script src="../js/allerta.js" defer></script>
<?php
include('../includes/footer.php');
?>