# Documentazione Notizie

Questa cartella contiene la parte pubblica delle news del sito.
Le news vengono caricate dal database SQLite `data/site.sqlite` e visualizzate nelle pagine di elenco e dettaglio.

---

## File principali
- `index.php` - elenco pubblico delle news, con filtri per categoria e anno e paginazione
- `detail.php` - visualizzazione di una singola news

---

## Come funziona

### news/index.php
- Carica tutte le news pubblicate con `news_published()` da `../includes/news_public.php`.
- Supporta i seguenti parametri in query string:
  - `?cat=...` - filtra per categoria valida (vedi elenco categorie più sotto)
  - `?anno=YYYY` - filtra per anno di pubblicazione
  - `?anno=0` - mostra le news di **tutti** gli anni
  - `?page=N` - naviga tra le pagine dei risultati
  - I parametri sono combinabili, es. `?cat=allerta&anno=2025&page=2`
- **Filtro anno di default**: se `?anno` non è presente in URL, la pagina mostra **solo le news dell'anno corrente** (non tutte le news pubblicate). Per vedere lo storico completo è necessario passare esplicitamente `?anno=0` o cliccare su "Tutti gli anni" nei filtri. È un comportamento intenzionale ma da tenere presente: un utente che apre `news/` senza parametri non vede automaticamente gli articoli degli anni precedenti.
- **Paginazione**: implementata, 12 news per pagina (`$per_page = 12`), con link Precedente/Successiva e numerazione pagine con ellissi per elenchi lunghi.
- Mostra una card per ogni news con:
  - immagine di anteprima (se presente), con `alt` generato da titolo + sommario se `immagine_alt` non è impostato
  - data formattata (`d/m/Y`)
  - categoria con badge colorato (classi CSS custom `cat-badge-*`, definite inline nella pagina)
  - titolo e sommario
  - link verso `detail.php?id=...`
- Se non ci sono articoli per i filtri selezionati, mostra un messaggio di nessuna news trovata, con indicazione dei filtri attivi e un pulsante per azzerarli.

### news/detail.php
- Valida l'ID passato in query string (`?id=...`) con regex `^[a-f0-9]{32}$`.
- Verifica che l'articolo esista e sia pubblicato.
- Se non valido o non trovato, restituisce HTTP 404 e pagina di errore.
- Usa **HTMLPurifier** per pulire il contenuto HTML prima della visualizzazione (vedi sezione dedicata più sotto).
- Formatta la data in italiano tramite `IntlDateFormatter` (es. "10 giugno 2026"), con fallback automatico a `date('d/m/Y')` se l'estensione `intl` di PHP non è disponibile sul server.
- Mostra titolo, sommario, immagine con `alt` (da `immagine_alt`, fallback sul titolo), categoria con badge colorato (classi Bootstrap `bg-*` tramite `news_categoria_color()`) e data.

**Esempio di URL di dettaglio:**
```
https://protezionecivile.comune.esempio.provincia.it/news/detail.php?id=a1b2c3d4e5f6...
```
Il file si trova in `news/detail.php` e viene raggiunto direttamente — non da root.

> Nota di coerenza stilistica: `index.php` e `detail.php` renderizzano il colore della categoria in due modi diversi (classi CSS custom vs classi Bootstrap `bg-*`). È intenzionale (contesti grafici diversi: card vs pagina dettaglio), va tenuto a mente se in futuro si modificano i colori delle categorie: vanno aggiornati in entrambi i punti.

---

## HTMLPurifier: caricamento e whitelist

### Percorso di caricamento
`detail.php` carica HTMLPurifier con un percorso relativo alla struttura del progetto:
```php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
```
Il percorso risale dalla cartella pubblica `html/` alla directory `/var/www/` che contiene `vendor/`, rendendo il caricamento indipendente dal dominio e dal nome del virtual host.

**Implicazione pratica**: durante il deploy, mantenere `vendor/` al livello superiore rispetto alla cartella pubblica `html/`, come descritto nel README principale, e installarlo con `composer install`.

### Whitelist HTML applicata
Il contenuto HTML delle news (campo `contenuto`, inserito via editor WYSIWYG in area admin) viene purificato sia al salvataggio sia prima della visualizzazione. Le due whitelist non sono identiche:

