document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('deleteUsername').textContent = btn.dataset.username;
            document.getElementById('deleteUsernameInput').value = btn.dataset.username;
            document.getElementById('deleteAdminPassword').value = '';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    document.querySelectorAll('.btn-reset-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('resetUsername').textContent = btn.dataset.username;
            document.getElementById('resetUsernameInput').value = btn.dataset.username;
            document.getElementById('resetAdminPassword').value = '';
            new bootstrap.Modal(document.getElementById('resetModal')).show();
        });
    });

    // QR code per il reveal (se presente)
    var el = document.getElementById('qrcode-reveal');
    var uriEl = document.getElementById('qrcode-uri');
    if (el && uriEl && typeof QRCode !== 'undefined') {
        new QRCode(el, {
            text: uriEl.dataset.uri,
            width: 180,
            height: 180
        });
    }
});