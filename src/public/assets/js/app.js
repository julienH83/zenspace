/* =====================================================================
   ZenSpace — JavaScript du front-end (amélioration progressive)
     1. Apparition douce des sections au défilement (IntersectionObserver)
     2. Ombre discrète de l'en-tête une fois la page défilée
     3. Filtrage dynamique du catalogue (fetch, sans rechargement)
   Sans JavaScript, tout reste fonctionnel : le contenu est visible et le
   formulaire de filtres GET fonctionne côté serveur.
   ===================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initReveal();
    initHeaderScroll();
    initCatalogueFilters();
});

/* --- 0. Menu mobile (hamburger) ----------------------------------- */
function initNav() {
    const toggle = document.getElementById('nav-toggle');
    const panel = document.getElementById('primary-nav');
    if (!toggle || !panel) return;

    // Signale que le JS gère le menu : le CSS ne replie le menu QUE dans ce cas
    // (sans JS, la navigation reste visible et accessible).
    document.documentElement.classList.add('js-nav');

    const close = () => {
        panel.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Ouvrir le menu');
    };
    const open = () => {
        panel.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Fermer le menu');
    };

    toggle.addEventListener('click', () => {
        panel.classList.contains('is-open') ? close() : open();
    });
    // Fermer après un clic sur un lien du menu.
    panel.addEventListener('click', (e) => { if (e.target.closest('a')) close(); });
    // Fermer avec la touche Échap.
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    // Fermer en cliquant en dehors du menu.
    document.addEventListener('click', (e) => {
        if (panel.classList.contains('is-open') && !panel.contains(e.target) && !toggle.contains(e.target)) close();
    });
    // Repli propre si l'on repasse en affichage large.
    window.addEventListener('resize', () => { if (window.innerWidth > 820) close(); });
}

/* --- 1. Apparition au défilement ---------------------------------- */
function initReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    const revealAll = () => els.forEach((el) => el.classList.add('reveal-armed', 'is-visible'));

    // Repli : sans IntersectionObserver, on affiche tout immédiatement.
    if (!('IntersectionObserver' in window)) { revealAll(); return; }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -5% 0px' });

    // On « arme » (masque) puis on observe, dans la même passe : seul ce script
    // pose .reveal-armed, donc un app.js manquant/obsolète ne peut rien masquer.
    els.forEach((el) => { el.classList.add('reveal-armed'); observer.observe(el); });

    // Filet de sécurité : tout révéler après 2 s, même si un observateur reste muet.
    setTimeout(revealAll, 2000);
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

    // Icône SVG horloge (identique à icons.php côté serveur), pour rester
    // cohérent avec le rendu PHP lorsque la carte est régénérée en JS.
    const CLOCK_SVG = '<svg class="icon-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7.5V12l3 2"></path></svg>';

    // Carte de prestation premium : média + badge prix, puce catégorie, CTA.
    function cardHtml(s) {
        const href = '/prestation/' + encodeURIComponent(s.slug);
        const price = Number(s.price).toFixed(2).replace('.', ',') + ' €';
        const webp = s.image ? s.image.replace(/\.(jpe?g|png)$/i, '.webp') : '';
        const media = s.image
            ? `<a class="card-media" href="${href}" tabindex="-1" aria-hidden="true"><picture><source srcset="/assets/images/${escapeHtml(webp)}" type="image/webp"><img src="/assets/images/${escapeHtml(s.image)}" alt="" loading="lazy" decoding="async"></picture></a>`
            : '';
        return `
            <article class="card service-card">
                <div class="card-media-wrap">
                    ${media}
                    <span class="price-badge">${price}</span>
                </div>
                <div class="card-body">
                    <span class="tag-chip">${escapeHtml(s.category_label)}</span>
                    <h3><a href="${href}">${escapeHtml(s.title)}</a></h3>
                    ${starsHtml(s.rating_avg, s.rating_count)}
                    <p class="meta meta-duration">${CLOCK_SVG}<span>${s.duration_min}&nbsp;min</span></p>
                    <a class="btn btn-primary btn-sm card-cta" href="${href}">Réserver ce soin</a>
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
