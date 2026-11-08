document.addEventListener('DOMContentLoaded', function () {
    var alertBox = document.getElementById('box-allerta');
    if (!alertBox) return;

    alertBox.addEventListener('click', function () {
        window.location.href = '/allerte/';
    });

    alertBox.style.cursor = 'pointer';
});
