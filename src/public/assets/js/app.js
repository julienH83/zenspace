/* =====================================================================
   ZenSpace — JavaScript du front-end (amélioration progressive)
     1. Apparition douce des sections au défilement (IntersectionObserver)
     2. Ombre discrète de l'en-tête une fois la page défilée
     3. Filtrage dynamique du catalogue (fetch, sans rechargement)
   Sans JavaScript, tout reste fonctionnel : le contenu est visible et le
   formulaire de filtres GET fonctionne côté serveur.
   ===================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    initReveal();
    initHeaderScroll();
    initCatalogueFilters();
});

/* --- 1. Apparition au défilement ---------------------------------- */
function initReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    // Repli : sans IntersectionObserver, on affiche tout immédiatement.
    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    els.forEach((el) => observer.observe(el));
}

/* --- 2. Ombre de l'en-tête au défilement -------------------------- */
function initHeaderScroll() {
    const header = document.querySelector('.site-header');
    if (!header) return;
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/* --- Étoiles de note (mêmes données que le rendu serveur) ---------- */
function starsHtml(avg, count) {
    count = parseInt(count, 10) || 0;
    if (count === 0) return '';
    avg = Math.max(0, Math.min(5, parseFloat(avg) || 0));
    const filled = Math.round(avg);
    const glyphs = '★'.repeat(filled) + '☆'.repeat(5 - filled);
    const avgTxt = avg.toFixed(1).replace('.', ',');
    return `<span class="card-rating"><span class="stars" aria-label="Note : ${avgTxt} sur 5, ${count} avis">${glyphs}</span> <span class="rating-count">${avgTxt} · ${count}</span></span>`;
}

/* --- 3. Filtrage dynamique du catalogue --------------------------- */
function initCatalogueFilters() {
    const filtersForm = document.getElementById('filters');
    const results = document.getElementById('results');
    if (!filtersForm || !results) return; // pas sur la page catalogue

    let timer = null;
    const debounce = (fn, delay = 250) => {
        clearTimeout(timer);
        timer = setTimeout(fn, delay);
    };

    // Affiche dynamiquement la valeur des curseurs (prix / durée).
    const priceInput = document.getElementById('max_price');
    const priceOut = document.getElementById('max_price_value');
    const durInput = document.getElementById('max_duration');
    const durOut = document.getElementById('max_duration_value');
    const syncOutputs = () => {
        if (priceOut && priceInput) priceOut.textContent = priceInput.value + ' €';
        if (durOut && durInput) durOut.textContent = durInput.value + ' min';
    };
    syncOutputs();

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // Carte de prestation : média + titre liés à la fiche, bouton « Réserver ».
    function cardHtml(s) {
        const href = '/prestation/' + encodeURIComponent(s.slug);
        const price = Number(s.price).toFixed(2).replace('.', ',') + ' €';
        const webp = s.image ? s.image.replace(/\.(jpe?g|png)$/i, '.webp') : '';
        const media = s.image
            ? `<a class="card-media" href="${href}" tabindex="-1" aria-hidden="true"><picture><source srcset="/assets/images/${escapeHtml(webp)}" type="image/webp"><img src="/assets/images/${escapeHtml(s.image)}" alt="" loading="lazy" decoding="async"></picture></a>`
            : '';
        return `
            <article class="card service-card">
                ${media}
                <div class="card-body">
                    <span class="tag">${escapeHtml(s.category_label)}</span>
                    <h3><a href="${href}">${escapeHtml(s.title)}</a></h3>
                    ${starsHtml(s.rating_avg, s.rating_count)}
                    <p class="meta">${s.duration_min} min</p>
                    <div class="card-foot">
                        <span class="price">${price}</span>
                        <a class="btn btn-primary btn-sm" href="${href}">Réserver</a>
                    </div>
                </div>
            </article>`;
    }

    function buildParams() {
        const raw = new URLSearchParams(new FormData(filtersForm));
        const clean = new URLSearchParams();
        for (const [key, value] of raw.entries()) {
            if (value !== '' && value != null) clean.append(key, value);
        }
        return clean;
    }

    async function loadServices() {
        const params = buildParams();
        const query = params.toString();

        // URL partageable : on reflète les filtres dans la barre d'adresse.
        const action = filtersForm.getAttribute('action') || '/prestations';
        history.replaceState(null, '', query ? action + '?' + query : action);

        results.setAttribute('aria-busy', 'true');
        try {
            const res = await fetch('/api/prestations?' + query, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            const services = data.services || [];
            results.innerHTML = services.length === 0
                ? '<p class="muted">Aucune prestation ne correspond à vos critères.</p>'
                : '<div class="grid">' + services.map(cardHtml).join('') + '</div>';
        } catch (e) {
            results.innerHTML = '<p class="error-text">Erreur de chargement. Veuillez réessayer.</p>';
        } finally {
            results.removeAttribute('aria-busy');
        }
    }

    filtersForm.addEventListener('input', () => { syncOutputs(); debounce(loadServices, 250); });
    filtersForm.addEventListener('change', () => debounce(loadServices, 250));
    filtersForm.addEventListener('submit', (e) => { e.preventDefault(); loadServices(); });
}
