(function () {
    const CONFIG = {
        jsonUrl: '/php/glossario_public.php?type=entries',
        settingsUrl: '/php/glossario_public.php?type=settings',
        highlightAll: false,
        ignoreSelector: 'a, code, pre, script, style, textarea, input, select, nav, footer, .no-glossary',
        mobileBreakpoint: 767.98
    };

    function isMobileViewport() {
        return window.matchMedia('(max-width: ' + CONFIG.mobileBreakpoint + 'px)').matches;
    }

    function closeActiveGlossary(excludeTermEl) {
        const active = window.__glossaryActive;
        if (!active || !active.popover) return;
        if (excludeTermEl && active.termEl === excludeTermEl) return;

        active.popover.remove();
        active.termEl.setAttribute('aria-expanded', 'false');
        window.__glossaryActive = null;
    }

    function escapeRegExp(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function buildRegex(terms) {
        const sorted = terms.slice().sort((a, b) => b.length - a.length);
        const pattern = sorted.map(term => escapeRegExp(term)).join('|');
        return new RegExp(`(?<![\\p{L}\\p{N}])(${pattern})(?![\\p{L}\\p{N}])`, 'giu');
    }

    function createTermIndex(terms) {
        const index = new Map();
        terms.forEach(function (item) {
            index.set(item.termine.toLowerCase(), item);
        });
        return index;
    }

    function shouldSkip(node) {
        if (!node || node.nodeType !== Node.TEXT_NODE) return true;
        const parent = node.parentElement;
        if (!parent) return true;
        if (parent.closest(CONFIG.ignoreSelector)) return true;
        if (parent.classList && parent.classList.contains('glossary-term')) return true;
        return false;
    }

    function isValidTextNode(node) {
        if (!node || node.nodeType !== Node.TEXT_NODE) return false;
        const text = node.textContent || '';
        if (!text.trim()) return false;
        return text.search(/\S/) >= 0;
    }

    function createPopover(termEl, definition) {
        const term = termEl.getAttribute('data-term') || '';
        const mobile = isMobileViewport();

        const popover = document.createElement('div');
        popover.className = 'glossary-popover';
        popover.setAttribute('role', 'tooltip');
        popover.setAttribute('aria-hidden', 'true');
        if (mobile) {
            popover.setAttribute('data-mobile', 'true');
        }

        const head = document.createElement('div');
        head.className = 'glossary-popover__head';

        const icon = document.createElement('span');
        icon.className = 'glossary-popover__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<i class="bi bi-info-circle-fill"></i>';

        const title = document.createElement('strong');
        title.className = 'glossary-popover__title';
        title.textContent = term;

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'glossary-popover__close';
        closeButton.setAttribute('aria-label', 'Chiudi definizione');
        closeButton.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';

        head.appendChild(icon);
        head.appendChild(title);
        head.appendChild(closeButton);

        const content = document.createElement('p');
        content.className = 'glossary-popover__body';
        content.textContent = definition;

        popover.appendChild(head);
        popover.appendChild(content);
        document.body.appendChild(popover);

        function positionPopover() {
            if (isMobileViewport()) {
                popover.style.left = '12px';
                popover.style.right = '12px';
                popover.style.top = 'auto';
                popover.style.bottom = 'max(12px, env(safe-area-inset-bottom))';
                popover.style.width = 'auto';
                popover.style.maxWidth = 'none';
                popover.style.removeProperty('--arrow-left');
                popover.setAttribute('data-placement', 'bottom');
                return;
            }

            const rect = termEl.getBoundingClientRect();
            const popRect = popover.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            let left = rect.left + window.scrollX + rect.width / 2 - popRect.width / 2;
            let top = rect.top + window.scrollY - popRect.height - 10;
            let placement = 'top';

            if (left < 8) left = 8;
            if (left + popRect.width > viewportWidth - 8) left = viewportWidth - popRect.width - 8;
            if (top < 8 + window.scrollY) {
                top = rect.bottom + window.scrollY + 8;
                placement = 'bottom';
            }
            if (top + popRect.height > viewportHeight + window.scrollY - 8) {
                top = rect.top + window.scrollY - popRect.height - 8;
                placement = 'top';
            }

            popover.style.left = Math.max(8, left) + 'px';
            popover.style.top = Math.max(8 + window.scrollY, top) + 'px';
            popover.style.right = 'auto';
            popover.style.bottom = 'auto';
            popover.style.width = '';
            popover.style.maxWidth = '';
            popover.setAttribute('data-placement', placement);

            const anchorCenter = rect.left + window.scrollX + rect.width / 2;
            const arrowLeft = anchorCenter - Math.max(8, left);
            const clampedArrowLeft = Math.min(popRect.width - 16, Math.max(16, arrowLeft));
            popover.style.setProperty('--arrow-left', clampedArrowLeft + 'px');
        }

        closeButton.addEventListener('click', function (event) {
            event.stopPropagation();
            if (window.__glossaryActive && window.__glossaryActive.termEl === termEl) {
                window.__glossaryActive.popover.remove();
                window.__glossaryActive.termEl.setAttribute('aria-expanded', 'false');
                window.__glossaryActive = null;
            }
        });

        positionPopover();
        window.addEventListener('resize', positionPopover);
        window.addEventListener('scroll', positionPopover, true);
        return popover;
    }

    function bindInteractions(termEl, definition) {
        let popover = null;
        let hideTimeout = null;

        function showPopover() {
            closeActiveGlossary(termEl);
            if (popover) return;
            popover = createPopover(termEl, definition);
            popover.setAttribute('aria-hidden', 'false');
            termEl.setAttribute('aria-expanded', 'true');
            window.__glossaryActive = { termEl: termEl, popover: popover };
        }

        function hidePopover() {
            if (!popover) return;
            popover.remove();
            popover = null;
            termEl.setAttribute('aria-expanded', 'false');
            if (window.__glossaryActive && window.__glossaryActive.termEl === termEl) {
                window.__glossaryActive = null;
            }
        }

        function scheduleHide() {
            clearTimeout(hideTimeout);
            hideTimeout = window.setTimeout(hidePopover, 180);
        }

        termEl.addEventListener('mouseenter', function () {
            if (!isMobileViewport()) {
                showPopover();
            }
        });
        termEl.addEventListener('mouseleave', function () {
            if (!isMobileViewport()) {
                scheduleHide();
            }
        });
        termEl.addEventListener('focus', function () {
            if (!isMobileViewport()) {
                showPopover();
            }
        });
        termEl.addEventListener('blur', function () {
            if (!isMobileViewport()) {
                scheduleHide();
            }
        });
        termEl.addEventListener('click', function (event) {
            event.preventDefault();
            if (popover) {
                hidePopover();
            } else {
                showPopover();
            }
        });
        termEl.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                hidePopover();
            }
        });
        termEl.addEventListener('touchstart', function (event) {
            event.preventDefault();
            if (popover) {
                hidePopover();
            } else {
                showPopover();
            }
        }, { passive: false });

        termEl.addEventListener('touchend', function () {
            if (popover && !isMobileViewport()) {
                scheduleHide();
            }
        });
    }

    function highlightText(root, terms) {
        const termIndex = createTermIndex(terms);
        const regex = buildRegex(terms.map(item => item.termine));
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return shouldSkip(node) ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
            }
        });

        const nodes = [];
        let currentNode = walker.nextNode();
        while (currentNode) {
            if (isValidTextNode(currentNode)) {
                nodes.push(currentNode);
            }
            currentNode = walker.nextNode();
        }

        const highlighted = new Set();
        nodes.forEach(function (node) {
            const text = node.textContent || '';
            if (!text) return;

            const parent = node.parentNode;
            if (!parent) return;

            const localRegex = new RegExp(regex.source, 'giu');
            const matches = [];
            let match;
            while ((match = localRegex.exec(text)) !== null) {
                matches.push(match);
            }

            if (!matches.length) return;

            const fragments = [];
            let lastIndex = 0;
            matches.forEach(function (matchItem) {
                const matchedTerm = matchItem[1];
                const matchIndex = matchItem.index;
                if (matchIndex > lastIndex) {
                    fragments.push(document.createTextNode(text.slice(lastIndex, matchIndex)));
                }
                const termEntry = termIndex.get(matchedTerm.toLowerCase());
                if (termEntry && (!CONFIG.highlightAll && !highlighted.has(termEntry.termine.toLowerCase()))) {
                    const span = document.createElement('span');
                    span.className = 'glossary-term';
                    span.setAttribute('tabindex', '0');
                    span.setAttribute('role', 'button');
                    span.setAttribute('aria-label', 'Definizione di ' + termEntry.termine);
                    span.setAttribute('aria-expanded', 'false');
                    span.setAttribute('data-term', termEntry.termine);
                    span.setAttribute('data-def', termEntry.definizione);
                    span.appendChild(document.createTextNode(matchedTerm));
                    const infoIcon = document.createElement('i');
                    infoIcon.className = 'bi bi-info-circle-fill glossary-term__icon';
                    infoIcon.setAttribute('aria-hidden', 'true');
                    span.appendChild(infoIcon);
                    fragments.push(span);
                    highlighted.add(termEntry.termine.toLowerCase());
                } else {
                    fragments.push(document.createTextNode(matchedTerm));
                }
                lastIndex = matchIndex + matchedTerm.length;
            });

            if (fragments.length && lastIndex < text.length) {
                fragments.push(document.createTextNode(text.slice(lastIndex)));
            }

            if (fragments.length) {
                const fragment = document.createDocumentFragment();
                fragments.forEach(function (chunk) {
                    fragment.appendChild(chunk);
                });
                parent.replaceChild(fragment, node);
            }
        });

        document.querySelectorAll('.glossary-term').forEach(function (el) {
            bindInteractions(el, el.getAttribute('data-def'));
        });
    }

    document.addEventListener('click', function (event) {
        if (!isMobileViewport()) return;

        const target = event.target;
        if (!(target instanceof Node)) return;

        const active = window.__glossaryActive;
        if (!active || !active.popover) return;
        if (target.closest('.glossary-term') || target.closest('.glossary-popover')) return;

        active.popover.remove();
        active.termEl.setAttribute('aria-expanded', 'false');
        window.__glossaryActive = null;
    }, true);

    function initGlossary() {
        if (!document.body || window.__glossaryInitialized) return;
        window.__glossaryInitialized = true;

        Promise.all([
            fetch(CONFIG.settingsUrl).then(function (response) {
                if (!response.ok) return { enabled: false };
                return response.json();
            }),
            fetch(CONFIG.jsonUrl).then(function (response) {
                if (!response.ok) throw new Error('Glossario non disponibile');
                return response.json();
            })
        ])
            .then(function ([settings, terms]) {
                if (!settings || !settings.enabled) return;
                if (!Array.isArray(terms) || !terms.length) return;
                const newsContents = document.querySelectorAll('.news-content');
                newsContents.forEach(function (newsContent) {
                    highlightText(newsContent, terms);
                });
            })
            .catch(function (error) {
                console.warn('Glossario non inizializzato:', error);
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlossary);
    } else {
        initGlossary();
    }
})();