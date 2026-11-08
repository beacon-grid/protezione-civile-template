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

require_once __DIR__ . '/../php/config.php';
$site = $site ?? app_config()['site'];
require_once __DIR__ . '/../php/site_search.php';
$search_settings = site_search_settings(); ?>

<footer class="pt-5 pb-3 mt-5" aria-label="Piè di pagina istituzionale">
    <div class="container">
        <div class="row g-4">

            <div class="col-lg-4 col-md-6">
                <h3 class="footer-heading h5">
                    <i class="bi bi-shield-fill me-2" aria-hidden="true"></i>Protezione Civile
                </h3>

                <div class="d-flex align-items-start gap-3 mb-3">
                    <img src="/assets/img/logo-comune.png"
                         alt="<?= htmlspecialchars('Logo ufficiale della ' . $site['nome_breve']) ?>"
                         height="58"
                         style="flex-shrink: 0; filter: drop-shadow(0 2px 6px rgba(0,0,0,.4));"
                         onerror="this.style.display='none'">
                    <div>
                        <p class="footer-text mb-1">
                            <?= htmlspecialchars('Gruppo Comunale Volontari di Protezione Civile di ' . $site['address']['city']) ?>.
                        </p>
                        <p class="footer-text mb-0">
                            Operiamo sotto la direzione del Sindaco come prima autorità locale di
                            Protezione Civile per la tutela della cittadinanza e del territorio.
                        </p>
                    </div>
                </div>

                <div class="footer-privacy-box">
                    <i class="bi bi-info-circle-fill text-info me-1" aria-hidden="true"></i>
                    <strong>Nota Privacy:</strong> Questo portale è esclusivamente informativo,
                    non utilizza cookie di profilazione e rispetta pienamente le direttive GDPR.
                </div>
                <br>
                <!-- DATI ISTITUZIONALI DELL'ENTE (AGGIUNTI) -->
                <div class="mb-3 p-2 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); font-size: 0.8rem; opacity: 0.85;">
                    <div class="row g-1">
                        <div class="col-6"><strong>C.F.:</strong> <?= htmlspecialchars($site['comune']['cf'] ?? '8000XXXXXXX') ?></div>
                        <div class="col-6"><strong>P.IVA:</strong> <?= htmlspecialchars($site['comune']['piva'] ?? '0023XXXXXXX') ?></div>
                        <div class="col-12 mt-1" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 3px;">
                            <strong>Codice iPA:</strong> <code style="color: #ff9f43; background: none; padding: 0; font-size: 0.85rem;"><?= htmlspecialchars($site['comune']['ipa'] ?? 'c_' . strtolower($site['address']['province'] ?? 'xx')) ?></code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <h3 class="footer-heading h5">
                    <i class="bi bi-link-45deg me-2" aria-hidden="true"></i>Collegamenti Utili
                </h3>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="/"><i class="bi bi-chevron-right" aria-hidden="true"></i> Home Page</a>
                    </li>
                    <li>
                        <a href="/chi-siamo.php"><i class="bi bi-chevron-right" aria-hidden="true"></i> Chi Siamo e Struttura</a>
                    </li>
                    <li>
                        <a href="/#rischi"><i class="bi bi-chevron-right" aria-hidden="true"></i> Prevenzione Rischi</a>
                    </li>
                    <li>
                        <a href="/news/"><i class="bi bi-chevron-right" aria-hidden="true"></i> Notizie</a>
                    </li>
                    <?php if ($search_settings['enabled']): ?>
                    <li>
                        <a href="/cerca"><i class="bi bi-search" aria-hidden="true"></i> Cerca nel sito</a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="/privacy.php"><i class="bi bi-shield-check" aria-hidden="true"></i> Informativa Privacy</a>
                    </li>
                    <li>
                        <a href="/accessibilita.php"><i class="bi bi-universal-access-circle" aria-hidden="true"></i> Dichiarazione Accessibilità</a>
                    </li>
                    <li>
                        <a href="/assets/pdf/regolamento_protezione_civile.pdf"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Regolamento P.C. Comunale</a>
                    </li>
                    
                    <li class="footer-divider" aria-hidden="true"></li>

                    <li>
                        <a href="https://www.protezionecivile.gov.it" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Dipartimento Nazionale P.C.
                            <span class="visually-hidden">(Apre in una nuova finestra)</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($site['urls']['allerte_meteo_regionale']) ?>" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Allerta Meteo <?= htmlspecialchars($site['address']['region']) ?>
                            <span class="visually-hidden">(Apre in una nuova finestra)</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-4 col-12">
                <h3 class="footer-heading h5">
                    <i class="bi bi-telephone-fill me-2" aria-hidden="true"></i>Contatti e Sede
                </h3>
                <ul class="list-unstyled footer-contacts">
                    <li>
                        <i class="bi bi-geo-alt-fill footer-icons" aria-hidden="true"></i>
                        <span><strong>Sede:</strong> <?= htmlspecialchars($site['address']['street']) ?> — <?= htmlspecialchars($site['address']['postal_code'] . ' ' . $site['address']['city'] . ' (' . $site['address']['province'] . ')') ?></span>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill footer-icons" aria-hidden="true"></i>
                        <span><strong>Email:</strong>
                            <a href="mailto:<?= htmlspecialchars($site['emails']['primary']) ?>" class="footer-email-link"><?= htmlspecialchars($site['emails']['primary']) ?></a>
                        </span>
                    </li>
                    <li>
                        <i class="bi bi-telephone-forward-fill footer-icons" aria-hidden="true"></i>
                        <span><strong>Sala Operativa Regionale:</strong>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^+0-9]/', '', $site['phones']['sala_operativa_regionale'])) ?>" class="footer-phone-link"><?= htmlspecialchars($site['phones']['sala_operativa_regionale']) ?></a>
                        </span>
                    </li>
                </ul>

                <div class="footer-socials">
                    <span class="footer-socials-label">Seguici sui social</span>
                    <div class="footer-socials-icons">
                        <?php if (!empty($site['socials']['facebook'])): ?>
                            <a href="<?= htmlspecialchars($site['socials']['facebook']) ?>" class="footer-social-link facebook" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <i class="bi bi-facebook" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($site['socials']['instagram'])): ?>
                            <a href="<?= htmlspecialchars($site['socials']['instagram']) ?>" class="footer-social-link instagram" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <i class="bi bi-instagram" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($site['socials']['whatsapp'])): ?>
                            <a href="<?= htmlspecialchars($site['socials']['whatsapp']) ?>" class="footer-social-link whatsapp" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="footer-emergency-box">
                    <span class="footer-emergency-label">In Caso di Emergenza</span>
                    <a href="tel:<?= htmlspecialchars($site['phones']['emergency']) ?>" class="footer-emergency-number">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> <?= htmlspecialchars($site['phones']['emergency']) ?>
                    </a>
                    <small class="footer-emergency-sub">Numero Unico di Emergenza Europeo</small>
                </div>
            </div>

        </div>

        <hr class="footer-hr" aria-hidden="true">

        <div class="row align-items-center footer-bottom">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?php echo date('Y'); ?> — Comune di <?= htmlspecialchars($site['address']['city']) ?> / Gruppo Protezione Civile
            </div>
            <div class="col-md-6 text-center text-md-end">
                Sito Istituzionale realizzato su base Volontaria
            </div>
        </div>
    </div>
