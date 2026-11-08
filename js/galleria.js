document.addEventListener('DOMContentLoaded', function () {
    const images = window.galleryImages || [];
    if (!images.length) return;

    // ===== Toolbar galleria: ricerca, ordinamento, vista =====
    const galleryGrid    = document.getElementById('galleryGrid');
    const searchInput    = document.getElementById('gallerySearch');
    const sortSelect      = document.getElementById('gallerySort');
    const viewGridBtn     = document.getElementById('galleryViewGrid');
    const viewListBtn     = document.getElementById('galleryViewList');
    const resultsCountEl  = document.getElementById('galleryResultsCount');
    const noResultsEl     = document.getElementById('galleryNoResults');

    function updateResultsCount(visible, total) {
        if (!resultsCountEl) return;
        resultsCountEl.textContent = visible === total
            ? total + (total === 1 ? ' immagine' : ' immagini')
            : visible + ' di ' + total + ' immagini';
    }

    function applyFilterAndSort() {
        if (!galleryGrid) return;
        const term = (searchInput.value || '').trim().toLowerCase();
        const sortValue = sortSelect.value;
        const allItems = Array.from(galleryGrid.querySelectorAll('.gallery-item'));

        let visibleCount = 0;
        allItems.forEach(function (item) {
            const matches = !term || item.getAttribute('data-title').includes(term);
            item.classList.toggle('d-none', !matches);
            if (matches) visibleCount++;
        });

        const sorted = allItems.slice().sort(function (a, b) {
            const dataA = images[parseInt(a.getAttribute('data-index'), 10)];
            const dataB = images[parseInt(b.getAttribute('data-index'), 10)];
            switch (sortValue) {
                case 'name-desc': return dataB.title.localeCompare(dataA.title, 'it', { sensitivity: 'base' });
                case 'date-desc': return (dataB.mtime || 0) - (dataA.mtime || 0);
                case 'date-asc':  return (dataA.mtime || 0) - (dataB.mtime || 0);
                default:          return dataA.title.localeCompare(dataB.title, 'it', { sensitivity: 'base' });
            }
        });
        sorted.forEach(function (item) { galleryGrid.appendChild(item); });

        updateResultsCount(visibleCount, allItems.length);
        if (noResultsEl) noResultsEl.classList.toggle('d-none', visibleCount !== 0);
        galleryGrid.classList.toggle('d-none', visibleCount === 0);
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilterAndSort);
    }
    if (sortSelect) {
        sortSelect.addEventListener('change', applyFilterAndSort);
    }
    if (viewGridBtn && viewListBtn && galleryGrid) {
        viewGridBtn.addEventListener('click', function () {
            galleryGrid.classList.remove('is-list');
            viewGridBtn.classList.add('is-active');
            viewListBtn.classList.remove('is-active');
            viewGridBtn.setAttribute('aria-pressed', 'true');
            viewListBtn.setAttribute('aria-pressed', 'false');
        });
        viewListBtn.addEventListener('click', function () {
            galleryGrid.classList.add('is-list');
            viewListBtn.classList.add('is-active');
            viewGridBtn.classList.remove('is-active');
            viewListBtn.setAttribute('aria-pressed', 'true');
            viewGridBtn.setAttribute('aria-pressed', 'false');
        });
    }

    if (galleryGrid) {
        updateResultsCount(images.length, images.length);
    }

    // ===== Lightbox =====
    const items         = Array.from(document.querySelectorAll('.gallery-item'));
    const lightbox      = document.getElementById('pcLightbox');
    const stage          = document.getElementById('pcLbStage');
    const lbImage        = document.getElementById('pcLbImage');
    const lbTitle        = document.getElementById('pcLightboxTitle');
    const lbCounter      = document.getElementById('pcLbCounter');
    const lbSpinner      = document.getElementById('pcLbSpinner');
    const prevBtn        = document.getElementById('pcLbPrev');
    const nextBtn        = document.getElementById('pcLbNext');
    const fullscreenBtn  = document.getElementById('pcLbFullscreen');
    const zoomInBtn      = document.getElementById('pcLbZoomIn');
    const zoomOutBtn     = document.getElementById('pcLbZoomOut');
    const zoomLevelEl    = document.getElementById('pcLbZoomLevel');
    const resetBtn       = document.getElementById('pcLbReset');
    const rotateBtn      = document.getElementById('pcLbRotate');
    const downloadBtn    = document.getElementById('pcLbDownload');

    const MIN_SCALE = 1;
    const MAX_SCALE = 4;

    let currentIndex = 0;
    let lastFocused  = null;

    // Stato zoom / pan / rotazione
    let scale = 1;
    let panX = 0;
    let panY = 0;
    let rotation = 0;

    // Stato interazione puntatore (drag/pinch)
    const pointers = new Map();
    let isDragging = false;
    let dragStart = { x: 0, y: 0, panX: 0, panY: 0 };
    let pinchStartDist = 0;
    let pinchStartScale = 1;

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function applyTransform(withTransition) {
        lbImage.classList.toggle('is-transitioning', !!withTransition);
        lbImage.style.transform =
            'translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ') rotate(' + rotation + 'deg)';
        lbImage.classList.toggle('is-zoomed', scale > 1);
        zoomLevelEl.textContent = Math.round(scale * 100) + '%';
    }

    function resetTransform(withTransition) {
        scale = 1;
        panX = 0;
        panY = 0;
        rotation = 0;
        applyTransform(withTransition);
    }

    // Zoom centrato su un punto dello schermo (cursore, tap, centro pinch)
    function zoomTo(newScale, clientX, clientY) {
        newScale = clamp(newScale, MIN_SCALE, MAX_SCALE);
        const rect = lbImage.getBoundingClientRect();
        const rectCenterX = rect.left + rect.width / 2;
        const rectCenterY = rect.top + rect.height / 2;
        const offsetX = clientX - rectCenterX + panX;
        const offsetY = clientY - rectCenterY + panY;
        const ratio = newScale / scale;

        panX = offsetX * (1 - ratio) + ratio * panX;
        panY = offsetY * (1 - ratio) + ratio * panY;
        scale = newScale;

        if (scale === MIN_SCALE) {
            panX = 0;
            panY = 0;
        }

        applyTransform(true);
    }

    function preload(index) {
        const img = new Image();
        img.src = images[(index + images.length) % images.length].src;
    }

    function renderImage(index) {
        currentIndex = (index + images.length) % images.length;
        const data = images[currentIndex];

        lbImage.classList.remove('is-visible');
        lbSpinner.classList.add('is-active');
        resetTransform(false);

        const loader = new Image();
        loader.onload = function () {
            lbImage.src = data.src;
            lbImage.alt = data.title;
            lbSpinner.classList.remove('is-active');
            requestAnimationFrame(function () {
                lbImage.classList.add('is-visible');
            });
        };
        loader.src = data.src;

        lbTitle.textContent = data.title;
        lbCounter.textContent = (currentIndex + 1) + ' / ' + images.length;

        preload(currentIndex + 1);
        preload(currentIndex - 1);
    }

    function openLightbox(index, triggerEl) {
        lastFocused = triggerEl || document.activeElement;
        renderImage(index);
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        document.querySelector('.pc-lb-close').focus();
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (lastFocused) lastFocused.focus();
    }

    items.forEach(function (item) {
        const index = parseInt(item.getAttribute('data-index'), 10);

        item.addEventListener('click', function () {
            openLightbox(index, item);
        });

        item.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openLightbox(index, item);
            }
        });
    });

    prevBtn.addEventListener('click', function () { renderImage(currentIndex - 1); });
    nextBtn.addEventListener('click', function () { renderImage(currentIndex + 1); });

    lightbox.querySelectorAll('[data-close]').forEach(function (el) {
        el.addEventListener('click', closeLightbox);
    });

    fullscreenBtn.addEventListener('click', function () {
        window.open(images[currentIndex].src, '_blank', 'noopener,noreferrer');
    });

    // ===== Controlli zoom / rotazione / download =====
    zoomInBtn.addEventListener('click', function () {
        const rect = lbImage.getBoundingClientRect();
        zoomTo(scale + 0.5, rect.left + rect.width / 2, rect.top + rect.height / 2);
    });

    zoomOutBtn.addEventListener('click', function () {
        const rect = lbImage.getBoundingClientRect();
        zoomTo(scale - 0.5, rect.left + rect.width / 2, rect.top + rect.height / 2);
    });

    resetBtn.addEventListener('click', function () {
        resetTransform(true);
    });

    rotateBtn.addEventListener('click', function () {
        rotation = (rotation + 90) % 360;
        applyTransform(true);
    });

    downloadBtn.addEventListener('click', function () {
        const data = images[currentIndex];
        const link = document.createElement('a');
        link.href = data.src;
        link.download = data.title || 'immagine';
        document.body.appendChild(link);
        link.click();
        link.remove();
    });

    // Doppio click / doppio tap: zoom rapido
    lbImage.addEventListener('dblclick', function (event) {
        event.preventDefault();
        if (scale > 1) {
            resetTransform(true);
        } else {
            zoomTo(2.5, event.clientX, event.clientY);
        }
    });

    // Zoom con rotellina del mouse
    stage.addEventListener('wheel', function (event) {
        event.preventDefault();
        const delta = event.deltaY < 0 ? 0.35 : -0.35;
        zoomTo(scale + delta, event.clientX, event.clientY);
    }, { passive: false });

    // ===== Pan e pinch-to-zoom con Pointer Events =====
    lbImage.addEventListener('pointerdown', function (event) {
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

        if (pointers.size === 1 && scale > 1) {
            isDragging = true;
            dragStart = { x: event.clientX, y: event.clientY, panX: panX, panY: panY };
            lbImage.classList.add('is-panning');
            lbImage.setPointerCapture(event.pointerId);
        }

        if (pointers.size === 2) {
            isDragging = false;
            const pts = Array.from(pointers.values());
            pinchStartDist = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y);
            pinchStartScale = scale;
        }
    });

    lbImage.addEventListener('pointermove', function (event) {
        if (!pointers.has(event.pointerId)) return;
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

        if (pointers.size === 2) {
            const pts = Array.from(pointers.values());
            const dist = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y);
            const midX = (pts[0].x + pts[1].x) / 2;
            const midY = (pts[0].y + pts[1].y) / 2;
            const newScale = clamp(pinchStartScale * (dist / pinchStartDist), MIN_SCALE, MAX_SCALE);
            zoomTo(newScale, midX, midY);
            return;
        }

        if (isDragging) {
            panX = dragStart.panX + (event.clientX - dragStart.x);
            panY = dragStart.panY + (event.clientY - dragStart.y);
            applyTransform(false);
        }
    });

    function endPointer(event) {
        pointers.delete(event.pointerId);
        if (pointers.size < 2) pinchStartDist = 0;
        if (pointers.size === 0) {
            isDragging = false;
            lbImage.classList.remove('is-panning');
        }
    }

    lbImage.addEventListener('pointerup', endPointer);
    lbImage.addEventListener('pointercancel', endPointer);
    lbImage.addEventListener('pointerleave', function (event) {
        if (pointers.size <= 1) endPointer(event);
    });

    document.addEventListener('keydown', function (event) {
        if (!lightbox.classList.contains('is-open')) return;

        if (event.key === 'Escape') closeLightbox();
        if (event.key === 'ArrowLeft') renderImage(currentIndex - 1);
        if (event.key === 'ArrowRight') renderImage(currentIndex + 1);
        if (event.key === '+' || event.key === '=') zoomInBtn.click();
        if (event.key === '-' || event.key === '_') zoomOutBtn.click();
        if (event.key === '0') resetBtn.click();
        if (event.key.toLowerCase() === 'r') rotateBtn.click();

        // Focus trap semplice
        if (event.key === 'Tab') {
            const focusable = lightbox.querySelectorAll('button');
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    // Swipe touch per cambiare immagine: attivo solo se non si è zoomati
    let touchStartX = 0;
    lightbox.addEventListener('touchstart', function (event) {
        if (scale > 1 || event.touches.length > 1) return;
        touchStartX = event.changedTouches[0].screenX;
    }, { passive: true });

    lightbox.addEventListener('touchend', function (event) {
        if (scale > 1) return;
        const delta = event.changedTouches[0].screenX - touchStartX;
        if (Math.abs(delta) > 50) {
            delta > 0 ? renderImage(currentIndex - 1) : renderImage(currentIndex + 1);
        }
    }, { passive: true });

    // Effetto ingresso scaglionato per le card della griglia
    items.forEach(function (item, i) {
        item.style.animationDelay = (i * 0.04) + 's';
    });
});
