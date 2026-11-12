# Sito Template Protezione Civile - Documentazione e Installazione

## 0. Note legali e disclaimer

**Natura del progetto**
Questo è un progetto indipendente, sviluppato a titolo personale da un volontario di 
protezione civile nel proprio tempo libero. È distribuito esclusivamente come codice 
sorgente open source, a scopo di riuso da parte di gruppi comunali, associazioni di 
volontariato o enti locali che desiderino adattarlo alle proprie esigenze.

**Non affiliazione**
Il progetto non è affiliato, autorizzato, sponsorizzato né in alcun modo collegato a:
- il Dipartimento della Protezione Civile della Presidenza del Consiglio dei Ministri;
- alcun gruppo comunale, associazione di volontariato o ente locale specifico, incluso 
  quello di cui l'autore fa parte.

L'autore non ha coinvolto il proprio gruppo comunale nello sviluppo di questo template 
e non lo rappresenta in alcuna forma attraverso questo repository.

**Stato del progetto**
Il template non è mai stato messo in produzione né reso pubblicamente accessibile 
online dall'autore. Viene distribuito unicamente come codice, perché chiunque lo adotti 
possa configurarlo, personalizzarlo e pubblicarlo sotto la propria responsabilità.

**Marchi e segni distintivi**
Tutti i loghi, gli emblemi e i nomi presenti nel codice (es. "Comune di Esempio", 
"protezionecivile.comune.esempio.provincia.it") sono placeholder fittizi a scopo 
dimostrativo. Non sono inclusi loghi, emblemi o segni distintivi ufficiali del 
Dipartimento della Protezione Civile, del Servizio Nazionale o di alcuna associazione 
reale. Chi adotta questo template è responsabile di sostituire i placeholder con i 
propri contenuti e di rispettare la normativa vigente sull'uso dei segni distintivi 
istituzionali (art. 30, D.Lgs. 1/2018 e relativo regolamento attuativo).

**Nessuna garanzia ("as is")**
Il software è fornito "così com'è", senza alcuna garanzia esplicita o implicita di 
correttezza, sicurezza o idoneità a uno scopo particolare. L'autore non si assume 
responsabilità per danni derivanti dall'uso, dalla configurazione o dalla messa in 
produzione del codice da parte di terzi.

**Responsabilità di chi adotta il template**
Chiunque utilizzi questo template in un contesto reale è tenuto a:
- verificare la conformità GDPR/privacy per i dati effettivamente trattati (contatti, 
  moduli, eventuali dati dei volontari);
- rispettare la normativa sull'accessibilità (WCAG 2.1 AA / AGID) per i siti destinati 
  a un ente pubblico;
- adattare autonomamente lo script `allerte/sync.php`, il cui parsing HTML è specifico 
  per il sito di un singolo Comune e non è plug-and-play (vedi sezione 6.4);
- rispettare le regole sull'uso dei segni distintivi ufficiali di protezione civile, 
  qualora il sito sia gestito da un ente o gruppo riconosciuto;
- non utilizzare il widget di stato allerta come fonte ufficiale per l'informazione 
  alla cittadinanza senza aver prima verificato l'affidabilità e l'aggiornamento 
  della fonte dati collegata; il template non garantisce l'accuratezza o la 
  tempestività delle allerte mostrate, e non deve in alcun caso sostituire i canali 
  ufficiali di allerta (Dipartimento Nazionale, Regione, Prefettura).

**Licenza**

Questo progetto è distribuito con licenza **GNU Affero General Public License v3.0 
(AGPL-3.0)**. Testo completo nel file `LICENCE.md`.

**Cosa significa in pratica:**
- Puoi usare, installare, modificare e distribuire liberamente questo software, 
  gratuitamente o come servizio a pagamento (es. installazione, personalizzazione, 
  manutenzione per conto di un ente o associazione).
- Se modifichi il codice e lo rendi disponibile ad altri — anche solo eseguendolo 
  come servizio web (SaaS) senza distribuire il codice stesso — sei tenuto a 
  rendere pubbliche le tue modifiche con la stessa licenza AGPL-3.0.
- Non è consentito trasformare questo software, o una sua versione modificata, 
  in un prodotto proprietario chiuso.

In sintesi: il software resta libero e aperto per chiunque, anche se qualcuno 
lo personalizza o lo offre come servizio a pagamento a un ente.

---

*Ultimo aggiornamento di questa sezione: 11 luglio 2026*

## 1. Panoramica
Questo repository include un template generico di sito web per un gruppo comunale di Protezione Civile.
Il progetto è una web application PHP basata su file statici e template inclusi, con dati persistenti in SQLite.
La personalizzazione del sito avviene principalmente tramite `data/config.php`, che rende il sito facilmente adattabile a qualsiasi Comune o ente locale.
Lo scopo principale del sito è fornire informazioni su allerta meteo, prevenzione, mappa del territorio, news, galleria e contatti istituzionali.
Per un avvio rapido, è inoltre disponibile `setup.php`, utile per verificare la configurazione iniziale e i prerequisiti di base.

