<?php 
require_once __DIR__ . '/php/config.php';
$config = app_config();
$site = $config['site'];
include('includes/header.php'); 
?>

<main id="main-content" class="container my-5" tabindex="-1">
    <div class="row text-center mb-5">
        <div class="col-12">
            <h1 class="display-4 fw-bold" style="color: var(--pc-blue);">Chi Siamo</h1>
            <p class="lead text-secondary">Un gruppo di professionisti e volontari, ma prima di tutto una grande famiglia al servizio della comunità.</p>
            <hr class="w-25 mx-auto" style="border-top: 3px solid var(--pc-yellow-text); opacity: 1;" aria-hidden="true">
        </div>
    </div>

    <!-- CAROSELLO -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-lg-10">

            <div id="carouselVolontari"
                 class="carousel slide rounded-3 overflow-hidden shadow carousel-hero"
                 data-bs-ride="carousel"
                 data-bs-interval="4500"
                 role="region"
                 aria-label="Galleria foto volontari ed eventi">

                <!-- Indicatori -->
                <div class="carousel-indicators">
                    <button type="button"
                            data-bs-target="#carouselVolontari"
                            data-bs-slide-to="0"
                            class="active" aria-current="true"
                            aria-label="Foto di gruppo dei volontari">
                    </button>
                    <?php foreach ($site['galleria'] as $i => $foto): ?>
                    <button type="button"
                            data-bs-target="#carouselVolontari"
                            data-bs-slide-to="<?= $i + 1 ?>"
                            aria-label="<?= htmlspecialchars($foto['didascalia']) ?>">
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Slide -->
                <div class="carousel-inner" style="max-height: 450px;">

                    <!-- Prima slide fissa: sempre chi-siamo.png -->
                    <div class="carousel-item active">
                        <img src="assets/img/chi-siamo.png"
                            class="d-block w-100"
                            alt="<?= htmlspecialchars('Fotografia di gruppo dei volontari del ' . $site['nome_ente']) ?>"
                            style="object-fit: cover; height: 450px;">
                        <div class="carousel-caption d-none d-md-block"
                            style="background: rgba(0,0,0,.45); border-radius: .5rem; padding: .6rem 1rem;">
                            <p class="mb-0 fw-semibold">
                                <?= htmlspecialchars('Fotografia di gruppo dei volontari del ' . $site['nome_ente']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Slide dinamiche dal config -->
                    <?php foreach ($site['galleria'] as $foto): ?>
                    <div class="carousel-item">
                        <img src="assets/img/galleria/<?= htmlspecialchars($foto['src']) ?>"
                            class="d-block w-100"
                            alt="<?= htmlspecialchars($foto['alt']) ?>"
                            style="object-fit: cover; height: 450px;">
                        <div class="carousel-caption d-none d-md-block"
                            style="background: rgba(0,0,0,.45); border-radius: .5rem; padding: .6rem 1rem;">
                            <p class="mb-0 fw-semibold">
                                <?= htmlspecialchars($foto['didascalia']) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>

                <!-- Controlli frecce -->
                <button class="carousel-control-prev" type="button"
                        data-bs-target="#carouselVolontari" data-bs-slide="prev"
                        aria-label="Foto precedente">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button"
                        data-bs-target="#carouselVolontari" data-bs-slide="next"
                        aria-label="Foto successiva">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>

            </div>
            <div class="text-center mt-3">
                <a href="/assets/img/galleria/" class="btn btn-outline-primary fw-medium px-4 py-2 rounded-pill">
                    <i class="bi bi-images me-2"></i>Guarda tutte le foto della galleria
                </a>
            </div>
        </div>
    </div>

    <div class="row align-items-center mb-5">
        <div class="col-md-6 mb-4 mb-md-0">
            <span class="hero-kicker text-warning-on-light">La nostra comunità</span>
            <h2 class="fw-bold mb-3 section-title">I tuoi vicini di casa, in divisa</h2>
            <p class="fs-5 text-secondary lh-base">
                Siamo un gruppo di volontari che opera sul territorio per supportare la comunità nelle attività di protezione civile, prevenzione e gestione delle emergenze.
            </p>

            <p class="text-dark">
                Collaboriamo con le istituzioni e con le altre realtà del sistema di protezione civile, mettendo a disposizione tempo, competenze e formazione continua per intervenire quando necessario.
            </p>

            <p class="text-dark">
                Le nostre attività comprendono il supporto durante eventi e manifestazioni, le campagne informative rivolte alla cittadinanza, le esercitazioni e gli interventi in situazioni di emergenza.
            </p>

            <p class="fw-semibold text-dark">
                <i class="bi bi-chat-heart-fill text-danger me-2" aria-hidden="true"></i>   Fare volontariato vuol dire mettersi a disposizione degli altri. Hai bisogno di informazioni o vuoi fare due chiacchiere? Avvicinati a un volontario: saremo felici di incontrarti.
            </p>
        </div>
        <div class="col-md-6">
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 bg-white rounded shadow-sm text-center border-bottom border-danger border-3 h-100 info-card">
                        <i class="bi bi-shield-fill-check text-danger fs-1"></i>                        <h3 class="fw-bold fs-5 mt-3 mb-1 text-dark">Sicurezza</h3>
                        <small class="text-secondary fw-semibold">
                            Tutela delle persone e del territorio
                        </small>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 bg-white rounded shadow-sm text-center border-bottom border-primary border-3 h-100 info-card">
                        <i class="bi bi-lightning-charge-fill text-primary fs-1"></i>
                        <h3 class="fw-bold fs-5 mt-3 mb-1 text-dark">Prontezza</h3>
                        <small class="text-secondary fw-semibold">
                            Intervento rapido nelle emergenze
                        </small>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 bg-white rounded shadow-sm text-center border-bottom border-success border-3 h-100 info-card">
                        <i class="bi bi-people-fill text-success fs-1"></i>
                        <h3 class="fw-bold fs-5 mt-3 mb-1 text-dark">Volontariato</h3>
                        <small class="text-secondary fw-semibold">
                            Impegno al servizio della comunità
                        </small>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 bg-white rounded shadow-sm text-center border-bottom border-3 h-100 info-card"
                        style="border-color:#0F766E!important;">
                        <i class="bi bi-diagram-3-fill fs-1" style="color:#0F766E;"></i>
                        <h3 class="fw-bold fs-5 mt-3 mb-1 text-dark">Coordinamento</h3>
                        <small class="text-secondary fw-semibold">
                            Lavoro di squadra e gestione delle operazioni
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center my-5">
        <div class="col-md-10">
            <div class="p-5 bg-light rounded-3 shadow-sm border text-center">
                <h2 class="fw-bold mb-4 h3 section-title">Le nostre risorse sul campo</h2>
                <div class="row g-4">
                    <div class="col-6 col-md-6">
                        <div class="p-3">
                            <i class="bi bi-people-fill text-primary display-3 mb-2 d-block" aria-hidden="true"></i>
                            <span class="display-4 fw-bold text-dark counter" data-target="<?= htmlspecialchars($site['stats']['volontari_attivi']) ?>">0</span>
                            <p class="fs-5 text-secondary fw-semibold mt-2">Volontari Attivi</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="p-3">
                            <i class="bi bi-truck text-warning-on-light display-3 mb-2 d-block" aria-hidden="true"></i>
                            <span class="display-4 fw-bold text-dark counter" data-target="<?= htmlspecialchars($site['stats']['mezzi_soccorso']) ?>">0</span>
                            <p class="fs-5 text-secondary fw-semibold mt-2">Mezzi di Soccorso</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <hr class="my-5 opacity-25" aria-hidden="true">

    
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            
            <div class="accordion shadow-sm" id="accordionIstituzionale">
                <div class="accordion-item border-0 rounded-3 overflow-hidden">
                    
                    <h2 class="accordion-header" id="headingIstituzionale">
                        <button class="accordion-button collapsed bg-light fw-bold fs-5 py-3 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIstituzionale" aria-expanded="false" aria-controls="collapseIstituzionale" style="color: var(--pc-blue);">
                            <i class="bi bi-info-circle-fill me-2 text-primary" aria-hidden="true"></i> 
                            Inquadramento Operativo e Ruolo Istituzionale (Approfondisci)
                        </button>
                    </h2>
                    
                    <div id="collapseIstituzionale" class="accordion-collapse collapse" role="region" aria-labelledby="headingIstituzionale" data-bs-parent="#accordionIstituzionale">
                        <div class="accordion-body bg-white p-4 p-md-5 border-top">
                            
                            <div class="border-start border-primary border-4 ps-4 mb-5">
                                <p class="fs-5 lh-base text-dark mb-4">
                                    La Protezione Civile comunale è la struttura deputata alla pianificazione, alla prevenzione dei rischi e alla gestione delle emergenze nel territorio di competenza. Opera sotto la direzione del Sindaco, Autorità locale di protezione civile, mobilitando il personale del Comune e le organizzazioni di volontariato iscritte all'elenco territoriale, in piena conformità con le pianificazioni di emergenza vigenti.
                                </p>
                                <p class="lh-lg text-secondary mb-0">
                                    Le attività di previsione e prevenzione sono tese a limitare i possibili danni derivanti dai rischi insiti nel territorio (terremoti, inondazioni, frane, inquinamento acque, incidenti industriali/ferroviari, ecc.) attraverso la comunicazione alla popolazione degli indicatori di pericolo/disagio (ondate di calore, rischio idrogeologico, rischio precipitazioni eccezionali, ecc.) e la tenuta in efficienza di mezzi e attrezzature per interventi tempestivi in caso di calamità.
                                </p>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded border h-100">
                                        <h3 class="fw-bold text-dark h5"><i class="bi bi-graph-up-arrow text-primary me-2" aria-hidden="true"></i>Previsione attiva</h3>
                                        <p class="small text-secondary mb-0" style="color: var(--pc-text-secondary)!important;">Seguiamo l'evoluzione dei rischi analizzando i dati regionali per tradurli in avvisi chiari e comprensibili a tutti i cittadini.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded border h-100">
                                        <h3 class="fw-bold text-dark h5"><i class="bi bi-shield-shaded text-success me-2" aria-hidden="true"></i>Prevenzione e Mezzi</h3>
                                        <p class="small text-secondary mb-0" style="color: var(--pc-text-secondary)!important;">Curiamo quotidianamente le nostre attrezzature e i mezzi idraulici o logistici perché siano pronti a partire in pochissimi minuti.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded border h-100">
                                        <h3 class="fw-bold text-dark h5"><i class="bi bi-diagram-3-fill text-warning-on-light me-2" aria-hidden="true"></i>Associazioni Specializzate</h3>
                                        <p class="small text-secondary mb-0" style="color: var(--pc-text-secondary)!important;">In base all'evento (idrico, sismico o ambientale) attiviamo i team con le competenze più adatte allo scenario in corso.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-lg-10">
                                    <div class="card border border-light shadow-sm">
                                        <div class="card-body p-4">
                                            <h3 class="fw-bold text-center mb-3 h5" style="color: var(--pc-blue);"><i class="bi bi-diagram-2-fill me-2" aria-hidden="true"></i>Chi coordina le attività</h3>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <caption class="visually-hidden">Organigramma e ruoli di coordinamento della Protezione Civile Comunale</caption>
                                                    <thead class="visually-hidden">
                                                        <tr>
                                                            <th scope="col">Ruolo</th>
                                                            <th scope="col">Persona</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($site['organigramma'] as $membro): ?>
                                                        <tr>
                                                            <th scope="row" class="fw-bold py-3 text-dark" style="width: 50%;">
                                                                <i class="bi <?= htmlspecialchars($membro['icona']) ?> <?= htmlspecialchars($membro['icona_classe']) ?> me-2 fs-5" aria-hidden="true"></i>
                                                                <?= htmlspecialchars($membro['ruolo']) ?>
                                                            </th>
                                                            <td class="text-dark"><?= htmlspecialchars($membro['nome']) ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const style = document.createElement('style');
    style.textContent = `
        .hero-kicker {
            letter-spacing: .16em;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .carousel-hero .carousel-caption {
            left: 1.5rem;
            right: 1.5rem;
            bottom: 1.25rem;
            text-align: left;
            max-width: 70%;
        }
        .info-card {
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .8rem 1.5rem rgba(0,0,0,.08) !important;
        }
        .section-title {
            color: var(--pc-blue);
            letter-spacing: -.01em;
        }
        .accordion-button:not(.collapsed) {
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.06);
        }
    `;
    document.head.appendChild(style);

    const counters = document.querySelectorAll('.counter');
    const speed = 100;

    const startCounter = (counter) => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = Math.ceil(target / speed);

            if (count < target) {
                counter.innerText = count + inc > target ? target : count + inc;
                setTimeout(updateCount, 40);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                startCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
});
</script>

<?php 
// Includiamo il footer comune
include('includes/footer.php'); 
?>


