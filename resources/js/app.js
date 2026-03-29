/**
 * app.js — Entry point principal
 * Inicializa: Alpine.js, Lenis, GSAP, cursor y navegación activa
 */

// ── Alpine.js ─────────────────────────────────────────────────
import Alpine from 'alpinejs';
import focus   from '@alpinejs/focus';

Alpine.plugin(focus);

// Exponer Alpine globalmente (necesario para x-data inline en HTML)
window.Alpine = Alpine;
Alpine.start();


// ── Scroll suave (Lenis) ───────────────────────────────────────
import { initLenis, scrollTo } from './scroll.js';

const lenis = initLenis();

// Conectar Lenis con GSAP ScrollTrigger
import { gsap, ScrollTrigger, refreshScrollTrigger } from './animations.js';

gsap.ticker.add((time) => {
    lenis.raf(time * 1000);
});
gsap.ticker.lagSmoothing(0);

// Actualizar ScrollTrigger en cada frame de Lenis
lenis.on('scroll', () => ScrollTrigger.update());


// ── Animaciones ────────────────────────────────────────────────
import {
    animateHero,
    initScrollReveals,
    initHeroParallax,
    initHeaderScroll,
} from './animations.js';

document.addEventListener('DOMContentLoaded', () => {

    // Secuencia de entrada
    animateHero();

    // Reveals al scroll
    initScrollReveals();

    // Parallax hero
    initHeroParallax();

    // Header sticky
    initHeaderScroll();

    // Scroll suave para links internos (#hash)
    initAnchorLinks();

    // Cursor personalizado
    initCursor();

    // Navegación activa por sección
    initActiveNav();
});


// ── Links de anclaje suave ─────────────────────────────────────
function initAnchorLinks() {
    document.querySelectorAll('a[href^="/#"], a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            const hash = href.startsWith('/') ? href.slice(1) : href;

            // Si estamos en otra página, dejar navegación normal
            if (href.startsWith('/') && window.location.pathname !== '/') return;

            const target = document.querySelector(hash);
            if (!target) return;

            e.preventDefault();
            scrollTo(target);
        });
    });
}


// ── Cursor personalizado ───────────────────────────────────────
function initCursor() {
    const cursor      = document.getElementById('cursor');
    const cursorTrail = document.getElementById('cursor-trail');

    if (!cursor || !cursorTrail) return;

    // Solo en dispositivos con cursor real
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    let mx = -100, my = -100;

    document.addEventListener('mousemove', (e) => {
        mx = e.clientX;
        my = e.clientY;
        gsap.to(cursor,      { x: mx, y: my, duration: 0.05 });
        gsap.to(cursorTrail, { x: mx, y: my, duration: 0.25 });
    });

    // Hover sobre interactivos
    const interactives = document.querySelectorAll('a, button, [data-cursor-hover]');
    interactives.forEach((el) => {
        el.addEventListener('mouseenter', () => cursor.classList.add('is-hovering'));
        el.addEventListener('mouseleave', () => cursor.classList.remove('is-hovering'));
    });

    // Ocultar al salir de la ventana
    document.addEventListener('mouseleave', () => {
        gsap.to([cursor, cursorTrail], { opacity: 0, duration: 0.3 });
    });
    document.addEventListener('mouseenter', () => {
        gsap.to([cursor, cursorTrail], { opacity: 1, duration: 0.3 });
    });
}


// ── Navegación activa por sección visible ──────────────────────
function initActiveNav() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    if (!sections.length || !navLinks.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const id = entry.target.id;
                navLinks.forEach((link) => {
                    const href = link.getAttribute('href');
                    link.classList.toggle('active', href === `/#${id}` || href === `#${id}`);
                });
            });
        },
        { rootMargin: '-40% 0px -55% 0px' }
    );

    sections.forEach((s) => observer.observe(s));
}


// ── Formulario de contacto (AJAX) ──────────────────────────────
const contactForm = document.getElementById('contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn      = contactForm.querySelector('[type="submit"]');
        const feedback = document.getElementById('form-feedback');
        btn.disabled   = true;

        try {
            const res  = await fetch('/contacto/enviar', {
                method: 'POST',
                body: new FormData(contactForm),
            });
            const data = await res.json();

            feedback.textContent  = data.message;
            feedback.className    = data.success
                ? 'mt-4 text-green-400 text-sm'
                : 'mt-4 text-red-400 text-sm';

            if (data.success) contactForm.reset();
        } catch {
            feedback.textContent = 'Error de red. Inténtalo de nuevo.';
            feedback.className   = 'mt-4 text-red-400 text-sm';
        } finally {
            btn.disabled = false;
        }
    });
}