- **Al salvataggio**, `news_clean_html()` in `php/news_helper_admin.php` conserva i tag `p, br, strong, b, em, i, ul, ol, li, a, h2, h3, h4, blockquote`; per i link conserva `href` e ammette gli schemi `http` e `https`.
- **Alla visualizzazione**, `detail.php` applica una seconda whitelist: `p, br, strong, b, em, i, u, s, ul, ol, li, a, h2, h3, h4, h5, h6, blockquote, pre, code`; per i link ammette gli attributi `href` e `title` e gli schemi `http`, `https` e `mailto`.

La whitelist al salvataggio è più restrittiva: i tag e gli attributi che non ammette vengono rimossi già quando la news è salvata; il filtro di visualizzazione non può ripristinarli. In pratica l'editor non conserva immagini inline, tabelle, `class`/`style`, `<h1>`, `<h5>`, `<h6>`, `<pre>`, `<code>` o attributi diversi da `href`. Lo schema `mailto:` è ammesso dal filtro di visualizzazione, ma non da quello al salvataggio nell'area admin. Un contenuto proveniente da dati legacy può quindi avere un risultato diverso da uno salvato oggi dall'editor.

### Normalizzazione automatica dei link
Dopo la purificazione in visualizzazione, `detail.php` applica una funzione custom (`normalize_news_links()`) ai link rimasti:
- **link interni** (stesso host, percorsi relativi, ancore `#...`, query `?...`, `mailto:`) → `target` e `rel` vengono rimossi, se presenti
- **link esterni** (`http`/`https` verso un altro host) → viene forzato `target="_blank" rel="noopener noreferrer"`

Questo comportamento è automatico e **sovrascrive** qualsiasi impostazione di `target`/`rel` presente nei dati. Lo schema `tel:` non è ammesso dal filtro HTMLPurifier e il relativo link viene rimosso prima della normalizzazione. La funzione non è configurabile da `data/config.php`; per modificarla bisogna intervenire in `detail.php`.

### Requisiti tecnici
- **Cache HTMLPurifier**: la cache di parsing viene scritta in `sys_get_temp_dir()` (tipicamente `/tmp`). Il processo PHP/web server deve avere permessi di scrittura su questa directory, altrimenti HTMLPurifier funziona comunque ma senza cache (con un piccolo overhead ad ogni richiesta) — utile da sapere in fase di troubleshooting permessi.
- **Estensione `php-intl`**: usata da `detail.php` per la data in italiano (`IntlDateFormatter`). Non è tra le estensioni elencate nella sezione 2 del README principale del progetto. È **opzionale** grazie al fallback su `date('d/m/Y')`, ma senza di essa si perde la formattazione estesa ("10 giugno 2026") a favore del formato numerico ("10/06/2026"). Consigliato installarla:
  ```bash
  sudo apt install php8.4-intl
  ```

---

## Gestione dei contenuti
Le news non devono essere modificate direttamente in questa cartella.
Usa l'area admin (`admin/`) per creare, aggiornare o eliminare gli articoli.

## Glossario nel testo
Nelle pagine di dettaglio il glossario viene applicato esclusivamente al contenitore `.news-content`, quindi titoli, menu, sommari e altri elementi del sito non vengono modificati. I termini riconosciuti mostrano una piccola icona informativa Bootstrap e aprono la definizione con click o tastiera. Il contenuto dell'articolo viene inoltre adattato per spezzare parole, URL e stringhe molto lunghe senza spazi.

---

## Struttura dei dati delle news

Ogni news è archiviata nella tabella relazionale `app_news`. Le pagine PHP la espongono con questa struttura:

```json
{
  "id": "a1b2c3d4e5f67890abcdef1234567890",
  "titolo": "Titolo della news",
  "data": "2026-06-10",
  "categoria": "esercitazione",
  "sommario": "Breve descrizione dell'articolo.",
  "contenuto": "<p>Testo HTML completo dell'articolo.</p>",
  "immagine": "assets/img/news/foto.jpg",
  "immagine_alt": "Descrizione alternativa esplicita dell'immagine",
  "in_evidenza": true,
  "pubblicato": true
}
```

