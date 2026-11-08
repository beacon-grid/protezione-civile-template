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

require_once __DIR__ . '/php/config.php';
$config = app_config();
$site = $config['site'];

// Meta specifici di questa pagina
$meta_title       = 'Privacy e Licenza Contenuti - ' . $site['nome_breve'];
$meta_description = 'Informativa sulla privacy, sui cookie e licenza dei contenuti del sito di ' . $site['nome_ente'] . '.';
$meta_url         = $site['urls']['home'] . '/privacy.php';

/**
 * Data di ultimo aggiornamento di QUESTA pagina.
 * Aggiornare manualmente questa costante ogni volta che il testo
 * dell'informativa viene modificato in modo sostanziale.
 */
const PRIVACY_LAST_UPDATE = '21 luglio 2026';

/**
 * Licenza applicata ai contenuti editoriali del sito (testi, news, materiali
 * informativi) - DISTINTA dalla licenza del codice sorgente (AGPL-3.0).
 * Personalizzare scegliendo una delle opzioni descritte più sotto nella pagina.
 * Valori tipici: 'CC BY 4.0', 'CC BY-NC-SA 4.0', 'Tutti i diritti riservati'.
 */
$content_license = $site['content_license'] ?? 'Tutti i diritti riservati';

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <script src="/js/banda-check-config.js"></script>
    <script src="/js/banda-check.js"></script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="geo.region" content="IT-<?= htmlspecialchars($site['address']['province']) ?>">
    <meta name="geo.placename" content="<?= htmlspecialchars($site['address']['city']) ?>">
    <meta name="theme-color" content="<?= htmlspecialchars($site['colore_tema']) ?>">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords"    content="<?= htmlspecialchars($site['meta_keywords']) ?>">
    <meta name="author"      content="<?= htmlspecialchars($site['meta_author']) ?>">
    <meta name="robots"      content="index, follow">
    <link rel="canonical"    href="<?= htmlspecialchars($meta_url) ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= htmlspecialchars($meta_url) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($meta_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($site['og_image']) ?>">
    <meta property="og:image:alt"   content="<?= htmlspecialchars($site['nome_breve'] . ' - logo del gruppo') ?>">
    <meta property="og:locale"      content="it_IT">
    <meta property="og:site_name"   content="<?= htmlspecialchars($site['nome_breve']) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= htmlspecialchars($meta_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($site['og_image']) ?>">

    <link rel="icon" type="image/x-icon" href="/assets/img/logo.png">

    <link href="/css/bootstrap.min.css"        rel="stylesheet">
    <link href="/css/bootstrap-icons.min.css"  rel="stylesheet">
    <link href="/css/style.css"                rel="stylesheet">
    <link href="/css/glossario.css"            rel="stylesheet">
    
    <!-- Structured Data JSON-LD: Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "parentOrganization": {
            "@type": "GovernmentOrganization",
            "name": <?= json_encode('Comune di ' . $site['nome_comune']) ?>
        },
        "name":          <?= json_encode($site['nome_ente']) ?>,
        "alternateName": <?= json_encode($site['nome_breve']) ?>,
        "description":   <?= json_encode('Sito informativo dei volontari di protezione civile di ' . $site['nome_comune'] . ' (' . $site['address']['province'] . '), gestito autonomamente da volontari del gruppo') ?>,
        "url":           <?= json_encode($site['urls']['home']) ?>,
        "logo":          <?= json_encode($site['og_image']) ?>,
        "image":         <?= json_encode($site['og_image']) ?>,
        "address": {
            "@type":           "PostalAddress",
            "streetAddress":   <?= json_encode($site['address']['street']) ?>,
            "addressLocality": <?= json_encode($site['address']['city']) ?>,
            "addressRegion":   <?= json_encode($site['address']['region']) ?>,
            "postalCode":      <?= json_encode($site['address']['postal_code']) ?>,
            "addressCountry":  <?= json_encode($site['address']['country']) ?>
        },
        "telephone": <?= json_encode($site['phones']['sede']) ?>,
        "email":     <?= json_encode($site['emails']['primary']) ?>,
        "contactPoint": {
            "@type":       "ContactPoint",
            "contactType": "Informazioni",
            "telephone":   <?= json_encode($site['phones']['sede']) ?>,
            "email":       <?= json_encode($site['emails']['primary']) ?>
        },
        "areaServed": {
            "@type": "City",
            "name":  <?= json_encode($site['address']['city']) ?>,
            "containedInPlace": {
                "@type": "State",
                "name":  <?= json_encode($site['address']['province_name']) ?>
            }
        },
        "knowsAbout": [
            {
                "@type":       "DefinedTerm",
                "name":        "Protezione Civile",
                "description": "Sistema integrato di enti e soggetti pubblici e privati che svolgono attività di previsione, prevenzione e mitigazione dei rischi, gestione delle emergenze e superamento dei loro effetti.",
                "url":         "https://www.protezionecivile.gov.it",
                "sameAs":      "https://www.wikidata.org/wiki/Q206936"
            },
            {
                "@type":       "DefinedTerm",
                "name":        "Gestione dell'Emergenza",
                "description": "Discipline di gestione e prevenzione di disastri sia naturali che antropici.",
                "url":         "https://www.protezionecivile.gov.it/it/emergenze",
                "sameAs":      "https://www.wikidata.org/wiki/Q1460420"
            },
            {
                "@type":       "DefinedTerm",
                "name":        "Volontariato di Protezione Civile",
                "description": "Organizzazioni di volontari integrate nel sistema nazionale italiano di protezione civile per attività di prevenzione, preparazione e soccorso.",
                "url":         "https://www.protezionecivile.gov.it/it/volontariato",
                "keywords": [
                    "volontariato protezione civile",
                    "organizzazioni di soccorso",
                    "primo intervento emergenza",
                    <?= json_encode('protezione civile ' . $site['nome_comune']) ?>,
                    <?= json_encode('volontari ' . $site['nome_comune']) ?>,
                    <?= json_encode('protezione civile ' . $site['address']['province_name']) ?>
                ]
            },
            {
                "@type":       "DefinedTerm",
                "name":        <?= json_encode('Protezione Civile Locale - ' . $site['nome_comune']) ?>,
                "description": <?= json_encode('Gruppo volontari di protezione civile operativo in ' . $site['nome_comune'] . ', ' . $site['address']['province'] . ' per prevenzione, preparazione e soccorso nelle emergenze locali.') ?>,
                "url":         <?= json_encode($site['urls']['home']) ?>,
                "areaServed": {
                    "@type": "City",
                    "name":  <?= json_encode($site['address']['city']) ?>
                },
                "keywords": [
                    <?= json_encode('protezione civile ' . $site['nome_comune']) ?>,
                    <?= json_encode('volontari protezione civile ' . $site['nome_comune']) ?>,
                    <?= json_encode('emergenza ' . $site['nome_comune']) ?>,
                    <?= json_encode('soccorso provincia ' . $site['address']['province_name']) ?>
                ]
            }
        ]
    }
    </script>
