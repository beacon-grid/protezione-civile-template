// sidebar.js
(function () {
    var btn      = document.getElementById('sessionToggle');
    var dropdown = document.getElementById('sessionDropdown');
    var chevron  = document.getElementById('sessionChevron');
    if (!btn || !dropdown) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = dropdown.style.display === 'block';
        dropdown.style.display = open ? 'none' : 'block';
        chevron.style.transform = open ? '' : 'rotate(180deg)';
        chevron.style.transition = 'transform .2s';
    });

    // Chiudi cliccando fuori
    document.addEventListener('click', function () {
        dropdown.style.display = 'none';
        chevron.style.transform = '';
    });
})();