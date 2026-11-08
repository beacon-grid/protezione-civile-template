# Documentazione Allerte

Questa cartella gestisce il recupero e la visualizzazione delle informazioni di allerta meteo/rischio dal sito del Comune, oltre a un bollettino meteo del territorio e a un radar precipitazioni in tempo reale.

## File principali

| File | Ruolo |
|---|---|
| `allerta_core.php` | Funzioni condivise: download pagina, parsing (con fallback), calcolo allerta e cache SQLite |
| `sync.php` | Sincronizzazione allerte, eseguibile da browser (con token) o CLI |
| `get_allerta.php` | API JSON pubblica con i dati di allerta (letti dalla cache, con auto-sync se il dato non è fresco) |
| `get_meteo.php` | Proxy con cache verso Open-Meteo (previsioni + geocodifica comune) |
| `get_radar.php` | Proxy con cache verso LibreWXR (frame radar precipitazioni) |
| `get_temporale.php` | Rilevamento puntuale "temporale in corso" a un punto specifico |
| `index.php` | Pagina di interfaccia: bollettino allerte, widget meteo, radar |
| `js/allerta.js` | Logica frontend di tutti e tre i widget della pagina |

## Come funziona

### allerta_core.php
- Contiene la logica condivisa tra `sync.php` e `get_allerta.php`.
- `scaricaPagina()`: download via cURL con `CURLOPT_SSL_VERIFYPEER` e `CURLOPT_SSL_VERIFYHOST` **attivi** (verifica SSL abilitata).
- `estraiDaTabellaHome()`: parser primario, cerca nella home una tabella HTML contenente la stringa `Tipologia di rischi`.
- `estraiDaPaginaDedicata()`: parser di **fallback**, usato solo se il parser primario non trova nulla, sulla pagina indicata da `site.urls.comune_allerta_dedicata` (se configurata).
- `calcolaAllertaMassima()`: determina l'allerta globale più grave tra i rischi estratti.
- `leggiCacheSicura()` / `scriviCacheSicura()`: lettura/scrittura della cache delle allerte in SQLite.
- `eseguiSincronizzazione()`: orchestratore che mette insieme i passaggi sopra — scarica, prova il parser primario, se fallisce prova il fallback, calcola l'allerta, scrive la cache. Usato sia da `sync.php` sia (indirettamente) da `get_allerta.php`.

### sync.php
- Legge la configurazione applicativa da `data/config.php` e prende l'URL del Comune da `site.urls.comune`.
- Se eseguito da CLI, è sempre autorizzato (pensato per il cron).
- Se eseguito da browser, richiede un token valido in query string (`?token=...`), confrontato via hash SHA-256 con `FORZA_AGGIORNAMENTO_TOKEN` (env) o `site.security.forza_aggiornamento_token` (config); senza match risponde 403.
- I trigger manuali via browser sono inoltre soggetti a un **rate limit di 5 minuti** memorizzato in SQLite; il cron CLI non è soggetto a questo limite.
- Chiama `eseguiSincronizzazione()` da `allerta_core.php` e salva i dati in SQLite.
- Restituisce output HTML in browser (pagina di stato) o testo semplice in caso di esecuzione CLI.

> **⚠️ ATTENZIONE:** questa sezione è pensata per il sito di uno specifico Comune ed è da adattare alle proprie esigenze — ad esempio collegandolo all'API ARPA della propria regione — modificando la logica di parsing in `allerta_core.php` in base al formato della fonte dati scelta.

### get_allerta.php
- Restituisce JSON con i dati di cache delle allerte letti da SQLite.
- Imposta header `Content-Type: application/json` e `Access-Control-Allow-Origin` solo se l'origine della richiesta coincide esattamente con `site.urls.home`.
- Se la cache è più vecchia di 20 minuti (non fresca), **non si limita a servire il dato vecchio**: tenta di innescare una risincronizzazione lato server chiamando `eseguiSincronizzazione()`, protetta da un lock SQLite non bloccante e da un intervallo minimo di 60 secondi tra tentativi. Se un altro processo sta già sincronizzando, o è passato troppo poco tempo dall'ultimo tentativo, la richiesta corrente non aspetta e serve semplicemente la cache esistente (anche se non freschissima).
- Dopo l'eventuale tentativo di sync, ricarica la cache (che nel frattempo potrebbe essere stata aggiornata da questo stesso processo o da un altro) e la restituisce, con `data_fresca` e `cache_age_seconds` calcolati sul dato effettivamente servito.

