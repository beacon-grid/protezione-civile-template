<link rel="stylesheet" href="../css/rischi-prevenzione.css">
<script src="../js/rischi-prevenzione.js"></script>

<div class="modal fade" id="modalKit" tabindex="-1" role="dialog" aria-labelledby="modalKitTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <!-- Header -->
            <div class="mk-header">
                <h2 class="mk-header-title" id="modalKitTitle">
                    <i class="bi bi-box-seam-fill" aria-hidden="true"></i>
                    Guida ai Kit di Emergenza
                    <span class="mk-header-badge">Protezione Civile</span>
                </h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi finestra modale"></button>
            </div>

            <!-- Body: sidebar + contenuto -->
            <div class="mk-body">

                <!-- Sidebar navigazione -->
                <nav class="mk-sidebar" aria-label="Sezioni guida kit">
                    <div class="mk-sidebar-section">Tipologie</div>
                    <button class="mk-nav-btn active" data-target="panoramica">
                        <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> Panoramica
                    </button>
                    <button class="mk-nav-btn" data-target="evacuazione">
                        <i class="bi bi-backpack2" aria-hidden="true"></i> Zaino evacuazione
                    </button>
                    <button class="mk-nav-btn" data-target="casa">
                        <i class="bi bi-house-door" aria-hidden="true"></i> Kit casa 72h
                    </button>
                    <button class="mk-nav-btn" data-target="auto">
                        <i class="bi bi-car-front" aria-hidden="true"></i> Kit automobile
                    </button>
                    <div class="mk-sidebar-section">Approfondimenti</div>
                    <button class="mk-nav-btn" data-target="acqua">
                        <i class="bi bi-droplet-half" aria-hidden="true"></i> Scorte d'acqua
                    </button>
                    <button class="mk-nav-btn" data-target="privacy">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i> Privacy documenti
                    </button>
                    <button class="mk-nav-btn" data-target="manutenzione">
                        <i class="bi bi-calendar-check" aria-hidden="true"></i> Manutenzione
                    </button>
                </nav>

                <!-- Contenuto sezioni -->
                <div class="mk-content">

                    <!-- Panoramica -->
                    <div class="mk-section active" id="mk-panoramica">
                        <p class="mk-section-title"><i class="bi bi-info-circle" aria-hidden="true"></i> Tre kit per tre situazioni diverse</p>
                        <p class="mk-section-sub">Preparare un kit non significa vivere nella paura. Significa avere l'essenziale pronto se un'emergenza interrompe la normalità. Ogni scenario richiede una risposta diversa.</p>
                        <table class="mk-overview-table">
                            <caption class="visually-hidden">Riepilogo dei tre kit di emergenza: quando usarli, dove tenerli e obiettivo</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Kit</th>
                                    <th scope="col">Quando serve</th>
                                    <th scope="col">Dove tenerlo</th>
                                    <th scope="col">Obiettivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row" style="border-radius: 0;"><span class="mk-kit-label"><i class="bi bi-backpack2-fill text-danger" aria-hidden="true"></i> Evacuazione</span></th>
                                    <td>Devi uscire in pochi minuti.</td>
                                    <td>Vicino all'uscita principale.</td>
                                    <td>Solo l'essenziale salvavita.</td>
                                </tr>
                                <tr>
                                    <th scope="row" style="border-radius: 0;"><span class="mk-kit-label"><i class="bi bi-house-door-fill text-primary" aria-hidden="true"></i> Casa 72h</span></th>
                                    <td>Resti in casa senza utenze.</td>
                                    <td>Contenitore protetto e accessibile.</td>
                                    <td>Autonomia per i primi 3 giorni.</td>
                                </tr>
                                <tr>
                                    <th scope="row" style="border-radius: 0;"><span class="mk-kit-label"><i class="bi bi-car-front-fill text-secondary" aria-hidden="true"></i> Automobile</span></th>
                                    <td>Bloccato in strada.</td>
                                    <td>Bagagliaio del veicolo.</td>
                                    <td>Luce, calore, primo soccorso.</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mk-overview-mobile" style="display:none;">
                            <div class="mk-overview-mobile-card">
                                <strong><i class="bi bi-backpack2-fill text-danger me-1" aria-hidden="true"></i> Zaino evacuazione</strong>
                                <span><b>Quando:</b> Devi uscire in pochi minuti.</span>
                                <span><b>Dove:</b> Vicino all'uscita principale.</span>
                                <span><b>Obiettivo:</b> Solo l'essenziale salvavita.</span>
                            </div>
                            <div class="mk-overview-mobile-card">
                                <strong><i class="bi bi-house-door-fill text-primary me-1" aria-hidden="true"></i> Kit casa 72h</strong>
                                <span><b>Quando:</b> Resti in casa senza utenze.</span>
                                <span><b>Dove:</b> Contenitore protetto e accessibile.</span>
                                <span><b>Obiettivo:</b> Autonomia per i primi 3 giorni.</span>
                            </div>
                            <div class="mk-overview-mobile-card">
                                <strong><i class="bi bi-car-front-fill text-secondary me-1" aria-hidden="true"></i> Kit automobile</strong>
                                <span><b>Quando:</b> Bloccato in strada.</span>
                                <span><b>Dove:</b> Bagagliaio del veicolo.</span>
                                <span><b>Obiettivo:</b> Luce, calore, primo soccorso.</span>
                            </div>
                        </div>

                        <div class="mk-alert mk-alert-info">
                            <i class="bi bi-lightbulb" aria-hidden="true"></i>
                            <span>Usa la barra laterale per esplorare le dotazioni dettagliate di ciascun kit e gli approfondimenti su acqua, documenti e manutenzione.</span>
                        </div>
                    </div>

                    <!-- Evacuazione -->
                    <div class="mk-section" id="mk-evacuazione">
                        <p class="mk-section-title"><i class="bi bi-backpack2-fill text-danger" aria-hidden="true"></i> Zaino per evacuazione rapida</p>
                        <p class="mk-section-sub">Leggero, impermeabile, trasportabile a spalle. Va preso al volo — deve contenere solo l'essenziale per le prime ore.</p>
                        <div class="mk-kit-grid">
                            <div class="mk-kit-item"><strong>Documenti</strong>Copie cartacee di documenti e tessere sanitarie.</div>
                            <div class="mk-kit-item"><strong>Farmaci</strong>Terapie quotidiane per alcuni giorni + lista di allergie scritta.</div>
                            <div class="mk-kit-item"><strong>Tech</strong>Telefono, cavo di ricarica e power bank carico.</div>
                            <div class="mk-kit-item"><strong>Segnalazione</strong>Torcia elettrica funzionante e fischietto.</div>
                            <div class="mk-kit-item"><strong>Sussistenza</strong>Una bottiglia d'acqua e snack energetici pronti.</div>
                            <div class="mk-kit-item"><strong>Contante</strong>Banconote di piccolo taglio.</div>
                            <div class="mk-kit-item"><strong>Autonomia</strong>Chiavi di riserva; occhiali o ausili acustici.</div>
                            <div class="mk-kit-item"><strong>Bambini</strong>Un oggetto rassicurante (giocattolo, peluche).</div>
                        </div>
                        <div class="mk-alert mk-alert-danger">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span><strong>Regola del peso:</strong> il totale non deve superare il 15–20% del peso corporeo di chi lo porta. Se non riesci a camminare 30 minuti con indosso, alleggeriscilo subito.</span>
                        </div>
                    </div>

                    <!-- Casa 72h -->
                    <div class="mk-section" id="mk-casa">
                        <p class="mk-section-title"><i class="bi bi-house-door-fill text-primary" aria-hidden="true"></i> Kit casa — autonomia 72 ore</p>
                        <p class="mk-section-sub">Da usare quando l'edificio è sicuro ma le reti esterne (acqua, luce, gas, telefono) sono interrotte.</p>
                        <div class="mk-kit-grid">
                            <div class="mk-kit-item"><strong>Acqua potabile</strong>Scorte calcolate per il nucleo familiare (vedi sezione dedicata).</div>
                            <div class="mk-kit-item"><strong>Alimenti</strong>Cibi a lunga conservazione senza cottura + apriscatole manuale.</div>
                            <div class="mk-kit-item"><strong>Informazione</strong>Radio a batterie o a manovella con batterie di ricambio.</div>
                            <div class="mk-kit-item"><strong>Illuminazione</strong>Torce LED. Evita candele — rischio fughe di gas.</div>
                            <div class="mk-kit-item"><strong>Igiene</strong>Prodotti personali, carta igienica, salviette, sacchi per rifiuti.</div>
                            <div class="mk-kit-item"><strong>Clima</strong>Coperte pesanti o indumenti adatti alla stagione.</div>
                            <div class="mk-kit-item"><strong>Documenti</strong>Copie e elenco cartaceo dei contatti utili.</div>
                            <div class="mk-kit-item"><strong>Esigenze speciali</strong>Scorte per neonati, anziani, disabili e animali.</div>
                        </div>
                    </div>

                    <!-- Auto -->
                    <div class="mk-section" id="mk-auto">
                        <p class="mk-section-title"><i class="bi bi-car-front-fill text-secondary" aria-hidden="true"></i> Kit automobile</p>
                        <p class="mk-section-sub">Dotazione permanente nel bagagliaio. Indispensabile per blocchi stradali, bufere o guasti notturni. Non lasciare farmaci o cibo esposti al calore estivo.</p>
                        <div class="mk-kit-grid">
                            <div class="mk-kit-item"><strong>Scorte</strong>Acqua e snack non deperibili.</div>
                            <div class="mk-kit-item"><strong>Primo soccorso</strong>Cassetta compatta e torcia elettrica.</div>
                            <div class="mk-kit-item"><strong>Calore</strong>Coperta termica e impermeabile leggero.</div>
                            <div class="mk-kit-item"><strong>Visibilità</strong>Giubbotto ad alta visibilità e guanti da lavoro.</div>
                            <div class="mk-kit-item"><strong>Energia</strong>Caricabatterie da auto e power bank.</div>
                            <div class="mk-kit-item"><strong>Contatti</strong>Numeri di emergenza e assistenza stradale stampati.</div>
                        </div>
                        <div class="mk-season-grid">
                            <div class="mk-season-card winter">
                                <div class="mk-season-title"><i class="bi bi-snow2" aria-hidden="true"></i> Integrazione invernale (Novembre–Marzo)</div>
                                <ul>
                                    <li>Guanti pesanti, cappello, calze di ricambio.</li>
                                    <li>Catene da neve o pneumatici invernali.</li>
                                    <li>Coperta in pile aggiuntiva; liquido per il radiatore.</li>
                                </ul>
                            </div>
                            <div class="mk-season-card summer">
                                <div class="mk-season-title"><i class="bi bi-sun-fill" aria-hidden="true"></i> Integrazione estiva (Giugno–Settembre)</div>
                                <ul>
                                    <li>Parasole, copricapo, mini-ventilatore.</li>
                                    <li>Scorta idrica aumentata (specie con bimbi).</li>
                                    <li>Integratori di sali minerali.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Acqua -->
                    <div class="mk-section" id="mk-acqua">
                        <p class="mk-section-title"><i class="bi bi-droplet-half text-primary" aria-hidden="true"></i> Calcolo delle scorte d'acqua</p>
                        <p class="mk-section-sub">Riferimento per il kit casa. La stima base è 4 litri/persona/giorno.</p>
                        <table class="mk-water-table">
                            <caption class="visually-hidden">Fabbisogno idrico per nucleo familiare nelle prime 72 ore di emergenza</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Nucleo familiare</th>
                                    <th scope="col">24 ore</th>
                                    <th scope="col">48 ore</th>
                                    <th scope="col">Target 72 ore</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row" style="border-radius: 0;">1 persona</th>
                                    <td>4 L</td><td>8 L</td><td>12 L</td>
                                </tr>
                                <tr>
                                    <th scope="row" style="border-radius: 0;">2 persone</th>
                                    <td>8 L</td><td>16 L</td><td>24 L</td>
                                </tr>
                                <tr>
                                    <th scope="row" style="border-radius: 0;">4 persone</th>
                                    <td>16 L</td><td>32 L</td><td>48 L</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="mk-section-title" style="margin-top:1.25rem;"><i class="bi bi-plus-circle" aria-hidden="true"></i> Fabbisogni aggiuntivi</p>
                        <table class="mk-special-table">
                            <caption class="visually-hidden">Fabbisogni idrici aggiuntivi per categorie speciali</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Caso speciale</th>
                                    <th scope="col">Motivazione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row"><i class="bi bi-baby me-1" aria-hidden="true"></i> Neonato / bambino</th>
                                    <td>Latte formulato e igiene quotidiana aumentata.</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="bi bi-activity me-1" aria-hidden="true"></i> Anziano / patologia cronica</th>
                                    <td>Terapie farmacologiche, dialisi, medicazioni frequenti.</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="bi bi-gender-female me-1" aria-hidden="true"></i> Gravidanza / allattamento</th>
                                    <td>Fabbisogno idrico biologico notevolmente aumentato.</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="bi bi-paw me-1" aria-hidden="true"></i> Animale domestico</th>
                                    <td>Scorta dedicata in base a specie, peso e temperatura.</td>
                                </tr>
                            </tbody>
                        </table>
                        <span class="mk-alert mk-alert-muted">Queste sono indicazioni logistiche, non prescrizioni cliniche. Per esigenze mediche specifiche consulta il tuo medico.</span>
                    </div>

                    <!-- Privacy -->
                    <div class="mk-section" id="mk-privacy">
                        <p class="mk-section-title"><i class="bi bi-shield-lock-fill text-success" aria-hidden="true"></i> Documenti e tutela della privacy</p>
                        <p class="mk-section-sub">I kit contengono dati personali sensibili. Adotta queste precauzioni per evitare furti d'identità o violazioni.</p>
                        <div class="mk-tips-grid">
                            <div class="mk-tip"><i class="bi bi-eye-slash" aria-hidden="true"></i><span>Non lasciare le schede compilate o i kit incustoditi in luoghi pubblici.</span></div>
                            <div class="mk-tip"><i class="bi bi-chat-x" aria-hidden="true"></i><span>Evita di fotografare o inviare copie dei documenti via app di messaggistica.</span></div>
                            <div class="mk-tip"><i class="bi bi-ban" aria-hidden="true"></i><span>Non pubblicare elenchi, foto del kit o dati logistici sui social.</span></div>
                            <div class="mk-tip"><i class="bi bi-scissors" aria-hidden="true"></i><span>Distruggi con un trita-documenti le vecchie copie cartacee quando le sostituisci.</span></div>
                        </div>
                    </div>

                    <!-- Manutenzione -->
                    <div class="mk-section" id="mk-manutenzione">
                        <p class="mk-section-title"><i class="bi bi-calendar-check-fill" aria-hidden="true"></i> Manutenzione periodica</p>
                        <p class="mk-section-sub">I materiali si deteriorano nel tempo. Verifica e rinnova le dotazioni almeno due volte l'anno.</p>
                        <div class="mk-maintenance-box">
                            <p>Controlla: scadenze di acqua e cibo, efficacia dei farmaci, carica di batterie e power bank, integrità dei vestiti, aggiornamento dei contatti utili.</p>
                            <div class="mk-maintenance-tip">
                                <i class="bi bi-clock-history" aria-hidden="true"></i>
                                <span>Fissalo al cambio dell'ora legale — ultima domenica di marzo e ultima domenica di ottobre. Il calendario ti ricorderà di controllare la sicurezza della famiglia.</span>
                            </div>
                        </div>
                    </div>

                </div><!-- fine mk-content -->
            </div><!-- fine mk-body -->

            <!-- Footer -->
            <div class="mk-footer">
                <button type="button" class="mk-btn-close" data-bs-dismiss="modal">Ho capito</button>
            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="modalPiano" tabindex="-1" role="dialog" aria-labelledby="modalPianoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="mp-header">
                <h2 class="mp-header-title" id="modalPianoTitle">
                    <i class="bi bi-people-fill" aria-hidden="true"></i>
                    Piano di Emergenza Familiare
                    <span class="mp-header-badge">Guida</span>
                </h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi finestra modale"></button>
            </div>

            <div class="mp-body">

                <p class="mp-intro">
                    Sapere esattamente cosa fare riduce il panico. Riunisci la famiglia e stabilite insieme le regole base della vostra sicurezza — prima che serva davvero.
                </p>

                <!-- Tre pilastri -->
                <p class="mp-section-title">I tre elementi fondamentali</p>
                <div class="mp-pillars">
                    <div class="mp-pillar">
                        <div class="mp-pillar-icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></div>
                        <h3>Punti di incontro</h3>
                        <p>Scegliete due luoghi: uno vicino a casa e uno fuori dal quartiere per evacuazioni d'area.</p>
                    </div>
                    <div class="mp-pillar">
                        <div class="mp-pillar-icon" aria-hidden="true"><i class="bi bi-telephone-outbound-fill"></i></div>
                        <h3>Contatto esterno</h3>
                        <p>Un parente in un'altra città che centralizzi le notizie se le linee locali sono sature.</p>
                    </div>
                    <div class="mp-pillar">
                        <div class="mp-pillar-icon" aria-hidden="true"><i class="bi bi-person-check-fill"></i></div>
                        <h3>Compiti assegnati</h3>
                        <p>Chi prende il kit? Chi chiude gas e acqua? Chi assiste i più deboli? Decidi prima.</p>
                    </div>
                </div>

                <!-- Sicurezza domestica -->
                <p class="mp-section-title">Sicurezza domestica</p>
                <ul class="mp-checklist">
                    <li>
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        <span>Tutti in famiglia devono sapere dove sono e come chiudere i rubinetti generali di <strong>gas</strong>, <strong>acqua</strong> e l'interruttore principale della <strong>luce</strong>.</span>
                    </li>
                    <li>
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        <span>Mantieni corridoi e vie di fuga sempre liberi da mobili, scatoloni e ostacoli.</span>
                    </li>
                    <li>
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        <span>Insegna ai bambini come chiamare il <strong>112</strong> e i dati da comunicare: nome, cognome, indirizzo.</span>
                    </li>
                </ul>

                <!-- CTA scheda famiglia -->
                <div class="mp-cta">
                    <div class="mp-cta-icon" aria-hidden="true"><i class="bi bi-file-earmark-person"></i></div>
                    <div class="mp-cta-text">
                        <h3>Vuoi salvare il tuo piano personalizzato?</h3>
                        <p>Compila il modulo con i dati della tua famiglia, i contatti medici e il tuo kit. Potrai stamparlo o tenerlo sul telefono.</p>
                        <a href="scheda-famiglia.php" class="mp-cta-btn">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i> Compila il piano ora
                        </a>
                    </div>
                </div>

            </div>

            <div class="mp-footer">
                <button type="button" class="mp-btn-close" data-bs-dismiss="modal">Chiudi</button>
            </div>

        </div>
    </div>
