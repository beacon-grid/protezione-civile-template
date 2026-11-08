<!-- MENU -->
<?php if (empty($is_print_view)): ?>
<div class="bg-white sticky-top shadow-sm border-bottom py-2 d-block risk-menu"
     style="top: 70px; z-index: 99;">

    <div class="container text-center d-flex flex-wrap justify-content-center align-items-center gap-2 risk-menu-inner">

        
        <details open>
            <summary>
                <span class="small fw-semibold text-muted me-3 mb-2 mb-md-0 w-100 w-md-auto">
                    Salta a:
                </span>
            </summary>
            <a href="#"
            data-target="meteo"
            class="risk-btn btn btn-sm btn-secondary mx-1 fw-medium">
                <i class="bi bi-cloud-lightning-rain mx-1 text-info"></i>
                Meteo
            </a>

            <a href="#"
            data-target="neve"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-snowflake mx-1 text-info"></i>
                Neve
            </a>

            <a href="#"
            data-target="terremoto"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-house-exclamation me-1 text-danger"></i>
                Terremoto
            </a>

            <a href="#"
            data-target="alluvione"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-water mx-1 text-primary"></i>
                Alluvione
            </a>

            <a href="#"
            data-target="incendio"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-fire mx-1 text-warning-on-light"></i>
                Incendio Boschivo
            </a>

            <a href="#"
            data-target="vulcanico"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-triangle-half mx-1"
                style="color: #6f42c1;"></i>
                Vulcanico
            </a>

            <a href="#"
            data-target="frana"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-layers-half mx-1"
                style="color: #a0522d;"></i>
                Frana
            </a>

            <a href="#"
            data-target="valanga"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-snow2 mx-1"
                style="color: #17a2b8;"></i>
                Valanga
            </a>

            <a href="#"
            data-target="crisi-idrica"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-droplet-half mx-1"
                style="color: #0d6efd;"></i>
                Crisi Idrica
            </a>

            <a href="#"
            data-target="ciclone"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-hurricane mx-1"
                style="color: #198754;"></i>
                Ciclone
            </a>

            <a href="#"
            data-target="maremoto"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-tsunami mx-1"
                style="color: #0a58ca;"></i>
                Maremoto
            </a>

            <a href="#"
            data-target="nucleare"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-radioactive mx-1"
                style="color: #d39e00;"></i>
                Nucleare
            </a>

            <a href="#"
            data-target="industriale"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-factory mx-1"
                style="color: #6c757d;"></i>
                Industriale
            </a>

            <a href="#"
            data-target="blackout"
            class="risk-btn btn btn-sm btn-outline-secondary mx-1 fw-medium">
                <i class="bi bi-lightning-charge mx-1" style="color: #fd7e14;"></i>
                Blackout
            </a>
        </details>
        
        <button type="button" class="btn btn-sm btn-outline-primary mx-1 fw-medium"
                data-bs-toggle="modal" data-bs-target="#printModal">
            <i class="bi bi-printer mx-1"></i> Stampa / Esporta PDF
        </button>
    </div>