![Homepage](docs/images/Homepage.png)

> **Attenzione**: `allerte/sync.php` non è un componente plug-and-play da clonare e usare 
> così com'è. Il parsing HTML è stato realizzato ad hoc per il sito di uno specifico Comune 
> e va ricreato o adattato per ogni Comune o fonte dati (es. API ARPA regionale) prima di 
> essere usato in produzione.

### Anteprima
- [Homepage](docs/images/Homepage.png)
- [Mappa](docs/images/Mappa.png)
- [Bollettino](docs/images/Bollettino.png)
- [I rischi](docs/images/I%20rischi.png)
- [Chi siamo](docs/images/Chi-siamo.png)

---

## 2. Requisiti di sistema
- Server Linux (Debian/Ubuntu consigliato)
- Web server Apache o Nginx
- PHP 8.4 o superiore
- Estensioni PHP richieste:
  - `php-json`
  - `php-sqlite3` (include `pdo_sqlite`, obbligatorio per i dati del sito)
  - `php-curl`
  - `php-mbstring` (necessario per gestione news e parsing delle allerte)
  - `php-xml` (necessario per `DOMDocument` nel parsing delle allerte)
  - `php-gd` (opzionale: abilita il rilevamento puntuale dei temporali)
  - `php-opcache` (opzionale ma raccomandato)

---

## 3. Struttura del progetto

Root principale:
- `index.php` - homepage 
- `chi-siamo.php` - pagina informativa "Chi siamo"
- `mappa.php` - mappa interattiva con punti di interesse
- `cerca.php` - ricerca pubblica di notizie, guide, documenti e glossario (URL `/cerca`)
- `glossario.php` - elenco pubblico delle definizioni del glossario (URL `/glossario`)
- `accessibilita.php` - dichiarazione accessibilità
- `sitemap.xml` - sitemap SEO
- `robots.txt` - regole crawler
- `data/config.php` - configurazione generale del sito
- `emergency.php` - pagina server-side dello stato di emergenza
- `emergency.html` - fallback statico ultra-leggero della pagina emergenza

Cartelle principali:
- `admin/` - pannello di amministrazione, autenticazione e gestione news
- `allerte/` - script di sincronizzazione e recupero allerta meteo
- `assets/` - immagini, PDF e risorse statiche
- `css/` - fogli di stile
- `js/` - script client-side
- `news/` - elenco news pubbliche
- `includes/` - frammenti HTML riutilizzati
- `data/` - database SQLite, dati sorgente legacy e protezione `.htaccess`
  - `site.sqlite` - account, contenuti, stato emergenza e cache

> `vendor/` e i manifest Composer **non sono inclusi nel repository**. Per installare HTMLPurifier e mPDF fuori dal webroot, segui la sezione 9.

---

## 4. Installazione

### 4.1 Installazione ambiente
Esempio su Debian/Ubuntu con Apache:

```bash
sudo apt update
sudo apt install apache2 composer php8.4 libapache2-mod-php8.4 php8.4-curl php8.4-json php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-gd
```

Per Nginx con PHP-FPM:

```bash
sudo apt update
sudo apt install nginx composer php8.4-fpm php8.4-curl php8.4-json php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-gd
```

### 4.2 Posizionamento dei file e checklist di primo deploy
- Copia tutto il contenuto di questa cartella in `DocumentRoot` del server.
- Esempio Apache: `/var/www/html`
- Esempio Nginx: `/usr/share/nginx/html` o la root definita nella configurazione.
- Dopo il caricamento, completa questa checklist **prima** di eseguire `setup.php` o rendere il sito pubblicamente raggiungibile:

1. **Estensioni PHP** — verifica che le estensioni installate in 4.1 siano effettivamente attive: `php -m | grep -E 'json|curl|sqlite|mbstring|xml|gd|opcache'`
2. **Composer** — installa HTMLPurifier e mPDF come descritto in sezione 9 e verifica che sia disponibile `/var/www/vendor/autoload.php`
3. **Protezione di `data/`** — verifica che `data/.htaccess` contenga `Require all denied` (per Nginx, vedi sezione 4.3)
4. **Permessi su `data/`** — imposta owner e permessi come indicato in sezione 4.3
5. **Esegui `setup.php`** per generare `data/config.php` tramite il wizard guidato
6. **Proteggi `data/config.php`** — una volta completato il setup, togli il permesso di scrittura dal file (vedi sezione 4.3.1)
7. **Elimina o rinomina `setup.php`** dal server, per sicurezza extra, una volta completata la configurazione
8. **HTTPS** — attiva il certificato SSL seguendo la sezione 4.6
9. **Security header** — applica gli header di sicurezza consigliati in sezione 7.7
10. **Credenziali admin** — crea gli account dal wizard e completa il cambio password al primo accesso
11. **`allerte/sync.php` / `allerte/allerta_core.php`** — adatta la logica di parsing alla fonte dati del tuo Comune (vedi avviso in sezione 6.4); con il parsing originale il sito non funzionerà correttamente per un Comune diverso da quello di esempio
12. **Cron** — configura la sincronizzazione periodica delle allerte come descritto in sezione 8

