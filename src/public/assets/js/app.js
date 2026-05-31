/* =====================================================================
   ZenSpace — JavaScript du front-end
   1) Animations « qui voltigent » : pétales qui tombent + apparitions au défilement.
   2) Filtrage DYNAMIQUE du catalogue (fetch, sans rechargement de page).
   ===================================================================== */

/* ------------------------------------------------------------------
   1) ANIMATIONS « FLUTTER »
   ------------------------------------------------------------------ */
document.addEventListener('DOMContentLoaded', () => {
    // Respecte la préférence système « réduire les animations » (accessibilité).
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) return;

    // --- a) Pétales qui tombent doucement en fond de page ---
    const layer = document.createElement('div');
    layer.className = 'petals-layer';
    layer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(layer);

    const PETAL_COUNT = 14;
    for (let i = 0; i < PETAL_COUNT; i++) {
        const petal = document.createElement('span');
        petal.className = 'petal';
        // Chaque pétale a une position, une taille, une durée et un délai aléatoires
        // pour que la chute paraisse naturelle (jamais synchronisée).
        petal.style.left = Math.random() * 100 + 'vw';
        const size = 8 + Math.random() * 12;
        petal.style.width = size + 'px';
        petal.style.height = size + 'px';
        petal.style.animationDuration = (9 + Math.random() * 9) + 's';
        petal.style.animationDelay = (-Math.random() * 12) + 's';
        petal.style.opacity = (0.35 + Math.random() * 0.4).toFixed(2);
        layer.appendChild(petal);
    }

    // --- b) Apparition en fondu des éléments quand ils entrent à l'écran ---
    const revealTargets = document.querySelectorAll('.card, .review, .stat-card, .section, .form-card, table.data');
    revealTargets.forEach(el => el.classList.add('reveal'));

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // on n'anime qu'une fois
            }
        });
    }, { threshold: 0.12 });

    revealTargets.forEach(el => observer.observe(el));
});

/* ------------------------------------------------------------------
   2) FILTRAGE DYNAMIQUE DU CATALOGUE
   ------------------------------------------------------------------ */
document.addEventListener('DOMContentLoaded', () => {
    const filtersForm = document.getElementById('filters');
    const results = document.getElementById('results');
    if (!filtersForm || !results) return; // pas sur la page catalogue

    // Petit "debounce" : on attend que l'utilisateur arrête de bouger les
    // curseurs avant d'interroger le serveur (évite les appels en rafale).
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

    function cardHtml(s) {
        const duration = s.duration_min + ' min';
        const price = Number(s.price).toFixed(2) + ' €';
        const media = s.image
            ? `<div class="card-media"><img src="/assets/images/${escapeHtml(s.image)}" alt="${escapeHtml(s.title)}" loading="lazy"></div>`
            : '';
        return `
            <article class="card">
                ${media}
                <div class="card-body">
                    <span class="tag">${escapeHtml(s.category_label)}</span>
                    <h3>${escapeHtml(s.title)}</h3>
                    <p class="meta">${duration}</p>
                    <p class="price">${price}</p>
                    <a class="btn btn-primary btn-block" href="/prestation/${encodeURIComponent(s.slug)}">Voir le détail</a>
                </div>
            </article>`;
    }

    async function loadServices() {
        const params = new URLSearchParams(new FormData(filtersForm));
        results.setAttribute('aria-busy', 'true');
        try {
            const res = await fetch('/api/prestations?' + params.toString());
            const data = await res.json();
            const services = data.services || [];
            if (services.length === 0) {
                results.innerHTML = '<p class="muted">Aucune prestation ne correspond à vos critères.</p>';
            } else {
                results.innerHTML = '<div class="grid">' + services.map(cardHtml).join('') + '</div>';
            }
        } catch (e) {
            results.innerHTML = '<p class="error-text">Erreur de chargement. Veuillez réessayer.</p>';
        } finally {
            results.removeAttribute('aria-busy');
        }
    }

    // À chaque changement de filtre, on recharge la liste sans recharger la page.
    filtersForm.addEventListener('input', () => {
        syncOutputs();
        debounce(loadServices);
    });
    filtersForm.addEventListener('change', () => debounce(loadServices));

    // On empêche l'envoi classique du formulaire (tout se fait en JS).
    filtersForm.addEventListener('submit', (e) => {
        e.preventDefault();
        loadServices();
    });
});
