# Documentazione Area Admin

Questa cartella contiene il pannello di amministrazione per la gestione delle news, degli utenti e dell'accesso riservato.
Il codice gestisce autenticazione, sessioni, CSRF, rate limiting, gestione delle news e amministrazione degli account.

## File principali
- `index.php` - pagina di login dell'area riservata
- `auth.php` - autenticazione, sessione, rate limiting, CSRF, verifica ruoli e obbligo di cambio password
- `logout.php` - chiude la sessione e reindirizza al login
- `news.php` - pannello principale per la gestione delle news
- `news_edit.php` - form per creare o modificare una news
- `php/news_helper_admin.php` - funzioni per leggere, salvare, validare e pulire le news
- `manage_users.php` - gestione utenti, attivazione/disattivazione, reset credenziali e cancellazione
- `change_password.php` - cambio password obbligatorio per gli utenti appena creati o resettati
- `dashboard.php` - metriche, azioni rapide e gestione dello stato di emergenza
- `glossario.php` - gestione delle voci e delle impostazioni del glossario
- `site_search_settings.php` - attivazione della ricerca pubblica e selezione delle sezioni incluse

## Funzionamento generale

### Autenticazione
- Il login richiede username, password e codice OTP (TOTP).
- L'accesso è protetto da CSRF e da un sistema di rate limiting.
- Dopo 5 tentativi falliti, l'IP viene bloccato per 15 minuti.
- La sessione ha una durata massima di 1 ora e termina dopo 30 minuti di inattività. Usa cookie `HttpOnly` e `SameSite=Lax`; se il sito è riconosciuto come servito via HTTPS, viene attivato anche `Secure`.
- A ogni richiesta autenticata vengono verificati l'indirizzo IP e lo user agent registrati al login.
- Le pagine amministrative richiedono il cambio password obbligatorio quando previsto.

### Gestione utenti
- Tutti gli account sono salvati nel database SQLite `data/site.sqlite`; il ruolo superadmin è separato dagli utenti gestibili.
- Admin e superadmin possono creare utenti, reimpostare password e OTP, disabilitare o eliminare gli account gestibili dal proprio ruolo.
- Il superadmin accede solo alla gestione utenti: dashboard, contenuti e sincronizzazione non sono presenti nel menu e le relative pagine lo escludono tramite controllo del ruolo.
- Le password vengono salvate come hash con `password_hash()`.
- La creazione di un nuovo utente genera una password temporanea e un segreto TOTP da mostrare una sola volta.
- Il pulsante di stato aggiorna attivazione/disattivazione dell'account e verifica la persistenza del nuovo valore.
- Gli account superadmin sono archiviati separatamente nel database e non compaiono nell'elenco degli utenti gestibili.

### Stato di emergenza
- Dalla dashboard si usa il pulsante contestuale `Attiva allerta` oppure `Annulla allerta`.
- L'attivazione apre un modal Bootstrap con tipo problema, titolo e descrizione obbligatori.
- I dati vengono salvati in SQLite e le modifiche sono protette da token CSRF.
- Quando attivo, il sito pubblico reindirizza a `/emergency.php`; la pagina mostra il popup dell'emergenza a ogni apertura.
- Dalla pagina emergenza l'utente può scegliere `emergency_bypass=1` per aprire il sito completo: il server registra un cookie di sessione e consente la navigazione senza disattivare l'emergenza globale.
- I link interni della pagina emergenza includono automaticamente il bypass; il parametro non concede accesso all'area admin e non modifica lo stato salvato in SQLite.

### Editor news
- L'editor Quill dispone di un picker glossario ricercabile, con inserimento del termine nella selezione corrente.
- Il form rileva le modifiche a campi, switch, immagine e contenuto Quill; salva e annulla sono abilitati solo quando necessario.
- Sono disponibili `Ctrl/Cmd+S` globale alla pagina, `Ctrl/Cmd+B`, `Ctrl/Cmd+I` e `Ctrl/Cmd+G` nell'editor.

### Salvataggio delle news
- Le news vengono salvate nel database SQLite `data/site.sqlite`.
- Le immagini caricate vengono memorizzate in `assets/img/news/`.
- I percorsi delle immagini vengono validati per evitare path traversal e upload non autorizzati.
- Il contenuto HTML viene pulito con una whitelist di tag e attributi prima di essere salvato.

### Sicurezza applicata
- Il login e i form amministrativi usano token CSRF.
- Le pagine restituiscono header di sicurezza come `X-Frame-Options`, `X-Content-Type-Options` e una policy CSP base.
- La visualizzazione degli errori PHP è disattivata nelle pagine di autenticazione; i dettagli tecnici restano nei log del server e non vengono esposti all'utente.
- Con Apache e `mod_headers` attivo, il file `.htaccess` alla root invia HSTS su tutto il sito; per Nginx l'header va configurato nel server block.
- I file sensibili della cartella `data/` sono protetti da `.htaccess` e non devono essere accessibili direttamente dal browser.

## Validazione delle news
- Campi obbligatori: `titolo`, `sommario`, `contenuto`, `categoria`, `data`.
- `immagine` deve essere un percorso valido dentro `assets/img/news/` e puntare a un file esistente.
- Se è presente un'immagine, `immagine_alt` è obbligatorio e non può superare 140 caratteri.
- La categoria deve appartenere all'elenco supportato.

## Database e migrazione
- `php/database.php` inizializza `data/site.sqlite` e importa una sola volta i dati legacy da JSON e dai file PHP degli account.
- News e voci del glossario sono salvate nelle tabelle relazionali `app_news` e `app_glossary_entries`; le impostazioni del glossario sono in `app_glossary_settings`. La migrazione dei contenuti è versionata e rimuove i JSON aggregati dal database solo dopo il trasferimento riuscito.
- Account, emergenze, tentativi di accesso e cache sono gestiti da SQLite.
- `data/.htaccess` blocca l'accesso HTTP diretto al database e ai file di dati.

## Permessi necessari
Il server web deve poter scrivere:
- nella cartella `data/` e nel database `data/site.sqlite` (SQLite usa anche file temporanei accanto al DB)
- `assets/img/news/` per gli upload delle immagini

Mantieni permessi restrittivi e non esporre `data/` pubblicamente. Per i dettagli su proprietario e permessi del database, consulta la sezione 4.3 del README principale.