### Formato dei campi

**`id`** — stringa esadecimale di 32 caratteri. Viene generato automaticamente dal pannello admin. Per un'eventuale importazione manuale, generare un ID con:
```php
echo md5(uniqid(rand(), true));
```
oppure da terminale:
```bash
php -r "echo md5(uniqid(rand(), true)) . PHP_EOL;"
```
Non usare ID numerici o di altro formato: la validazione in `detail.php` accetta solo 32 caratteri esadecimali (`^[a-f0-9]{32}$`, senza distinzione tra maiuscole e minuscole).

**`data`** — stringa in formato ISO 8601: `YYYY-MM-DD` (es. `2026-06-10`). Viene usata sia per la formattazione (italiana in `detail.php`, `d/m/Y` in `index.php`) sia per il **filtro anno di default** in `index.php` (vedi sopra): un articolo con data dell'anno scorso non comparirà nell'elenco principale finché non si seleziona esplicitamente quell'anno o "Tutti gli anni".

**`immagine_alt`** — testo alternativo esplicito per l'immagine, usato come `alt` in `detail.php` se presente. In `news/index.php` l'`alt` viene invece generato dinamicamente da titolo e sommario. Se `immagine_alt` è vuoto o assente, anche `detail.php` ricade sul titolo come fallback.

**`in_evidenza`** — se `true`, la news viene contrassegnata con un badge visivo nell'elenco e può apparire nella sezione news in homepage (`includes/news_section.php`). Non influenza l'ordinamento, che rimane cronologico inverso.

**`pubblicato`** — solo le news con `true` vengono mostrate nelle pagine pubbliche. Le news con `false` sono conservate nel database ma non visibili.

---

## Note su sicurezza
- `news_public.php` legge le news dal database e restituisce solo gli articoli con `pubblicato: true`.
- Le pagine pubbliche eseguono `htmlspecialchars()` su tutti i campi testuali (titolo, sommario, categoria, data).
- Il campo `contenuto`, che può contenere HTML ricco inserito dall'editor WYSIWYG, viene purificato con HTMLPurifier al salvataggio e nuovamente prima della visualizzazione in `detail.php` (whitelist descritte sopra), riducendo il rischio di XSS da markup non ammesso o malformato.
- I link nel contenuto vengono inoltre normalizzati automaticamente (vedi sopra) per evitare `target="_blank"` senza `rel="noopener noreferrer"` su link esterni, mitigando il rischio di [reverse tabnabbing](https://owasp.org/www-community/attacks/Reverse_Tabnabbing).
- La cartella `data/` è protetta da accesso diretto via URL. Non rimuovere questa protezione.
- Il processo PHP deve poter scrivere nella cartella `data/` e nel database `site.sqlite`; mantieni i permessi restrittivi e non usare permessi globali.

---

## Categorie supportate
Le categorie sono definite in `php/news_helper_admin.php` e includono almeno:
- `esercitazione`
- `prevenzione`
- `formazione`
- `allerta`
- `evento`
- `comunicato`

Usare categorie non presenti in questa lista causa la mancata visualizzazione del badge colore e può produrre risultati imprevisti nel filtro per categoria. Per aggiungere nuove categorie, modificare `php/news_helper_admin.php` **e** aggiornare le classi CSS `cat-badge-*` / `filtro-cat-btn-*` in `index.php` e la funzione `news_categoria_color()` usata in `detail.php`, altrimenti la nuova categoria comparirà senza colore coerente.

---

## Database SQLite

News e altri dati modificabili sono salvati in `data/site.sqlite`. Le news sono in `app_news`; la migrazione versionata trasferisce i dati precedentemente archiviati come JSON nella tabella dedicata e rimuove la copia aggregata solo dopo il trasferimento riuscito. L'importazione dei file legacy non li elimina automaticamente. Per creare, aggiornare o eliminare news usa il pannello admin.

Per ridurre il rischio di perdita, esegui backup regolari del database con `sqlite3 data/site.sqlite ".backup '/percorso/backup.sqlite'"` e includi anche `assets/img/news/`.

---

**Ultima revisione**: 8 novembre 2026