</div>




<div class="modal fade" id="modalFragili" tabindex="-1" role="dialog" aria-labelledby="modalFragiliTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="mf-header">
                <h2 class="mf-header-title" id="modalFragiliTitle">
                    <i class="bi bi-heart-pulse-fill" aria-hidden="true"></i>
                    Persone con Necessità Specifiche
                    <span class="mf-header-badge">Guida</span>
                </h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi finestra modale"></button>
            </div>

            <div class="mf-body">

                <nav class="mf-sidebar" aria-label="Sezioni guida fragilità">
                    <div class="mf-sidebar-section">Panoramica</div>
                    <button class="mf-nav-btn active" data-target="intro">
                        <i class="bi bi-info-circle" aria-hidden="true"></i> Chi è a rischio
                    </button>
                    <div class="mf-sidebar-section">In emergenza</div>
                    <button class="mf-nav-btn" data-target="preparazione">
                        <i class="bi bi-shield-plus" aria-hidden="true"></i> Preparazione
                    </button>
                    <button class="mf-nav-btn" data-target="motoria">
                        <i class="bi bi-person-wheelchair" aria-hidden="true"></i> Difficoltà motorie
                    </button>
                    <button class="mf-nav-btn" data-target="sensoriale">
                        <i class="bi bi-eye-slash" aria-hidden="true"></i> Disabilità sensoriale
                    </button>
                    <button class="mf-nav-btn" data-target="bambini">
                        <i class="bi bi-backpack" aria-hidden="true"></i> Neonati e bambini
                    </button>
                    <div class="mf-sidebar-section">Comunità</div>
                    <button class="mf-nav-btn" data-target="vicinato">
                        <i class="bi bi-heart" aria-hidden="true"></i> Rete di vicinato
                    </button>
                </nav>

                <div class="mf-content">

                    <!-- Intro -->
                    <div class="mf-section active" id="mf-intro">
                        <p class="mf-section-title"><i class="bi bi-people-fill" aria-hidden="true"></i> Chi sono le persone con necessità specifiche</p>
                        <p class="mf-section-sub">In ogni emergenza, le persone fragili sono le più vulnerabili. Pianificare in anticipo le loro esigenze è fondamentale per garantirne la sicurezza.</p>
                        <div class="mf-tags">
                            <span class="mf-tag"><i class="bi bi-person" aria-hidden="true"></i> Anziani soli o con difficoltà motorie</span>
                            <span class="mf-tag"><i class="bi bi-person-wheelchair" aria-hidden="true"></i> Disabilità motoria, sensoriale o cognitiva</span>
                            <span class="mf-tag"><i class="bi bi-backpack" aria-hidden="true"></i> Bambini sotto i 6 anni</span>
                            <span class="mf-tag"><i class="bi bi-gender-female" aria-hidden="true"></i> Donne in gravidanza</span>
                            <span class="mf-tag"><i class="bi bi-activity" aria-hidden="true"></i> Patologie croniche o terapie continue</span>
                            <span class="mf-tag"><i class="bi bi-plug" aria-hidden="true"></i> Dipendenti da apparecchiature elettromedicali</span>
                            <span class="mf-tag"><i class="bi bi-translate" aria-hidden="true"></i> Difficoltà linguistiche</span>
                        </div>
                        <div class="mf-law-box">
                            <div class="mf-law-label"><i class="bi bi-journal-text" aria-hidden="true"></i> Riferimenti normativi</div>
                            Il principio di attenzione prioritaria a queste categorie è recepito dalla norma <strong>ISO 22315:2014</strong> <em>(Mass evacuation — Guidelines for planning)</em>. In Italia, il <strong>Codice della Protezione Civile</strong> (D.Lgs. 1/2018) e le linee guida del Dipartimento ne definiscono l'applicazione nei Piani di Emergenza Comunali.
                        </div>
                    </div>

                    <!-- Preparazione -->
                    <div class="mf-section" id="mf-preparazione">
                        <p class="mf-section-title"><i class="bi bi-shield-plus" aria-hidden="true"></i> Preparazione — cosa fare ora</p>
                        <p class="mf-section-sub">Per caregiver e famiglie: azioni concrete da completare prima che si verifichi un'emergenza.</p>
                        <div class="mf-cards">
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-diagram-3" aria-hidden="true"></i> Piano familiare</div>
                                Includi le esigenze specifiche di ogni componente. Verifica che le vie di fuga siano sempre accessibili, prive di ostacoli e dotate di rampe se necessario.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-bag-plus" aria-hidden="true"></i> Zaino medico personalizzato</div>
                                Prepara un kit contenente medicinali salvavita, protesi e gli ausili d'uso quotidiano della persona.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-file-medical" aria-hidden="true"></i> Dati scritti sempre aggiornati</div>
                                Lista delle terapie in corso, medici curanti e numeri di emergenza. Individua vicini o persone di fiducia che possano intervenire rapidamente.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-plug" aria-hidden="true"></i> Apparecchiature elettromedicali</div>
                                Se la persona dipende da ventilatori o dialisi, contatta la ASL per l'iscrizione alla lista delle <strong>utenze non disalimentabili</strong>.
                            </div>
                        </div>
                    </div>

                    <!-- Motoria -->
                    <div class="mf-section" id="mf-motoria">
                        <p class="mf-section-title"><i class="bi bi-person-wheelchair" aria-hidden="true"></i> Difficoltà motorie e anziani soli</p>
                        <p class="mf-section-sub">Indicazioni pratiche per chi ha mobilità ridotta o vive solo.</p>
                        <div class="mf-cards">
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-door-open" aria-hidden="true"></i> In caso di evacuazione</div>
                                Chiedi aiuto ai vicini o attendi i soccorsi in un punto sicuro dell'abitazione. Non usare mai l'ascensore durante terremoti o incendi. Con la sedia a rotelle: blocca le ruote e riparati sotto una scrivania o tavolo robusto.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-telephone" aria-hidden="true"></i> Anziani che vivono soli</div>
                                Tieni sempre il telefono carico e a portata di mano. Informa un vicino di fiducia del tuo stato di salute e lascia una copia delle chiavi di casa con lui.
                            </div>
                        </div>
                        <div class="mf-alert mf-alert-warning">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            <span><strong>Ascensori:</strong> non utilizzarli mai durante un terremoto o un incendio, nemmeno per scendere pochi piani.</span>
                        </div>
                    </div>

                    <!-- Sensoriale -->
                    <div class="mf-section" id="mf-sensoriale">
                        <p class="mf-section-title"><i class="bi bi-eye-slash" aria-hidden="true"></i> Disabilità sensoriale e cognitiva</p>
                        <p class="mf-section-sub">Misure specifiche per non udenti, non vedenti e persone con disabilità cognitive.</p>
                        <div class="mf-cards">
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-ear" aria-hidden="true"></i> Persone non udenti</div>
                                Predisponi sistemi di allarme visivi (luci lampeggianti) o messaggistica dedicata. Verifica che l'app <strong>IT-alert</strong> sia attiva e configurata sul telefono.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-eye-slash" aria-hidden="true"></i> Persone non vedenti</div>
                                Memorizza i percorsi di uscita da casa e dal luogo di lavoro. Tieni il bastone guida sempre vicino al letto o alla postazione. Segui le indicazioni tattili sul territorio.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-chat-heart" aria-hidden="true"></i> Disabilità cognitive</div>
                                In caso di stress usa linguaggio calmo, frasi brevi e istruzioni semplici. Tieni pronti oggetti familiari o anti-stress per rassicurare la persona.
                            </div>
                        </div>
                    </div>

                    <!-- Bambini -->
                    <div class="mf-section" id="mf-bambini">
                        <p class="mf-section-title"><i class="bi bi-backpack" aria-hidden="true"></i> Neonati e bambini piccoli</p>
                        <p class="mf-section-sub">I bambini hanno bisogno di rassicurazione e non devono mai essere lasciati soli.</p>
                        <div class="mf-cards">
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-chat-dots" aria-hidden="true"></i> Comunicazione</div>
                                Spiega con calma cosa sta succedendo, usando parole semplici adatte all'età. Non trasmettere panico o ansia.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-person-arms-up" aria-hidden="true"></i> Identificazione</div>
                                Assicurati che conoscano nome completo, indirizzo e numero di un genitore. Inserisci sempre un biglietto con i contatti nel loro zainetto.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-box-seam" aria-hidden="true"></i> Kit per neonati e bambini</div>
                                Includi: latte in polvere, omogeneizzati, pannolini, salviette, biberon e un piccolo giocattolo o libro per distrarli durante l'attesa.
                            </div>
                        </div>
                        <div class="mf-alert mf-alert-warning">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            <span>Non lasciare mai i bambini incustoditi durante un'emergenza, nemmeno per pochi minuti.</span>
                        </div>
                    </div>

                    <!-- Vicinato -->
                    <div class="mf-section" id="mf-vicinato">
                        <p class="mf-section-title"><i class="bi bi-heart-fill" aria-hidden="true"></i> Rete di vicinato e tutela animali</p>
                        <p class="mf-section-sub">La comunità è la prima risorsa. Una telefonata può salvare una vita.</p>
                        <div class="mf-cards">
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-people" aria-hidden="true"></i> Conosci i tuoi vicini fragili</div>
                                Presentati e scambia i numeri di telefono. Offriti di verificare il loro stato in caso di allerta. Segnala situazioni di isolamento estremo al Comune o ai servizi sociali. Durante ondate di calore o freddo intenso, una visita può fare la differenza.
                            </div>
                            <div class="mf-card">
                                <div class="mf-card-title"><i class="bi bi-paw" aria-hidden="true"></i> Animali domestici</div>
                                Non abbandonarli in caso di evacuazione. Tieni sempre pronti: trasportino, guinzaglio, museruola (spesso obbligatoria nei mezzi di soccorso), scorta di cibo, acqua e libretto sanitario con il codice del microchip.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="mf-footer">
                <button type="button" class="mf-btn-close" data-bs-dismiss="modal">Chiudi</button>
            </div>

        </div>
    </div>
</div>


