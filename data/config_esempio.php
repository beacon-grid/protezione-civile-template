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

$nome_comune = 'Esempio';
$chiaveSyncAlert = 'ProtezioneCivileEsempio01!';
$config = [
    'site' => [
        'colore_tema' => '#003B7A', // deve corrispondere a --pc-blue in style.css
        'nome_comune' => $nome_comune,
        'nome_ente' => 'Gruppo Comunale di Protezione Civile di ' . $nome_comune,
        'nome_breve' => 'Protezione Civile ' . $nome_comune,
        'meta_title' => 'Protezione Civile ' . $nome_comune . ' | Gruppo Comunale',
        'meta_description' => 'Sito ufficiale del Gruppo Comunale di Protezione Civile di ' . $nome_comune . '. Allerte meteo, prevenzione, mappa di sicurezza e numeri utili per le emergenze.',
        'meta_keywords' => 'protezione civile, ' . $nome_comune . ', emergenze, allerta meteo, volontari, Provincia', //sostituire "Provincia" con la propria provincia
        'meta_author' => 'Gruppo Comunale Protezione Civile di ' . $nome_comune,
        'og_image' => 'https://protezionecivile.comune.esempio.provincia.it/assets/img/logo.png',
        'content_license' => 'CC BY-NC-SA 4.0', // oppure 'CC BY 4.0' o 'Tutti i diritti riservati'
        'urls' => [
            'home' => 'https://protezionecivile.comune.esempio.provincia.it',
            'comune' => 'https://www.comune.esempio.provincia.it/it',
            'comune_root' => 'https://www.comune.esempio.provincia.it',
            'allerte_meteo_regionale' => 'https://www.allerta.meteo.regione.esempio.it',
        ],
        'security' => [
            // Generiamo un hash SHA256 unico che cambia automaticamente ogni giorno.
            // Unisce la chiave segreta alla data odierna (es. "2026-06-21").
            'forza_aggiornamento_token' => hash_hmac('sha256', date('Y-m-d'), $chiaveSyncAlert),
        ],
        'address' => [
            'street' => 'Piazza Principale di Esempio',
            'postal_code' => '00000',
            'city' => $nome_comune,
            'province' => 'ES',
            'province_name' => 'Esempio (Provincia)',
            'region' => 'Esempio (Regione)',
            'country' => 'IT',
            'lat' => '41.87194',
            'lng' => '12.56738',
        ],
        'comune' => [
            'cf' => '00000000000',       // Codice Fiscale dell'Ente
            'piva' => '00000000000',     // Partita IVA del Comune
            'ipa' => 'a_b123'            // Codice univoco indice PA
        ],
        'phones' => [
            'sede' => '+39 123 456 7890',
            'sala_operativa_regionale' => '+39 123 456 7891',
            'polizia_locale' => '+39 123 456 7892',
            'emergency' => '112',
            'societa_elettrica' => '000 000', //numero pronto intervento guasto linee elettriche
        ],
        // Numeri utili: 112 NUE, 113 Polizia, 115 Vigili del Fuoco,
        // 117 Guardia di Finanza, 118 emergenza sanitaria, 1515 incendi boschivi,
        // 1522 antiviolenza e stalking, 1530 Guardia Costiera.
        'emergencies' => ['112', '113', '115', '117', '118', '1515', '1522', '1530'],
        'emergency_labels' => [],
        'emails' => [
            'primary' => 'protezionecivile@esempio.com',
        ],
        'stats' => [
            'volontari_attivi' => 0,
            'mezzi_soccorso' => 0,
        ],
        'map_points' => [
            'sedi' => [
                [
                    'coords' => [41.87194, 12.56738],
                    'colore' => '#D62828',
                    'icona' => 'bi-shield-fill',
                    'titolo' => 'S.O.C. — Sala Operativa Comunale',
                    'indirizzo' => 'Piazza Principale di Esempio',
                    'desc' => 'Centro Operativo Comunale attivo in emergenza.',
                ],
                [
                    'coords' => [41.87194, 12.56738],
                    'colore' => '#003B7A',
                    'icona' => 'bi-shield-lock-fill',
                    'titolo' => 'Comando Polizia Locale',
                    'indirizzo' => 'Piazza Principale di Esempio, 1',
                    'desc' => 'Pronto intervento urbano.',
                    'phone' => '+39 123 456 7890',
                ],
            ],
            'attesa' => [
                ['coords' => [41.86, 12.57738], 'num' => 1, 'cod' => 'ES-03.1', 'loc' => 'Parcheggio Centro Sportivo / Tennis'],
                ['coords' => [41.87194, 12.56738], 'num' => 2, 'cod' => 'ES-03.2', 'loc' => 'Villaggio 1'],
                ['coords' => [41.84194, 12.57738], 'num' => 3, 'cod' => 'ES-03.3', 'loc' => 'Villaggio 2'],
                ['coords' => [41.89194, 12.58738], 'num' => 4, 'cod' => 'ES-03.4', 'loc' => 'Via taldeitali'],
                ['coords' => [41.87194, 12.59738], 'num' => 5, 'cod' => 'ES-03.5', 'loc' => 'Via taldeitali 2'],
                ['coords' => [41.88194, 12.55738], 'num' => 6, 'cod' => 'ES-03.6', 'loc' => 'Via taldeitali 3'],
                ['coords' => [41.85194, 12.54738], 'num' => 7, 'cod' => 'ES-03.7', 'loc' => 'Via taldeitali 4'],
                ['coords' => [41.86194, 12.53738], 'num' => 8, 'cod' => 'ES-03.8', 'loc' => 'Via taldeitali 5'],
            ],
            'accoglienza' => [
                ['coords' => [41.84194, 12.56738], 'num' => 1, 'cod' => 'ES-04.1', 'loc' => 'localita 1'],
                ['coords' => [41.87194, 12.56738], 'num' => 2, 'cod' => 'ES-04.2', 'loc' => 'localita 2'],
                ['coords' => [41.86194, 12.56738], 'num' => 3, 'cod' => 'ES-04.3', 'loc' => 'localita 3'],
            ],
        ],
        'galleria' => [ // queste foto finiscono sia nel carosello di /chi-siamo che nella galleria
            [
                'src'        => 'evento-1.png',
                'alt'        => 'Volontari durante un\'esercitazione sul campo',
                'didascalia' => 'Esercitazione alluvione – Marzo 2024',
            ],
            [
                'src'        => 'evento-2.png',
                'alt'        => 'Stand informativo in piazza durante la Giornata della Protezione Civile',
                'didascalia' => 'Giornata della Protezione Civile – Giugno 2024',
            ],
            [
                'src'        => 'evento-3.png',
                'alt'        => 'Squadra al completo prima di una missione',
                'didascalia' => 'La nostra squadra – Settembre 2024',
            ],
            // aggiungi quante foto vuoi... (l'src prende le foto dalla caretlla /galleria/)
        ],
        'organigramma' => [
            [
                'ruolo'        => 'Responsabile Massimo',
                'nome'         => 'Il Sindaco (autorità locale di P.C.)',
                'icona'        => 'bi-award',
                'icona_classe' => 'text-warning-on-light',
            ],
            [
                'ruolo'        => 'Coordinatore Operativo',
                'nome'         => '[Nome Cognome]',
                'icona'        => 'bi-person-badge',
                'icona_classe' => 'text-primary',
            ],
            [
                'ruolo'        => 'Vice-Coordinatore',
                'nome'         => '[Nome Cognome]',
                'icona'        => 'bi-person',
                'icona_classe' => '',
            ],
        ],

        'socials' => [
            'facebook'  => 'https://www.facebook.com/nomepagina',
            'instagram' => 'https://www.instagram.com/nomeaccount',
            'whatsapp'  => 'https://wa.me/393331234567', // formato internazionale, senza + né spazi
        ],

        'manutenzione' => [
            'attiva'           => false,   // true = tutto il sito reindirizza a /errors/manutenzione.php.
            'fase_attuale'     => 2,    // 1) Controllo di sicurezza
                                        // 2) Miglioramento servizio
                                        // 3) Messa online
            'rientro_previsto' => "13 luglio ore 18:00",    // es. "13 luglio ore 18:00"
        ],
    ],

];

if (($config['site']['manutenzione']['attiva'] ?? false) === true) {
    $pagina_corrente = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $pagina_corrente = rtrim($pagina_corrente, '/') ?: '/';
    $esclusi = ['/errors/manutenzione.php', '/news.php', '/news'];
    $area_admin = $pagina_corrente === '/admin' || str_starts_with($pagina_corrente, '/admin/');
    if (!in_array($pagina_corrente, $esclusi, true) && !$area_admin) {
        header('Location: /errors/manutenzione.php');
        header('Retry-After: 3600'); // opzionale, in secondi
        exit;
    }
}

return $config;
