<?php 
include('../includes/header.php'); 
$voci = require __DIR__ . '/../includes/piano_familiare_voci.php';
?>
<link href="../css/scheda-famiglia.css" rel="stylesheet">



<div class="scroll-progress-bar d-print-none" id="scrollProgressBar" aria-hidden="true"></div>

<main class="emergency-container" id="main-content" tabindex="-1">

    <form id="emergencyPlanForm" novalidate>
        <input type="hidden" id="householdId" value="">

        <div class="print-header">
            <div class="print-header-logo">
                <img src="../assets/img/logo.png" alt="<?= htmlspecialchars('Logo ' . $site['nome_breve']) ?>">
            </div>
            <div class="print-header-title">
                <h1>PIANO FAMILIARE DI EMERGENZA</h1>
                <p class="print-subtitle">Comune di <?= htmlspecialchars($site['address']['city']) ?></p>
                <p class="print-subtitle">Documento di protezione civile per la sicurezza del nucleo familiare</p>
            </div>
        </div>

        <div class="card-emergency card-highlight d-print-none">
            <h2>Come usare questa scheda</h2>
            <div class="intro-block">
                <p><strong>I tuoi dati restano protetti:</strong> il piano viene salvato automaticamente solo nel browser. I dati sono inviati al server solo quando generi il PDF e non vengono archiviati sul sito.</p>
                <ol class="plan-guide-list">
                    <li>Compila i dati della famiglia e aggiungi i suoi componenti.</li>
                    <li>Indica ritrovi, contatti, kit ed eventuali esigenze specifiche.</li>
                    <li>Completa tutti i punti della checklist finale.</li>
                    <li>In fondo alla pagina, premi <strong>Stampa / Genera PDF</strong>.</li>
                </ol>
                <p>Compila e rileggi il piano insieme alla famiglia. Aggiornalo almeno una volta all'anno e ogni volta che cambiano indirizzo, contatti o necessità.</p>
                <p class="plan-guide-import">
                    Hai già un piano stampato?
                    <a href="#importTitle">Vai al ripristino da QR in fondo alla pagina</a>.
                    Il ripristino sostituisce i dati attualmente compilati.
                </p>
            </div>
        </div>

        <div class="progress-container d-print-none" role="region" aria-label="Percentuale di completamento del piano">
            <div class="progress-bar-view">
                <div class="progress-info">
                    <span>Completamento del tuo piano:</span>
                    <span id="progressPercent">0%</span>
                </div>
                <div class="progress-bar-track"
                     role="progressbar"
                     aria-label="Completamento del piano familiare"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-valuenow="0"
                     id="progressBarTrack">
                    <div class="progress-bar-fill" id="progressBarFill" aria-hidden="true"></div>
                </div>
            </div>
            <div class="progress-ring-view">
                <div class="progress-ring" id="progressRing">
                    <div class="progress-ring-inner"><span id="progressPercentRing">0%</span></div>
                </div>
                <div class="progress-ring-label">Piano<br>completato</div>
            </div>
        </div>

        <div class="card-emergency">
            <h2>1. La mia famiglia</h2>
            <div class="grid-emergency">
                <div class="field-emergency">
                    <label for="family">Cognome della famiglia</label>
                    <input type="text" id="family" required aria-invalid="false" aria-describedby="err_family"
                        placeholder="Es. Rossi"
                        maxlength="60"
                        autocomplete="family-name"
                        oninput="autoSave(); validateNotEmpty(this, 'Inserisci il cognome della famiglia')">
                    <span class="field-error-msg" id="err_family" aria-live="polite"></span>
                </div>
                <div class="field-emergency">
                    <label for="address">Indirizzo di casa</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        placeholder="Via/Piazza, Civico, Città"
                        maxlength="120"
                        autocomplete="off"
                        oninput="autoSave(); validateNotEmpty(this, 'Inserisci l\'indirizzo di casa')"
                        required aria-invalid="false" aria-describedby="err_address">
                    <span class="field-error-msg" id="err_address" aria-live="polite"></span>
                </div>
                <div class="field-emergency">
                    <label for="phone">Telefono principale</label>
                    <input type="tel" id="phone"
                        placeholder="Es. +39 333 1234567"
                        maxlength="20"
                        autocomplete="tel"
                        oninput="autoSave(); validatePhone(this)"
                        aria-invalid="false" aria-describedby="err_phone">
                    <span class="field-error-msg" id="err_phone" aria-live="polite"></span>
                </div>
                <div class="field-emergency">
                    <label for="email">Email della famiglia</label>
                    <input type="email" id="email"
                        placeholder="nome@esempio.it"
                        maxlength="100"
                        autocomplete="email"
                        oninput="autoSave(); validateEmail(this)"
                        aria-invalid="false" aria-describedby="err_email">
                    <span class="field-error-msg" id="err_email" aria-live="polite"></span>
                </div>
            </div>
        </div>

        <div class="card-emergency">
            <h2>2. Componenti della famiglia</h2>
            <p class="small text-muted d-print-none member-hint">Aggiungi i dettagli per ogni membro del nucleo familiare (inclusi bambini, ragazzi o anziani). Le schede lasciate completamente vuote non vengono stampate.</p>
            
            <div id="members"></div>

            <div class="actions-emergency">
                <button type="button" class="btn-emergency secondary-btn" onclick="addMember()">
                    + Aggiungi componente
                </button>
            </div>
        </div>

        <div class="card-emergency">
            <h2>3. I nostri punti di ritrovo e contatti</h2>
            <div class="grid-emergency">
                <div class="field-emergency">
                    <label for="meeting1">Punto di ritrovo vicino casa</label>
                    <textarea id="meeting1" class="auto-resize" rows="1" maxlength="300"
                            placeholder="Es. Area di attesa comunale / Piazza mercato"
                            oninput="autoSave()"></textarea>
                </div>
                <div class="field-emergency">
                    <label for="meeting2">Punto di ritrovo lontano da casa</label>
                    <textarea id="meeting2" class="auto-resize" rows="1" maxlength="300"
                            placeholder="Es. Parcheggio stadio / Fuori dal quartiere"
                            oninput="autoSave()"></textarea>
                </div>
                <div class="field-emergency">
                    <label for="contact">Contatto di riferimento fuori città</label>
                    <input type="text" id="contact"
                        placeholder="Nome del parente o amico distante"
                        maxlength="80"
                        oninput="autoSave()">
                </div>
                <div class="field-emergency">
                    <label for="contactPhone">Telefono referente fuori città</label>
                    <input type="tel" id="contactPhone"
                        placeholder="Es. +39 333 1234567"
                        maxlength="20"
                        oninput="autoSave(); validatePhone(this)">
                    <span class="field-error-msg" id="err_contactPhone"></span>
                </div>
                <div class="field-emergency">
                    <label for="familyDoctor">Medico di famiglia (Nome e Telefono)</label>
                    <input type="text" id="familyDoctor"
                        placeholder="Dott. Bianchi — 02 1234567"
                        maxlength="100"
                        oninput="autoSave()">
                </div>
                <div class="field-emergency">
                    <label for="kitLocation">Dove si trova il kit di emergenza?</label>
                    <input type="text" id="kitLocation"
                        placeholder="Es. Armadio ingresso / Ripostiglio"
                        maxlength="120"
                        oninput="autoSave()">
                </div>
                <div class="field-emergency span-2">
                    <label for="utilitiesLocation">Dove sono rubinetti gas/acqua e quadro elettrico?</label>
                    <textarea id="utilitiesLocation" class="auto-resize" rows="1" maxlength="300"
                            placeholder="Es. Gas in cucina sotto lavello, Acqua sul balcone, Luce ingresso"
                            oninput="autoSave()"></textarea>
                </div>
            </div>
        </div>

        <div class="card-emergency">
            <h2>4. Esigenze specifiche e Note Sanitarie</h2>
            <div class="field-emergency">
                <label for="specialNeeds">Farmaci indispensabili, ausili, disabilità o allergie gravi</label>
                <textarea id="specialNeeds" class="auto-resize" rows="2" maxlength="1000"
                        placeholder="Indica qui i farmaci salvavita o continuativi, necessità e ausili particolari per persone fragili o indicazioni sanitarie d'urgenza..."
                        oninput="autoSave()"></textarea>
            </div>
        </div>

        <div class="card-emergency">
            <h2>5. Verifica dotazione del Kit di Emergenza</h2>
            <fieldset class="checklist-emergency">
                <legend class="visually-hidden">Elementi da verificare nel kit di emergenza</legend>
                <?php foreach ($voci['kit'] as $id => $label): ?>
                <label class="check-item"><input type="checkbox" id="<?= htmlspecialchars($id) ?>"> <?= htmlspecialchars($label) ?></label>
                <?php endforeach; ?>
            </fieldset>
        </div>

        <div class="card-emergency d-print-none">
            <h2>Consigli di Protezione Civile per i membri della famiglia</h2>
            
            <h3>Consigli per famiglie con bambini</h3>
            <ul class="advice-list text-muted small">
                <li>Spiega ai bambini cosa potrebbe succedere con parole semplici, evitando racconti o immagini spaventose.</li>
                <li>Assicurati che sappiano dire il loro nome completo, l'indirizzo di casa e almeno un numero di cellulare dei genitori.</li>
                <li>Insegna loro a riconoscere il suono dei sistemi di allarme e a seguire tempestivamente l'adulto di riferimento.</li>
                <li>Fai delle piccole simulazioni sotto forma di gioco per raggiungere insieme il punto di ritrovo scelto.</li>
                <li>Inserisci una nota identificativa o un contatto utile nello zainetto di scuola o del kit, ed inserisci un loro piccolo oggetto rassicurante (es. un peluche).</li>
            </ul>

            <h3>Consigli per famiglie con anziani</h3>
            <ul class="advice-list text-muted small">
                <li>Verifica costantemente che i corridoi e le vie di fuga della casa siano liberi da ostacoli mobili o scatoloni.</li>
                <li>Tieni una lista scritta, chiara e sempre aggiornata di tutti i farmaci assunti quotidianamente.</li>
                <li>Se la persona vive sola, lascia una copia delle chiavi di casa a un familiare o a un vicino di fiducia (previo consenso).</li>
                <li>Configura il telefono cellulare o il salvavita posizionando i numeri di emergenza chiave in chiamata rapida.</li>
                <li>Riponi ausili essenziali (occhiali da vista, apparecchi acustici con batterie, bastoni) in un punto della stanza fisso e facile da afferrare.</li>
            </ul>

            <h3>Consigli per persone con disabilità o bisogni specifici</h3>
            <ul class="advice-list text-muted small">
                <li>Includi esplicitamente nel piano tutti gli ausili tecnici vitali (es. sedia a rotelle, comunicatori, protesi).</li>
                <li><strong>Apparecchiature elettromedicali:</strong> Se dipendi da dispositivi alimentati a corrente, concorda in anticipo un piano d'azione logistico con il tuo medico e contatta il gestore della rete elettrica per le tutele previste.</li>
                <li>Individua e accordati preventivamente con almeno due persone di fiducia (vicini di casa, parenti) pronte ad aiutarti durante una rapida evacuazione.</li>
                <li>Studia percorsi d'uscita accessibili e valuta valide alternative qualora ascensori o rampe principali risultassero bloccati.</li>
            </ul>

            <h3>Consigli per gli animali domestici</h3>
            <ul class="advice-list text-muted small">
                <li>Includi nel kit d'emergenza cibo dedicato, acqua extra, ciotole leggere, sacchetti igienici e i loro specifici farmaci veterinari.</li>
                <li>Tieni sempre pronti e vicini alla porta d'uscita principale il guinzaglio, la pettorina, il trasportino ed una museruola.</li>
                <li>Conserva in una busta sigillata il libretto sanitario aggiornato, i dati del microchip e una fotografia recente dell'animale.</li>
                <li>Identifica in anticipo un amico, un parente o una struttura d'accoglienza disposta a ospitarli se la casa dovesse risultare inagibile.</li>
            </ul>
        </div>

        <div class="card-emergency d-print-none">
            <h2>Checklist finale di controllo</h2>
            <fieldset class="checklist-emergency">
                <legend class="visually-hidden">Attività da completare prima della stampa</legend>
                <?php foreach ($voci['checklist'] as $id => $label): ?>
                <label class="check-item"><input type="checkbox" id="<?= htmlspecialchars($id) ?>"> <?= htmlspecialchars($label) ?></label>
                <?php endforeach; ?>
            </fieldset>
            <p class="small text-muted bottom-note">
                <strong>Quando aggiornare il piano?</strong> Almeno una volta all'anno; in caso di traslochi o cambi di sede scolastica/lavorativa; se cambia la composizione anagrafica del nucleo; al variare di terapie mediche o all'arrivo/mancanza di un animale domestico.
            </p>
        </div>

        <div class="card-emergency">
            <h2>Cosa fare in emergenza</h2>
            <div class="info-box-emergency">
                <strong>Terremoto:</strong> proteggiti sotto strutture solide (tavoli, travi), allontanati da finestre, specchi o mobili pesanti e non usare assolutamente gli ascensori.<br><br>
                <strong>Incendio:</strong> evacua immediatamente l'edificio proteggendo le vie respiratorie con un panno umido, non usare l'ascensore e allerta subito i soccorsi.<br><br>
                <strong>Alluvione:</strong> raggiungi tempestivamente i piani alti della casa o di una struttura sicura; non scendere assolutamente in cantine, garage o sottopassi ed evita di usare l'auto.<br><br>
                <strong>Numero Unico di Emergenza Europeo:</strong> 112
            </div>
        </div>

        <div class="card-emergency d-print-none">
            <h2>Opzioni del PDF</h2>
            <fieldset class="checklist-emergency">
                <legend class="visually-hidden">Opzioni di generazione del PDF</legend>
                <label class="check-item"><input type="checkbox" id="opt_qr" checked> Includi il QR code con i dati del piano</label>
                <label class="check-item"><input type="checkbox" id="opt_cards" checked> Aggiungi le mini-schede personali ritagliabili (una per componente, con QR)</label>
            </fieldset>
            <p class="small text-muted bottom-note">Il QR e le mini-schede possono contenere dati personali e sanitari: stampa e conserva il PDF con attenzione. In caso di emergenza la mini-scheda può essere letta dalla segreteria di campo per velocizzare la tua registrazione.</p>
        </div>

        <div class="card-emergency d-print-none">
            <div class="actions-emergency">
                <button type="button" class="btn-emergency accent-btn" onclick="handlePrint()">
                    <i class="bi bi-printer-fill" aria-hidden="true"></i>
                    Stampa / Genera PDF
                </button>
            </div>
            <p id="printStatus" class="visually-hidden" role="status" aria-live="assertive"></p>
        </div>
    </form>

    <section class="card-emergency d-print-none" aria-labelledby="importTitle">
        <h2 id="importTitle">Hai già un piano? Ripristinalo da QR</h2>
        <p class="small text-muted">
            Carica una foto o uno screenshot del QR del piano, oppure incolla il testo letto con un lettore QR.
            La lettura avviene direttamente in questo browser. I dati attualmente compilati verranno sostituiti.
        </p>
        <div class="field-emergency">
            <label for="importText">Contenuto del QR (testo)</label>
            <textarea id="importText" rows="3" placeholder="PFP1…"></textarea>
        </div>
        <div class="actions-emergency">
            <button type="button" class="btn-emergency secondary-btn" onclick="importFromText()">Importa dal testo</button>
            <button type="button" class="btn-emergency secondary-btn" onclick="document.getElementById('importImage').click()">Carica immagine del QR</button>
            <input type="file" id="importImage" accept="image/*" class="visually-hidden" tabindex="-1" aria-hidden="true">
        </div>
        <p id="importStatus" class="small" role="status" aria-live="polite"></p>
    </section>
