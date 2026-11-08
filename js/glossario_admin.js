(function () {
    'use strict';

    var rowsContainer = document.getElementById('glossary-rows');
    if (!rowsContainer) return;
    var form = rowsContainer.closest('form');
    var template = document.getElementById('glossary-row-template');
    if (!form || !template) return;

    var searchInput = document.getElementById('glossary-search');
    var clearSearch = document.getElementById('glossary-clear-search');
    var countLabel = document.getElementById('glossary-count');
    var emptyState = document.getElementById('glossary-empty-state');
    var searchEmpty = document.getElementById('glossary-search-empty');
    var searchEmptyText = document.getElementById('glossary-search-empty-text');
    var addButton = document.getElementById('add-glossary-row');
    var sortButton = document.getElementById('glossary-sort');
    var sortMenu = document.getElementById('glossary-sort-menu');
    var savebar = document.getElementById('glossary-savebar');
    var savebarText = savebar && savebar.querySelector('.glossary-savebar__text');
    var saveButton = document.getElementById('glossary-save');
    var cancelButton = document.getElementById('glossary-cancel');
    var deleteModalElement = document.getElementById('glossaryDeleteModal');
    var deleteModal = window.bootstrap && deleteModalElement ? new window.bootstrap.Modal(deleteModalElement) : null;
    var deleteConfirm = document.getElementById('glossaryDeleteConfirm');
    var deleteTerm = document.getElementById('glossaryDeleteTerm');
    var actionModalElement = document.getElementById('glossaryActionModal');
    var actionModal = window.bootstrap && actionModalElement ? new window.bootstrap.Modal(actionModalElement) : null;
    var actionText = document.getElementById('glossaryActionModalText');
    var actionConfirm = document.getElementById('glossaryActionConfirm');
    var backToTop = document.getElementById('glossary-back-to-top');
    var pendingDelete = null;
    var pendingAction = null;
    var saving = false;
    var maxDefinitionLength = 500;
    var scrollFrame = null;

    function rows() { return Array.prototype.slice.call(rowsContainer.querySelectorAll('.glossary-row')); }
    function term(row) { return row.querySelector('input[name="termine[]"], input[name="deleted_termine[]"]'); }
    function definition(row) { return row.querySelector('textarea[name="definizione[]"], textarea[name="deleted_definizione[]"]'); }
    function normalize(value) {
        return String(value || '').trim().toLocaleLowerCase('it-IT').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    function isDeleted(row) { return row.dataset.state === 'deleted'; }
    function dirty(row) { return ['new', 'modified', 'deleted'].indexOf(row.dataset.state) !== -1; }
    function currentQuery() { return normalize(searchInput ? searchInput.value : ''); }
    function error(row, name, message) {
        var input = name === 'termine' ? term(row) : definition(row);
        var target = row.querySelector('.glossary-error[data-error="' + name + '"]');
        if (input) input.classList.toggle('is-invalid', !!message);
        if (target) { target.textContent = message || ''; target.classList.toggle('is-visible', !!message); }
    }
    function clearErrors(row) { error(row, 'termine', ''); error(row, 'definizione', ''); }
    function updateCharCount(row) {
        var input = definition(row);
        var counter = row.querySelector('.glossary-char-count');
        if (!input || !counter) return;
        var length = input.value.length;
        counter.textContent = length + ' / ' + maxDefinitionLength + ' caratteri';
        counter.classList.toggle('is-near-limit', length >= maxDefinitionLength - 50);
        counter.classList.toggle('is-limit', length >= maxDefinitionLength);
        input.style.height = 'auto';
        input.style.height = Math.min(Math.max(input.scrollHeight, 76), 260) + 'px';
    }
    function validateRow(row) {
        if (isDeleted(row)) { clearErrors(row); return true; }
        var termValue = term(row).value.trim();
        var definitionValue = definition(row).value.trim();
        if (!termValue && !definitionValue) { clearErrors(row); return true; }
        var valid = true;
        if (!termValue) { error(row, 'termine', 'Inserisci un termine.'); valid = false; } else error(row, 'termine', '');
        if (!definitionValue) { error(row, 'definizione', 'Inserisci una definizione.'); valid = false; } else error(row, 'definizione', '');
        if (definitionValue.length > maxDefinitionLength) { error(row, 'definizione', 'Massimo 500 caratteri.'); valid = false; }
        if (termValue && rows().some(function (other) { return other !== row && !isDeleted(other) && normalize(term(other).value) === normalize(termValue); })) {
            error(row, 'termine', 'Questo termine è già presente.'); valid = false;
        }
        return valid;
    }
    function validateAll() {
        var invalid = rows().filter(function (row) { return !validateRow(row); });
        if (!invalid.length) { form.removeAttribute('data-validation-error'); return true; }
        form.setAttribute('data-validation-error', 'Impossibile salvare: controlla ' + invalid.length + ' ' + (invalid.length === 1 ? 'voce.' : 'voci.'));
        var first = term(invalid[0]).classList.contains('is-invalid') ? term(invalid[0]) : definition(invalid[0]);
        if (first) first.focus();
        return false;
    }
    function updateState(row) {
        if (isDeleted(row) || row.dataset.state === 'new') return;
        row.dataset.state = term(row).value === row.dataset.originalTermine && definition(row).value === row.dataset.originalDefinizione ? 'unchanged' : 'modified';
    }
    function matches(row, query) {
        if (isDeleted(row)) return !query;
        if (!query) return true;
        return normalize(term(row).value + ' ' + definition(row).value).indexOf(query) !== -1;
    }
    function renderRows() {
        var query = currentQuery();
        var active = rows().filter(function (row) { return !isDeleted(row); });
        var visible = 0;
        rows().forEach(function (row) {
            var shown = matches(row, query);
            row.hidden = !shown;
            row.setAttribute('aria-hidden', shown ? 'false' : 'true');
            if (shown && !isDeleted(row)) visible++;
        });
        if (countLabel) countLabel.textContent = query ? visible + ' risultati su ' + active.length : active.length + (active.length === 1 ? ' voce' : ' voci');
        if (emptyState) emptyState.hidden = active.length !== 0 || !!query;
        if (searchEmpty) searchEmpty.hidden = !query || visible !== 0;
        if (searchEmptyText && query) searchEmptyText.textContent = 'Nessun risultato per “' + searchInput.value.trim() + '”';
        if (clearSearch) clearSearch.hidden = !searchInput || !searchInput.value;
        updateSavebar();
    }
    function updateSavebar() {
        var count = rows().filter(dirty).length;
        if (!savebar) return;
        savebar.dataset.dirty = count ? 'true' : 'false';
        if (savebarText) savebarText.textContent = count ? count + (count === 1 ? ' modifica non salvata' : ' modifiche non salvate') : 'Tutto salvato';
        if (saveButton) saveButton.disabled = !count || saving;
        if (cancelButton) cancelButton.disabled = !count || saving;
    }
    function initRow(row) {
        if (row.dataset.state === 'new') {
            row.dataset.originalTermine = '';
            row.dataset.originalDefinizione = '';
        } else {
            row.dataset.state = 'unchanged';
            row.dataset.originalTermine = term(row).value;
            row.dataset.originalDefinizione = definition(row).value;
        }
        updateCharCount(row);
        validateRow(row);
    }
    function markDeleted(row) {
        row.dataset.state = 'deleted';
        term(row).name = 'deleted_termine[]';
        definition(row).name = 'deleted_definizione[]';
        term(row).readOnly = true;
        definition(row).readOnly = true;
        var button = row.querySelector('.remove-row');
        if (button) { button.className = 'btn btn-outline-secondary remove-row'; button.innerHTML = '<i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i><span> Ripristina</span>'; button.setAttribute('aria-label', 'Ripristina voce'); }
        renderRows();
    }
    function restore(row) {
        row.dataset.state = 'modified';
        term(row).name = 'termine[]';
        definition(row).name = 'definizione[]';
        term(row).readOnly = false;
        definition(row).readOnly = false;
        var button = row.querySelector('.remove-row');
        if (button) { button.className = 'btn btn-outline-danger remove-row'; button.innerHTML = '<i class="bi bi-trash3" aria-hidden="true"></i><span> Elimina</span>'; button.setAttribute('aria-label', 'Segna la voce per l’eliminazione'); }
        validateRow(row); renderRows();
    }
    function confirmAction(message, action) {
        pendingAction = action;
        if (!actionModal) { action(); pendingAction = null; return; }
        actionText.textContent = message; actionModal.show();
    }
    function addRow() {
        var row = template.content.cloneNode(true).querySelector('.glossary-row');
        rowsContainer.appendChild(row);
        initRow(row);
        renderRows();
        row.scrollIntoView({behavior: 'smooth', block: 'center'});
        term(row).focus();
    }

    rowsContainer.addEventListener('input', function (event) {
        var row = event.target.closest('.glossary-row');
        if (!row) return;
        if (event.target.matches('textarea')) updateCharCount(row);
        updateState(row); validateRow(row); renderRows();
    });
    rowsContainer.addEventListener('click', function (event) {
        var button = event.target.closest('.remove-row');
        if (!button) return;
        var row = button.closest('.glossary-row');
        if (isDeleted(row)) { restore(row); return; }
        if (row.dataset.state === 'new') { row.remove(); renderRows(); return; }
        pendingDelete = row;
        if (deleteTerm) deleteTerm.textContent = term(row).value.trim() || '(senza termine)';
        if (deleteModal) deleteModal.show(); else markDeleted(row);
    });
    if (deleteConfirm) deleteConfirm.addEventListener('click', function () { if (pendingDelete) markDeleted(pendingDelete); pendingDelete = null; if (deleteModal) deleteModal.hide(); });
    if (addButton) addButton.addEventListener('click', addRow);
    var bottomTabbar = document.getElementById('bottomTabbar');
    if (bottomTabbar && !bottomTabbar.querySelector('[data-glossary-add]')) {
        var bottomAddButton = document.createElement('button');
        bottomAddButton.type = 'button';
        bottomAddButton.className = 'tabbar-item glossary-tabbar-add';
        bottomAddButton.setAttribute('data-glossary-add', '');
        bottomAddButton.setAttribute('aria-label', 'Aggiungi voce');
        bottomAddButton.title = 'Aggiungi voce';
        bottomAddButton.innerHTML = '<i class="bi bi-plus-circle" aria-hidden="true"></i><span>Aggiungi</span>';
        bottomAddButton.addEventListener('click', addRow);
        bottomTabbar.appendChild(bottomAddButton);
    }
    function resetSearch() { searchInput.value = ''; searchInput.focus(); renderRows(); }
    if (searchInput) { searchInput.addEventListener('input', renderRows); searchInput.addEventListener('keydown', function (event) { if (event.key === 'Escape') { event.preventDefault(); resetSearch(); } }); }
    if (clearSearch) clearSearch.addEventListener('click', resetSearch);
    var emptyClear = document.getElementById('glossary-empty-clear'); if (emptyClear) emptyClear.addEventListener('click', resetSearch);
    if (sortButton && sortMenu) {
        sortButton.addEventListener('click', function () { sortMenu.hidden = !sortMenu.hidden; sortButton.setAttribute('aria-expanded', String(!sortMenu.hidden)); });
        sortMenu.addEventListener('click', function (event) {
            var option = event.target.closest('[data-sort]'); if (!option) return;
            var sort = function () { var direction = option.dataset.sort === 'desc' ? -1 : 1; rows().sort(function (a, b) { return normalize(term(a).value).localeCompare(normalize(term(b).value), 'it') * direction; }).forEach(function (row) { rowsContainer.appendChild(row); }); renderRows(); };
            if (rows().some(dirty)) confirmAction('L’ordinamento non elimina le modifiche: cambia solo l’ordine delle voci.', sort); else sort();
            sortMenu.hidden = true; sortButton.setAttribute('aria-expanded', 'false');
        });
    }
    if (cancelButton) cancelButton.addEventListener('click', function () { confirmAction('Annullare tutte le modifiche? Le modifiche non salvate verranno perse.', function () { window.location.reload(); }); });
    if (actionConfirm) actionConfirm.addEventListener('click', function () { if (pendingAction) pendingAction(); pendingAction = null; if (actionModal) actionModal.hide(); });
    form.addEventListener('submit', function (event) {
        if (!validateAll() || saving) { event.preventDefault(); renderRows(); return; }
        saving = true;
        if (saveButton) saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Salvataggio...';
        updateSavebar();
    });
    window.addEventListener('beforeunload', function (event) { if (rows().some(dirty) && !saving) { event.preventDefault(); event.returnValue = ''; } });
    function updateBackToTop() {
        if (!backToTop) return;
        backToTop.hidden = window.scrollY < 480;
    }
    window.addEventListener('scroll', function () {
        if (scrollFrame) return;
        scrollFrame = window.requestAnimationFrame(function () { scrollFrame = null; updateBackToTop(); });
    }, {passive: true});
    if (backToTop) backToTop.addEventListener('click', function () { window.scrollTo({top: 0, behavior: 'smooth'}); });
    updateBackToTop();
    rows().forEach(initRow);
    renderRows();
})();