> Nota: le librerie frontend come Bootstrap, Leaflet e Quill sono già incluse nel repository nelle cartelle `css/` e `js/`; non serve installarle separatamente.

### 4.3 Permessi filesystem
Il sito salva dati, account e cache nel database SQLite `data/site.sqlite`. La cartella `data/` deve essere scrivibile dal processo PHP (SQLite crea anche file temporanei accanto al database) e non esposta pubblicamente. I dati legacy presenti in JSON e nei file PHP degli account vengono importati una sola volta; l'importazione non elimina automaticamente i file sorgente.

Per una configurazione più sicura, evita permessi aperti come `777` e usa un owner dedicato per i file di dati, lasciando al processo web solo l'accesso necessario:

```bash
sudo chown -R utente-dati:www-data /var/www/html/data
sudo find /var/www/html/data -type d -exec chmod 770 {} \;
sudo find /var/www/html/data -type f -exec chmod 640 {} \;
```

Il processo PHP imposta `data/site.sqlite` a `660` alla creazione; se lo crei manualmente, applica `sudo chmod 660 /var/www/html/data/site.sqlite`.

Dove:
- `utente-dati` è l'utente dedicato alla proprietà dei file in `data/`
- `www-data` è l'utente del web server che deve poter leggere/scrivere solo ciò che serve

Se il web server è eseguito con un altro utente, sostituisci `www-data` con il nome corretto. Se invece preferisci usare direttamente l'utente del web server come proprietario, assicurati comunque che i permessi siano restringibili e che la cartella non sia accessibile in scrittura a tutti.

Ulteriori raccomandazioni:
- mantenere attivo il file `data/.htaccess` per bloccare la navigazione diretta della cartella e del database
- evitare permessi globali o world-writable
- se un amministratore modifica i file via shell, verificare che il proprietario e il gruppo siano coerenti con il processo web
- con Nginx, che non legge i file `.htaccess`, aggiungere al blocco `server` e verificare la configurazione prima del reload:

```nginx
location ^~ /data/ {
    deny all;
}
```

### 4.3.1 Permessi di `data/config.php`

Il wizard genera la configurazione in `data/config.php`, quindi non è necessario rendere
scrivibile la root del sito. Il processo PHP deve poter creare il file nella cartella `data/`,
già necessaria per SQLite.

Il file si trova in una cartella già protetta da `data/.htaccess`. Non rimuovere la direttiva
`Require all denied`: blocca le richieste HTTP dirette anche al file di configurazione.

```bash
sudo chmod 440 /var/www/html/data/config.php
```

Per modificare manualmente la configurazione, ripristina temporaneamente il permesso di scrittura,
modifica il file e poi riportalo in sola lettura:

```bash
sudo chmod 640 /var/www/html/data/config.php
# ... modifica data/config.php ...
sudo chmod 440 /var/www/html/data/config.php
```

Nginx non legge i file `.htaccess`: configura la regola `location ^~ /data/ { deny all; }`
indicata nella sezione 4.3. Negli aggiornamenti di installazioni precedenti, sposta il vecchio
`config.php` in `data/config.php`; il codice mantiene la lettura della vecchia posizione come
compatibilità se il nuovo file non è presente.

### 4.4 Configurazione del virtual host Apache
Esempio di file `000-default.conf`:

