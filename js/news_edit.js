// ── Contatori caratteri ───────────────────────────────────
function initCounter(inputId, countId) {
    const el = document.getElementById(inputId);
    const counter = document.getElementById(countId);
    if (!el || !counter) return;
    const max = parseInt(el.getAttribute('maxlength') || '999');
    const update = () => {
        const n = el.value.length;
        counter.textContent = n;
        counter.parentElement.classList.toggle('warn', n > max * .85);
    };
    el.addEventListener('input', update);
    update();
}
initCounter('titolo', 'titolo-count');
initCounter('sommario', 'sommario-count');

// ── Quill ─────────────────────────────────────────────────
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: 'Testo completo dell\'articolo…',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            [{ header: [2, 3, false] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link'],
            ['clean']
        ]
    }
});

// ── Inserimento rapido termini del glossario ──────────────
(function initGlossaryPicker() {
    const toolbar = document.querySelector('#quill-editor')
        ? document.querySelector('#quill-editor').parentElement.querySelector('.ql-toolbar')
        : null;
    if (!toolbar || !quill) return;
    const pickerHost = document.createElement('div');
    pickerHost.className = 'glossary-picker-host';
    toolbar.parentNode.insertBefore(pickerHost, document.querySelector('#quill-editor'));

    const pickerGroup = document.createElement('span');
    pickerGroup.className = 'ql-formats ql-glossary-group';
    const pickerButton = document.createElement('button');
    pickerButton.type = 'button';
    pickerButton.className = 'ql-glossary';
    pickerButton.setAttribute('aria-label', 'Inserisci termine dal glossario');
    pickerButton.title = 'Inserisci termine dal glossario';
    pickerButton.innerHTML = '<i class="bi bi-book" aria-hidden="true"></i>';
    pickerGroup.appendChild(pickerButton);
    toolbar.appendChild(pickerGroup);

    let entries = [];
    let loaded = false;
    let loading = false;
    let savedSelection = { index: 0, length: 0 };

    function normalize(value) {
        return String(value || '').trim().toLocaleLowerCase('it-IT').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function closePicker() {
        const picker = pickerHost.querySelector('.glossary-picker');
        if (picker) picker.remove();
        pickerButton.setAttribute('aria-expanded', 'false');
    }

    function insertTerm(entry) {
        if (!entry || !entry.termine) return;
        quill.focus();
        quill.setSelection(savedSelection.index, savedSelection.length, 'silent');
        quill.deleteText(savedSelection.index, savedSelection.length, 'user');
        quill.insertText(savedSelection.index, entry.termine + ' ', 'user');
        quill.setSelection(savedSelection.index + entry.termine.length + 1, 0, 'silent');
        closePicker();
    }

    function renderResults(container, search) {
        const query = normalize(search);
        const matches = entries.filter(function (entry) {
            return normalize(entry.termine).includes(query) || normalize(entry.definizione).includes(query);
        }).slice(0, 8);

        container.innerHTML = '';
        if (!matches.length) {
            container.innerHTML = '<div class="glossary-picker-empty">Nessun termine trovato.</div>';
            return;
        }

        matches.forEach(function (entry) {
            const result = document.createElement('button');
            result.type = 'button';
            result.className = 'glossary-picker-result';
            result.innerHTML = '<strong></strong><small></small>';
            result.querySelector('strong').textContent = entry.termine;
            result.querySelector('small').textContent = entry.definizione;
            result.addEventListener('mousedown', function (event) {
                event.preventDefault();
                insertTerm(entry);
            });
            container.appendChild(result);
        });
    }

    function loadEntries() {
        if (loaded || loading) return Promise.resolve();
        loading = true;
        return Promise.all([
            fetch('/php/glossario_public.php?type=settings').then(response => response.ok ? response.json() : { enabled: false }),
            fetch('/php/glossario_public.php?type=entries').then(response => response.ok ? response.json() : [])
        ]).then(function ([settings, data]) {
            entries = settings.enabled && Array.isArray(data) ? data.filter(entry => entry && entry.termine && entry.definizione) : [];
            loaded = true;
        }).catch(function () {
            entries = [];
            loaded = true;
        }).finally(function () {
            loading = false;
        });
    }

    function openPicker() {
        const current = quill.getSelection();
        if (current) savedSelection = current;
        closePicker();

        const picker = document.createElement('div');
        picker.className = 'glossary-picker';
        picker.setAttribute('role', 'dialog');
        picker.setAttribute('aria-label', 'Inserisci termine del glossario');
        picker.innerHTML = '<div class="glossary-picker-title">Inserisci dal glossario</div>' +
            '<input type="search" class="form-control form-control-sm glossary-picker-search" placeholder="Cerca termine o definizione..." aria-label="Cerca nel glossario" autocomplete="off">' +
            '<div class="glossary-picker-results" aria-live="polite"></div>';
        pickerHost.appendChild(picker);
        pickerButton.setAttribute('aria-expanded', 'true');

        const search = picker.querySelector('.glossary-picker-search');
        const results = picker.querySelector('.glossary-picker-results');
        search.addEventListener('input', function () { renderResults(results, search.value); });
        loadEntries().then(function () {
            renderResults(results, search.value);
            search.focus();
        });
    }

    quill.on('selection-change', function (range) {
        if (range) savedSelection = range;
    });
    quill.root.addEventListener('keydown', function (event) {
        if (!event.ctrlKey && !event.metaKey) return;

        const key = event.key.toLowerCase();
        if (key === 'g') {
            event.preventDefault();
            if (pickerHost.querySelector('.glossary-picker')) closePicker();
            else openPicker();
        }
    });
    pickerButton.addEventListener('click', function () {
        if (pickerHost.querySelector('.glossary-picker')) closePicker();
        else openPicker();
    });
    document.addEventListener('mousedown', function (event) {
        if (!toolbar.contains(event.target) && !pickerHost.contains(event.target)) closePicker();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && pickerHost.querySelector('.glossary-picker')) closePicker();
    });
})();

