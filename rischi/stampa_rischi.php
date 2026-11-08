<?php
// Flag letto da rischi.php: quando è true, il menu di navigazione e il
// modale "Esporta PDF" non vengono nemmeno generati (vedi rischi.php).
$is_print_view = true;

// Sezioni richieste, es: stampa_rischi.php?sezioni=meteo,terremoto,alluvione
// Se il parametro è assente o vuoto, vengono stampate TUTTE le sezioni.
$sezioniRichieste = [];
if (!empty($_GET['sezioni'])) {
    $sezioniRichieste = array_values(array_filter(array_map('trim', explode(',', $_GET['sezioni']))));
}
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $site ?? $config['site'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Stampa - Comportamenti in caso di Rischio</title>
<meta name="robots" content="noindex, nofollow">

<!-- Stile dedicato SOLO alla stampa, indipendente dal CSS a schermo del sito -->
<link rel="stylesheet" href="../css/stampa_rischi.css">
<link rel="stylesheet" href="../css/style.css">
    <link href="/css/bootstrap-icons.min.css"  rel="stylesheet">

</head>
<body class="stampa-rischi-page">

    <div class="ps-header" id="psHeader">
        <img src="/assets/img/logo.png" alt="Logo Protezione Civile" class="pc-logo">
        <div class="ps-header-text">
            <p class="ps-eyebrow">
                Scheda informativa — Protezione Civile <?php echo htmlspecialchars($site['nome_comune']); ?>
            </p>                
            <h2 id="ps-title"></h2>
        </div>
        <span class="ps-badge" id="ps-type"></span>
    </div>

        <div class="ps-emergency-banner" id="psEmergencyBanner">
        <h3 class="ps-emergency-title">Numeri utili in caso di emergenza</h3>
        <ul class="ps-emergency-list">
            <li><strong>112</strong> — Numero unico di emergenza (Carabinieri / soccorso generico)</li>
            <li><strong>113</strong> — Polizia di Stato</li>
            <li><strong>115</strong> — Vigili del Fuoco</li>
            <li><strong>118</strong> — Emergenza sanitaria</li>
            <li><strong>1530</strong> — Guardia Costiera (emergenze in mare)</li>
            <li><strong>1515</strong> — Emergenza ambientale / incendi boschivi (Carabinieri Forestali)</li>
        </ul>
        <h3 class="ps-emergency-title">In caso di emergenza, ricorda sempre di:</h3>
        <ul class="ps-emergency-list">
            <li>Mantenere la calma e seguire le indicazioni delle autorità locali e della Protezione Civile, evitando di diffondere o affidarsi a informazioni non ufficiali.</li>
            <li>Tenere a portata di mano un kit di emergenza (acqua, torcia, radio a batterie, medicinali essenziali, documenti) e verificare che il cellulare sia carico per poter chiamare o ricevere comunicazioni.</li>
        </ul>
        <p class="ps-emergency-note">
            In caso di pericolo immediato, chiama subito il <strong>112</strong>. 
            Segui le indicazioni delle autorità locali e della Protezione Civile.
        </p>
    </div>
    
    <?php
        // Includiamo i rischi per poi stamparlo (con stile personalizzato).
        // Grazie a $is_print_view = true, menu, modale e gli script che
        // servono solo per l'interfaccia a schermo non vengono generati.
        include('rischi.php');
    ?>

    <div class="no-print print-toolbar">
        <button type="button" id="printNowBtn">Stampa / Salva PDF</button>
        <button type="button" id="printCloseBtn">Chiudi</button>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

    const richieste = <?= json_encode($sezioniRichieste) ?>;
    const sections = document.querySelectorAll(".risk-section");

    sections.forEach(function (section) {
        const daMostrare = richieste.length === 0 || richieste.includes(section.id);
        section.classList.toggle("active", daMostrare);
        section.style.setProperty("display", daMostrare ? "block" : "none", "important");
    });

    // Intestazione sempre di default (blu PC), niente colore dinamico
    const psTitle = document.getElementById("ps-title");
    const psType = document.getElementById("ps-type");

    const sezioniVisibili = Array.from(sections).filter(function (section) {
        return richieste.length === 0 || richieste.includes(section.id);
    });

    psTitle.textContent = "Comportamenti in caso di Rischio";
    psType.textContent = richieste.length === 0
        ? "Rassegna completa"
        : sezioniVisibili.length + " sezioni selezionate";

    document.getElementById("printNowBtn").addEventListener("click", function () {
        window.print();
    });
    document.getElementById("printCloseBtn").addEventListener("click", function () {
        window.close();
    });

    window.addEventListener("afterprint", function () {
        window.close();
    });

    setTimeout(function () {
        window.print();
    }, 300);
});
</script>

</body>
</html>