/* =====================================================================
   ZenSpace — Animation 3D du héros (Three.js)
   Effet « bokeh » : des points lumineux doux qui flottent dans la
   profondeur, façon poussière dorée/sauge. Ambiance premium et apaisante.
   Réagit délicatement à la souris (parallaxe).
   ===================================================================== */

import * as THREE from 'three';

const canvas = document.getElementById('hero-canvas');
if (canvas) {
    // Respecte la préférence « animations réduites » (accessibilité).
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const hero = canvas.parentElement;
    let width = hero.clientWidth;
    let height = hero.clientHeight;

    // --- Scène, caméra, rendu ---
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 100);
    camera.position.z = 18;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    // --- Texture ronde et floue : c'est elle qui donne l'effet « bokeh » ---
    const sprite = makeBokehTexture();

    // --- Couleurs du thème (sauge, doré, rosé) ---
    const palette = [0x6b8772, 0xc08552, 0xd6a878, 0xcfa3a3, 0x8aa890];

    // On crée plusieurs "couches" de bokeh à des profondeurs différentes :
    // les petites au fond (nettes/fixes), les grosses devant (floues) →
    // ça donne une vraie sensation de profondeur de champ.
    const layers = [];
    const layerSpecs = [
        { count: 80, size: 0.6, spread: 26, depth: -6, opacity: 0.5, drift: 0.06 },
        { count: 50, size: 1.4, spread: 30, depth: 0,  opacity: 0.45, drift: 0.10 },
        { count: 22, size: 3.0, spread: 34, depth: 6,  opacity: 0.30, drift: 0.16 },
    ];

    for (const spec of layerSpecs) {
        const positions = new Float32Array(spec.count * 3);
        const colors = new Float32Array(spec.count * 3);
        const phases = [];

        for (let i = 0; i < spec.count; i++) {
            positions[i * 3]     = (Math.random() - 0.5) * spec.spread;
            positions[i * 3 + 1] = (Math.random() - 0.5) * (spec.spread * 0.6);
            positions[i * 3 + 2] = spec.depth + (Math.random() - 0.5) * 4;

            const c = new THREE.Color(palette[Math.floor(Math.random() * palette.length)]);
            colors[i * 3] = c.r; colors[i * 3 + 1] = c.g; colors[i * 3 + 2] = c.b;

            phases.push({
                speed: 0.15 + Math.random() * 0.4,
                offset: Math.random() * Math.PI * 2,
                ampX: 0.3 + Math.random() * 0.8,
                ampY: 0.3 + Math.random() * 0.8,
            });
        }

        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

        const material = new THREE.PointsMaterial({
            size: spec.size,
            map: sprite,
            vertexColors: true,
            transparent: true,
            opacity: spec.opacity,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
            sizeAttenuation: true,
        });

        const points = new THREE.Points(geometry, material);
        scene.add(points);
        layers.push({ points, geometry, phases, spec, base: positions.slice() });
    }

    // --- Interaction souris (parallaxe douce) ---
    let targetX = 0, targetY = 0;
    if (!reduceMotion) {
        window.addEventListener('mousemove', (e) => {
            targetX = (e.clientX / window.innerWidth - 0.5);
            targetY = (e.clientY / window.innerHeight - 0.5);
        });
    }

    // --- Boucle d'animation : chaque point flotte autour de sa position ---
    const clock = new THREE.Clock();
    function render() {
        const t = clock.getElapsedTime();

        for (const layer of layers) {
            const pos = layer.geometry.attributes.position;
            for (let i = 0; i < layer.phases.length; i++) {
                const p = layer.phases[i];
                pos.array[i * 3]     = layer.base[i * 3]     + Math.sin(t * p.speed + p.offset) * p.ampX;
                pos.array[i * 3 + 1] = layer.base[i * 3 + 1] + Math.cos(t * p.speed + p.offset) * p.ampY;
            }
            pos.needsUpdate = true;
            // Les couches du fond bougent moins que celles de devant (parallaxe).
            layer.points.position.x = targetX * 4 * layer.spec.drift * 10;
            layer.points.position.y = -targetY * 3 * layer.spec.drift * 10;
        }

        renderer.render(scene, camera);
        if (!reduceMotion) requestAnimationFrame(render);
    }
    render();

    // --- Redimensionnement responsive ---
    window.addEventListener('resize', () => {
        width = hero.clientWidth;
        height = hero.clientHeight;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    });

    /**
     * Crée une texture circulaire au bord très flou (halo) : c'est ce flou
     * qui transforme un simple point en jolie tache de lumière « bokeh ».
     */
    function makeBokehTexture() {
        const size = 128;
        const c = document.createElement('canvas');
        c.width = c.height = size;
        const ctx = c.getContext('2d');
        const g = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
        g.addColorStop(0,   'rgba(255,255,255,0.95)');
        g.addColorStop(0.25,'rgba(255,255,255,0.55)');
        g.addColorStop(0.6, 'rgba(255,255,255,0.12)');
        g.addColorStop(1,   'rgba(255,255,255,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, size, size);
        return new THREE.CanvasTexture(c);
    }
}