// Carica contenuto esistente (modalità modifica)
// Usa l'API ufficiale del clipboard invece di scrivere direttamente
// nel DOM con quill.root.innerHTML
const existingContent = document.getElementById('contenuto').value;
if (existingContent) {
    quill.clipboard.dangerouslyPasteHTML(existingContent);
}

// Prima del submit copia l'HTML nel textarea hidden
// Usa getSemanticHTML() (Quill 2.0+): produce HTML pulito senza gli span
// di servizio ql-ui e le classi interne di Quill
document.querySelector('form').addEventListener('submit', () => {
    document.getElementById('contenuto').value = quill.getSemanticHTML();
});

// ── Stato modifiche e azioni del form ─────────────────────
const newsForm = document.querySelector('form');
const saveButton = document.getElementById('news-save-button');
const cancelButton = document.getElementById('news-cancel-button');
let newsSaving = false;
let initialNewsSnapshot = null;

function readNewsSnapshot() {
    const fields = ['titolo', 'sommario', 'immagine_alt', 'data', 'categoria', 'immagine'];
    const snapshot = {};
    fields.forEach(id => {
        const field = document.getElementById(id);
        snapshot[id] = field ? field.value : '';
    });
    ['pubblicato', 'in_evidenza'].forEach(id => {
        const field = document.getElementById(id);
        snapshot[id] = field ? field.checked : false;
    });
    snapshot.contenuto = quill.getSemanticHTML();
    return JSON.stringify(snapshot);
}

function updateNewsDirtyState() {
    if (!newsForm || !saveButton || initialNewsSnapshot === null) return;
    const dirty = readNewsSnapshot() !== initialNewsSnapshot;
    newsForm.classList.toggle('has-unsaved-changes', dirty);
    saveButton.disabled = !dirty || newsSaving;
    saveButton.innerHTML = dirty
        ? '<i class="bi bi-floppy-fill me-2" aria-hidden="true"></i>Salva modifiche'
        : '<i class="bi bi-check2 me-2" aria-hidden="true"></i>Tutto salvato';
    if (cancelButton) {
        cancelButton.classList.toggle('is-disabled', !dirty || newsSaving);
        cancelButton.setAttribute('aria-disabled', dirty && !newsSaving ? 'false' : 'true');
        cancelButton.tabIndex = dirty && !newsSaving ? 0 : -1;
    }
}

if (newsForm) {
    initialNewsSnapshot = readNewsSnapshot();
    newsForm.addEventListener('input', updateNewsDirtyState);
    newsForm.addEventListener('change', updateNewsDirtyState);
    quill.on('text-change', updateNewsDirtyState);
    newsForm.addEventListener('submit', function (event) {
        if (newsSaving) {
            event.preventDefault();
            return;
        }
        newsSaving = true;
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvataggio...';
        }
        if (cancelButton) {
            cancelButton.classList.add('is-disabled');
            cancelButton.setAttribute('aria-disabled', 'true');
            cancelButton.tabIndex = -1;
        }
    });
    if (cancelButton) {
        cancelButton.addEventListener('click', function (event) {
            if (cancelButton.getAttribute('aria-disabled') === 'true') event.preventDefault();
        });
    }
    updateNewsDirtyState();
    window.addEventListener('beforeunload', function (event) {
        if (!newsSaving && newsForm.classList.contains('has-unsaved-changes')) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
}

// Ctrl/Cmd+S salva dalla pagina intera, indipendentemente dal campo attivo.
document.addEventListener('keydown', function (event) {
    if ((!event.ctrlKey && !event.metaKey) || event.key.toLowerCase() !== 's') return;
    event.preventDefault();
    if (newsForm && saveButton && !saveButton.disabled && typeof newsForm.requestSubmit === 'function') {
        newsForm.requestSubmit();
    }
});

// ── Anteprima immagine ────────────────────────────────────
document.getElementById('immagine_upload').addEventListener('change', function () {
    const preview = document.getElementById('img-preview');
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(file);
        document.getElementById('immagine').value = '';
    } else {
        preview.style.display = 'none';
    }
});

// ── Auto-fill testo alternativo dal titolo ────────────────
const titleInput = document.getElementById('titolo');
const altInput   = document.getElementById('immagine_alt');
if (titleInput && altInput) {
    altInput.dataset.auto = 'true';
    titleInput.addEventListener('input', () => {
        if (altInput.dataset.auto !== 'true') return;
        altInput.value = titleInput.value.trim();
    });
    altInput.addEventListener('input', () => { altInput.dataset.auto = 'false'; });
}