```apache
<VirtualHost *:80>
    ServerName protezionecivile.comune.esempio.provincia.it
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Riavvia Apache:

```bash
sudo systemctl restart apache2
```

### 4.5 Configurazione Nginx
Esempio di blocco server:

```nginx
server {
    listen 80;
    server_name protezionecivile.comune.esempio.provincia.it;
    root /var/www/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ $uri.php?$query_string =404;
    }

    location ^~ /data/ {
        deny all;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Riavvia Nginx e PHP-FPM:

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
```

### 4.6 HTTPS con Let's Encrypt (Certbot)
Il sito usa URL canonici `https://` — abilitare SSL è obbligatorio in produzione.

**Apache:**
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d protezionecivile.comune.esempio.provincia.it
```

**Nginx:**
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d protezionecivile.comune.esempio.provincia.it
```

Certbot configura automaticamente il rinnovo. Verificare che sia attivo:
```bash
sudo systemctl status certbot.timer
```

Per testare il rinnovo manuale:
```bash
sudo certbot renew --dry-run
```

> **Nota**: la verifica del certificato SSL (`CURLOPT_SSL_VERIFYPEER`/`CURLOPT_SSL_VERIFYHOST`) è già attiva di default in `allerte/allerta_core.php` (funzione `scaricaPagina()`, usata sia da `sync.php` sia da `get_allerta.php`). Non serve alcuna modifica dopo aver abilitato HTTPS: assicurarsi solo che il certificato del sito del Comune target sia valido, altrimenti le richieste cURL falliranno.

---

## 5. Configurazione del sito
Il file principale di configurazione è `data/config.php`.
Qui sono definiti:
- nome del Comune e dell'ente
- titolo e descrizione meta
- URL canonici
- contatti telefonici ed email
- punti della mappa
- organigramma

### 5.1 Utenti di accesso admin
Gli account sono archiviati nel database `data/site.sqlite`, con i superadmin separati logicamente dagli account admin/user gestibili dal pannello. I file PHP legacy vengono importati automaticamente al primo avvio e conservati come copia sorgente.

Il wizard `setup.php` crea un superadmin obbligatorio e un admin opzionale, genera i segreti TOTP e imposta `must_change_password = true`. I segreti TOTP vengono mostrati una sola volta al termine del wizard. Al primo accesso l'utente deve cambiare password; il flag viene azzerato solo dopo un salvataggio riuscito.

### 5.2 Personalizzazione del sito
Il sito è un template generico: tutte le informazioni specifiche del Comune o del gruppo di protezione civile devono essere personalizzate in `data/config.php`.
Modifica questi valori per adattare il sito alla tua realtà locale:
- `nome_comune`, `nome_ente`, `nome_breve`
- `meta_title`, `meta_description`, `meta_keywords`, `meta_author`
- URL canonici in `urls`, tra cui `home` e `comune`
- indirizzo, città, provincia, regione e dati di contatto
- numeri di emergenza e email
- punti della mappa in `map_points`
- organigramma e titoli di ruolo
- dati di statistica e numeri utili

Se vuoi personalizzare ulteriormente i contenuti grafici o il layout, puoi modificare anche i template in `includes/`, le pagine in `news/`, `rischi/`, `area-formazione/` e i CSS in `css/`.

---

## 6. Funzionalità principali

### 6.1 Amministrazione news
- `admin/index.php` - login area riservata
- `admin/news.php` - pannello di gestione notizie
- `admin/news_edit.php` - modifica / creazione news
- `php/news_helper_admin.php` - salvataggio nel database SQLite `data/site.sqlite`

L'editor Quill include un picker del glossario ricercabile e supporta le scorciatoie desktop `Ctrl/Cmd+S` (salva pagina), `Ctrl/Cmd+B` (grassetto), `Ctrl/Cmd+I` (corsivo) e `Ctrl/Cmd+G` (apre il glossario). Il salvataggio evidenzia le modifiche non salvate e impedisce l'invio duplicato.

### 6.2 Stato di emergenza
- La dashboard consente di attivare o annullare l'allerta da un pulsante contestuale.
- In attivazione vengono richiesti tipo, titolo e descrizione; i dati sono salvati nel database SQLite.
- Quando l'allerta è attiva, le pagine pubbliche vengono indirizzate a `emergency.php`, esclusi admin, errori e la pagina stessa.
- `emergency.php` include la pagina ultra-leggera e mostra il popup con i dati dell'emergenza già nel markup server-side.
- L'utente può scegliere di proseguire sul sito completo tramite il link dedicato: il parametro `emergency_bypass=1` registra un cookie di sessione e impedisce nuovi redirect durante quella sessione del browser.
- I link interni presenti nella pagina emergenza propagano automaticamente il bypass; il bypass non modifica lo stato globale dell'emergenza e non si applica all'area admin.

### 6.3 Ricerca e consultazione del glossario
- La ricerca pubblica è disponibile dalla homepage, dal piè di pagina e all'indirizzo `/cerca`; usa un modulo GET, perciò query e filtri sono condivisibili e funzionano senza JavaScript.
- Cerca tra news pubblicate, definizioni attive del glossario, guide curate e documenti PDF presenti.
- Gli amministratori possono attivare/disattivare la ricerca e scegliere le sezioni indicizzate da `admin/site_search_settings.php`; le impostazioni sono salvate in SQLite.
- I filtri comprendono tipologia, categoria e anno per le notizie. I risultati sono ordinati per rilevanza; il testo è confrontato senza distinzione di maiuscole e accenti.
- I documenti PDF sono ricercabili per titolo e descrizione, non per il testo interno.
- Il glossario è consultabile anche all'indirizzo `/glossario`; se viene disattivato dal pannello, la pagina e la ricerca ne rispettano lo stato.
- La ricerca è server-side, non incorpora servizi di terze parti, limita l'output a 50 risultati e non carica lo script di evidenziazione automatica del glossario.
- `js/banda-check.js` usa `emergency.php` come destinazione per connessioni lente; `emergency.html` resta un fallback statico.

### 6.4 Allerte meteo, bollettino meteo e radar precipitazioni
- `allerte/sync.php` - scarica la pagina del Comune, esegue il parsing della tabella dei rischi e salva la cache in SQLite
- `allerte/get_allerta.php` - API pubblica generica (nessuna logica sito-specifica) che restituisce il JSON dalla cache; se il dato non è fresco (oltre 20 minuti), tenta automaticamente una risincronizzazione lato server prima di rispondere
- `allerte/get_meteo.php` - proxy con cache verso Open-Meteo per il bollettino meteo del territorio (previsioni + geocodifica del comune)
- `allerte/get_radar.php` - proxy con cache verso LibreWXR per i frame del radar precipitazioni (composito nazionale Radar-DPC)
- `allerte/get_temporale.php` - rilevamento puntuale "temporale in corso" su una coordinata specifica, basato sulla lettura di un singolo tile radar
- `js/allerta.js` - logica frontend di tutti i widget della pagina (bollettino criticità, meteo del territorio, radar)

> **Attenzione**: la sezione di sincronizzazione non è un componente  plug-and-play. Il parsing (in `allerta_core.php`) è stato realizzato ad hoc per il sito di uno specifico Comune: cerca in via primaria una tabella HTML `Tipologia di rischi` nella home, con un parser di fallback su una pagina dedicata (`site.urls.comune_allerta_dedicata`, se configurata). Va ricreato o adattato per ogni Comune o fonte dati — ad esempio collegandolo all'API ARPA della propria regione — prima di essere usato in produzione.

> **Nota**: `get_meteo.php`, `get_radar.php` e `get_temporale.php` sono invece generici e non richiedono personalizzazione sito-specifica; per dettagli su cache condivise, il fallback quando GD non è installata e le soglie di rilevamento vedi `allerte/README.md`.

### 6.5 Mappa interattiva
- `mappa.php` utilizza i punti definiti in `data/config.php` per mostrare la mappa
- `assets/` contiene immagini e icone di supporto

### 6.6 Galleria
- `assets/img/galleria/` contiene le immagini utilizzate dalla galleria del sito
- `js/galleria.js` gestisce la logica client-side della galleria
- `css/galleria.css` definisce lo stile della sezione galleria

### 6.7 Sitemap e robots
- `sitemap.xml` - sitemap del sito con URL delle pagine principali
- `robots.txt` - definisce politiche di scansione per i motori di ricerca

### 6.8 Documentazione cartelle specifiche
Il progetto include documentazione aggiuntiva per parti critiche della soluzione:
- `admin/README.md` - pannello di amministrazione e gestione news
- `allerte/README.md` - sincronizzazione e API dell'allerta meteo
- `news/README.md` - visualizzazione pubblica delle news

---

## 7. Sicurezza

### 7.1 SSL/TLS
La verifica del certificato SSL verso il server remoto (es. il sito del Comune) è centralizzata in `allerte/allerta_core.php`, funzione `scaricaPagina()`, ed è già attiva di default:

```php
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
```

Sia `sync.php` sia `get_allerta.php` (tramite l'auto-sync) passano da questa funzione, quindi non serve alcuna configurazione aggiuntiva. Non disabilitare questa verifica: esporrebbe le comunicazioni con il server del Comune a potenziali attacchi man-in-the-middle.

### 7.2 Protezione dei trigger di sincronizzazione
`get_allerta.php` non espone un parametro di forzamento pubblico: quando la cache non è fresca (oltre 20 minuti), tenta automaticamente un auto-sync lato server, protetto da un lock non bloccante e da un intervallo minimo di 60 secondi tra tentativi, entrambi gestiti in SQLite.

Il forzamento manuale della sincronizzazione avviene invece tramite `sync.php`, già protetto da:
- **token segreto**: confrontato via `hash_equals()` con `FORZA_AGGIORNAMENTO_TOKEN` (variabile d'ambiente) o `site.security.forza_aggiornamento_token` in `data/config.php`; senza match risponde 403
- **rate limit di 5 minuti** sui trigger via browser, memorizzato in SQLite; il cron CLI non è soggetto a questo limite

Chiamata sicura (solo da cron o pannello admin):
```
https://dominio.it/allerte/sync.php?token=tua-stringa-segreta
```
Assicurarsi di impostare `FORZA_AGGIORNAMENTO_TOKEN` con una stringa lunga e casuale in produzione: senza un token configurato, i trigger manuali via browser risultano sempre non autorizzati.

### 7.3 Errori cURL non esposti al client
Gli errori interni di cURL (nomi server, percorsi, messaggi di sistema) non devono mai uscire nella risposta JSON pubblica. Sostituire:

```php
// DA EVITARE
echo json_encode(['error' => curl_error($ch)]);
```

Con:
```php
error_log('cURL error: ' . curl_error($ch)); // solo log server
echo json_encode(['error' => 'Dati temporaneamente non disponibili']);
```

### 7.4 CORS
`get_allerta.php` invia `Access-Control-Allow-Origin` solo quando l'header `Origin` della richiesta coincide esattamente con `site.urls.home` in `data/config.php`. Non usa `Access-Control-Allow-Origin: *`; se il dominio cambia, aggiorna la configurazione del sito.

### 7.5 Hardening sessione admin
Il pannello imposta cookie di sessione `HttpOnly` e `SameSite=Lax`; abilita `Secure` quando la richiesta è riconosciuta come HTTPS. La sessione scade dopo un'ora e viene terminata dopo 30 minuti di inattività. Inoltre, ogni richiesta autenticata verifica che IP e user agent corrispondano a quelli registrati al login. Se il sito è dietro un proxy, configura correttamente la terminazione HTTPS e gli header inoltrati.

### 7.6 CSRF sui form admin
Se il pannello admin usa form POST, assicurarsi che ogni form includa un token CSRF valido. In assenza di questa protezione, un utente autenticato potrebbe essere indotto a compiere azioni indesiderate tramite richieste cross-site.

### 7.7 Security header generali
Il pannello admin invia `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options` e `Referrer-Policy`; con Apache e `mod_headers` attivo, la configurazione `.htaccess` alla root invia anche HSTS. Questi header non sono impostati dal codice PHP su tutte le pagine pubbliche. Se vuoi applicarli globalmente, configurali nel virtual host o nel server web, verificando la compatibilità della CSP con le risorse effettivamente caricate:
- `Content-Security-Policy`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY` oppure `frame-ancestors 'none'` in CSP
- `Referrer-Policy: strict-origin-when-cross-origin`

Questi header riducono il rischio di clickjacking, injection di contenuto e tipi MIME ambigui.

### 7.8 Protezione file sensibili
Assicurarsi che Apache/Nginx blocchi l'accesso diretto a file di configurazione e librerie:

**Apache** (in `.htaccess` o virtual host):
```apache
<FilesMatch "^(config\.php|\.env)$">
    Require all denied
</FilesMatch>
```

**Nginx**:
```nginx
location ^~ /data/ {
    deny all;
}

location ~* ^/(config\.php|\.env) {
    deny all;
}
```

---

## 8. Cron job per sincronizzazione allerte

`allerte/sync.php` dovrebbe essere eseguito periodicamente tramite cron per mantenere aggiornata la cache delle allerte.

Aprire il crontab dell'utente `www-data`:
```bash
sudo crontab -u www-data -e
```

Aggiungere una delle seguenti righe a seconda della frequenza desiderata:

```cron
# Ogni 30 minuti
*/30 * * * * /usr/bin/php /var/www/html/allerte/sync.php >> /var/log/allerte_sync.log 2>&1

# Ogni ora
0 * * * * /usr/bin/php /var/www/html/allerte/sync.php >> /var/log/allerte_sync.log 2>&1

# Ogni 15 minuti (consigliato in periodi di allerta attiva)
*/15 * * * * /usr/bin/php /var/www/html/allerte/sync.php >> /var/log/allerte_sync.log 2>&1
```

Verificare che il log sia scrivibile:
```bash
sudo touch /var/log/allerte_sync.log
sudo chown www-data:www-data /var/log/allerte_sync.log
```

Per controllare l'ultimo esito:
```bash
tail -20 /var/log/allerte_sync.log
```

Per un backup coerente del database SQLite installa la CLI (`sudo apt install sqlite3`) e usa il comando `.backup`, ad esempio:

```cron
0 2 * * * sqlite3 /var/www/html/data/site.sqlite ".backup '/backups/protezionecivile-$(date +\%F).sqlite'"
```

Includere anche `assets/img/news/` nei backup. Il backup della cartella `data/` può conservare anche i file legacy originali; evitare di copiare solo `site.sqlite` mentre il sito è attivo senza usare `.backup`.

---

## 9. Dipendenze Composer (HTMLPurifier e mPDF)

HTMLPurifier e mPDF sono gestiti tramite **Composer**. La cartella `vendor/` non è inclusa nel repository.

### 9.1 Installazione

Assicurarsi che Composer sia installato sul server (`sudo apt install composer` su Debian/Raspberry Pi OS). Il repository non include `composer.json` né `composer.lock`: per il layout d'esempio con webroot `/var/www/html`, crea il progetto Composer e installa le dipendenze in `/var/www`, fuori dalla cartella pubblica:

```bash
cd /var/www
composer require ezyang/htmlpurifier:^4.19 mpdf/mpdf:^8.2
```

Il comando crea i manifest Composer e `/var/www/vendor/`, contenente le librerie e il relativo autoloader. Conserva `composer.json` e `composer.lock` fuori dal webroot: per i deploy successivi, esegui `composer install` in `/var/www` dopo aver ripristinato entrambi i manifest.

> La cartella `vendor/` deve stare **un livello sopra** il webroot (`html/`), non dentro di esso, per non essere accessibile via browser.

### 9.2 Struttura attesa

```
/var/www/
├── composer.json     ← creato/provisionato fuori dal repository
├── composer.lock     ← conservato per installazioni riproducibili
├── vendor/           ← generato da composer install, NON nel repository
│   └── autoload.php
└── html/             ← webroot
    ├── admin/
    ├── news/
    └── ...
```

### 9.3 Caricamento nei file PHP

I file che usano le dipendenze Composer caricano l'autoloader con un percorso relativo, indipendente dalla posizione assoluta del repository:

```php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
```

### 9.4 Aggiornamento

Per aggiornare le dipendenze in futuro:

```bash
cd /var/www
composer update ezyang/htmlpurifier mpdf/mpdf
```

### 9.5 Esportazione PDF dei rischi

L'esportazione usa mPDF per creare un PDF A4 scaricabile. Il contenuto dei rischi
è definito una sola volta in `rischi/contenuti_rischi.php` ed è incluso sia dalla
pagina web sia dal generatore PDF. In questo modo titoli e indicazioni restano
sincronizzati, mentre web e PDF possono avere presentazioni distinte.

Il server deve avere attive le estensioni PHP `mbstring`, `xml` e `gd`, oltre alle
dipendenze Composer. Verificare che `/var/www/vendor/autoload.php` sia leggibile
dal processo PHP.

Quando si aggiorna manualmente il sito, pubblicare insieme `rischi/rischi.php`,
`rischi/contenuti_rischi.php` e `rischi/stampa_rischi.php`: pagina web ed export
condividono il file dei contenuti. Il logo viene letto da `assets/img/logo.png`
e incorporato nel PDF da mPDF.

### 9.6 Aggiunta a .gitignore

Assicurarsi che `vendor/` sia escluso dal repository:

```
vendor/
```

---

## 10. Accessibilità e conformità

Il template è stato sviluppato tenendo conto delle linee guida WCAG 2.1 AA e dei criteri 
AGID per l'accessibilità dei siti della Pubblica Amministrazione, ma **non ha ricevuto 
alcuna certificazione formale di conformità** né una verifica di audit esterno.

> **Importante**: l'assenza di certificazione riguarda sia il template base sia, a maggior 
> ragione, qualsiasi sito realizzato a partire da esso. Contenuti, immagini, loghi, colori 
> e testi aggiunti o modificati da chi adotta il template (es. tramite `data/config.php`,
> pannello admin, o modifiche a `includes/` e `css/`) possono introdurre non conformità — 
> ad esempio testi alternativi mancanti su immagini caricate, contrasti insufficienti su 
> loghi personalizzati, o struttura semantica alterata. **È responsabilità di chi adotta 
> e personalizza il template effettuare le verifiche di accessibilità prima della 
> pubblicazione**, specialmente se il sito è gestito da un ente pubblico soggetto agli 
> obblighi AGID.

### 10.1 Verifiche consigliate
- WAVE WebAIM: https://wave.webaim.org/
- Chrome DevTools Lighthouse (Accessibility)
- Axe DevTools (estensione Chrome)
- Screen reader NVDA o VoiceOver
- Validazione HTML W3C: https://validator.w3.org/

---

## 11. Manutenzione

### 11.1 Aggiornare contenuti
- Modificare `data/config.php` per metadati, numeri e punti mappa
- Aggiornare le news tramite il pannello admin (salvate nel database SQLite)
- Per modifiche statiche, editare i file PHP in cartelle `includes/`, `news/`, `rischi/` e `area-formazione/`

### 11.2 Permessi e scrittura
Assicurarsi che il server web possa scrivere nella cartella `data/` e nel database SQLite `data/site.sqlite`.

### 11.3 Debug
Verifica la sintassi PHP con:

```bash
cd /var/www/html
find admin news -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

---

## 12. Troubleshooting

### La cache delle allerte è sempre vuota
- Verificare che `allerte/sync.php` venga eseguito e non produca errori:
```bash
  sudo -u www-data php /var/www/html/allerte/sync.php
```
- In alternativa, una chiamata a `allerte/get_allerta.php` dovrebbe generare la cache automaticamente al primo accesso (auto-sync), a meno che un altro processo stia sincronizzando o l'intervallo minimo di 60s non sia ancora trascorso — se la cache resta vuota anche dopo diverse richieste distanziate nel tempo, controllare i log per l'errore di parsing/download sottostante.
- Controllare che `pdo_sqlite` sia attivo e che `data/` sia scrivibile da `www-data`.
- Verificare la connettività verso il server esterno del Comune.
- La verifica SSL è sempre attiva (`CURLOPT_SSL_VERIFYPEER true` in `allerta_core.php`): assicurarsi che il certificato del server remoto sia valido, altrimenti il download fallirà.

### Errore 403 o 404 sulle pagine PHP
- Controllare che il modulo `mod_rewrite` di Apache sia abilitato:
  ```bash
  sudo a2enmod rewrite
  sudo systemctl restart apache2
  ```
- Verificare che `AllowOverride All` sia impostato nel virtual host.

### Errori di permesso su `data/`
Se hai seguito la configurazione rigida descritta nella sezione 4.3, usa il medesimo modello anche nel troubleshooting:

```bash
sudo chown -R utente-dati:www-data /var/www/html/data
sudo find /var/www/html/data -type d -exec chmod 770 {} \;
sudo find /var/www/html/data -type f -exec chmod 640 {} \;
```

Se il web server usa un altro nome utente, sostituisci `www-data` con quello corretto. Se invece l'ambiente richiede un owner uguale a quello del processo web, mantieni comunque i permessi il più restrittivi possibile e evita `777` o `770` applicati a tutta la struttura in modo indiscriminato.

### Dipendenza Composer non trovata
Assicurarsi di aver predisposto i manifest Composer come descritto nella sezione 9 e poi eseguito `composer install` in `/var/www/` (un livello sopra il webroot):
```bash
cd /var/www
composer install
```
Verificare che esista `/var/www/vendor/autoload.php`.

Se l'esportazione dei rischi mostra un errore di configurazione, verificare anche che mPDF sia installato:
```bash
cd /var/www
composer show mpdf/mpdf
```

### PHP non mostra errori in sviluppo
Aggiungere temporaneamente in cima al file PHP da debuggare:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
> Rimuovere sempre prima del deploy in produzione.

### Certificato SSL non si rinnova automaticamente
```bash
sudo certbot renew --dry-run
sudo systemctl status certbot.timer
```
Se il timer non è attivo:
```bash
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

---

## 13. Controlli SEO e dominio
- Il dominio corretto utilizzato nei file è `https://protezionecivile.comune.esempio.provincia.it`
- `sitemap.xml` e `robots.txt` devono puntare a questo dominio
- Verifica che le immagini Open Graph (`og:image`) e il JSON-LD in `includes/header.php` siano coerenti con il dominio

---

## 14. Note importanti
- HTMLPurifier e mPDF sono gestiti tramite Composer; eseguire `composer install` in `/var/www/` dopo ogni nuovo deploy (vedi sezione 9).
- Le password degli account sono salvate come hash `password_hash()` nel database SQLite `data/site.sqlite`.
- Non esporre la cartella `admin/` o `data/config.php` senza protezione su server pubblico.
- `data/config.php` non deve mai essere scrivibile dopo il setup iniziale — vedi sezione 4.3.1.
- In produzione, impostare sempre `CURLOPT_SSL_VERIFYPEER` a `true`.

---

## 15. Contatti tecnici
Per assistenza operativa o aggiornamenti futuri, usa questi file come riferimento:
- `data/config.php`
- `php/news_helper_admin.php`
- `allerte/sync.php`
- `includes/header.php`
- `css/style.css`

---

## 16. Note aggiuntive utili

### 16.1 Glossario e contenuti gestiti
Il sito include anche una sezione glossario, gestita tramite pannello admin e salvata in SQLite. È utile tenere aggiornati i termini principali e verificare che i contenuti siano coerenti con la nomenclatura del Comune o dell'ente.

### 16.2 Area formazione
La cartella `area-formazione/` contiene la sezione dedicata alla formazione e ai contenuti tematici. Se si aggiungono nuove pagine o materiali, è consigliabile mantenerne la struttura uniforme con il resto del sito.

### 16.3 Backup consigliati
Prima di aggiornamenti importanti o modifiche di configurazione, fare sempre backup di:
- `data/config.php`
- `data/`
- eventuali file personalizzati in `includes/`, `css/` e `js/`

### 16.4 Dipendenze frontend principali
Il progetto usa componenti come Bootstrap, Leaflet, Quill e HTMLPurifier. In caso di aggiornamenti o problemi di compatibilità, verificare il comportamento delle pagine che li utilizzano.

### 16.5 Gestione amministrativa
Oltre alle news, il pannello admin consente di gestire glossario, utenti e sincronizzazione delle allerte. Il database `data/site.sqlite` e la cartella `data/` devono restare accessibili in scrittura al processo PHP e protetti dall'accesso pubblico.

---

**Ultima revisione**: 8 novembre 2026