</main>

<script src="/js/jsQR.min.js"></script>
<script>
const membersContainer = document.getElementById("members");

// Una famiglia raramente è composta da una sola persona: partiamo con più schede componente pronte
const DEFAULT_MEMBERS_COUNT = 2;

const KIT_IDS = <?= json_encode(array_keys($voci['kit'])) ?>;
const REQUIRED_PRINT_CHECKS = <?= json_encode(array_keys($voci['checklist'])) ?>;
const checklistIds = [...KIT_IDS, ...REQUIRED_PRINT_CHECKS];

const OPTION_IDS = { qr: "opt_qr", cards: "opt_cards" };

// Stessa mappa di genera_piano_familiare.php (QR_TOP_KEYS / QR_MEMBER_KEYS)
const QR_TOP_KEYS = {
    family: 'f', address: 'a', phone: 't', email: 'e',
    meeting1: 'r1', meeting2: 'r2', contact: 'c', contactPhone: 'ct',
    familyDoctor: 'md', kitLocation: 'k', utilitiesLocation: 'u', specialNeeds: 'n',
    householdId: 'h'
};
const QR_MEMBER_KEYS = {
    name: 'n', birth: 'b', cf: 'cf', blood: 'g', phone: 't', medical: 'm'
};

/* ═══════════════════════════════════════
   ID DEL NUCLEO (casuale, senza dati personali)
   Collega le mini-schede dei componenti quando vengono lette in campo.
═══════════════════════════════════════ */

