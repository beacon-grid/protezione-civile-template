# Come contribuire

Grazie per l'interesse verso questo progetto. Prima di aprire una issue o una 
pull request, ti chiedo di leggere questo documento: chiarisce cosa puoi 
aspettarti da questo repository e cosa no.

---

## 0. Prima di tutto: nessun supporto garantito

Questo è un progetto amatoriale, sviluppato e mantenuto a titolo personale nel 
tempo libero da un singolo volontario. Non è un prodotto commerciale né un 
servizio con SLA.

Questo significa in pratica che:
- **Non c'è garanzia di risposta** a issue o pull request, né entro tempi 
  definiti né in assoluto;
- **Non c'è impegno di manutenzione continuativa**: il progetto potrebbe 
  ricevere aggiornamenti saltuari o restare fermo per lunghi periodi;
- **Non c'è impegno a rilasciare patch di sicurezza tempestive**. Se adotti 
  questo template in un contesto reale, sei responsabile di monitorare 
  autonomamente eventuali vulnerabilità e di applicare le correzioni 
  necessarie, anche senza attendere un intervento da parte del mantenitore;
- **Non fornisco assistenza diretta** per l'installazione, la configurazione 
  o la personalizzazione del template per il tuo Comune/associazione. Per 
  questo tipo di supporto, valuta di rivolgerti a un professionista o a 
  un'agenzia web.

Se hai bisogno di un sistema con garanzie di manutenzione e supporto, questo 
progetto — come la maggior parte del software libero distribuito a titolo 
gratuito — probabilmente non è la soluzione adatta senza un accordo separato 
con chi lo implementa per te.

---

## 1. Come segnalare un problema (issue)

Prima di aprire una issue, verifica che non ne esista già una simile.

Per bug report, includi:
- versione di PHP e del server web utilizzati;
- passi per riprodurre il problema;
- comportamento atteso vs comportamento osservato;
- eventuali messaggi di errore (log, console del browser).

Per richieste di funzionalità, spiega il contesto d'uso: aiuta a valutare se 
la richiesta ha senso per un template generico, riutilizzabile da qualsiasi 
gruppo, e non solo per un caso specifico.

---

## 2. Segnalazione di vulnerabilità di sicurezza

**Non aprire una issue pubblica per vulnerabilità di sicurezza.**

Se scopri un problema di sicurezza (es. SQL injection, XSS, bypass di 
autenticazione, esposizione di dati sensibili), segnalalo privatamente tramite [beacon-grid@protonmail.com](mailto:beacon-grid@protonmail.com).

Ricorda che, come indicato nella sezione "Nessuna garanzia" del README, non 
c'è impegno a un tempo di risposta garantito, ma le segnalazioni di sicurezza 
hanno priorità rispetto ad altre richieste.

---

## 3. Come proporre una pull request

1. Fai un fork del repository.
2. Crea un branch descrittivo (es. `fix/validazione-form-contatti`, 
   `feature/dark-mode`).
3. Mantieni le modifiche focalizzate: una PR = una modifica logica. 
   PR che mescolano più cambiamenti non correlati sono più difficili da 
   revisionare e più lente da accettare.
4. Verifica che il codice PHP sia sintatticamente corretto:
```bash
   php -l percorso/del/file.php
```
5. **Non includere mai dati reali** nella tua PR: nomi di Comuni reali, 
   indirizzi, numeri di telefono, credenziali, chiavi API o token, anche 
   solo a scopo di test. Usa sempre i placeholder già presenti in 
   `data/config.php` o valori palesemente fittizi.
6. Se la tua modifica introduce nuove dipendenze (composer, librerie JS), 
   spiega nella descrizione della PR perché sono necessarie.
7. Descrivi nella PR cosa cambia e perché, non solo "come".

Aprendo una pull request, accetti implicitamente che il tuo contributo venga 
distribuito con la stessa licenza del progetto (AGPL-3.0) — vedi il file 
`LICENCE.md`.

---

## 4. Cosa NON verrà accettato

- Contributi che aggiungono loghi, emblemi o denominazioni ufficiali 
  reali (Dipartimento Protezione Civile, Comuni specifici, associazioni 
  reali) al posto dei placeholder generici — vedi la sezione "Marchi e 
  segni distintivi" nel README.
- Contributi che introducono tracking, analytics invasivi o dipendenze da 
  servizi di terze parti non necessari, senza discussione preventiva in 
  una issue.
- Contributi che rimuovono o alterano le note di licenza/copyright presenti 
  nei file sorgente.
- Funzionalità che richiedono raccolta di dati personali senza un 
  meccanismo di consenso o un'informativa privacy di base inclusa.

---

## 5. Comportamento

Non è previsto un codice di condotta formale separato: valgono le regole di 
buon senso e rispetto reciproco già proprie delle comunità open source — 
toni civili, critiche costruttive sul codice e non sulle persone, pazienza 
verso chi contribuisce per la prima volta.

---

## 6. Rapporto con enti e associazioni

Se rappresenti un gruppo comunale, un'associazione di volontariato o un 
ente locale e vuoi segnalare esigenze specifiche del tuo contesto (es. 
integrazione con un sistema regionale di allerta diverso da quello 
d'esempio), sei benvenuto ad aprire una issue — ma ricorda che, come 
indicato al punto 0, l'implementazione pratica per il tuo caso specifico 
resta a tuo carico o di chi incarichi per farlo.

---

*Ultimo aggiornamento: 8 novembre 2026*