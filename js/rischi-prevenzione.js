(function() {
    function initModalNavigation(modalId, navSelector, sectionSelector, targetPrefix) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const btns = modal.querySelectorAll(navSelector);
        const sections = modal.querySelectorAll(sectionSelector);

        if (!btns.length || !sections.length) return;

        function activateSection(target) {
            btns.forEach(function(btn) {
                btn.classList.toggle('active', btn.dataset.target === target);
            });

            sections.forEach(function(section) {
                section.classList.toggle('active', section.id === targetPrefix + target);
            });
        }

        btns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                activateSection(btn.dataset.target);
            });
        });

        activateSection(btns[0].dataset.target);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initModalNavigation('modalKit', '.mk-nav-btn', '.mk-section', 'mk-');
        initModalNavigation('modalFragili', '.mf-nav-btn', '.mf-section', 'mf-');
    });
})();
