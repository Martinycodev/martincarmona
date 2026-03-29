/**
 * animations.js — GSAP + ScrollTrigger
 * Todas las animaciones de la web
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Animación de entrada del Hero
 */
export function animateHero() {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    tl.fromTo('.hero-label',
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.8 },
        0.3
    )
    .fromTo('.hero-title',
        { opacity: 0, y: 40 },
        { opacity: 1, y: 0, duration: 1.2 },
        0.6
    )
    .fromTo('.hero-subtitle',
        { opacity: 0, y: 25 },
        { opacity: 1, y: 0, duration: 0.9 },
        1.0
    )
    .fromTo('.hero-ctas',
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.8 },
        1.3
    )
    .fromTo('.hero-scroll',
        { opacity: 0 },
        { opacity: 1, duration: 1 },
        1.8
    );

    return tl;
}

/**
 * Reveal al hacer scroll — elementos con clases .reveal-left, .reveal-right, .reveal-fade
 */
export function initScrollReveals() {
    // Fade + slide desde la izquierda
    gsap.utils.toArray('.reveal-left').forEach((el) => {
        gsap.fromTo(el,
            { opacity: 0, x: -40 },
            {
                opacity: 1,
                x: 0,
                duration: 1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 85%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse',
                },
            }
        );
    });

    // Fade + slide desde la derecha
    gsap.utils.toArray('.reveal-right').forEach((el) => {
        gsap.fromTo(el,
            { opacity: 0, x: 40 },
            {
                opacity: 1,
                x: 0,
                duration: 1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 85%',
                    toggleActions: 'play none none reverse',
                },
            }
        );
    });

    // Fade simple + subida
    gsap.utils.toArray('.reveal-fade').forEach((el, i) => {
        const delay = parseFloat(el.style.getPropertyValue('--delay') || 0) / 1000;
        gsap.fromTo(el,
            { opacity: 0, y: 25 },
            {
                opacity: 1,
                y: 0,
                duration: 0.9,
                delay,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 88%',
                    toggleActions: 'play none none reverse',
                },
            }
        );
    });
}

/**
 * Parallax sutil en el hero
 */
export function initHeroParallax() {
    const hero = document.getElementById('hero');
    if (!hero) return;

    gsap.to(hero, {
        yPercent: -15,
        ease: 'none',
        scrollTrigger: {
            trigger: hero,
            start: 'top top',
            end: 'bottom top',
            scrub: true,
        },
    });
}

/**
 * Header: añadir/quitar clase .scrolled
 */
export function initHeaderScroll() {
    const header = document.getElementById('site-header');
    if (!header) return;

    ScrollTrigger.create({
        start: 'top -60px',
        onUpdate: (self) => {
            header.classList.toggle('scrolled', self.scroll() > 60);
        },
    });
}

/**
 * Línea de progreso de scroll (opcional, para página de proyecto detalle)
 */
export function initProgressBar(selector = '#scroll-progress') {
    const bar = document.querySelector(selector);
    if (!bar) return;

    gsap.to(bar, {
        scaleX: 1,
        ease: 'none',
        scrollTrigger: {
            scrub: true,
            start: 'top top',
            end: 'bottom bottom',
        },
    });
    gsap.set(bar, { scaleX: 0, transformOrigin: 'left center' });
}

/**
 * Refresca ScrollTrigger tras cambios de DOM (Alpine.js toggling, etc.)
 */
export function refreshScrollTrigger() {
    ScrollTrigger.refresh();
}

export { gsap, ScrollTrigger };
