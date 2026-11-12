document.addEventListener('DOMContentLoaded', () => {
    const notice = document.querySelector('[data-internship-celebration]');
    if (!notice) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    notice.scrollIntoView({ behavior: reducedMotion ? 'instant' : 'smooth', block: 'center' });
    if (reducedMotion) return;

    const layer = document.createElement('div');
    layer.setAttribute('aria-hidden', 'true');
    Object.assign(layer.style, { position: 'fixed', inset: '0', pointerEvents: 'none', overflow: 'hidden', zIndex: '80' });
    document.body.append(layer);
    const colors = ['#0d9488', '#1e64b4', '#fbbf24', '#fb7185', '#a78bfa'];
    for (let i = 0; i < 70; i++) {
        const piece = document.createElement('span');
        Object.assign(piece.style, { position: 'absolute', left: `${Math.random() * 100}%`, top: '-20px', width: '8px', height: '14px', background: colors[i % colors.length], borderRadius: '2px' });
        layer.append(piece);
        piece.animate([
            { transform: 'translateY(0) rotate(0)', opacity: 1 },
            { transform: `translate(${Math.random() * 160 - 80}px, ${window.innerHeight + 40}px) rotate(${Math.random() * 720}deg)`, opacity: 0 },
        ], { duration: 2200 + Math.random() * 1400, delay: Math.random() * 500, easing: 'ease-in', fill: 'both' });
    }
    window.setTimeout(() => layer.remove(), 4200);
});