</head>
<body>
<main id="main-content" class="container py-5">

    <h1 class="mb-4">Privacy, Cookie e Licenza dei Contenuti</h1>

    <div class="note-box mb-4">
        <strong>Nota:</strong> questo testo è un modello generico, pensato per un sito di tipo
        "vetrina" che non raccoglie dati dai visitatori pubblici. Non costituisce consulenza legale.
        In caso di future integrazioni (moduli di contatto, iscrizione volontari, newsletter,
        strumenti di analisi del traffico) è necessario aggiornarlo e farlo verificare da un
        professionista competente in materia di privacy o dal proprio RPD/DPO, se presente.
    </div>

    <h2 class="mt-5">Parte 1: Informativa sulla Privacy e sui Cookie</h2>

    <h3>1.1 Titolare del trattamento</h3>
    <p>
        Il Titolare del trattamento dei dati relativi al presente sito è
        <strong><?= htmlspecialchars($site['nome_ente']) ?></strong>,
        con sede in <?= htmlspecialchars($site['address']['street']) ?>,
        <?= htmlspecialchars($site['address']['postal_code']) ?>
        <?= htmlspecialchars($site['address']['city']) ?>
        (<?= htmlspecialchars($site['address']['province']) ?>),
        contattabile all'indirizzo email
        <a href="mailto:<?= htmlspecialchars($site['emails']['primary']) ?>">
            <?= htmlspecialchars($site['emails']['primary']) ?>
        </a>
        <?php if (!empty($site['emails']['pec'])): ?>
            o via PEC all'indirizzo
            <a href="mailto:<?= htmlspecialchars($site['emails']['pec']) ?>">
                <?= htmlspecialchars($site['emails']['pec']) ?>
            </a>
        <?php endif; ?>.
    </p>

    <h3>1.2 Natura del sito e dati trattati</h3>
    <p>
        Questo sito ha natura puramente informativa ("sito vetrina"): fornisce contenuti relativi
        alle attività del Gruppo/Associazione di Protezione Civile di
        <?= htmlspecialchars($site['nome_comune']) ?> senza raccogliere, memorizzare o trasmettere
        al server alcun dato personale dei visitatori.
    </p>
    <p>In particolare:</p>
    <ul>
        <li>Il sito <strong>non contiene moduli di contatto, form di iscrizione o altri strumenti</strong>
            che inviano dati al server durante la normale consultazione da parte del pubblico;</li>
        <li>Il sito <strong>non utilizza cookie di profilazione, cookie di terze parti o strumenti di
            tracciamento/analisi statistica</strong> (es. Google Analytics, Meta Pixel o simili);</li>
        <li>Il sito <strong>non incorpora contenuti di terze parti</strong> che possano impostare
            cookie o raccogliere dati durante la normale navigazione, salvo quanto eventualmente
            specificato in una sezione dedicata, se applicabile al caso concreto.</li>
    </ul>
    <p>
        La consultazione ordinaria del sito da parte del cittadino, quindi, <strong>non comporta
        alcun trattamento di dati personali</strong> da parte di <?= htmlspecialchars($site['nome_ente']) ?>.
    </p>

    <h3>1.3 Cookie tecnici e area riservata</h3>
    <p>
        L'unico trattamento di dati effettuato dal sito riguarda l'<strong>area riservata di
        amministrazione</strong> (<a href="/admin/">/admin/</a>), accessibile esclusivamente al
        personale e ai volontari autorizzati alla gestione e manutenzione dei contenuti (es.
        pubblicazione news, aggiornamento allerte).
    </p>
    <p>
        Per consentire l'autenticazione a questa area, il sito utilizza <strong>esclusivamente
        cookie tecnici di sessione</strong>, con le seguenti caratteristiche:
    </p>
    <ul>
        <li>hanno la sola finalità di mantenere l'autenticazione dell'utente autorizzato durante la
            sessione di lavoro nel pannello di amministrazione;</li>
        <li>non vengono utilizzati per identificare, tracciare o profilare i visitatori pubblici del
            sito;</li>
        <li>vengono eliminati automaticamente alla chiusura della sessione o del browser, e comunque
            non sono utilizzati fuori dal contesto di autenticazione dell'area riservata;</li>
        <li>non richiedono consenso preventivo secondo la normativa vigente (art. 122 Codice Privacy
            e Provvedimento Cookie del Garante), trattandosi di cookie tecnici strettamente
            necessari all'erogazione del servizio richiesto dall'utente stesso (l'accesso all'area
            riservata).</li>
    </ul>
    <p>
        Questi cookie riguardano quindi <strong>esclusivamente il personale interno autorizzato</strong>,
        e non il cittadino che consulta il sito pubblicamente.
    </p>

    <h3>1.4 Diritti dell'interessato</h3>
    <p>
        Il personale autorizzato che accede all'area riservata, in qualità di interessato al
        trattamento relativo alla propria autenticazione, può esercitare in ogni momento i diritti
        previsti dagli articoli 15-22 del Regolamento (UE) 2016/679 (GDPR), tra cui il diritto di
        accesso, rettifica, cancellazione, limitazione del trattamento e opposizione, contattando il
        Titolare agli indirizzi indicati al punto 1.1.
    </p>

    <h3>1.5 Modifiche a questa informativa</h3>
    <p>
        Questa informativa può essere aggiornata in caso di modifiche alle modalità di funzionamento
        del sito. È responsabilità di <?= htmlspecialchars($site['nome_ente']) ?> mantenerla coerente
        con l'effettivo funzionamento del sito, aggiornandola tempestivamente qualora vengano
        introdotte nuove funzionalità che comportano il trattamento di dati personali dei visitatori.
    </p>
    <p><em>Ultimo aggiornamento di questa sezione: <?= htmlspecialchars(PRIVACY_LAST_UPDATE) ?></em></p>

    <hr class="my-5">

    <h2>Parte 2: Licenza dei Contenuti</h2>

    <div class="note-box mb-4">
        <strong>Nota per chi adotta questo template:</strong> questa sezione riguarda la licenza dei
        <em>contenuti editoriali</em> pubblicati sul sito (testi, notizie, foto, materiali
        informativi) — cosa distinta dalla licenza del <em>codice sorgente</em> del sito, distribuito
        sotto licenza AGPL-3.0. È l'Ente/associazione che pubblica il sito reale a dover scegliere la
        licenza più adatta ai propri contenuti.
        <!--impostando 'content_license' in config.php.-->
    </div>

    <h3>2.1 Distinzione tra codice e contenuti</h3>
    <p>
        Il <strong>codice sorgente</strong> di questo sito (template PHP, script, fogli di stile) è
        distribuito con licenza <strong>GNU Affero General Public License v3.0 (AGPL-3.0)</strong>,
        come indicato nel repository di origine del progetto.
    </p>
    <p>
        I <strong>contenuti pubblicati</strong> da <?= htmlspecialchars($site['nome_ente']) ?>
        attraverso questo sito (testi delle news, comunicati, materiale informativo, fotografie,
        loghi ed emblemi ufficiali) sono soggetti a una licenza distinta, di seguito specificata, e
        restano di titolarità di <?= htmlspecialchars($site['nome_ente']) ?> o dei rispettivi
        autori/fonti citate.
    </p>

    <h3>2.2 Licenza applicata ai contenuti di questo sito</h3>
    <p>
        Salvo diversa indicazione specifica su singoli contenuti, i testi e i materiali informativi
        pubblicati su questo sito sono rilasciati con la seguente licenza:
    </p>

    <div class="p-3 mb-3 border rounded">
        <strong class="d-block mb-2">Licenza attualmente configurata:
            <?= htmlspecialchars($content_license) ?>
        </strong>

        <?php if ($content_license === 'CC BY 4.0'): ?>
            <p class="mb-0">
                <strong>Creative Commons Attribuzione 4.0 Internazionale (CC BY 4.0)</strong> —
                chiunque può riprodurre, distribuire e rielaborare i contenuti, anche per usi
                commerciali, a condizione di citare la fonte
                (<?= htmlspecialchars($site['nome_ente']) ?>).
                <a href="https://creativecommons.org/licenses/by/4.0/deed.it" target="_blank" rel="noopener">
                    Testo della licenza
                </a>.
            </p>
        <?php elseif ($content_license === 'CC BY-NC-SA 4.0'): ?>
            <p class="mb-0">
                <strong>Creative Commons Attribuzione - Non Commerciale - Condividi allo stesso modo
                4.0 (CC BY-NC-SA 4.0)</strong> — riproduzione consentita con citazione della fonte,
                solo per scopi non commerciali; le rielaborazioni vanno ridistribuite con la stessa
                licenza.
                <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.it" target="_blank" rel="noopener">
                    Testo della licenza
                </a>.
            </p>
        <?php else: ?>
            <p class="mb-0">
                <strong>Tutti i diritti riservati</strong> — nessuna riproduzione dei contenuti di
                questo sito è consentita senza autorizzazione scritta di
                <?= htmlspecialchars($site['nome_ente']) ?>, salvo le eccezioni previste dalla legge
                (es. diritto di cronaca, citazione a fini di critica).
            </p>
        <?php endif; ?>
    </div>

    <p>
        Restano in ogni caso esclusi da qualsiasi licenza di riutilizzo: loghi, stemmi ed emblemi
        ufficiali di <?= htmlspecialchars($site['nome_ente']) ?> e del Servizio Nazionale della
        Protezione Civile, il cui uso è disciplinato dalla normativa vigente in materia (art. 30,
        D.Lgs. 1/2018 e relativo regolamento attuativo) e non dalla licenza scelta per gli altri
        contenuti.
    </p>

    <h3>2.3 Materiali di terzi</h3>
    <p>
        Qualora il sito includa contenuti non originali (es. immagini, mappe, dati meteorologici o
        bollettini provenienti da fonti esterne quali il Dipartimento Nazionale della Protezione
        Civile, Regione, ARPA o altri enti), tali materiali restano soggetti alla licenza d'uso
        stabilita dalla fonte originaria, che deve essere citata in modo chiaro nei pressi del
        contenuto stesso.
    </p>

    <p><em>Ultimo aggiornamento di questa sezione: <?= htmlspecialchars(PRIVACY_LAST_UPDATE) ?></em></p>

</main>
</body>
</html>