</footer>

<script src="/js/bootstrap.bundle.min.js"></script>
<?php if (empty($skip_glossary_enhancement)): ?>
<script src="/js/glossario.js"></script>
<?php endif; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlAPI = '/allerte/get_allerta.php';

    fetch(urlAPI)
        .then(response => {
            if (!response.ok) throw new Error('Risposta non valida: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const alertBar = document.querySelector('.alert-bar');
                const statoAllertaBox = document.querySelector('.stato-allerta');
                if (!alertBar || !statoAllertaBox) return;

                const stato = data.allerta_globale;
                const testo = " Stato Allerta: ";

                if (stato === 'VERDE') {
                    statoAllertaBox.innerHTML = '<i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>' + testo + "VERDE - Nessuna criticità segnalata";
                    alertBar.style.backgroundColor = '#0d6b02';
                    alertBar.style.color = '#ffffff';
                    alertBar.style.display = 'flex';
                } else if (stato === 'GIALLO') {
                    statoAllertaBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>' + testo + "GIALLO - Ordinaria criticità";
                    alertBar.style.backgroundColor = '#ffc107';
                    alertBar.style.color = '#212529';
                    alertBar.style.display = 'flex';
                    const linkComune = alertBar.querySelector('.link-comune');
                    if (linkComune) linkComune.style.setProperty('color', '#212529', 'important');
                } else if (stato === 'ARANCIONE') {
                    statoAllertaBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>' + testo + "ARANCIONE - Moderata criticità";
                    alertBar.style.backgroundColor = '#AA540C';
                    alertBar.style.color = '#ffffff';
                    alertBar.style.display = 'flex';
                } else if (stato === 'ROSSO') {
                    statoAllertaBox.innerHTML = '<i class="bi bi-shield-fill-x me-1" aria-hidden="true"></i>' + testo + "ROSSO - Elevata criticità";
                    alertBar.style.backgroundColor = '#ab0a19';
                    alertBar.style.color = '#ffffff';
                    alertBar.style.display = 'flex';
                }
            }
        })
        .catch(error => console.error('Errore widget allerta:', error));
});
</script>
</body>
</html>