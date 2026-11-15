
<?php
// Elenco dei rischi: id sezione => [etichetta, icona Bootstrap, colore icona]
$rischi_menu = [
    'meteo'        => ['Meteo',            'cloud-lightning-rain', '#0b7285'],
    'neve'         => ['Neve',             'snowflake',            '#0b7285'],
    'terremoto'    => ['Terremoto',        'house-exclamation',    '#b02a37'],
    'alluvione'    => ['Alluvione',        'water',                '#0d6efd'],
    'incendio'     => ['Incendio boschivo','fire',                 '#b45309'],
    'vulcanico'    => ['Vulcanico',        'triangle-half',        '#6f42c1'],
    'frana'        => ['Frana',            'layers-half',          '#a0522d'],
    'valanga'      => ['Valanga',          'snow2',                '#0e7490'],
    'crisi-idrica' => ['Crisi idrica',     'droplet-half',         '#0d6efd'],
    'ciclone'      => ['Ciclone',          'hurricane',            '#198754'],
    'maremoto'     => ['Maremoto',         'tsunami',              '#0a58ca'],
    'nucleare'     => ['Nucleare',         'radioactive',          '#946c00'],
    'industriale'  => ['Industriale',      'factory',              '#495057'],
    'blackout'     => ['Blackout',         'lightning-charge',     '#c2410c'],
];
?>
<?php if (empty($is_print_view)): ?>
<div class="rk-layout">

    <nav class="rk-nav" aria-label="Scegli un rischio da consultare">
        <p class="rk-nav-title">Scegli un rischio</p>
        <ul class="rk-list">
            <?php foreach ($rischi_menu as $id => $r): ?>
            <li>
                <a href="#<?= $id ?>" data-target="<?= $id ?>" class="risk-btn rk-link">
                    <i class="bi bi-<?= $r[1] ?>" style="color: <?= $r[2] ?>;" aria-hidden="true"></i>
                    <span><?= $r[0] ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="rk-print" data-bs-toggle="modal" data-bs-target="#printModal">
            <i class="bi bi-printer" aria-hidden="true"></i> Stampa / Esporta PDF
        </button>
    </nav>

    <div class="rk-content">
<?php endif; ?>

<?php include __DIR__ . '/contenuti_rischi.php'; ?>

<?php if (empty($is_print_view)): ?>
    </div>
</div>

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

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sections = document.querySelectorAll(".risk-section");
    const buttons = document.querySelectorAll(".risk-btn");

    function showSection(id, scrollToTarget = true) {

        sections.forEach(section => section.classList.remove("active"));

        const target = document.getElementById(id);
        if (target) {
            target.classList.add("active");
        }

        buttons.forEach(btn => {
            if (btn.dataset.target === id) {
                btn.setAttribute("aria-current", "true");
                // Mantiene visibile la voce attiva quando il menu esce dal viewport.
                btn.scrollIntoView({ block: "nearest", inline: "nearest" });
            } else {
                btn.removeAttribute("aria-current");
            }
        });

        if (scrollToTarget && target) {
            target.scrollIntoView({
                block: "start",
                behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth"
            });
        }
    }

    // Sezione iniziale: da #hash se valido, altrimenti "meteo"
    const initialRisk = window.location.hash.slice(1);
    const initialSection = document.getElementById(initialRisk);
    const isRiskAnchor = initialSection && initialSection.classList.contains("risk-section");
    showSection(isRiskAnchor ? initialRisk : "meteo", isRiskAnchor);

    buttons.forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            const target = this.dataset.target;
            window.history.replaceState(window.history.state, "", "#" + target);
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

        printModalEl.addEventListener("show.bs.modal", buildPrintCheckboxes);

        if (printToggleAllBtn) {
            printToggleAllBtn.addEventListener("click", function () {
                const checkboxes = printSectionList.querySelectorAll(".print-section-checkbox");
                const tutteSelezionate = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => { cb.checked = !tutteSelezionate; });
                updateToggleAllLabel();
            });
        }

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