function generateHouseholdId() {
    // 32 caratteri senza simboli ambigui (0/O, 1/I): 8 caratteri = 40 bit
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    const bytes = new Uint8Array(8);
    (window.crypto || window.msCrypto).getRandomValues(bytes);
    return Array.from(bytes, b => alphabet[b % alphabet.length]).join('');
}

function ensureHouseholdId() {
    const el = document.getElementById('householdId');
    if (el && !/^[A-Z0-9]{6,12}$/.test(el.value)) {
        el.value = generateHouseholdId();
        autoSave();
    }
}

/* ═══════════════════════════════════════
   VALIDAZIONI
═══════════════════════════════════════ */

function setFieldState(input, isValid, errorMsg) {
    const errEl = document.getElementById('err_' + input.id);
    input.setAttribute('aria-invalid', String(!isValid));
    input.classList.toggle('input-ok', isValid);
    input.classList.toggle('input-error', !isValid);
    if (errEl) {
        errEl.textContent = isValid ? '' : errorMsg;
        errEl.classList.toggle('visible', !isValid);
    }
}

function validateNotEmpty(input, msg) {
    const ok = input.value.trim().length > 0;
    setFieldState(input, ok, msg || 'Campo obbligatorio');
    return ok;
}

function validatePhone(input) {
    const val = input.value.trim();
    if (val === '') {
        input.classList.remove('input-ok', 'input-error');
        input.setAttribute('aria-invalid', 'false');
        const errEl = document.getElementById('err_' + input.id);
        if (errEl) { errEl.textContent = ''; errEl.classList.remove('visible'); }
        return true;
    }
    const digits = val.replace(/\D/g, '');
    const ok =
        /^[0-9\s\+\-\(\)\/]+$/.test(val) &&
        digits.length >= 6 &&
        digits.length <= 15;
    setFieldState(input, ok, 'Numero non valido — es. 02 1234567 oppure +39 333 1234567');
    return ok;
}