### get_meteo.php
- Proxy con cache lato server verso l'API Open-Meteo (previsioni `current`/`hourly`/`daily`).
- Se non riceve `lat`/`lng`, geocodifica il parametro `city` tramite Open-Meteo Geocoding API (cache 30 giorni, la posizione di un comune non cambia).
- Cache previsioni: 10 minuti, per coppia lat/lng arrotondata a 3 decimali.
- In caso di servizio esterno irraggiungibile, restituisce comunque la cache scaduta come fallback, se presente.
- Parametri `current` richiesti a Open-Meteo: `temperature_2m`, `apparent_temperature`, `precipitation`, `relative_humidity_2m`, `wind_speed_10m`, `wind_gusts_10m`, `wind_direction_10m`, `surface_pressure`, `cloud_cover`, `weather_code`.

### get_radar.php
- Proxy con cache verso [LibreWXR](https://librewxr.net) (API compatibile Rain Viewer v2), che per l'Italia usa come sorgente nativa Radar-DPC (composito nazionale a 24 radar, aggiornato ogni 5 minuti).
- Il radar è un dato globale (non serve lat/lng): un'unica cache condivisa da tutti gli utenti dell'app, TTL 5 minuti.
- Restituisce `host`, `frame_passati`, `frame_nowcast`, `color_schemes`, pronti per essere usati come tile layer (es. Leaflet).
- Per passare a un'istanza self-hosted di LibreWXR, basta cambiare la costante `LIBREWXR_BASE_URL`.
- Attribuzione richiesta in UI: LibreWXR + "Radar-DPC" (le tile con dati radar italiani ereditano la clausola share-alike CC-BY-SA 4.0 di DPC).

### get_temporale.php
- Risponde alla domanda puntuale "in questo punto, ora, il radar vede un fenomeno intenso?" — diverso da `get_meteo.php` (previsione da modello) e `get_radar.php` (frame per la mappa).
- Calcola quale singolo tile 256×256 contiene le coordinate richieste (matematica slippy-map) e scarica **solo quel tile** (non l'intero mosaico nazionale).
- Legge un pixel con l'estensione GD, richiedendo lo schema colore "Black and White" (id `0`): la luminosità del pixel è proporzionale alla riflettività radar, quindi non serve conoscere la tabella colori di altri schemi per stabilire una soglia.
- Se l'estensione GD non è disponibile, risponde comunque con JSON valido e `status: unavailable` / `livello: non_disponibile`; il widget frontend considera il rilevamento non disponibile senza bloccare la pagina.
- Riusa le stesse cache SQLite di `get_radar.php` (frame radar, chiave `librewxr_frames`) e di `get_meteo.php` (geocodifica città, chiave `geo_*`).
- **Le soglie di luminosità (`LUMINOSITA_SOGLIA_TEMPORALE = 180`, `LUMINOSITA_SOGLIA_PIOGGIA = 80`) sono un punto di partenza non calibrato su dati reali italiani.** Da tarare osservando il valore restituito durante un temporale noto e un giorno di pioggia leggera.

### index.php
- Tre card: Meteo del Territorio (widget con Open-Meteo), Bollettino Criticità (stato globale + tabella rischi oggi/domani), Radar Precipitazioni (collassabile, Leaflet caricato lazy al primo click).
- Passa a `js/allerta.js` le coordinate del sito, se disponibili (`data-lat`/`data-lng`/`data-city` sul widget meteo e sul container radar); in assenza, il widget meteo effettua una geocodifica automatica del nome del comune.
- Contiene solo markup, CSS inline e il tag `<script src="../js/allerta.js" defer>` — **tutta la logica JS vive in `allerta.js`**, non va duplicata qui.

### js/allerta.js
Tre moduli indipendenti, ciascuno in un proprio `DOMContentLoaded`:
1. **Bollettino criticità** — fetch di `get_allerta.php`, rendering stato globale e tabella rischi, fallback locale su errore/timeout (8s).
2. **Meteo del territorio** — fetch di `get_meteo.php`, mappatura icone sia per codici WMO (Open-Meteo) sia per `symbol_code` (MET Norway, non attualmente usato ma supportato), avvisi automatici su raffiche di vento (≥50 km/h) e piogge intense (≥70% probabilità e ≥10mm previsti); rilevamento "temporale in corso" via `get_temporale.php`, non bloccante in caso di errore.
3. **Radar precipitazioni** — Leaflet caricato lazy al primo click sul pannello, fetch di `get_radar.php` per host/frame/color_schemes, animazione play/pausa dei frame passati.

## Configurazione necessaria
Il file `data/config.php` deve contenere un URL valido in `site.urls.comune`.
Se la pagina del Comune cambia struttura, lo script di parsing potrebbe non trovare più la tabella e restituire un errore (a meno che `site.urls.comune_allerta_dedicata` sia configurato e il parser di fallback riesca a estrarre i dati da lì).

Per i trigger manuali via browser di `sync.php` serve inoltre configurare un token in `FORZA_AGGIORNAMENTO_TOKEN` (env) o `site.security.forza_aggiornamento_token` (`data/config.php`).

## Cache

| Cache SQLite | TTL | Usata da |
|---|---|---|
| Allerta sincronizzata | 20 minuti | `sync.php`, `get_allerta.php` |
| Lock e cooldown auto-sync | 120 secondi / 60 secondi | `get_allerta.php` |
| Rate limit trigger manuale | 5 minuti | `sync.php` |
| Previsioni meteo per coordinate | 10 minuti | `get_meteo.php` |
| Geocodifica città | 30 giorni | `get_meteo.php`, `get_temporale.php` |
| Frame LibreWXR | 5 minuti | `get_radar.php`, `get_temporale.php` |
| Risultato temporale per coordinate | 5 minuti | `get_temporale.php` |

Tutte le cache sono condivise tra i processi PHP e persistono nel database `data/site.sqlite`.

## Esecuzione in cron
Usa `sync.php` in CLI per aggiornamenti automatici:

```bash
cd /var/www/html/allerte
php sync.php
```

Puoi schedulare l'esecuzione ogni 10-15 minuti. Nota che, grazie all'auto-sync di `get_allerta.php`, il cron non è strettamente indispensabile al funzionamento (la cache si può rigenerare anche su richiesta API), ma resta consigliato per garantire dati sempre freschi senza dipendere dal traffico in ingresso.

## Permessi necessari
Il server web e lo script CLI devono poter scrivere nella cartella `data/` e nel database SQLite. La cache meteo/radar è conservata nello stesso database e non richiede una cartella temporanea.

Assicurati che tutte le cartelle coinvolte siano accessibili e scrivibili dall'utente che esegue il web server o il cron.

## Requisiti PHP
- estensione **cURL**, necessaria per la sincronizzazione in `allerta_core.php`; i proxy meteo, radar e temporale usano `file_get_contents` come fallback se cURL non è disponibile
- estensione **PDO SQLite** (`pdo_sqlite`)
- estensioni **mbstring** e **DOM/XML**, usate dal parser della sincronizzazione
- estensione **GD** opzionale per `get_temporale.php` (senza GD il rilevamento puntuale viene disattivato con una risposta JSON non bloccante; gli altri due proxy non ne hanno bisogno)

## Avvertenze
- `allerta_core.php` usa `CURLOPT_SSL_VERIFYPEER=true` e `CURLOPT_SSL_VERIFYHOST=2`: la verifica del certificato SSL è **attiva**.
- Se il sito del Comune non contiene la tabella prevista né una pagina dedicata funzionante, la sincronizzazione fallisce con messaggio di parsing (loggato via `error_log`).
- Il token dei trigger manuali di `sync.php` viene confrontato via hash SHA-256 (`hash_equals`); assicurati che `FORZA_AGGIORNAMENTO_TOKEN` sia impostato in produzione, altrimenti i trigger manuali via browser risulteranno sempre non autorizzati (a meno del valore di fallback in `data/config.php`).
- Le funzioni `chiamaUrlEsterno()` e `serviConCache()` sono duplicate (con piccole variazioni) nei tre proxy: mantenere coerenti le chiavi e i TTL delle cache SQLite.
- Le soglie di rilevamento temporale in `get_temporale.php` non sono ancora calibrate su dati reali (vedi sopra).