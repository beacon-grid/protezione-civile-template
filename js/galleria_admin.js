document.addEventListener('DOMContentLoaded', function () {

    // ── Modal dettaglio ──────────────────────────────────────
    const detailModalEl = document.getElementById('detailModal');
    const detailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;

    document.querySelectorAll('.btn-detail-trigger').forEach(function (el) {
        el.addEventListener('click', function () {
            document.getElementById('detailImg').src = this.dataset.url || '';
            document.getElementById('detailFilename').textContent = this.dataset.filename || '';
            const w = this.dataset.width || '0';
            const h = this.dataset.height || '0';
            document.getElementById('detailDims').textContent = w + ' × ' + h;
            document.getElementById('detailSize').textContent = this.dataset.size || '';
            document.getElementById('detailMtime').textContent = this.dataset.mtime || '';
            document.getElementById('detailUrl').textContent = this.dataset.url || '';
            if (detailModal) detailModal.show();
        });
    });

    // ── Modal rinomina ───────────────────────────────────────
    const renameModalEl = document.getElementById('renameModal');
    const renameModal = renameModalEl ? new bootstrap.Modal(renameModalEl) : null;

    document.querySelectorAll('.btn-rename-trigger').forEach(function (el) {
        el.addEventListener('click', function () {
            const filename = this.dataset.filename || '';
            const dotIndex = filename.lastIndexOf('.');
            const base = dotIndex > -1 ? filename.substring(0, dotIndex) : filename;
            const ext = dotIndex > -1 ? filename.substring(dotIndex) : '';

            document.getElementById('renameOldName').value = filename;
            document.getElementById('renameNewName').value = base;
            document.getElementById('renameExt').textContent = ext;

            if (renameModal) {
                renameModal.show();
                setTimeout(function () {
                    const input = document.getElementById('renameNewName');
                    input.focus();
                    input.select();
                }, 300);
            }
        });
    });

    // ── Modal eliminazione ───────────────────────────────────
    const deleteModalEl = document.getElementById('deleteModal');
    const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;

    document.querySelectorAll('.btn-delete-trigger').forEach(function (el) {
        el.addEventListener('click', function () {
            document.getElementById('deleteFilenameInput').value = this.dataset.filename || '';
            document.getElementById('deleteFilename').textContent = this.dataset.filename || '';
            if (deleteModal) deleteModal.show();
        });
    });

    // ── Upload: click, drag&drop e anteprima elenco file ─────
    const dropZone = document.getElementById('uploadDrop');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const uploadForm = document.getElementById('uploadForm');

    function renderFileList() {
        if (!fileInput.files.length) {
            fileList.textContent = '';
            return;
        }
        const names = Array.from(fileInput.files).map(f => f.name);
        fileList.textContent = names.length + ' file selezionati: ' + names.join(', ');
    }

    if (fileInput) {
        fileInput.addEventListener('change', renderFileList);
    }

    if (dropZone) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            dropZone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            dropZone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            });
        });

        dropZone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                fileInput.files = dt.files;
                renderFileList();
            }
        });
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                fileList.textContent = 'Seleziona almeno un file prima di caricare.';
            }
        });
    }
});