function validateEmail(input) {
    const ok = input.value.trim() === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
    setFieldState(input, ok, 'Indirizzo email non valido');
    return ok;
}

function validateCF(input) {
    const val = input.value.trim().toUpperCase();
    const reCF = /^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1}$/;
    const ok = val === '' || reCF.test(val);
    const errEl = input.closest('.field-emergency')?.querySelector('.field-error-msg');
    input.classList.toggle('input-ok', ok && val !== '');
    input.classList.toggle('input-error', !ok && val !== '');
    input.setAttribute('aria-invalid', String(!ok && val !== ''));
    if (errEl) {
        errEl.textContent = ok ? '' : 'Codice fiscale non valido (16 caratteri)';
        errEl.classList.toggle('visible', !ok && val !== '');
    }
    return ok;
}

function validateMemberPhone(input) {
    const val = input.value.trim();
    const errEl = input.closest('.field-emergency')?.querySelector('.field-error-msg');
    if (val === '') {
        input.classList.remove('input-ok', 'input-error');
        input.setAttribute('aria-invalid', 'false');
        if (errEl) { errEl.textContent = ''; errEl.classList.remove('visible'); }
        return true;
    }
    const digits = val.replace(/\D/g, '');
    const ok =
        /^[0-9\s\+\-\(\)\/]+$/.test(val) &&
        digits.length >= 6 &&
        digits.length <= 15;
    input.classList.toggle('input-ok', ok);
    input.classList.toggle('input-error', !ok);
    input.setAttribute('aria-invalid', String(!ok));
    if (errEl) {
        errEl.textContent = ok ? '' : 'Numero non valido — es. 02 1234567 oppure +39 333 1234567';
        errEl.classList.toggle('visible', !ok);
    }
    return ok;
}

function validateMemberName(input) {
    const ok = input.value.trim().length >= 2;
    const errEl = input.closest('.field-emergency')?.querySelector('.field-error-msg');
    input.classList.toggle('input-ok', ok && input.value.trim() !== '');
    input.classList.toggle('input-error', !ok && input.value.trim() !== '');
    input.setAttribute('aria-invalid', String(!ok && input.value.trim() !== ''));
    if (errEl) {
        errEl.textContent = ok ? '' : 'Inserisci nome e cognome (min. 2 caratteri)';
        errEl.classList.toggle('visible', !ok && input.value.trim() !== '');
    }
    return ok;
}

/* ═══════════════════════════════════════
   MEMBRI DELLA FAMIGLIA
═══════════════════════════════════════ */

function addMember(data = {}) {
    const div = document.createElement("div");
    div.className = "member-box";

    const memberId = 'member_' + Date.now() + Math.random().toString(36).substr(2, 5);
    div.id = memberId;

    div.innerHTML = `
        <button type="button" class="remove-member-btn d-print-none" onclick="removeMember('${memberId}')" aria-label="Rimuovi componente della famiglia">Rimuovi</button>
        <div class="grid-emergency">
            <div class="field-emergency">
                <label for="${memberId}_name">Nome e Cognome</label>
                <input type="text" id="${memberId}_name" class="name"
                       value="${escHtml(data.name || '')}"
                       placeholder="Es. Mario Rossi"
                       maxlength="80"
                       autocomplete="name"
                       oninput="autoSave(); validateMemberName(this)">
                <span class="field-error-msg"></span>
            </div>
            <div class="field-emergency">
                <label for="${memberId}_birth">Data di nascita</label>
                <input type="date" id="${memberId}_birth" class="birth"
                       value="${escHtml(data.birth || '')}"
                       onchange="autoSave()">
            </div>
            <div class="field-emergency">
                <label for="${memberId}_cf">Codice Fiscale</label>
                <input type="text" id="${memberId}_cf" class="cf"
                       value="${escHtml(data.cf || '')}"
                       placeholder="RSSMRA80A01H501U"
                       maxlength="16"
                       style="text-transform:uppercase"
                       oninput="this.value=this.value.toUpperCase(); autoSave(); validateCF(this)">
                <span class="field-error-msg"></span>
            </div>
            <div class="field-emergency">
                <label for="${memberId}_blood">Gruppo Sanguigno / Fattore Rh</label>
                <select id="${memberId}_blood" class="blood" onchange="autoSave()">
                    <option value="">Non specificato</option>
                    <option value="0+"  ${data.blood === '0+'  ? 'selected' : ''}>0+</option>
                    <option value="0-"  ${data.blood === '0-'  ? 'selected' : ''}>0-</option>
                    <option value="A+"  ${data.blood === 'A+'  ? 'selected' : ''}>A+</option>
                    <option value="A-"  ${data.blood === 'A-'  ? 'selected' : ''}>A-</option>
                    <option value="B+"  ${data.blood === 'B+'  ? 'selected' : ''}>B+</option>
                    <option value="B-"  ${data.blood === 'B-'  ? 'selected' : ''}>B-</option>
                    <option value="AB+" ${data.blood === 'AB+' ? 'selected' : ''}>AB+</option>
                    <option value="AB-" ${data.blood === 'AB-' ? 'selected' : ''}>AB-</option>
                </select>
            </div>
            <div class="field-emergency">
                <label for="${memberId}_phone">Telefono personale</label>
                <input type="tel" id="${memberId}_phone" class="phone"
                       value="${escHtml(data.phone || '')}"
                       placeholder="Es. +39 333 1234567"
                       maxlength="20"
                       oninput="autoSave(); validateMemberPhone(this)">
                <span class="field-error-msg"></span>
            </div>
            <div class="field-emergency span-2">
                <label for="${memberId}_medical">Note mediche (Patologie, Allergeni, Farmaci salvavita)</label>
                <textarea id="${memberId}_medical" class="medical auto-resize" rows="2" maxlength="500"
                          placeholder="Es. Diabete (prende insulina), allergia alla penicillina..."
                          oninput="autoSave(); autoResize(this)">${escHtml(data.medical || '')}</textarea>
            </div>
        </div>
    `;

    membersContainer.appendChild(div);
    div.querySelectorAll('textarea.auto-resize').forEach(ta => bindAutoResize(ta));
    updateProgress();
    updateScrollProgress();
}

