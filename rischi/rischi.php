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

<?php include __DIR__ . '/contenuti_rischi.php'; ?>
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
