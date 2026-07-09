/* =====================================================================
   ZenSpace — JavaScript du front-end
   Filtrage dynamique du catalogue (fetch, sans rechargement de page),
   en amélioration progressive : sans JS, le formulaire GET fonctionne
   normalement côté serveur. Aucune animation décorative.
   ===================================================================== */

document.addEventListener('DOMContentLoaded', () => {
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

    // La carte entière est un lien (pas de bouton superflu).
    function cardHtml(s) {
        const price = Number(s.price).toFixed(2).replace('.', ',') + ' €';
        const media = s.image
            ? `<div class="card-media"><img src="/assets/images/${escapeHtml(s.image)}" alt="${escapeHtml(s.title)}" loading="lazy"></div>`
            : '';
        return `
            <a class="card card-link" href="/prestation/${encodeURIComponent(s.slug)}">
                ${media}
                <div class="card-body">
                    <span class="tag">${escapeHtml(s.category_label)}</span>
                    <h3>${escapeHtml(s.title)}</h3>
                    <p class="meta">${s.duration_min} min · <span class="price">${price}</span></p>
                </div>
            </a>`;
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
});
