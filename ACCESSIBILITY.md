# Accessibilità

## Stato del template

Questo template è stato sviluppato tenendo conto delle linee guida **WCAG 2.1 
livello AA** e dei criteri di accessibilità **AGID** per i siti della Pubblica 
Amministrazione.

Non è però stata effettuata **alcuna certificazione formale di conformità**, né 
un audit esterno indipendente. Le indicazioni seguenti descrivono l'approccio 
adottato in fase di sviluppo, non una garanzia di conformità.

---

## Cosa è stato fatto nel template base

Nella sua configurazione originale, senza personalizzazioni, il template adotta 
accorgimenti come:
- markup HTML semantico (landmark, heading gerarchici, `alt` sulle immagini 
  incluse nel template);
- colori predefiniti scelti per favorire il contrasto; il contrasto va comunque verificato sulle pagine e sui contenuti effettivamente pubblicati;
- navigabilità da tastiera sui componenti interattivi inclusi (menu, form, 
  mappa, galleria);
- compatibilità di base con screen reader sui componenti standard.

Questi accorgimenti riguardano **solo** il codice e i contenuti di esempio così 
come distribuiti in questo repository.

---

## Cosa NON è coperto

La personalizzazione del sito (tramite `data/config.php`, pannello admin, o modifiche
a `includes/`, `css/`, `news/`) può introdurre problemi di accessibilità non 
presenti nel template originale, ad esempio:
- immagini caricate senza testo alternativo;
- loghi o colori personalizzati con contrasto insufficiente;
- contenuti aggiunti nell'editor news con struttura semantica scorretta 
  (es. uso di grassetto al posto di heading);
- PDF o allegati caricati non accessibili.

**È responsabilità di chi adotta e personalizza il template verificare 
l'accessibilità del proprio sito prima della pubblicazione**, in particolare se 
gestito da un ente pubblico soggetto agli obblighi AGID (L. 4/2004, art. 3-bis 
CAD e succ. mod.).

---

## Verifiche consigliate

Prima della pubblicazione, si consiglia di eseguire almeno:
- [WAVE WebAIM](https://wave.webaim.org/)
- Chrome DevTools → Lighthouse (sezione Accessibility)
- [Axe DevTools](https://www.deque.com/axe/devtools/) (estensione browser)
- Test con screen reader (NVDA su Windows, VoiceOver su macOS)
- [Validatore HTML W3C](https://validator.w3.org/)
- Verifica manuale della navigazione completa da tastiera (senza mouse)

Per i siti di enti pubblici, va inoltre predisposta la **dichiarazione di 
accessibilità** annuale prevista dalla normativa AGID, generata tramite il 
form ufficiale su [form-agid](https://form.agid.gov.it/), separata da questo 
documento tecnico.

---

## Segnalazioni

Eventuali problemi di accessibilità riscontrati nel template base (non nelle 
personalizzazioni di singoli adottanti) possono essere segnalati aprendo una 
issue sul repository — vedi `CONTRIBUTING.md`.

---

*Ultimo aggiornamento: 8 novembre 2026*