function removeMember(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
    autoSave();
    updateScrollProgress();
}

/* ═══════════════════════════════════════
   SALVATAGGIO / CARICAMENTO
═══════════════════════════════════════ */

function collectData() {
    const members = [];
    document.querySelectorAll(".member-box").forEach(member => {
        members.push({
            name:    member.querySelector(".name").value,
            birth:   member.querySelector(".birth").value,
            cf:      member.querySelector(".cf").value,
            blood:   member.querySelector(".blood").value,
            phone:   member.querySelector(".phone").value,
            medical: member.querySelector(".medical").value
        });
    });

    const checks = {};
    checklistIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) checks[id] = el.checked;
    });

    const options = {};
    Object.entries(OPTION_IDS).forEach(([key, id]) => {
        const el = document.getElementById(id);
        if (el) options[key] = el.checked;
    });

    return {
        family:            document.getElementById("family").value,
        address:           document.getElementById("address").value,
        phone:             document.getElementById("phone").value,
        email:             document.getElementById("email").value,
        meeting1:          document.getElementById("meeting1").value,
        meeting2:          document.getElementById("meeting2").value,
        contact:           document.getElementById("contact").value,
        contactPhone:      document.getElementById("contactPhone").value,
        familyDoctor:      document.getElementById("familyDoctor").value,
        kitLocation:       document.getElementById("kitLocation").value,
        utilitiesLocation: document.getElementById("utilitiesLocation").value,
        specialNeeds:      document.getElementById("specialNeeds").value,
        householdId:       document.getElementById("householdId").value,
        members,
        checks,
        options
    };
}

function autoSave() {
    try {
        localStorage.setItem("emergencyPlanComplete", JSON.stringify(collectData()));
    } catch (error) {
        announceStatus("Impossibile salvare automaticamente i dati in questo browser.");
    }
    updateProgress();
}

function saveData() {
    try {
        localStorage.setItem("emergencyPlanComplete", JSON.stringify(collectData()));
        announceStatus("Piano d'emergenza salvato correttamente nel browser.");
    } catch (error) {
        announceStatus("Impossibile salvare il piano in questo browser.");
    }
}

/** Applica un oggetto-piano al form (usato da caricamento da localStorage e da import QR). */
function applyData(data) {
    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) { el.value = typeof val === 'string' ? val : ''; autoResize(el); }
    };

    Object.keys(QR_TOP_KEYS).forEach(key => setVal(key, data[key]));

    if (data.checks && typeof data.checks === 'object' && !Array.isArray(data.checks)) {
        checklistIds.forEach(id => {
            const el = document.getElementById(id);
            if (el && data.checks[id] !== undefined) el.checked = !!data.checks[id];
        });
    }

    if (data.options && typeof data.options === 'object' && !Array.isArray(data.options)) {
        Object.entries(OPTION_IDS).forEach(([key, id]) => {
            const el = document.getElementById(id);
            if (el && typeof data.options[key] === 'boolean') el.checked = data.options[key];
        });
    }

    membersContainer.innerHTML = "";
    if (Array.isArray(data.members) && data.members.length > 0) {
        data.members.forEach(m => addMember(
            m && typeof m === 'object' && !Array.isArray(m) ? m : {}
        ));
    } else {
        for (let i = 0; i < DEFAULT_MEMBERS_COUNT; i++) addMember();
    }
}

function loadData() {
    let saved = null;
    try {
        saved = localStorage.getItem("emergencyPlanComplete");
    } catch (error) {
        saved = null;
    }

    if (!saved) {
        for (let i = 0; i < DEFAULT_MEMBERS_COUNT; i++) addMember();
        return;
    }

    let data;
    try {
        data = JSON.parse(saved);
    } catch (error) {
        for (let i = 0; i < DEFAULT_MEMBERS_COUNT; i++) addMember();
        announceStatus("I dati salvati in precedenza non sono validi; è stato preparato un nuovo piano.");
        return;
    }

    if (!data || typeof data !== 'object' || Array.isArray(data)) {
        for (let i = 0; i < DEFAULT_MEMBERS_COUNT; i++) addMember();
        announceStatus("I dati salvati in precedenza non sono validi; è stato preparato un nuovo piano.");
        return;
    }

    applyData(data);
}

