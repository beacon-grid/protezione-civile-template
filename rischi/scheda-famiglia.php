<?php 
include('../includes/header.php'); 
?>
<link href="../css/scheda-famiglia.css" rel="stylesheet">



<div class="scroll-progress-bar d-print-none" id="scrollProgressBar"></div>

<div class="emergency-container" id="main-content" tabindex="-1">
    <form id="emergencyPlanForm" action="#" method="post" onsubmit="return false;">
        <div class="print-header">
            <div class="print-header-logo">
                <img src="../assets/img/logo.png" alt="<?= htmlspecialchars('Logo ' . $site['nome_breve']) ?>">
            </div>
            <div class="print-header-title">
                <h1>PIANO FAMILIARE DI EMERGENZA</h1>
                <!-- FIX: era h2, è un sottotitolo del h1 → p -->
                <p class="print-subtitle">Comune di <?= htmlspecialchars($site['address']['city']) ?></p>
                <p class="print-subtitle">Documento di protezione civile per la sicurezza del nucleo familiare</p>
            </div>
        </div>

        <div class="card-emergency card-highlight d-print-none">
            <h2>Compila il tuo Piano Familiare di Emergenza</h2>
            <div class="intro-block">
                <p><strong>I tuoi dati restano al sicuro sul tuo computer:</strong> le informazioni inserite non vengono inviate al sito, non vengono salvate online e non vengono trasmesse a server esterni.</p>
                <p>Un piano familiare aiuta l'intero nucleo a sapere esattamente cosa fare quando non c'è tempo per decidere con calma. È uno strumento fondamentale, specialmente in presenza di bambini, anziani, persone con disabilità, specifiche terapie mediche da seguire o animali domestici.</p>
                <p><strong>Come procedere:</strong> Compila il piano insieme a tutta la famiglia. Rileggilo almeno una volta all'anno e provvedi ad aggiornarlo tempestivamente qualora dovessero cambiare residenza, scuole, posti di lavoro, numeri telefonici o esigenze sanitarie.</p>
            </div>
        </div>

        <div class="progress-container d-print-none" role="region" aria-label="Percentuale di completamento del piano">
            <div class="progress-bar-view">
                <div class="progress-info">
                    <span>Completamento del tuo piano:</span>
                    <span id="progressPercent">0%</span>
                </div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
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
                    <input type="text" id="family"
                        placeholder="Es. Rossi"
                        maxlength="60"
                        autocomplete="family-name"
                        oninput="autoSave(); validateNotEmpty(this, 'Inserisci il cognome della famiglia')">
                    <span class="field-error-msg" id="err_family"></span>
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
                        oninput="autoSave(); validateNotEmpty(this, 'Inserisci l\'indirizzo di casa')">
                    <span class="field-error-msg" id="err_address"></span>
                </div>
                <div class="field-emergency">
                    <label for="phone">Telefono principale</label>
                    <input type="tel" id="phone"
                        placeholder="Es. +39 333 1234567"
                        maxlength="20"
                        autocomplete="tel"
                        oninput="autoSave(); validatePhone(this)">
                    <span class="field-error-msg" id="err_phone"></span>
                </div>
                <div class="field-emergency">
                    <label for="email">Email della famiglia</label>
                    <input type="email" id="email"
                        placeholder="nome@esempio.it"
                        maxlength="100"
                        autocomplete="email"
                        oninput="autoSave(); validateEmail(this)">
                    <span class="field-error-msg" id="err_email"></span>
                </div>
            </div>
        </div>

        <div class="card-emergency">
            <h2>2. Componenti della famiglia</h2>
            <p class="small text-muted d-print-none member-hint">Aggiungi i dettagli per ogni membro del nucleo familiare (inclusi bambini, ragazzi o anziani).</p>
            
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
                    <textarea id="meeting1" class="auto-resize" rows="1"
                            placeholder="Es. Area di attesa comunale / Piazza mercato"
                            oninput="autoSave()"></textarea>
                </div>
                <div class="field-emergency">
                    <label for="meeting2">Punto di ritrovo lontano da casa</label>
                    <textarea id="meeting2" class="auto-resize" rows="1"
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
                    <textarea id="utilitiesLocation" class="auto-resize" rows="1"
                            placeholder="Es. Gas in cucina sotto lavello, Acqua sul balcone, Luce ingresso"
                            oninput="autoSave()"></textarea>
                </div>
            </div>
        </div>

        <div class="card-emergency">
            <h2>4. Esigenze specifiche e Note Sanitarie</h2>
            <div class="field-emergency">
                <label for="specialNeeds">Farmaci indispensabili, ausili, disabilità o allergie gravi</label>
                <textarea id="specialNeeds" class="auto-resize" rows="2"
                        placeholder="Indica qui i farmaci salvavita o continuativi, necessità e ausili particolari per persone fragili o indicazioni sanitarie d'urgenza..."
                        oninput="autoSave()"></textarea>
            </div>
        </div>

        <div class="card-emergency">
            <h2>5. Verifica dotazione del Kit di Emergenza</h2>
            <div class="checklist-emergency">
                <label class="check-item"><input type="checkbox" id="kit_acqua"> Acqua potabile (almeno 1.5L/die)</label>
                <label class="check-item"><input type="checkbox" id="kit_farmaci"> Farmaci abituali</label>
                <label class="check-item"><input type="checkbox" id="kit_soccorso"> Kit pronto soccorso</label>
                <label class="check-item"><input type="checkbox" id="kit_torcia"> Torcia elettrica (No candele)</label>
                <label class="check-item"><input type="checkbox" id="kit_radio"> Radio a pile / manovella</label>
                <label class="check-item"><input type="checkbox" id="kit_batterie"> Pile extra / Powerbank</label>
                <label class="check-item"><input type="checkbox" id="kit_documenti"> Copie dei documenti</label>
                <label class="check-item"><input type="checkbox" id="kit_vestiti"> Cambio di vestiti / Coperta</label>
                <label class="check-item"><input type="checkbox" id="kit_chiavi"> Chiavi di riserva casa/auto</label>
                <label class="check-item"><input type="checkbox" id="kit_contanti"> Denaro in contanti</label>
                <label class="check-item"><input type="checkbox" id="kit_igiene"> Salviette / Sacchi rifiuti</label>
                <label class="check-item"><input type="checkbox" id="kit_cibo"> Cibo non deperibile pronto</label>
            </div>
        </div>

        <div class="card-emergency d-print-none">
            <h2>Consigli di Protezione Civile per i membri della famiglia</h2>
            
            <h3>👶 Consigli per famiglie con bambini</h3>
            <ul class="advice-list text-muted small">
                <li>Spiega ai bambini cosa potrebbe succedere con parole semplici, evitando racconti o immagini spaventose.</li>
                <li>Assicurati che sappiano dire il loro nome completo, l'indirizzo di casa e almeno un numero di cellulare dei genitori.</li>
                <li>Insegna loro a riconoscere il suono dei sistemi di allarme e a seguire tempestivamente l'adulto di riferimento.</li>
                <li>Fai delle piccole simulazioni sotto forma di gioco per raggiungere insieme il punto di ritrovo scelto.</li>
                <li>Inserisci una nota identificativa o un contatto utile nello zainetto di scuola o del kit, ed inserisci un loro piccolo oggetto rassicurante (es. un peluche).</li>
            </ul>

            <h3>👵 Consigli per famiglie con anziani</h3>
            <ul class="advice-list text-muted small">
                <li>Verifica costantemente che i corridoi e le vie di fuga della casa siano liberi da ostacoli mobili o scatoloni.</li>
                <li>Tieni una lista scritta, chiara e sempre aggiornata di tutti i farmaci assunti quotidianamente.</li>
                <li>Se la persona vive sola, lascia una copia delle chiavi di casa a un familiare o a un vicino di fiducia (previo consenso).</li>
                <li>Configura il telefono cellulare o il salvavita posizionando i numeri di emergenza chiave in chiamata rapida.</li>
                <li>Riponi ausili essenziali (occhiali da vista, apparecchi acustici con batterie, bastoni) in un punto della stanza fisso e facile da afferrare.</li>
            </ul>

            <h3>♿ Consigli per persone con disabilità o bisogni specifici</h3>
            <ul class="advice-list text-muted small">
                <li>Includi esplicitamente nel piano tutti gli ausili tecnici vitali (es. sedia a rotelle, comunicatori, protesi).</li>
                <li><strong>Apparecchiature elettromedicali:</strong> Se dipendi da dispositivi alimentati a corrente, concorda in anticipo un piano d'azione logistico con il tuo medico e contatta il gestore della rete elettrica per le tutele previste.</li>
                <li>Individua e accordati preventivamente con almeno due persone di fiducia (vicini di casa, parenti) pronte ad aiutarti durante una rapida evacuazione.</li>
                <li>Studia percorsi d'uscita accessibili e valuta valide alternative qualora ascensori o rampe principali risultassero bloccati.</li>
            </ul>

            <h3>🐾 Consigli per gli animali domestici</h3>
            <ul class="advice-list text-muted small">
                <li>Includi nel kit d'emergenza cibo dedicato, acqua extra, ciotole leggere, sacchetti igienici e i loro specifici farmaci veterinari.</li>
                <li>Tieni sempre pronti e vicini alla porta d'uscita principale il guinzaglio, la pettorina, il trasportino ed una museruola.</li>
                <li>Conserva in una busta sigillata il libretto sanitario aggiornato, i dati del microchip e una fotografia recente dell'animale.</li>
                <li>Identifica in anticipo un amico, un parente o una struttura d'accoglienza disposta a ospitarli se la casa dovesse risultare inagibile.</li>
            </ul>
        </div>

        <div class="card-emergency d-print-none">
            <h2>Checklist finale di controllo</h2>
            <div class="checklist-emergency">
                <label class="check-item"><input type="checkbox" id="chk_f1"> Piano compilato e stampato</label>
                <label class="check-item"><input type="checkbox" id="chk_f2"> Piano conservato in luogo accessibile</label>
                <label class="check-item"><input type="checkbox" id="chk_f3"> Individuate le aree di attesa comunali</label>
                <label class="check-item"><input type="checkbox" id="chk_f4"> Punti di ritrovo concordati con tutti</label>
                <label class="check-item"><input type="checkbox" id="chk_f5"> Notificato il piano al referente esterno</label>
                <label class="check-item"><input type="checkbox" id="chk_f6"> Tutti sanno individuare i rubinetti generali</label>
                <label class="check-item"><input type="checkbox" id="chk_f7"> Scaricata l'app "Where Are U" (112)</label>
                <label class="check-item"><input type="checkbox" id="chk_f8"> Piano condiviso con anziani e bambini</label>
            </div>
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
            <div class="actions-emergency">
                <button type="button" class="btn-emergency accent-btn" onclick="handlePrint()">
                    🖨️ Stampa / Genera PDF
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const membersContainer = document.getElementById("members");