</div>
<?php endif; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- METEO -->
            <section id="meteo"
                     class="risk-section active py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-info border-4">

                    <div class="d-flex align-items-center mb-4">

                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle me-3">
                            <i class="bi bi-cloud-lightning-rain fs-2"></i>
                        </div>

                        <div>
                            <span class="badge bg-info text-dark text-uppercase mb-1">
                                Rischio Meteo-Idro
                            </span>

                            <h2 class="fw-bold m-0 text-dark">
                                Rischi Meteorologici
                            </h2>
                        </div>

                    </div>

                    <div class="row g-4">

                        <div class="col-lg-4">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">

                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-lightning-charge-fill me-2"></i>Temporali e Fulmini
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Pianificazione e All’aperto:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Verifica il meteo in fase di pianificazione di qualsiasi attività all’aperto.</li>
                                    <li class="mb-1">Se vedi lampi, anche lontani, il temporale potrebbe avvicinarsi: allontanati velocemente.</li>
                                    <li class="mb-1">Resta lontano da punti sporgenti (pali, alberi isolati) e togliti oggetti metallici di dosso.</li>
                                    <li class="mb-1"><strong>In montagna:</strong> Scendi di quota, evita creste o vette e allontanati da vie ferrate, funi metalliche, piccozze e sci. Riparati in una grotta lontano dalle pareti.</li>
                                    <li class="mb-1">Se isolato in un prato, accovacciati a piedi uniti. Evita di sdraiarti e mantieni 10 metri dagli altri.</li>
                                    <li class="mb-1"><strong>Al mare/lago:</strong> Evita il contatto con l'acqua, allontanati dalla riva e staccati da ombrelloni o canne da pesca.</li>
                                    <li class="mb-1"><strong>In campeggio:</strong> Riparati in strutture in muratura o nell'auto. In tenda, isolati dal terreno e stacca le apparecchiature elettriche.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">In Casa / Al chiuso:</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1">Scollega elettrodomestici, TV e computer dalle prese. Evita il telefono fisso.</li>
                                    <li class="mb-1">Non toccare elementi metallici collegati all’esterno (tubature, caloriferi, rubinetti).</li>
                                    <li class="mb-1">Non sostare su tettoie e balconi; resta all'interno con finestre e porte ben chiuse.</li>
                                </ul>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="p-3 bg-light rounded h-100 border-top border-primary border-3">

                                <h3 class="fw-bold text-primary mb-3 h5">
                                    <i class="bi bi-cloud-sleet-fill me-2"></i>Pioggia, Grandine e Nebbia
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Pioggia e Grandine:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">In campeggio scegli piazzole rialzate e distanti da torrenti, pendii ripidi o instabili.</li>
                                    <li class="mb-1">In città fai massima attenzione al passaggio in sottovia e sottopassi stradali.</li>
                                    <li class="mb-1">Alla guida riduci la velocità o effettua una sosta sicura fuori dalla carreggiata.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">In caso di Nebbia (Guida Sicura):</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1">Se puoi, evita di metterti in viaggio. Se necessario, mantieni la velocità molto bassa.</li>
                                    <li class="mb-1">Aumenta la distanza di sicurezza e segui i pannelli a messaggio variabile.</li>
                                    <li class="mb-1">Accendi anabbaglianti, fendinebbia e retronebbia (mai gli abbaglianti).</li>
                                    <li class="mb-1">Evita i sorpassi su strade a doppio senso senza spartitraffico. Non viaggiare sulla striscia laterale.</li>
                                    <li class="mb-1">Se devi fermarti, fallo fuori dalla carreggiata rallentando gradualmente, attivando le 4 frecce e tenendo i retronebbia accesi.</li>
                                </ul>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">

                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-wind me-2"></i>Vento, Mareggiate e Calore
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Venti forti e Mareggiate:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Evita le zone esposte, le aree verdi e le strade alberate (rischio caduta rami/alberi).</li>
                                    <li class="mb-1">Alla guida modera la velocità: attenzione alle raffiche laterali su viadotti e all'uscita dalle gallerie.</li>
                                    <li class="mb-1">Fissa tutti gli oggetti su balconi o aree aperte di casa che potrebbero volare via.</li>
                                    <li class="mb-1">Verifica la tenuta di strutture mobili (gazebo, impalcature).</li>
                                    <li class="mb-1">Non avvicinarti al litorale, moli o pontili. Evita tassativamente balneazione e imbarcazioni, assicurando preventivamente le barche in porto.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">Ondate di Calore:</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1">Evita di uscire dalle 12 alle 18, specie anziani, bambini e persone fragili.</li>
                                    <li class="mb-1">In casa scherma i vetri con tende o persiane. Mantieni il climatizzatore a 25-27°C.</li>
                                    <li class="mb-1">Non orientare i ventilatori direttamente sul corpo.</li>
                                    <li class="mb-1">Bevi molta acqua, consuma pasti leggeri e molta frutta. Evita alcol e caffeina.</li>
                                    <li class="mb-1">Usa abiti leggeri, chiari e in fibre naturali (evita i tessuti sintetici). Se assisti una persona malata, verifica che non sia coperta troppo.</li>
                                </ul>

                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!-- NEVE -->
            <section id="neve"
                     class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-info border-4">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle me-3" style="background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0 !important;">
                            <i class="bi bi-snowflake fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-dark text-uppercase mb-1" style="background-color: #0dcaf0;">Rischio Invernale</span>
                            <h2 class="fw-bold m-0 text-dark">Neve e Gelo</h2>
                        </div>
                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-dark mb-3 h5">
                                    <i class="bi bi-calendar-plus me-2 text-secondary"></i>PRIMA della nevicata
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Informati costantemente sull’evoluzione della situazione meteo tramite i canali ufficiali.</li>
                                    <li class="mb-2">Procurati l’attrezzatura necessaria contro neve e gelo (pala, scorte di sale stradale) e verificane lo stato.</li>
                                    <li class="mb-2">Monta pneumatici invernali o porta a bordo catene da neve (preferibilmente a montaggio rapido), facendo prima qualche prova di montaggio.</li>
                                    <li class="mb-2"><strong>Controlli sull'auto:</strong> Verifica il liquido antigelo nel radiatore, lo stato della batteria e l’efficienza delle spazzole tergicristallo.</li>
                                    <li>Tieni sempre in auto: cavi per l’accensione forzata, pinze, una torcia elettrica e guanti da lavoro robusti.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3" style="border-top-color: #0dcaf0 !important;">
                                <h3 class="fw-bold text-info mb-3 h5" style="color: #0dcaf0 !important;">
                                    <i class="bi bi-snow3 me-2"></i>DURANTE la nevicata
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Casa e viabilità privata:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Verifica la capacità di carico della copertura del tuo stabile (casa o capannone).</li>
                                    <li class="mb-1">Spala la neve dal tuo accesso privato o dal passo carraio. <strong>Non buttare la neve in strada</strong> per non intralciare i mezzi spazzaneve.</li>
                                    <li class="mb-1">Presta attenzione ai lastroni di neve e ghiaccio che possono staccarsi dai tetti, specie in fase di disgelo.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">Alla guida / In strada:</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1">Se puoi, evita di usare l’auto e lasciala in garage. Non utilizzare mai mezzi a due ruote.</li>
                                    <li class="mb-1">Se devi guidare, libera interamente l’auto dalla neve (non solo i vetri). Accendi le luci per farti vedere.</li>
                                    <li class="mb-1">Metti marce basse, riduci la velocità, aumenta la distanza di sicurezza ed evita frenate o sterzate brusche (usa il freno motore).</li>
                                    <li class="mb-1">In salita procedi con dolcezza senza mai arrestare il veicolo.</li>
                                    <li class="mb-1">Parcheggia l'auto correttamente per non ostacolare i mezzi sgombraneve.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-check-circle-fill me-2"></i>DOPO la nevicata
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Presta massima attenzione al fondo stradale per la possibile formazione di ghiaccio vivo: guida con estrema prudenza.</li>
                                    <li class="mb-2">Se ti sposti a piedi, scegli scarpe con suola scolpita antiscivolo per evitare cadute e muoviti con molta cautela.</li>
                                    <li>Continua a monitorare i canali del Comune per gli aggiornamenti sulla viabilità e la riapertura delle scuole o dei servizi pubblici.</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>

                </div>

            </section>

            <!-- TERREMOTO -->
            <section id="terremoto"
                     class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-danger border-4">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle me-3">
                            <i class="bi bi-house-exclamation fs-2"></i>
                        </div>
                        <div>
                            <span class="badge bg-danger text-uppercase mb-1">Rischio Sismico</span>
                            <h2 class="fw-bold m-0 text-dark">Terremoto</h2>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        
                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-dark mb-3 h5">
                                    <i class="bi bi-calendar2-check-fill me-2 text-secondary"></i>PRIMA del terremoto
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Fatti consigliare da un tecnico su come rinforzare i muri portanti o migliorare i collegamenti fra pareti e solai.</li>
                                    <li class="mb-2">Allontana mobili pesanti da letti o divani.</li>
                                    <li class="mb-2">Fissa alle pareti scaffali, librerie e mobili alti; appendi quadri e specchi con ganci chiusi.</li>
                                    <li class="mb-2">Metti gli oggetti pesanti sui ripiani bassi; su quelli alti fissali con del nastro biadesivo.</li>
                                    <li class="mb-2">In cucina, utilizza un fermo per l’apertura degli sportelli di piatti e bicchieri.</li>
                                    <li class="mb-2">Impara dove sono e come si chiudono i rubinetti di gas, acqua e l’interruttore generale della luce.</li>
                                    <li class="mb-2">Individua i punti sicuri dell’abitazione (vani delle porte, angoli delle pareti, sotto il tavolo o il letto).</li>
                                    <li class="mb-2">Tieni in casa una cassetta di pronto soccorso, una torcia elettrica, una radio a batterie e spiegone a tutti dove sono.</li>
                                    <li class="mb-2">Informati sul Piano di Protezione Civile del tuo Comune: pretendi che ci sia per sapere come comportarti.</li>
                                    <li>Elimina tutte le situazioni che possono rappresentare un pericolo per te o i tuoi familiari.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-house-fill me-2"></i>DURANTE il terremoto
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Se sei al chiuso:</strong> Mettiti sotto una trave, nel vano di una porta o vicino a una parete portante.</li>
                                    <li class="mb-2">Stai attento alle cose che cadendo potrebbero colpirti (intonaco, controsoffitti, vetri, mobili, oggetti).</li>
                                    <li class="mb-2">Fai attenzione all’uso delle scale: spesso sono poco resistenti e possono danneggiarsi.</li>
                                    <li class="mb-2">Assolutamente vietato l’uso dell’ascensore: si può bloccare improvvisamente.</li>
                                    <li class="mb-2"><strong>Se sei all’aperto:</strong> Allontanati da edifici, alberi, lampioni, linee elettriche. Potresti essere colpito da vasi, tegole o calcinacci.</li>
                                    <li>Fai attenzione alle altre conseguenze del sisma: crollo di ponti, frane, perdite di gas e incendi.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-arrow-right-circle-fill me-2"></i>DOPO il terremoto
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Assicurati dello stato di salute delle persone attorno a te e, se necessario, presta i primi soccorsi.</li>
                                    <li class="mb-2">Esci con prudenza, indossando le scarpe: in strada potresti ferirti con vetri rotti o detriti.</li>
                                    <li class="mb-2">Se sei in una zona a rischio maremoto, allontanati immediatamente dalla spiaggia e raggiungi un posto elevato.</li>
                                    <li class="mb-2">Raggiungi le <strong>Aree di Attesa</strong> previste dal Piano di Protezione Civile del tuo Comune.</li>
                                    <li class="mb-2">Limita, per quanto possibile, l’uso del telefono per non intasare le linee di soccorso.</li>
                                    <li>Limita l’uso dell’auto per evitare di intralciare il passaggio dei mezzi di emergenza.</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>

                </div>

            </section>

            <!-- ALLUVIONE -->
            <section id="alluvione"
                     class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-primary border-4">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-water fs-2"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary text-uppercase mb-1">Rischio Idrogeologico</span>
                            <h2 class="fw-bold m-0 text-dark">Alluvione e Allagamenti</h2>
                        </div>
                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-dark mb-3 h5">
                                    <i class="bi bi-shield-check me-2 text-secondary"></i>PRIMA dell'alluvione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Rispetta l’ambiente: segnala al Comune tombini intasati, corsi d’acqua ostruiti o rifiuti ingombranti abbandonati.</li>
                                    <li class="mb-2">Informati sul Piano di emergenza comunale: conosci le aree alluvionabili, le vie di fuga e le zone sicure.</li>
                                    <li class="mb-2">Individua gli strumenti ufficiali (Comune e Regione) utilizzati per diramare l’allerta meteo.</li>
                                    <li class="mb-2">Assicurati che la scuola o il posto di lavoro ricevano le allerte e abbiano un piano specifico di emergenza.</li>
                                    <li class="mb-2">Se in famiglia ci sono persone che necessitano di assistenza, verifica che siano previste misure specifiche nel Piano comunale.</li>
                                    <li class="mb-2">Evita assolutamente di conservare beni di valore in cantina o nei locali seminterrati.</li>
                                    <li class="mb-2">Assicurati che in caso di necessità sia agevole e rapido raggiungere i piani più alti del tuo edificio.</li>
                                    <li>Tieni in casa copia dei documenti, una cassetta di pronto soccorso, una torcia elettrica e una radio a batterie.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-primary border-3">
                                <h3 class="fw-bold text-primary mb-3 h5">
                                    <i class="bi bi-cloud-lightning-rain-fill me-2"></i>DURANTE l'alluvione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Al chiuso:</strong> Non scendere in cantine, seminterrati o garage per mettere al sicuro i beni o l'auto. È estremamente pericoloso.</li>
                                    <li class="mb-2">Se ti trovi a un piano terra o seminterrato, sali immediatamente ai piani superiori dell'edificio.</li>
                                    <li class="mb-2">Non usare l'ascensore: potrebbe bloccarsi a causa di un blackout elettrico.</li>
                                    <li class="mb-2">Chiudi il gas e disattiva l’impianto elettrico. Non toccare interruttori con mani o piedi bagnati.</li>
                                    <li class="mb-2">Non bere l'acqua dal rubinetto: potrebbe risultare contaminata.</li>
                                    <li class="mb-2"><strong>All'aperto:</strong> Allontanati subito dalla zona allagata e raggiungi l'area vicina più elevata (evita pendii o scarpate).</li>
                                    <li class="mb-2">Evita tassativamente l'uso dell'auto e non transitare mai su sottopassi, argini e ponti.</li>
                                    <li>Limita l’uso del cellulare per non intasare le linee e facilitare i soccorsi.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-check-circle-fill me-2"></i>DOPO l'alluvione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Segui attentamente le indicazioni delle autorità prima di rientrare in casa, spalare fango o svuotare le cantine.</li>
                                    <li class="mb-2">Non transitare lungo strade allagate: anche dove l’acqua si è ritirata il fondo stradale potrebbe essere instabile.</li>
                                    <li class="mb-2">Prima di riattivare il gas e l’impianto elettrico, fai verificare lo stato dei sistemi da un tecnico qualificato.</li>
                                    <li class="mb-2">Prima di utilizzare gli scarichi di casa, informati che le reti fognarie e i pozzi non abbiano subito danni strutturali.</li>
                                    <li>Non mangiare cibi venuti a contatto con l'acqua dell'alluvione e bevi acqua dal rubinetto solo dopo aver verificato l'assenza di ordinanze di divieto.</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>

                </div>

            </section>

            <!-- INCENDIO -->
            <section id="incendio" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-warning-light border-4">

                    <div class="d-flex align-items-center mb-4">

                        <div class="p-3 bg-warning bg-opacity-10 text-dark rounded-circle me-3">
                            <i class="bi bi-fire fs-2"></i>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark text-uppercase mb-1">Rischio Boschivo e Interfaccia</span>
                            <h2 class="fw-bold m-0 text-dark">Incendio</h2>
                        </div>
                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-dark mb-3 h5">
                                    <i class="bi bi-shield-plus me-2 text-secondary"></i>Come evitare un incendio
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Non gettare mozziconi di sigaretta o fiammiferi ancora accesi: possono incendiare l'erba secca con estrema facilità.</li>
                                    <li class="mb-2">Non accendere mai fuochi nel bosco, al di fuori delle aree appositamente attrezzate.</li>
                                    <li class="mb-2">Non abbandonare mai il fuoco e, prima di andare via, accertati che sia completamente ed effettivamente spento.</li>
                                    <li class="mb-2">Se parcheggi l'auto, assicurati che la marmitta calda non sia a contatto con l'erba secca per evitare l'innesco di fiamme.</li>
                                    <li class="mb-2">Non abbandonare rifiuti nei boschi: le discariche abusive costituiscono un pericoloso combustibile.</li>
                                    <li>Non bruciare mai stoppie, paglia o residui agricoli senza le dovute e obbligatorie misure di sicurezza.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">
                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-exclamation-octagon-fill me-2"></i>Se avvisti un incendio
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Telefona immediatamente al <strong>112</strong> o al <strong>1515</strong> per dare l’allarme. Non pensare che altri l'abbiano già fatto.</li>
                                    <li class="mb-2">Fornisci alle autorità indicazioni precise e necessarie per localizzare tempestivamente l'incendio.</li>
                                    <li class="mb-2">Cerca subito una via di fuga sicura, muovendoti verso una strada asfaltata o un corso d'acqua.</li>
                                    <li class="mb-2">Non fermarti mai in luoghi verso i quali soffia il vento: rischieresti di rimanere imprigionato senza vie di uscita.</li>
                                    <li class="mb-2">Se il fumo circonda la tua abitazione, sigilla porte e finestre con panni bagnati e stacca le bombole del gas esterne.</li>
                                    <li>L'incendio non è uno spettacolo: non sostare lungo le strade per guardare, intralceresti gravemente i mezzi di soccorso.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-life-preserver me-2"></i>In caso di pericolo vicino
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Se l'incendio è vicino, cerca una via di fuga sicura mantenendoti sempre <strong>controvento</strong>.</li>
                                    <li class="mb-2">Se possibile, cammina dove la vegetazione è già bruciata per ridurre il rischio di fiamme improvvise.</li>
                                    <li class="mb-2">In assenza di vie di fuga, stenditi a terra in un luogo privo di vegetazione incendiabile (terra nuda o roccia).</li>
                                    <li>Se non hai altra scelta, cerca di attraversare il fronte del fuoco dove è meno intenso, per passare dalla parte già bruciata e sicura.</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>

                </div>

            </section>

            <!-- VULCANICO -->
            <section id="vulcanico"
                     class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4"
                     style="border-left-color: #6f42c1 !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-purple bg-opacity-10 rounded-circle me-3" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                            <i class="bi bi-triangle-half fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #6f42c1; color: #fff;">Rischio Vulcanico</span>
                            <h2 class="fw-bold m-0 text-dark">Eruzione Vulcanica</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-fire me-2"></i>Colate Laviche e Piroclastiche
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Colate di lava:</strong> Non avvicinarci mai a una colata attiva, anche se defluisce regolarmente. Sono caldissime, sprigionano gas e causano esplosioni o rotolamento di massi incandescenti.</li>
                                    <li class="mb-2">Anche dopo la fine dell’eruzione, non camminare sulla superficie della lava: le colate trattengono il calore all'interno per anni.</li>
                                    <li><strong>Colate piroclastiche:</strong> Preparati all'evacuazione immediata. L’unica difesa da questi flussi di gas e materiale ad altissima temperatura è l'allontanamento preventivo dall'area.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">
                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Caduta di Bombe Vulcaniche
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Informati se la tua zona è soggetta alla caduta di materiali grossolani: in questo caso specifico, gli edifici non costituiscono un rifugio valido o sicuro.</li>
                                    <li class="mb-2">Prendi dettagliata visione del Piano di emergenza del tuo Comune e preparati a seguire le vie di fuga.</li>
                                    <li>L’allontanamento preventivo dall’area interessata prima dell'inizio dei fenomeni è l’unica forma di difesa possibile.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-secondary mb-3 h5">
                                    <i class="bi bi-cloud-haze2-fill me-2"></i>Gestione delle Ceneri Vulcaniche
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Durante la caduta (e nei giorni ventosi successivi), rimani in casa con le finestre chiuse. Se esci, indossa occhiali antipolvere e una mascherina protettiva.</li>
                                    <li class="mb-2">Se la cenere entra negli occhi, non strofinarli; lavali abbondantemente con acqua corrente.</li>
                                    <li class="mb-2">Rimuovi la cenere dai tetti (utilizzando imbracature e ponteggi di sicurezza) e dagli ambienti privati bagnando prima la superficie per non farla alzare.</li>
                                    <li class="mb-2">Non disperdere la cenere in strada: raccoglila in sacchetti e portala nei punti di raccolta comunali.</li>
                                    <li class="mb-2">Guida con estrema prudenza sulle strade coperte di cenere ed evita l'uso di motocicli.</li>
                                    <li>Lava accuratamente frutta e verdura prima di consumarle. Tieni gli animali in casa e dai al bestiame foraggio pulito.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-primary border-3">
                                <h3 class="fw-bold text-primary mb-3 h5">
                                    <i class="bi bi-moisture me-2"></i>Emissioni di Gas e Colate di Fango
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Emissioni gassose:</strong> Evita di sostare, campeggiare o inoltrarti in aree sotterranee o depressioni vicine alle zone vulcaniche.</li>
                                    <li class="mb-2">Non pensare di essere al sicuro stando lontano dal cratere principale: i gas tossici o asfissianti possono sprigionarsi anche dal terreno a grande distanza.</li>
                                    <li class="mb-2"><strong>Colate di fango (Lahar):</strong> Le ceneri fini accumulate al suolo possono combinarsi con la pioggia e innescare pericolose colate di fango lungo i corsi d’acqua.</li>
                                    <li>Segui il Piano di Protezione Civile, allontanati immediatamente dai letti dei fiumi e raggiungi le aree di attesa sicure.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--FRANA-->
            <section id="frana" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #a0522d !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(160, 82, 45, 0.1); color: #a0522d;">
                            <i class="bi bi-layers-half fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #a0522d; color: #fff;">Rischio Idrogeologico</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Frana</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3">
                                <h3 class="fw-bold text-info mb-3 h5">
                                    <i class="bi bi-shield-check me-2"></i>PRIMA della frana
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>La sicurezza prima di tutto:</strong> Ricorda che in caso di frana non ci sono case o muri che possano arrestarla. Soltanto un luogo più elevato ti può dare sicurezza.</li>
                                    <li class="mb-2">Evita di transitare nei pressi di aree già sottoposte a movimenti del terreno, in particolar modo durante temporali o piogge violente.</li>
                                    <li class="mb-2">Contatta il tuo Comune per sapere se nel territorio comunale sono presenti aree a rischio di frana.</li>
                                    <li class="mb-2">Osserva il terreno nelle tue vicinanze per rilevare la presenza di piccole frane o di piccole variazioni del terreno.</li>
                                    <li>Allontanati dai corsi d’acqua o dai solchi di torrenti nei quali vi può essere scorrimento di colate di fango.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>DURANTE la frana
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Allontanati il più velocemente possibile, cercando di raggiungere un posto più elevato o stabile.</li>
                                    <li class="mb-2">Se non è possibile scappare, rannicchiati il più possibile su te stesso e proteggi la tua testa.</li>
                                    <li class="mb-2">Guarda sempre verso la frana facendo attenzione a pietre o ad altri oggetti che ti potrebbero colpire.</li>
                                    <li class="mb-2">Non soffermarti sotto pali o tralicci.</li>
                                    <li class="mb-2">Non avvicinarci al ciglio di una frana.</li>
                                    <li>Se sei in strada cerca di segnalare il pericolo alle altre automobili che potrebbero sopraggiungere.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-heart-pulse-fill me-2"></i>DOPO la frana: Soccorso e Assistenza
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Controlla se ci sono feriti o persone intrappolate nell’area in frana, senza entrarvi direttamente, e segnala la loro presenza ai soccorritori.</li>
                                    <li class="mb-2">Subito dopo allontanati dall’area in frana.</li>
                                    <li>Verifica se vi sono persone che necessitano assistenza, in particolar modo bambini, anziani e persone disabili.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-secondary mb-3 h5">
                                    <i class="bi bi-wrench-adjacent me-2"></i>DOPO la frana: Utenze e Impianti
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Segnala eventuali interruzioni di acqua, luce e gas alle autorità competenti.</li>
                                    <li class="mb-2">Nel caso di perdita di gas da un palazzo, <strong>non entrare</strong> per chiudere il rubinetto.</li>
                                    <li class="mb-2">Verifica se vi è un interruttore generale fuori dall’abitazione ed in questo caso chiudilo.</li>
                                    <li>Segnala tempestivamente questa notizia ai Vigili del Fuoco o ad altro personale specializzato.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--VALANGA-->
            <section id="valanga" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #17a2b8 !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                            <i class="bi bi-snow2 fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #17a2b8; color: #fff;">Rischio Neve e Valanghe</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Valanga</h2>
                        </div>
                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3">
                                <h3 class="fw-bold text-info mb-3 h5">
                                    <i class="bi bi-shield-check me-2"></i>PRIMA della valanga
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Evita le aree a rischio nei periodi in cui si prevedono i distacchi, frequenti all’inizio della primavera.</li>
                                    <li class="mb-2">Informati dalla Società di gestione degli impianti delle condizioni di innevamento e dei versanti e consulta frequentemente i <strong>bollettini nivometeorologici</strong>.</li>
                                    <li class="mb-2"><strong>Non rimanere mai da solo</strong> e rispetta la segnaletica e le indicazioni presenti sulle piste riguardo le condizioni dei percorsi sci-alpinistici e di discesa fuori pista.</li>
                                    <li class="mb-2">Evita di passare attraverso versanti a forte pendenza con notevole innevamento (specialmente nelle ore più calde) e l’attraversamento di zone sospette come pendii aperti, canaloni e zone sottovento.</li>
                                    <li class="mb-2">Quando ti muovi utilizza i punti più sicuri del terreno, come rocce e tratti pianeggianti.</li>
                                    <li>Se sei in una comitiva, equipaggiati sempre dei dispositivi di autosoccorso obbligatori: <strong>ARVA</strong> (apparecchio di ricerca in valanga), <strong>sonda leggera</strong> e <strong>pala</strong>.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>DURANTE la valanga
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-3"><strong>Cerca una via di fuga laterale:</strong> Ricorda che la neve tende ad accumularsi nella zona centrale e quindi potrebbe essere più facile trovare una via di scampo verso i margini.</li>
                                    <li class="mb-3"><strong>Proteggi le vie respiratorie:</strong> Cerca di mantenere uno spazio libero davanti al petto (creando una sacca d'aria) per permetterti di respirare sotto la neve.</li>
                                    <li><strong>Nuota nella neve:</strong> Muovi energicamente braccia e gambe, come se nuotassi, per cercare di avvicinarti al margine della valanga e fare il possibile per rimanere in superficie.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--CRISI IDRICA-->
            <section id="crisi-idrica" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #0d6efd !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                            <i class="bi bi-droplet-half fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #0d6efd; color: #fff;">Rischio Ambientale ed Energetico</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Crisi Idrica</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-house-heart-fill me-2"></i>Risparmio e Manutenzione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Dispositivi frangigetto:</strong> Rifornisci i rubinetti di aeratori che consentano di risparmiare l'acqua miscelandola con l'aria.</li>
                                    <li class="mb-2"><strong>Verifica perdite:</strong> Se, con tutti i rubinetti chiusi, il contatore gira, chiama una ditta specializzata per controllare guasti nelle tubature o nei sanitari.</li>
                                    <li class="mb-2">Non lasciar scorrere inutilmente l'acqua del rubinetto e preferisci sempre la <strong>doccia</strong> al bagno.</li>
                                    <li class="mb-2">Nei servizi igienici utilizza i serbatoi a più portate (doppio pulsante dello scarico).</li>
                                    <li>Quando ti assenti per lunghi periodi da casa, ricordati di <strong>chiudere il rubinetto centrale</strong> dell’acqua.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3">
                                <h3 class="fw-bold text-info mb-3 h5">
                                    <i class="bi bi-recycle me-2"></i>Abitudini e Consumi Consapevoli
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Non utilizzare l’acqua corrente per lavare frutta e verdura: lasciale a bagno con un pizzico di bicarbonato e, quando possibile, <strong>riutilizza l’acqua usata</strong> per innaffiare.</li>
                                    <li class="mb-2"><strong>Elettrodomestici:</strong> Utilizza lavatrici o lavastoviglie possibilmente nelle ore notturne, solo a pieno carico, e attiva il programma economizzatore se il carico è ridotto.</li>
                                    <li>Non utilizzare mai l’acqua potabile per attività non essenziali, come ad esempio il lavaggio dell’automobile.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">
                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-hourglass-split me-2"></i>PRIMA della Sospensione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Fai una <strong>scorta minima di acqua</strong> per l'uso igienico e la cucina.</li>
                                    <li class="mb-2">Rifornisciti temporaneamente di piatti, posate e bicchieri monouso, oltre a ovatta e alcool denaturato per l'igiene rapida.</li>
                                    <li><strong>Spegni lo scaldabagno elettrico</strong> prima dell'interruzione per evitare gravi danni alle resistenze di riscaldamento una volta svuotato.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-exclamation-octagon-fill me-2"></i>AL RIPRISTINO dell'Acqua
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Riaccendi lo scaldabagno elettrico solo dopo che l'erogazione è tornata stabile e l'impianto si è riempito.</li>
                                    <li class="mb-2"><strong>Evita l'uso immediato</strong> di lavatrice, lavastoviglie e scaldabagno fino al ritorno della totale normalità.</li>
                                    <li>Nelle prime fasi del ripristino potrebbero infatti verificarsi transitori fenomeni di <strong>acqua scura o torbida</strong> che rischiano di macchiare i tessuti o danneggiare i filtri.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--CICLONE-->
            <section id="ciclone" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #198754 !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                            <i class="bi bi-hurricane fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #198754; color: #fff;">Rischio Meteo Intenso</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Ciclone e Venti Forti</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">
                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-cloud-lightning-rain-fill me-2"></i>Cosa fare all'aperto
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Allontanati dalla costa:</strong> Spostati rapidamente verso zone più elevate e trova immediato riparo in un edificio in muratura.</li>
                                    <li class="mb-2"><strong>In automobile:</strong> Presta attenzione alle raffiche che potrebbero farti sbandare. Rallenta e raggiungi il luogo sicuro più vicino. Se un cavo elettrico colpisce la vettura, <strong>rimani all'interno</strong> e attendi i soccorsi.</li>
                                    <li class="mb-2">Evita assolutamente di sostare sotto ponti, cavalcavia, impalcature, lampioni o strutture che potrebbero crollare.</li>
                                    <li>Limita al minimo l’uso del cellulare per tenere libere le linee e facilitare i soccorsi.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-house-dash-fill me-2"></i>In Casa: Protezione e Interni
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Non uscire assolutamente:</strong> Evita di farlo anche solo per mettere in sicurezza beni, oggetti esterni o veicoli.</li>
                                    <li class="mb-2">Chiudi accuratamente porte, finestre e imposte. Se possibile, applica ulteriori protezioni o rinforzi a vetrate e finestre.</li>
                                    <li class="mb-2"><strong>Trova un riparo sicuro:</strong> Riparati nella stanza più interna della casa o in un corridoio, stando alla massima distanza possibile da infissi e vetri.</li>
                                    <li>Fai entrare immediatamente in casa gli animali domestici.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-primary border-3">
                                <h3 class="fw-bold text-primary mb-3 h5">
                                    <i class="bi bi-building me-2"></i>In Casa: Piani e Impianti
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Spostati ai piani intermedi:</strong> Abbandona i piani seminterrati e i piani terra per portarti ai piani alti.</li>
                                    <li class="mb-2"><strong>Attenzione al tetto:</strong> Evita però di ripararti all’ultimo piano, poiché le forti raffiche di vento potrebbero scoperchiare o danneggiare le coperture.</li>
                                    <li class="mb-2">Se vivi in una struttura mobile (roulotte, prefabbricato, campeggio), abbandonala e cerca tempestivamente rifugio in un edificio sicuro.</li>
                                    <li>Chiudi i rubinetti del gas e disattiva il quadro elettrico generale, specialmente se gli impianti principali si trovano ai piani bassi soggetti ad allagamento.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-secondary mb-3 h5">
                                    <i class="bi bi-box-seam-fill me-2"></i>Kit di Emergenza e Attesa
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Tieni sempre a portata di mano:</strong> Documenti di identità, farmaci salvavita indispensabili, torcia elettrica, radio a batterie con batterie di ricambio, telefono cellulare e una scorta di acqua in bottiglia.</li>
                                    <li class="mb-2">Usa il cellulare solo per reali emergenze per non sovraccaricare le reti.</li>
                                    <li><strong>Falso senso di sicurezza:</strong> Anche se il fenomeno ti sembra in attenuazione (potrebbe trattarsi del passaggio dell'occhio del ciclone), non uscire assolutamente di casa. Attendi sempre le indicazioni ufficiali delle autorità.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--MAREMOTO-->
            <section id="maremoto" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #0a58ca !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(10, 88, 202, 0.1); color: #0a58ca;">
                            <i class="bi bi-tsunami fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #0a58ca; color: #fff;">Rischio Costiero</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Maremoto (Tsunami)</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3">
                                <h3 class="fw-bold text-info mb-3 h5">
                                    <i class="bi bi-shield-check me-2"></i>PRIMA di un marmoto
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Informati sul Piano Comunale:</strong> Chiedi alla Protezione Civile locale notizie su zone pericolose, vie e tempi di evacuazione, segnaletica e aree di attesa.</li>
                                    <li class="mb-2">Verifica la sicurezza della tua casa e assicurati che la scuola o il luogo di lavoro abbiano un piano di evacuazione con esercitazioni periodiche.</li>
                                    <li class="mb-2">Pianifica l'emergenza in famiglia stabilendo come raggiungere i punti sicuri.</li>
                                    <li>Tieni sempre pronta una cassetta di <strong>pronto soccorso</strong> e scorte di cibo e acqua.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>DURANTE un maremoto
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Sulla costa:</strong> Allontanati subito dalla spiaggia e raggiungi rapidamente l'area più elevata (collina o piani alti di un edificio robusto).</li>
                                    <li class="mb-2">Avverti le persone intorno a te e <strong>corri a piedi</strong> seguendo la via di fuga più rapida. <strong>Non usare l'automobile</strong> per evitare ingorghi.</li>
                                    <li class="mb-2"><strong>In barca:</strong> Se sei al largo e hai notizia di un sisma, rimani o portati in acque profonde.</li>
                                    <li><strong>In porto:</strong> Abbandona immediatamente l'imbarcazione e mettiti al sicuro in un posto elevato a terra.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-geo-alt-fill me-2"></i>DOPO un maremoto: Sicurezza e Rientro
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Rimani in quota:</strong> Non tornare verso la costa e scoraggia chi vuole farlo. Alla prima onda potrebbero seguirne altre successive e ancora più pericolose.</li>
                                    <li class="mb-2">Presta i primi soccorsi a chi ti circonda se sei in grado e usa il telefono solo per reali necessità per non intasare le linee.</li>
                                    <li class="mb-2">Rientra nella tua abitazione solo ed esclusivamente dopo l'<strong>autorizzazione ufficiale</strong> delle autorità.</li>
                                    <li>Ricorda che il maremoto può essere generato da sismi o vulcani: tieniti informato anche sulle relative norme di comportamento.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-secondary mb-3 h5">
                                    <i class="bi bi-exclamation-octagon-fill me-2"></i>DOPO un maremoto: Acqua e Alimenti
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-3"><strong>Rischio contaminazione:</strong> Non mangiare assolutamente cibi che siano venuti a contatto con l'acqua di inondazione o con i detriti trasportati dal maremoto.</li>
                                    <li class="mb-3"><strong>Acqua non potabile:</strong> Evita di bere l'acqua del rubinetto fino a quando le autorità non avranno confermato la totale integrità e salubrità della rete idrica comunale.</li>
                                    <li>Segui sempre le indicazioni ufficiali diffuse dai canali istituzionali.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--NUCLEARE-->
            <section id="nucleare" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #d39e00 !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(211, 158, 0, 0.1); color: #d39e00;">
                            <i class="bi bi-radioactive fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #d39e00; color: #fff;">Rischio Tecnologico</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Nucleare e Radiologico</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-box-arrow-right me-2"></i>In caso di Evacuazione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Prima di uscire:</strong> Chiudi accuratamente tutte le porte e le finestre dell'edificio.</li>
                                    <li class="mb-2"><strong>In automobile:</strong> Mantieni i finestrini della vettura completamente chiusi e tieni rigorosamente <strong>spento l’impianto di aerazione</strong> o climatizzazione.</li>
                                    <li>Ascolta costantemente la radio per ricevere informazioni ufficiali sulle vie di evacuazione stabilite e sulle indicazioni emesse dalle autorità di salute pubblica.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">
                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-house-lock-fill me-2"></i>Riparo al Chiuso: Ambiente
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Resta all'interno degli ambienti chiusi. Chiudi porte e finestre e <strong>spegni i condizionatori</strong> o qualsiasi sistema di presa d’aria esterna.</li>
                                    <li class="mb-2">Spostati, se disponibili, in <strong>ambienti seminterrati o interrati</strong> per aumentare la schermatura dalle radiazioni.</li>
                                    <li class="mb-2">Porta immediatamente gli animali domestici all’interno dell’edificio.</li>
                                    <li><strong>Non usare l’auto:</strong> Rischieresti un’esposizione maggiore e bloccheresti il transito dei mezzi di soccorso.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3">
                                <h3 class="fw-bold text-info mb-3 h5">
                                    <i class="bi bi-person-clean me-2"></i>Riparo al Chiuso: Decontaminazione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Cambio d'abito:</strong> Cambiati d’abito prima di entrare nel locale principale. Riponi i vestiti e le calzature usate all'esterno in sacchi di plastica sigillati, lontano dai luoghi frequentati.</li>
                                    <li class="mb-2">Fai una doccia accurata utilizzando acqua calda e sapone liquido (evita di strofinare energicamente la pelle).</li>
                                    <li><strong>In caso di pioggia:</strong> Riparati immediatamente e lascia fuori dall’abitazione l’ombrello, l’impermeabile e tutti gli indumenti bagnati.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-secondary mb-3 h5">
                                    <i class="bi bi-telephone-minus-fill me-2"></i>Riparo al Chiuso: Risorse e Comunicazioni
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Rimani costantemente aggiornato seguendo le indicazioni trasmesse via radio o televisione.</li>
                                    <li class="mb-2"><strong>Linee telefoniche:</strong> Riduci al minimo l’uso del cellulare al fine di non sovraccaricare la rete e garantire le linee libere per i soccorsi.</li>
                                    <li><strong>Uso dell'acqua:</strong> Puoi utilizzare l’acqua del rubinetto per le esigenze primarie, a meno che non sia espressamente sconsigliato o vietato dalle autorità sanitarie.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!--INCIDENTE INDUSTRIALE-->
            <section id="industriale" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #6c757d !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(108, 117, 125, 0.1); color: #6c757d;">
                            <i class="bi bi-factory fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #6c757d; color: #fff;">Rischio Tecnologico</span>
                            <h2 class="fw-bold m-0 text-dark">Rischio Industriale</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-info border-3">
                                <h3 class="fw-bold text-info mb-3 h5">
                                    <i class="bi bi-info-circle-fill me-2"></i>Informazione e Prevenzione
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Verifica la tua zona:</strong> Se abiti nei pressi di stabilimenti industriali, informati presso il tuo Comune per sapere se sono inseriti nell'elenco degli impianti a rischio soggetti a piano di emergenza.</li>
                                    <li><strong>Schede informative:</strong> Segui scrupolosamente le indicazioni contenute nelle schede di informazione alla popolazione distribuite dal Sindaco per conoscere le misure di sicurezza specifiche e le norme di comportamento.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-house-lock-fill me-2"></i>Isolamento al Chiuso
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2"><strong>Trova riparo:</strong> Rifugiati immediatamente in un luogo chiuso per ridurre al minimo l'esposizione alle sostanze tossiche eventualmente emesse dallo stabilimento.</li>
                                    <li class="mb-2">Chiudi accuratamente porte e finestre, avendo cura di <strong>proteggere gli spiragli e le fessure con tessuti bagnati</strong>.</li>
                                    <li><strong>Blocca il ricircolo dell'aria:</strong> Spegni tassativamente condizionatori, estrattori e aeratori per evitare qualunque interscambio di aria con l’esterno.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-warning-light border-3">
                                <h3 class="fw-bold text-warning-on-light mb-3 h5">
                                    <i class="bi bi-megaphone-fill me-2"></i>Gestione dell'Emergenza
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Presta la massima attenzione alle informazioni diffuse dalle autorità tramite impianti megafonici civici, sirene o altri mezzi di comunicazione straordinari.</li>
                                    <li><strong>Tieniti aggiornato:</strong> Fino al totale cessato allarme, segui costantemente i canali ufficiali di radio e TV per ricevere aggiornamenti in tempo reale sulla situazione in atto e sulle disposizioni da seguire.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-wind me-2"></i>Al Cessato Allarme
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-3"><strong>Ricambio d'aria:</strong> Solo ed esclusivamente ad allarme ufficialmente cessato, apri completamente porte e finestre per <strong>aerare abbondantemente tutti gli ambienti</strong>.</li>
                                    <li>Resta comunque sintonizzato sui media locali per monitorare l'evoluzione della fase di post-emergenza e conoscere eventuali indicazioni sanitarie integrative.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </section>
            
            <!--BLACKOUT-->
            <section id="blackout" class="risk-section py-4 scroll-margin">

                <div class="p-4 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #fd7e14 !important;">

                    <div class="d-flex align-items-center mb-4">
                        <div class="p-3 bg-opacity-10 rounded-circle me-3" style="background-color: rgba(253, 126, 20, 0.1); color: #fd7e14;">
                            <i class="bi bi-lightning-charge-fill fs-2"></i>
                        </div>
                        <div>
                            <span class="badge text-uppercase mb-1" style="background-color: #fd7e14; color: #fff;">Rischio Tecnologico</span>
                            <h2 class="fw-bold m-0 text-dark">Blackout</h2>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">

                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-secondary border-3">
                                <h3 class="fw-bold text-dark mb-3 h5">
                                    <i class="bi bi-calendar2-check-fill me-2 text-secondary"></i>PRIMA del blackout
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Perché si interrompe la corrente:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Il vento forte o la neve pesante possono rompere i cavi elettrici.</li>
                                    <li class="mb-1">Un fulmine o un incendio possono danneggiare la rete.</li>
                                    <li class="mb-1">Troppe persone che usano la corrente nello stesso momento.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">Cosa preparare in casa:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Torcia elettrica con batterie di ricambio e un powerbank sempre carico.</li>
                                    <li class="mb-1">Acqua in bottiglia e cibo che non ha bisogno di frigo o cottura.</li>
                                    <li class="mb-1">Una radio a batterie per ascoltare le notizie ufficiali.</li>
                                    <li class="mb-1">Farmaci importanti: chiedi al tuo medico quanto tempo possono restare fuori dal frigo.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">Numeri da salvare:</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1"><strong><?= htmlspecialchars($site['phones']['emergency']) ?></strong> — numero unico europeo di emergenza.</li>
                                    <li><strong><?= htmlspecialchars($site['phones']['societa_elettrica']) ?></strong> — numero per segnalare guasti alla rete elettrica.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-3" style="border-top-color: #fd7e14 !important;">
                                <h3 class="fw-bold mb-3 h5" style="color: #fd7e14 !important;">
                                    <i class="bi bi-plug-fill me-2"></i>DURANTE il blackout
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Nelle prime ore:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Guarda il quadro elettrico: se un tasto è abbassato, rialzalo.</li>
                                    <li class="mb-1">Stacca computer, TV e console: al ritorno della corrente un picco può romperli.</li>
                                    <li class="mb-1">Lascia accesa una lampada per accorgerti subito quando la corrente torna.</li>
                                    <li class="mb-1">Non aprire spesso il frigo: chiuso tiene il freddo 4-6 ore, il congelatore 24-48 ore.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">Cosa non fare mai:</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1">Non usare stufe a gas in casa: producono monossido di carbonio, un gas pericoloso che non si vede né si sente.</li>
                                    <li class="mb-1">Non usare un generatore elettrico dentro casa: usalo sempre all'aperto.</li>
                                    <li>Non lasciare mai candele accese senza sorveglianza.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-top border-success border-3">
                                <h3 class="fw-bold text-success mb-3 h5">
                                    <i class="bi bi-check-circle-fill me-2"></i>QUANDO la corrente torna
                                </h3>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-2">Controlla il cibo in frigo e congelatore: se si è scongelato, buttalo via (specie carne, pesce e formaggi freschi).</li>
                                    <li class="mb-2">Riaccendi i dispositivi elettronici uno alla volta, non tutti insieme.</li>
                                    <li class="mb-2">Controlla il quadro elettrico: se si abbassa di nuovo, chiama un elettricista.</li>
                                    <li>Rimetti l'orario su orologi e sveglie.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="row g-4">

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-danger border-3">
                                <h3 class="fw-bold text-danger mb-3 h5">
                                    <i class="bi bi-hourglass-split me-2"></i>Se il blackout dura molte ore
                                </h3>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Ascolta la radio a batterie per le comunicazioni ufficiali.</li>
                                    <li class="mb-1">Usa il telefono solo quando serve, per risparmiare la batteria.</li>
                                    <li class="mb-1">Controlla i vicini anziani o malati: senza ascensore possono restare bloccati in casa. Porta loro acqua, cibo e farmaci.</li>
                                    <li>Se fa molto caldo o molto freddo, vai in un luogo pubblico (centro commerciale, biblioteca) o da parenti con la corrente.</li>
                                </ul>
                                <p class="small text-muted fw-semibold mb-2">Se qualcuno è bloccato in ascensore:</p>
                                <ul class="small ps-3 mb-0" style="text-align: left;">
                                    <li class="mb-1">Chiama il <strong>112</strong>.</li>
                                    <li class="mb-1">Parla con la persona bloccata e dille di stare calma: gli ascensori moderni hanno aria per molte ore.</li>
                                    <li>Non cercare di aprire da solo le porte dell'ascensore: è pericoloso.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="p-3 bg-light rounded h-100 border-top border-primary border-3">
                                <h3 class="fw-bold text-primary mb-3 h5">
                                    <i class="bi bi-people-fill me-2"></i>Quando è una vera emergenza
                                </h3>
                                <p class="small text-muted fw-semibold mb-2">Un blackout che dura molte ore attiva la Protezione Civile:</p>
                                <ul class="small ps-3 mb-3" style="text-align: left;">
                                    <li class="mb-1">Il Sindaco apre il <strong>COC</strong> (Centro Operativo Comunale), che coordina i soccorsi.</li>
                                    <li class="mb-1">I <strong>Vigili del Fuoco</strong> aiutano le persone in pericolo, ad esempio bloccate negli ascensori.</li>
                                    <li class="mb-1">Il Gruppo Comunale Volontari di Protezione Civile aiuta le persone fragili.</li>
                                    <li>L'<strong>ASL</strong> aiuta chi usa apparecchi medici a casa, come ossigeno o dialisi.</li>
                                </ul>
                                <p class="small mb-0"><strong>Tu puoi aiutare</strong> i vicini anziani o malati, e tenere informate le autorità se vedi una vera emergenza.</p>
                            </div>
                        </div>

                    </div>

                </div>

            </section>
        </div>
    </div>
</div>

<?php if (empty($is_print_view)): ?>

<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-modal="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h4 class="modal-title fw-bold" id="printModalLabel">
          <i class="bi bi-printer me-2"></i>Esporta in PDF
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <p class="text-muted small mb-0">Seleziona le sezioni da includere:</p>
          <button type="button" class="btn btn-sm btn-link p-0" id="printToggleAll">Deseleziona tutto</button>
        </div>
        <div id="printSectionList" class="d-flex flex-column gap-2" style="max-height: 320px; overflow-y:auto;"></div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-primary fw-bold" id="printExportBtn">
          <i class="bi bi-file-earmark-pdf me-1"></i>Esporta PDF
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<!-- SCRIPT -->
<script>

document.addEventListener("DOMContentLoaded", function () {

    const sections = document.querySelectorAll(".risk-section");
    const buttons = document.querySelectorAll(".risk-btn");
    const details = document.querySelector(".risk-menu-inner details");
    const menu = document.querySelector(".risk-menu");

    function injectRiskMenuStyles() {
        const css = `
            .risk-menu-inner details {
                width: 100%;
                background: rgba(248, 249, 250, 0.95);
                border-radius: 1rem;
                padding: 0.35rem;
                box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08);
            }
            .risk-menu-inner summary {
                list-style: none;
                cursor: pointer;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.5rem 0.75rem;
                border-radius: 1rem;
                background-color: #ffffff;
                transition: background-color 0.2s ease;
            }
            .risk-menu-inner summary:hover {
                background-color: #f1f3f5;
            }
            .risk-menu-inner summary::-webkit-details-marker {
                display: none;
            }
            .risk-menu-inner details[open] summary {
                background-color: #e9ecef;
            }
            .risk-menu-inner .risk-btn {
                margin: 0.2rem 0.15rem;
            }
        `;
        const style = document.createElement("style");
        style.type = "text/css";
        style.appendChild(document.createTextNode(css));
        document.head.appendChild(style);
    }

    function closeDetailsOnScroll() {
        if (!details || !details.open || !menu) {
            return;
        }

        const rect = menu.getBoundingClientRect();
        if (rect.top <= 70) {
            details.open = false;
        }
    }

    function showSection(id) {

        // Nasconde tutte
        sections.forEach(section => {
            section.classList.remove("active");
        });

        // Mostra quella scelta
        const target = document.getElementById(id);

        if (target) {
            target.classList.add("active");
        }

        // Stato pulsanti
        buttons.forEach(btn => {
            btn.classList.remove("btn-secondary");
            btn.classList.add("btn-outline-secondary");
        });

        const activeBtn = document.querySelector(`[data-target="${id}"]`);

        if (activeBtn) {
            activeBtn.classList.remove("btn-outline-secondary");
            activeBtn.classList.add("btn-secondary");
        }

        // Scroll top
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    // Default
    injectRiskMenuStyles();
    if (details) {
        details.open = true;
    }
    closeDetailsOnScroll();
    const initialRisk = window.location.hash.slice(1);
    const initialSection = document.getElementById(initialRisk);
    showSection(initialSection && initialSection.classList.contains("risk-section") ? initialRisk : "meteo");

    if (details) {
        details.addEventListener("click", function (event) {
            if (!event.target.closest("summary")) {
                return;
            }

            if (!details.open) {
                requestAnimationFrame(function () {
                    details.open = true;
                });
            }
        });
    }

    window.addEventListener("scroll", closeDetailsOnScroll);

    // Click sui bottoni "Salta a:"
    buttons.forEach(button => {

        button.addEventListener("click", function (e) {

            e.preventDefault();

            const target = this.dataset.target;

            showSection(target);

        });

    });

    // ---- MODALE STAMPA / ESPORTA PDF ----
    const printModalEl = document.getElementById("printModal");

    if (printModalEl) {
        const printSectionList = document.getElementById("printSectionList");
        const printToggleAllBtn = document.getElementById("printToggleAll");
        const printExportBtn = document.getElementById("printExportBtn");

        function updateToggleAllLabel() {
            const checkboxes = printSectionList.querySelectorAll(".print-section-checkbox");
            const tutteSelezionate = Array.from(checkboxes).every(cb => cb.checked);
            printToggleAllBtn.textContent = tutteSelezionate ? "Deseleziona tutto" : "Seleziona tutto";
        }

        function buildPrintCheckboxes() {
            printSectionList.innerHTML = "";

            sections.forEach(function (section) {
                const titleEl = section.querySelector("h2");
                const title = titleEl ? titleEl.textContent.trim() : section.id;

                const wrapper = document.createElement("div");
                wrapper.className = "form-check";

                const input = document.createElement("input");
                input.type = "checkbox";
                input.className = "form-check-input print-section-checkbox";
                input.id = "printCheck-" + section.id;
                input.value = section.id;
                input.checked = true;

                input.addEventListener("change", updateToggleAllLabel);

                const label = document.createElement("label");
                label.className = "form-check-label small";
                label.setAttribute("for", input.id);
                label.textContent = title;

                wrapper.appendChild(input);
                wrapper.appendChild(label);
                printSectionList.appendChild(wrapper);
            });

            updateToggleAllLabel();
        }

        // Rigenera le checkbox ogni volta che il modale si apre
        printModalEl.addEventListener("show.bs.modal", buildPrintCheckboxes);

        // Seleziona / Deseleziona tutto
        if (printToggleAllBtn) {
            printToggleAllBtn.addEventListener("click", function () {
                const checkboxes = printSectionList.querySelectorAll(".print-section-checkbox");
                const tutteSelezionate = Array.from(checkboxes).every(cb => cb.checked);

                checkboxes.forEach(cb => {
                    cb.checked = !tutteSelezionate;
                });

                updateToggleAllLabel();
            });
        }

        // Esporta PDF
        if (printExportBtn) {
            printExportBtn.addEventListener("click", function () {
                const checkboxes = printSectionList.querySelectorAll(".print-section-checkbox:checked");
                const selezionate = Array.from(checkboxes).map(cb => cb.value);

                if (selezionate.length === 0) {
                    alert("Seleziona almeno una sezione da stampare.");
                    return;
                }

                let url = "stampa_rischi.php";
                if (selezionate.length < sections.length) {
                    url += "?sezioni=" + encodeURIComponent(selezionate.join(","));
                }
                // Se sono selezionate tutte le sezioni, nessun parametro = stampa tutto

                window.open(url, "_blank");

                const modalInstance = bootstrap.Modal.getInstance(printModalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        }
    }

});

</script>