/* ═══════════════════════════════════════
   RIPRISTINO DA QR
   Formati accettati:
   - token compatto PFP1… (attuale): solo A-Z 0-9, con CRC, JSON compresso (deflate) in base32
   - JSON v2 in chiaro (vecchi PDF): {"doc":"PFE","v":2,...}
═══════════════════════════════════════ */

const TOKEN_RE = /(PF[PS]1)([0-9A-F]{8})([A-Z2-7]+)/;
const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

const CRC_TABLE = (() => {
    const table = new Uint32Array(256);
    for (let n = 0; n < 256; n++) {
        let c = n;
        for (let k = 0; k < 8; k++) c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
        table[n] = c >>> 0;
    }
    return table;
})();

function crc32(bytes) {
    let c = 0xFFFFFFFF;
    for (let i = 0; i < bytes.length; i++) c = CRC_TABLE[(c ^ bytes[i]) & 0xFF] ^ (c >>> 8);
    return (c ^ 0xFFFFFFFF) >>> 0;
}

function base32ToBytes(text) {
    const out = [];
    let buffer = 0;
    let bits = 0;
    for (const ch of text) {
        const value = BASE32_ALPHABET.indexOf(ch);
        if (value < 0) throw new Error('Carattere non valido nel codice.');
        buffer = (buffer << 5) | value;
        bits += 5;
        if (bits >= 8) {
            bits -= 8;
            out.push((buffer >> bits) & 0xFF);
            buffer &= (1 << bits) - 1;
        }
    }
    return new Uint8Array(out);
}

async function inflateRaw(bytes) {
    if (!('DecompressionStream' in window)) {
        throw new Error('Questo browser è troppo vecchio per leggere il QR. Aggiornalo oppure usa un altro browser.');
    }
    const stream = new Blob([bytes]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
    return new Uint8Array(await new Response(stream).arrayBuffer());
}

/** Decodifica un token PFP1/PFS1. Restituisce null se il testo non contiene un token. */
async function decodeToken(text) {
    const compact = String(text).toUpperCase().replace(/[^A-Z0-9]/g, '');
    const match = compact.match(TOKEN_RE);
    if (!match) return null;
    const [, type, crcHex, data] = match;

    const incomplete = () => new Error('Il codice risulta incompleto o alterato (controllo di integrità fallito). Rileggi il QR e incolla tutto il testo.');
    let bytes;
    try {
        bytes = await inflateRaw(base32ToBytes(data));
    } catch (error) {
        if (error.message && error.message.includes('browser')) throw error;
        throw incomplete();
    }
    if (crc32(bytes).toString(16).toUpperCase().padStart(8, '0') !== crcHex) throw incomplete();

    try {
        return { type, obj: JSON.parse(new TextDecoder('utf-8').decode(bytes)) };
    } catch (error) {
        throw incomplete();
    }
}

function setImportStatus(message, ok) {
    const el = document.getElementById('importStatus');
    if (!el) return;
    el.textContent = message;
    el.classList.toggle('text-success', !!ok);
    el.classList.toggle('text-danger', ok === false);
}

/** Vecchio formato: JSON in chiaro. Tollera entità HTML (&quot;), blocchi di codice e testo attorno. */
function parseLegacyJson(text) {
    const holder = document.createElement('textarea');
    holder.innerHTML = String(text);   // decodifica &quot; &amp; &#039; ...
    const raw = holder.value
        .replace(/^\uFEFF/, '')
        .trim()
        .replace(/^```(?:json)?\s*/i, '')
        .replace(/\s*```$/, '')
        .trim();
    const candidates = [raw];
    const start = raw.indexOf('{');
    const end = raw.lastIndexOf('}');
    if (start >= 0 && end > start && (start !== 0 || end !== raw.length - 1)) {
        candidates.push(raw.slice(start, end + 1));
    }

    for (const candidate of candidates) {
        try {
            let parsed = JSON.parse(candidate);
            if (typeof parsed === 'string') {
                try { parsed = JSON.parse(parsed); } catch (error) {}
            }
            return parsed;
        } catch (error) {}
    }

    throw new SyntaxError('Il testo non è un QR di piano familiare valido. Incolla il contenuto completo restituito dal lettore QR (per i piani nuovi inizia con PFP1).');
}

/** Dal testo del QR all'oggetto con chiavi abbreviate. */
async function readQrContent(text) {
    const token = await decodeToken(text);
    if (token) {
        if (token.type !== 'PFP1') {
            throw new Error('Questa è una mini-scheda personale, non un piano completo. Usa il QR della sezione "Dati del piano nel QR code".');
        }
        return token.obj;
    }

    const obj = parseLegacyJson(text);
    if (!obj || typeof obj !== 'object' || Array.isArray(obj) || obj.doc !== 'PFE' || obj.v !== 2) {
        throw new Error('Questo QR non è un piano familiare di emergenza.');
    }
    return obj;
}

/** Ricostruisce un oggetto-piano dalle chiavi abbreviate del QR. */
function planFromQr(obj) {
    if (!obj || typeof obj !== 'object' || Array.isArray(obj)) {
        throw new Error('Questo QR non è un piano familiare di emergenza.');
    }
    const str = v => (typeof v === 'string' ? v : '');
    const plan = { members: [], checks: {} };

    Object.entries(QR_TOP_KEYS).forEach(([long, short]) => { plan[long] = str(obj[short]); });

    (Array.isArray(obj.p) ? obj.p : []).slice(0, 20).forEach(m => {
        const row = {};
        Object.entries(QR_MEMBER_KEYS).forEach(([long, short]) => { row[long] = str(m && m[short]); });
        plan.members.push(row);
    });

    KIT_IDS.forEach(id => { plan.checks[id] = false; });
    (Array.isArray(obj.kit) ? obj.kit : []).forEach(k => {
        if (typeof k === 'string' && KIT_IDS.includes('kit_' + k)) plan.checks['kit_' + k] = true;
    });

    return plan;
}

async function importFromString(text) {
    try {
        const plan = planFromQr(await readQrContent(text));

        if (!confirm('Importare i dati del QR? Il piano compilato adesso verrà sostituito.')) {
            setImportStatus('Importazione annullata.');
            return;
        }

        applyData(plan);
        ensureHouseholdId();   // i vecchi QR non hanno l'ID del nucleo
        autoSave();
        setImportStatus('Piano ripristinato dal QR. Controlla i dati e completa i campi mancanti.', true);
    } catch (error) {
        setImportStatus(error.message || 'Contenuto non valido.', false);
    }
}

async function importFromText() {
    const text = document.getElementById('importText').value;
    if (text.trim() === '') {
        setImportStatus('Incolla prima il testo letto dal QR.', false);
        return;
    }
    await importFromString(text);
}

/* ─── Lettura del QR da immagine ─── */

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const element = new Image();
        element.onload = () => { URL.revokeObjectURL(url); resolve(element); };
        element.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Impossibile aprire l’immagine selezionata.')); };
        element.src = url;
    });
}

