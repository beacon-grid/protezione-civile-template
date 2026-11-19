(function () {
    'use strict';

    function isEditableTarget(target) {
        return target instanceof HTMLElement && (
            target.isContentEditable ||
            target.matches('input, textarea, select, [role="textbox"]')
        );
    }

    function focusSearch() {
        const searchInput = document.getElementById('home-search-query')
            || document.getElementById('search-query');

        if (searchInput instanceof HTMLInputElement) {
            searchInput.focus();
            return;
        }

        const searchUrl = new URL('/cerca', window.location.origin);
        searchUrl.searchParams.set('focus', '1');
        window.location.assign(searchUrl);
    }

    function focusSearchAfterNavigation() {
        const url = new URL(window.location.href);
        if (url.pathname !== '/cerca' || url.searchParams.get('focus') !== '1') {
            return;
        }

        const focusInput = function () {
            const searchInput = document.getElementById('search-query');
            if (searchInput instanceof HTMLInputElement) {
                searchInput.focus();
                url.searchParams.delete('focus');
                window.history.replaceState(null, '', url);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', focusInput, { once: true });
        } else {
            focusInput();
        }
    }

    document.addEventListener('keydown', function (event) {
        const isSlash = event.key === '/'
            || event.code === 'Slash'
            || event.code === 'NumpadDivide'
            || (event.code === 'Digit7' && event.shiftKey);

        if (
            event.defaultPrevented
            || event.repeat
            || event.isComposing
            || event.altKey
            || (!event.ctrlKey && !event.metaKey)
            || !isSlash
            || isEditableTarget(event.target)
        ) {
            return;
        }

        event.preventDefault();
        focusSearch();
    });

    focusSearchAfterNavigation();
})();
