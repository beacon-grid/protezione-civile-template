document.addEventListener('DOMContentLoaded', function () {
    const deleteModal   = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteIdInput = document.getElementById('deleteId');
    const deleteTitleEl = document.getElementById('deleteTitle');

    document.querySelectorAll('.btn-delete-trigger').forEach(button => {
        button.addEventListener('click', function () {
            deleteIdInput.value       = this.dataset.id;
            deleteTitleEl.textContent = '"' + this.dataset.titolo + '"';
            deleteModal.show();
        });
    });
});