/** Disegna l'immagine in scala su sfondo bianco, con margine (quiet zone) attorno: i decoder ne hanno bisogno. */
function renderForDecode(image, scale) {
    const w = Math.max(1, Math.round(image.naturalWidth * scale));
    const h = Math.max(1, Math.round(image.naturalHeight * scale));
    const pad = Math.round(Math.max(w, h) * 0.12);
    const canvas = document.createElement('canvas');
    canvas.width = w + pad * 2;
    canvas.height = h + pad * 2;
    const context = canvas.getContext('2d', { willReadFrequently: true });
    if (!context) return null;
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.imageSmoothingEnabled = scale < 1;   // ingrandendo: pixel netti, senza sfocatura
    context.drawImage(image, pad, pad, w, h);
    return canvas;
}

/** Prova più scale (ingrandimento per screenshot piccoli, riduzione per foto enormi), con decoder nativo e jsQR. */
async function decodeQrFromImage(image) {
    const largest = Math.max(image.naturalWidth, image.naturalHeight);
    if (largest === 0) throw new Error('L’immagine selezionata non è leggibile.');

    const candidates = largest < 1000 ? [2, 3, 4, 1] : (largest > 2400 ? [0.5, 1, 0.35] : [1, 1.5, 0.7]);
    let scales = candidates.filter(s => largest * s >= 250 && largest * s <= 2800);
    if (scales.length === 0) scales = [Math.min(1, 2800 / largest)];

    const detector = 'BarcodeDetector' in window ? new BarcodeDetector({ formats: ['qr_code'] }) : null;

    for (let i = 0; i < scales.length; i++) {
        setImportStatus('Lettura del QR in corso… (tentativo ' + (i + 1) + ' di ' + scales.length + ')');
        await new Promise(resolve => setTimeout(resolve, 0));   // lascia respirare l'interfaccia

        const canvas = renderForDecode(image, scales[i]);
        if (!canvas) continue;

        if (detector) {
            try {
                const codes = await detector.detect(canvas);
                if (codes[0] && codes[0].rawValue) return codes[0].rawValue;
            } catch (error) {
                // si passa al decoder incluso nella pagina
            }
        }

        if (typeof window.jsQR === 'function') {
            const context = canvas.getContext('2d', { willReadFrequently: true });
            const pixels = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = window.jsQR(pixels.data, pixels.width, pixels.height, { inversionAttempts: 'attemptBoth' });
            if (code && code.data) return code.data;
        }
    }
    return null;
}

async function importFromImage(file) {
    if (!file) return;
    if (file.size > 15 * 1024 * 1024) {
        setImportStatus('L’immagine è troppo grande. Scegli un file da massimo 15 MB.', false);
        return;
    }

    if (typeof window.jsQR !== 'function' && !('BarcodeDetector' in window)) {
        setImportStatus('Lettore QR non disponibile. Ricarica la pagina e riprova.', false);
        return;
    }

    setImportStatus('Lettura del QR in corso…');
    try {
        const image = await loadImage(file);
        const qrText = await decodeQrFromImage(image);
        if (!qrText) {
            throw new Error('Nessun QR leggibile trovato. Ingrandisci il PDF (almeno 150%) prima dello screenshot, oppure usa una foto nitida con tutto il codice inquadrato; in alternativa leggi il QR con la fotocamera e incolla il testo qui sopra.');
        }
        await importFromString(qrText);
    } catch (error) {
        setImportStatus(error.message || 'Impossibile leggere l’immagine selezionata.', false);
    }
}

/* ═══════════════════════════════════════
   AUTO-RESIZE TEXTAREA
═══════════════════════════════════════ */