// Una famiglia raramente è composta da una sola persona: partiamo con più schede componente pronte
const DEFAULT_MEMBERS_COUNT = 2;

const checklistIds = [
    "kit_acqua", "kit_farmaci", "kit_soccorso", "kit_torcia", "kit_radio", "kit_batterie",
    "kit_documenti", "kit_vestiti", "kit_chiavi", "kit_contanti", "kit_igiene", "kit_cibo",
    "chk_f1", "chk_f2", "chk_f3", "chk_f4", "chk_f5", "chk_f6", "chk_f7", "chk_f8"
];

/* ═══════════════════════════════════════
   VALIDAZIONI
═══════════════════════════════════════ */

function setFieldState(input, isValid, errorMsg) {
    const errEl = document.getElementById('err_' + input.id);
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
        const errEl = document.getElementById('err_' + input.id);
        if (errEl) { errEl.textContent = ''; errEl.classList.remove('visible'); }
        return true;
    }
    const digits = val.replace(/\D/g, '');
    const ok =
        /^[0-9\s\+\-\(\)\/]+$/.test(val) &&
        digits.length >= 6 &&
        digits.length <= 15 &&
        !/^[1-9]{1}$/.test(digits);
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

    // FIX: tutti i campi hanno id dinamico e label con for corrispondente
    div.innerHTML = `
        <button type="button" class="remove-member-btn d-print-none" onclick="removeMember('${memberId}')">Rimuovi</button>
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
                <textarea id="${memberId}_medical" class="medical auto-resize" rows="2"
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
        members,
        checks
    };
}

function autoSave() {
    localStorage.setItem("emergencyPlanComplete", JSON.stringify(collectData()));
    updateProgress();
}

function saveData() {
    localStorage.setItem("emergencyPlanComplete", JSON.stringify(collectData()));
    alert("Piano d'emergenza salvato correttamente nel browser.");
}

function loadData() {
    const saved = localStorage.getItem("emergencyPlanComplete");

    if (!saved) {
        for (let i = 0; i < DEFAULT_MEMBERS_COUNT; i++) addMember();
        return;
    }

    const data = JSON.parse(saved);

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) { el.value = val || ''; autoResize(el); }
    };

    setVal("family",            data.family);
    setVal("address",           data.address);
    setVal("phone",             data.phone);
    setVal("email",             data.email);
    setVal("meeting1",          data.meeting1);
    setVal("meeting2",          data.meeting2);
    setVal("contact",           data.contact);
    setVal("contactPhone",      data.contactPhone);
    setVal("familyDoctor",      data.familyDoctor);
    setVal("kitLocation",       data.kitLocation);
    setVal("utilitiesLocation", data.utilitiesLocation);
    setVal("specialNeeds",      data.specialNeeds);

    if (data.checks) {
        checklistIds.forEach(id => {
            const el = document.getElementById(id);
            if (el && data.checks[id] !== undefined) el.checked = data.checks[id];
        });
    }

    membersContainer.innerHTML = "";
    if (data.members && data.members.length > 0) {
        data.members.forEach(m => addMember(m));
    } else {
        for (let i = 0; i < DEFAULT_MEMBERS_COUNT; i++) addMember();
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
    const fields = document.querySelectorAll(
        '#emergencyPlanForm input:not([type="button"]), #emergencyPlanForm select, #emergencyPlanForm textarea'
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
    if (fillEl) fillEl.style.width = percent + '%';
    if (percentEl) percentEl.textContent = percent + '%';

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
    if (bar) bar.style.width = percent + '%';
}

/* ═══════════════════════════════════════
   STAMPA CONTROLLATA (checklist finale obbligatoria)
═══════════════════════════════════════ */

const REQUIRED_PRINT_CHECKS = [
    "chk_f1", "chk_f2", "chk_f3", "chk_f4",
    "chk_f5", "chk_f6", "chk_f7", "chk_f8"
];

function handlePrint() {
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
        alert('Prima di stampare, completa tutti i punti della "Checklist finale di controllo" (evidenziati in rosso).');
        return;
    }

    window.print();
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

/* ═══════════════════════════════════════
   INIT
═══════════════════════════════════════ */

document.addEventListener("DOMContentLoaded", () => {
    loadData();
    document.querySelectorAll('textarea.auto-resize').forEach(ta => bindAutoResize(ta));

    // I checkbox (kit emergenza + checklist finale) non avevano un listener: li agganciamo qui
    document.querySelectorAll('.checklist-emergency input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
            autoSave();
            if (cb.checked) {
                const item = cb.closest('.check-item');
                if (item) item.classList.remove('check-item-missing');
            }
        });
    });

    updateProgress();
    updateScrollProgress();
});

window.addEventListener('scroll', updateScrollProgress, { passive: true });
window.addEventListener('resize', updateScrollProgress);
</script>

<?php 
include('../includes/footer.php'); 
?>