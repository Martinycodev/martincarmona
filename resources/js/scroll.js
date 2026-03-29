/**
 * scroll.js — Lenis smooth scroll
 * Inicializa Lenis y lo integra con GSAP ScrollTrigger
 */

import Lenis from 'lenis';

let lenis;

export function initLenis() {
    lenis = new Lenis({
        duration: 1.2,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        direction: 'vertical',
        gestureDirection: 'vertical',
        smooth: true,
        smoothTouch: false,
        touchMultiplier: 2,
    });

    // Integración con GSAP ticker para que ScrollTrigger funcione correctamente
    // (gsap se importa en animations.js y se expone globalmente)
    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    return lenis;
}

export function getLenis() {
    return lenis;
}

/**
 * Scroll suave a un selector o elemento
 */
export function scrollTo(target, options = {}) {
    if (!lenis) return;
    lenis.scrollTo(target, {
        offset: options.offset ?? -80,
        duration: options.duration ?? 1.2,
        easing: options.easing,
    });
}

/**
 * Activa/desactiva el scroll (útil para modales, lightbox, etc.)
 */
export function lockScroll() {
    lenis?.stop();
}

export function unlockScroll() {
    lenis?.start();
}