function autoResize(el) {
    if (!el || el.tagName !== 'TEXTAREA') return;
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

function bindAutoResize(el) {
    if (!el) return;
    el.style.overflow = 'hidden';
    el.addEventListener('input', function () { autoResize(this); });
    setTimeout(() => autoResize(el), 0);
}

/* ═══════════════════════════════════════
   BARRA DI AVANZAMENTO (compilazione campi)
═══════════════════════════════════════ */

function updateProgress() {
    // Escluse le opzioni del PDF (opt_*) e il campo nascosto dell'ID nucleo: non fanno parte della compilazione
    const fields = document.querySelectorAll(
        '#emergencyPlanForm input:not([type="button"]):not([type="hidden"]):not([id^="opt_"]), #emergencyPlanForm select, #emergencyPlanForm textarea'
    );
    let total = 0;
    let filled = 0;

    fields.forEach(el => {
        total++;
        if (el.type === 'checkbox') {
            if (el.checked) filled++;
        } else if (el.value.trim() !== '') {
            filled++;
        }
    });

    const percent = total > 0 ? Math.round((filled / total) * 100) : 0;

    const fillEl = document.getElementById('progressBarFill');
    const percentEl = document.getElementById('progressPercent');
    const trackEl = document.getElementById('progressBarTrack');
    if (fillEl) fillEl.style.width = percent + '%';
    if (percentEl) percentEl.textContent = percent + '%';
    if (trackEl) trackEl.setAttribute('aria-valuenow', String(percent));

    const ringEl = document.getElementById('progressRing');
    const percentRingEl = document.getElementById('progressPercentRing');
    if (ringEl) ringEl.style.setProperty('--percent', percent);
    if (percentRingEl) percentRingEl.textContent = percent + '%';
}

/* ═══════════════════════════════════════
   BARRA DI SCORRIMENTO (posizione nella pagina)
═══════════════════════════════════════ */

function updateScrollProgress() {
    const scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const percent = docHeight > 0 ? Math.min(100, Math.max(0, (scrollTop / docHeight) * 100)) : 0;
    const bar = document.getElementById('scrollProgressBar');
    if (bar) {
        const navbar = document.querySelector('.navbar.sticky-top');
        const navbarBottom = navbar ? navbar.getBoundingClientRect().bottom : 0;
        bar.style.top = Math.max(0, navbarBottom) + 'px';
        bar.style.width = percent + '%';
    }
}

/* ═══════════════════════════════════════
   GENERAZIONE PDF (checklist finale obbligatoria)
═══════════════════════════════════════ */

async function handlePrint() {
    const familyValid = validateNotEmpty(document.getElementById('family'), 'Inserisci il cognome della famiglia');
    const addressValid = validateNotEmpty(document.getElementById('address'), "Inserisci l'indirizzo di casa");
    const optionalInputsValid = [
        validatePhone(document.getElementById('phone')),
        validateEmail(document.getElementById('email')),
        validatePhone(document.getElementById('contactPhone'))
    ].every(Boolean);
    const membersValid = [...document.querySelectorAll('.member-box')].every(member => {
        const name = member.querySelector('.name');
        return (!name.value.trim() || validateMemberName(name))
            && validateCF(member.querySelector('.cf'))
            && validateMemberPhone(member.querySelector('.phone'));
    });

    if (!familyValid || !addressValid || !optionalInputsValid || !membersValid) {
        const invalidField = document.querySelector('#emergencyPlanForm .input-error');
        if (invalidField) {
            invalidField.focus();
            invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        announceStatus('Correggi i campi evidenziati prima di generare il PDF.');
        return;
    }

    const missing = REQUIRED_PRINT_CHECKS.filter(id => {
        const el = document.getElementById(id);
        return !el || !el.checked;
    });

    REQUIRED_PRINT_CHECKS.forEach(id => {
        const el = document.getElementById(id);
        const item = el ? el.closest('.check-item') : null;
        if (item) item.classList.toggle('check-item-missing', missing.includes(id));
    });

    if (missing.length > 0) {
        const firstCard = document.getElementById(missing[0])?.closest('.card-emergency');
        if (firstCard) firstCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const missingItem = document.getElementById(missing[0]);
        if (missingItem) missingItem.focus();
        announceStatus('Prima di generare il PDF, completa tutti i punti della "Checklist finale di controllo".');
        return;
    }

    ensureHouseholdId();

    announceStatus('Generazione del PDF in corso…');
    const button = document.querySelector('.accent-btn');
    if (button) {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    }

    try {
        const response = await fetch('/rischi/genera_piano_familiare', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/pdf' },
            body: JSON.stringify(collectData()),
            cache: 'no-store'
        });

        if (response.status === 429) {
            const err = new Error('rate-limit');
            err.userMessage = 'Hai generato molti PDF in poco tempo: riprova tra qualche minuto.';
            throw err;
        }
        if (!response.ok) {
            throw new Error(await response.text());
        }

        const blob = await response.blob();
        if (!blob.type || !blob.type.includes('pdf') || blob.size < 4) {
            throw new Error('Il server non ha restituito un PDF valido.');
        }

        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        const serverFilename = response.headers.get('X-PDF-Filename');
        link.download = serverFilename && /^[A-Za-z0-9._-]+\.pdf$/.test(serverFilename)
            ? serverFilename
            : 'piano-familiare-emergenza.pdf';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        announceStatus('PDF generato correttamente.');
    } catch (error) {
        const message = error.userMessage || 'Impossibile generare il PDF in questo momento.';
        announceStatus(error.userMessage || 'Il PDF non è stato generato. Riprova o verifica la connessione.');
        alert(message);
    } finally {
        if (button) {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        }
    }
}

/* ═══════════════════════════════════════
   UTILITY
═══════════════════════════════════════ */

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function announceStatus(message) {
    const status = document.getElementById('printStatus');
    if (status) {
        status.textContent = '';
        window.setTimeout(() => { status.textContent = message; }, 20);
    }
}

/* ═══════════════════════════════════════
   INIT
═══════════════════════════════════════ */

document.addEventListener("DOMContentLoaded", () => {
    loadData();
    ensureHouseholdId();
    document.querySelectorAll('textarea.auto-resize').forEach(ta => bindAutoResize(ta));

    // Checkbox (kit, checklist finale, opzioni PDF): salvataggio automatico
    document.querySelectorAll('.checklist-emergency input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
            autoSave();
            if (cb.checked) {
                const item = cb.closest('.check-item');
                if (item) item.classList.remove('check-item-missing');
            }
        });
    });

    const imageInput = document.getElementById('importImage');
    if (imageInput) {
        imageInput.addEventListener('change', () => {
            importFromImage(imageInput.files && imageInput.files[0]);
            imageInput.value = '';
        });
    }

    updateProgress();
    updateScrollProgress();

    const navbar = document.querySelector('.navbar.sticky-top');
    if (navbar && 'ResizeObserver' in window) {
        new ResizeObserver(updateScrollProgress).observe(navbar);
    }
});

window.addEventListener('scroll', updateScrollProgress, { passive: true });
window.addEventListener('resize', updateScrollProgress);
</script>

<?php 
include('../includes/footer.php'); 
?>