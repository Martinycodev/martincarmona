<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Martín Carmona — Diseñador, fotógrafo, videógrafo y desarrollador web desde Jaén. Una década convirtiendo ideas en cosas reales.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://martincarmona.com">

    <title><?= htmlspecialchars($title ?? 'Martín Carmona') ?></title>

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= htmlspecialchars($title ?? 'Martín Carmona') ?>">
    <meta property="og:description" content="Una década convirtiendo ideas en cosas reales.">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="https://martincarmona.com">
    <meta property="og:locale"      content="es_ES">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Martín Carmona",
        "url": "https://martincarmona.com",
        "email": "info@martincarmona.com",
        "jobTitle": "Diseñador, fotógrafo, videógrafo y desarrollador web",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Jaén",
            "addressCountry": "ES"
        }
    }
    </script>

    <!-- Tipografías: Fraunces (display, serif cálido) + Switzer (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://api.fontshare.com">
    <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700&display=swap">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <style>
        /* ─── Reset ────────────────────────────────────────── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ─── Paleta extraída del personaje ──────────────────
           Parchment cálido como fondo principal, navy oscuro
           para el texto, sage (cascos) y tan (piel) como
           acentos, marrón cuero para detalles.
        ─────────────────────────────────────────────────────── */
        :root {
            --bg:           #f4ead8;  /* papel cálido */
            --bg-soft:      #ede1c9;  /* parchment más cargado */
            --bg-deep:      #e3d4b3;  /* parchment fuerte */
            --ink:          #1a2233;  /* navy texto principal */
            --ink-mid:      #3c4a60;  /* navy medio */
            --ink-soft:     #6e6a5e;  /* gris cálido */
            --ink-mute:     #a09a89;  /* sutil sobre parchment */
            --sage:         #a5c882;  /* verde de los cascos */
            --sage-deep:    #6e924a;  /* sage más profundo */
            --tan:          #d4a384;  /* tono piel / cálido */
            --brown:        #7a4a30;  /* marrón cuero */
            --line:         rgba(26, 34, 51, 0.12);
            --line-strong:  rgba(26, 34, 51, 0.22);
        }

        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: 'Switzer', 'Helvetica Neue', system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        /* Textura de papel sutil (grain) */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: 0.06;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 220px 220px;
            mix-blend-mode: multiply;
        }

        /* Mancha cálida muy difuminada que suaviza la página */
        body::after {
            content: '';
            position: fixed;
            top: -20%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            max-width: 800px;
            max-height: 800px;
            background: radial-gradient(circle, var(--tan) 0%, transparent 65%);
            opacity: 0.18;
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
        }

        /* ─── Selección y focus ───────────────────────────── */
        ::selection {
            background: var(--sage);
            color: var(--ink);
        }

        :focus-visible {
            outline: 2px solid var(--sage-deep);
            outline-offset: 3px;
            border-radius: 2px;
        }

        /* ─── Scrollbar discreta ──────────────────────────── */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-soft); }
        ::-webkit-scrollbar-thumb { background: var(--ink-mute); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--brown); }

        /* ─── Layout principal ────────────────────────────── */
        main {
            position: relative;
            z-index: 2;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 clamp(1.25rem, 4vw, 3rem);
        }

        /* ─── Header ──────────────────────────────────────── */
        .site-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            transition: background 0.4s ease, backdrop-filter 0.4s ease, border-color 0.4s ease;
            border-bottom: 1px solid transparent;
        }

        .site-header.is-scrolled {
            background: rgba(244, 234, 216, 0.85);
            backdrop-filter: blur(14px) saturate(140%);
            -webkit-backdrop-filter: blur(14px) saturate(140%);
            border-bottom-color: var(--line);
        }

        .site-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 0;
        }

        .brand {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
            color: var(--ink);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .brand__dot {
            width: 8px;
            height: 8px;
            background: var(--sage-deep);
            border-radius: 50%;
            display: inline-block;
        }

        .header-nav {
            display: flex;
            gap: 2rem;
            list-style: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--ink-mid);
        }

        .header-nav a {
            color: inherit;
            text-decoration: none;
            transition: color 0.25s ease;
            position: relative;
        }

        .header-nav a:hover {
            color: var(--sage-deep);
        }

        @media (max-width: 640px) {
            .header-nav { display: none; }
        }

        /* ─── Tipografía utilitaria ───────────────────────── */
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--sage-deep);
        }

        .eyebrow::before {
            content: '';
            width: 28px;
            height: 1px;
            background: var(--sage-deep);
        }

        .display {
            font-family: 'Fraunces', serif;
            font-weight: 700;
            line-height: 1.02;
            letter-spacing: -0.035em;
            color: var(--ink);
        }

        .lead {
            font-size: clamp(1rem, 1.4vw, 1.15rem);
            color: var(--ink-mid);
            line-height: 1.65;
            max-width: 38rem;
        }

        /* ─── Hero ────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 8rem 0 4rem;
            position: relative;
        }

        .hero__label {
            margin-bottom: 2rem;
            opacity: 0;
            animation: rise 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
        }

        .hero__title {
            font-size: clamp(3rem, 9vw, 7.5rem);
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: rise 1s cubic-bezier(0.16, 1, 0.3, 1) 0.4s forwards;
        }

        .hero__title em {
            font-style: italic;
            font-weight: 500;
            color: var(--sage-deep);
        }

        .hero__subtitle {
            font-size: clamp(1.05rem, 1.6vw, 1.3rem);
            color: var(--ink-mid);
            max-width: 36rem;
            line-height: 1.55;
            opacity: 0;
            animation: rise 1s cubic-bezier(0.16, 1, 0.3, 1) 0.6s forwards;
        }

        .hero__meta {
            margin-top: 3rem;
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            font-size: 0.82rem;
            color: var(--ink-soft);
            opacity: 0;
            animation: rise 1s cubic-bezier(0.16, 1, 0.3, 1) 0.85s forwards;
        }

        .hero__meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hero__meta span::before {
            content: '';
            width: 5px;
            height: 5px;
            background: var(--brown);
            border-radius: 50%;
        }

        .hero__scroll {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.7rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--ink-mute);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            animation: rise 1s cubic-bezier(0.16, 1, 0.3, 1) 1.1s forwards;
        }

        .hero__scroll::after {
            content: '';
            width: 1px;
            height: 36px;
            background: linear-gradient(to bottom, var(--ink-mute), transparent);
            animation: scrollPulse 2.4s ease-in-out infinite;
        }

        /* ─── Sección genérica ────────────────────────────── */
        .section {
            padding: clamp(5rem, 10vw, 9rem) 0;
            position: relative;
        }

        .section--soft {
            background: var(--bg-soft);
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }

        .section__head {
            margin-bottom: clamp(2.5rem, 5vw, 4.5rem);
        }

        .section__title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-top: 1.25rem;
            max-width: 24ch;
        }

        /* ─── Sobre mí ────────────────────────────────────── */
        .about {
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(2.5rem, 5vw, 5rem);
            align-items: center;
        }

        @media (min-width: 880px) {
            .about {
                grid-template-columns: 1fr 1.2fr;
            }
        }

        .about__portrait {
            position: relative;
            aspect-ratio: 4 / 5;
            border-radius: 14px;
            overflow: hidden;
            background: var(--bg-deep);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.5) inset,
                0 30px 60px -30px rgba(26, 34, 51, 0.35);
        }

        /* Recortamos el sheet del personaje para mostrar solo
           el front view (la cara/torso). object-position desplaza
           la imagen para encuadrar al personaje frontal. */
        .about__portrait img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 18% 8%;
            transform: scale(2.45);
            transform-origin: 22% 18%;
        }

        .about__portrait::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 65%, rgba(244, 234, 216, 0.4));
            pointer-events: none;
        }

        .about__portrait-mark {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: end;
            font-family: 'Fraunces', serif;
            font-size: 0.78rem;
            color: var(--ink);
            letter-spacing: 0.02em;
            z-index: 2;
        }

        .about__portrait-mark span:last-child {
            font-style: italic;
            color: var(--ink-mid);
        }

        .about__text p {
            margin-bottom: 1.25rem;
            color: var(--ink-mid);
            font-size: 1.02rem;
            line-height: 1.75;
            max-width: 36rem;
        }

        .about__text p:first-of-type {
            font-family: 'Fraunces', serif;
            font-size: clamp(1.25rem, 2.2vw, 1.65rem);
            font-weight: 400;
            line-height: 1.45;
            color: var(--ink);
            margin-bottom: 2rem;
        }

        .about__text p strong {
            color: var(--ink);
            font-weight: 600;
        }

        .about__signature {
            margin-top: 2.5rem;
            padding-top: 1.75rem;
            border-top: 1px solid var(--line);
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--ink-mid);
            background: rgba(255, 255, 255, 0.45);
            border: 1px solid var(--line);
            border-radius: 100px;
            transition: all 0.25s ease;
        }

        .chip:hover {
            border-color: var(--sage-deep);
            color: var(--sage-deep);
        }

        /* ─── Trayectoria / Disciplinas ───────────────────── */
        .disciplines {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
            gap: 1px;
            background: var(--line);
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
        }

        .discipline {
            background: var(--bg-soft);
            padding: 2.25rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            transition: background 0.4s ease;
        }

        .discipline:hover {
            background: var(--bg-deep);
        }

        .discipline__num {
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-style: italic;
            font-size: 0.95rem;
            color: var(--brown);
        }

        .discipline__name {
            font-family: 'Fraunces', serif;
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--ink);
            letter-spacing: -0.02em;
        }

        .discipline__desc {
            font-size: 0.92rem;
            color: var(--ink-mid);
            line-height: 1.6;
        }

        /* ─── Trabajo (grid de proyectos) ─────────────────── */
        .work {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
            gap: 1.5rem;
        }

        .work-card {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            background: rgba(255, 255, 255, 0.35);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1rem;
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.3s ease,
                        background 0.3s ease;
        }

        .work-card:hover {
            transform: translateY(-4px);
            border-color: var(--line-strong);
            background: rgba(255, 255, 255, 0.6);
        }

        .work-card--featured {
            grid-column: span 2;
        }

        @media (max-width: 720px) {
            .work-card--featured { grid-column: auto; }
        }

        .work-card__media {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            overflow: hidden;
            background: var(--bg-deep);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .work-card--featured .work-card__media {
            aspect-ratio: 16 / 9;
        }

        .work-card__media-tag {
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-size: 0.85rem;
            color: var(--ink-mute);
            letter-spacing: 0.04em;
        }

        /* Patrón decorativo dentro del thumbnail */
        .work-card__media::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(165, 200, 130, 0.18), transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(212, 163, 132, 0.15), transparent 55%);
            opacity: 0.9;
        }

        .work-card__body {
            padding: 0.4rem 0.6rem 0.8rem;
        }

        .work-card__cat {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--sage-deep);
            margin-bottom: 0.5rem;
        }

        .work-card__title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.2rem;
            line-height: 1.25;
            color: var(--ink);
            margin-bottom: 0.4rem;
            letter-spacing: -0.015em;
        }

        .work-card__meta {
            font-size: 0.82rem;
            color: var(--ink-soft);
        }

        .work-card__media--star::after {
            content: '★ destacado';
            position: absolute;
            top: 0.85rem;
            left: 0.85rem;
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-size: 0.7rem;
            padding: 0.25rem 0.6rem;
            background: rgba(244, 234, 216, 0.92);
            border: 1px solid var(--line);
            border-radius: 100px;
            color: var(--brown);
            backdrop-filter: blur(6px);
        }

        /* ─── Footer ──────────────────────────────────────── */
        .site-footer {
            position: relative;
            z-index: 2;
            background: var(--ink);
            color: rgba(244, 234, 216, 0.7);
            padding: 4rem 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 720px) {
            .footer-grid {
                grid-template-columns: 2fr 1fr 1fr;
                gap: 3rem;
            }
        }

        .footer-block h4 {
            font-family: 'Fraunces', serif;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 1.25rem;
        }

        .footer-block p,
        .footer-block li {
            font-size: 0.92rem;
            line-height: 1.7;
            color: rgba(244, 234, 216, 0.65);
        }

        .footer-block ul {
            list-style: none;
        }

        .footer-block li + li {
            margin-top: 0.5rem;
        }

        .footer-block a {
            color: rgba(244, 234, 216, 0.85);
            text-decoration: none;
            transition: color 0.25s ease;
        }

        .footer-block a:hover {
            color: var(--sage);
        }

        .footer-brand {
            font-family: 'Fraunces', serif;
            font-size: 1.65rem;
            font-weight: 600;
            color: var(--bg);
            letter-spacing: -0.02em;
            margin-bottom: 0.85rem;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(244, 234, 216, 0.12);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 0.78rem;
            color: rgba(244, 234, 216, 0.45);
        }

        @media (min-width: 720px) {
            .footer-bottom {
                flex-direction: row;
                align-items: center;
            }
        }

        /* Acceso al dashboard — discreto pero localizable */
        .dashboard-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            color: rgba(244, 234, 216, 0.55);
            text-decoration: none;
            padding: 0.45rem 0.9rem;
            border: 1px solid rgba(244, 234, 216, 0.14);
            border-radius: 100px;
            transition: all 0.3s ease;
        }

        .dashboard-link:hover {
            color: var(--sage);
            border-color: var(--sage);
            background: rgba(165, 200, 130, 0.08);
        }

        .dashboard-link svg {
            width: 12px;
            height: 12px;
        }

        /* ─── Animaciones ────────────────────────────────── */
        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes scrollPulse {
            0%, 100% { opacity: 0.4; transform: scaleY(1); }
            50%      { opacity: 1;   transform: scaleY(1.15); }
        }

        /* Reveal on scroll (controlado por IntersectionObserver) */
        [data-reveal] {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
        }

        [data-reveal].is-visible {
            opacity: 1;
            transform: none;
        }

        /* Reduce motion: respetar preferencia del usuario */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

    <!-- ════ Header ════ -->
    <header class="site-header" id="siteHeader">
        <div class="container site-header__inner">
            <a href="/" class="brand">
                <span class="brand__dot"></span>
                Martín Carmona
            </a>
            <ul class="header-nav">
                <li><a href="#sobre-mi">Sobre mí</a></li>
                <li><a href="#trayectoria">Trayectoria</a></li>
                <li><a href="#trabajo">Trabajo</a></li>
            </ul>
        </div>
    </header>

    <!-- ════ Contenido principal ════ -->
    <main>
        <?= $content ?>
    </main>

    <!-- ════ Footer ════ -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-block">
                    <p class="footer-brand">Martín Carmona</p>
                    <p>
                        Diseñador, fotógrafo, videógrafo y desarrollador
                        web desde Jaén. Creando cosas desde 2014.
                    </p>
                </div>

                <div class="footer-block">
                    <h4>Contacto</h4>
                    <ul>
                        <li><a href="mailto:info@martincarmona.com">info@martincarmona.com</a></li>
                        <li>Jaén, España</li>
                    </ul>
                </div>

                <div class="footer-block">
                    <h4>En la red</h4>
                    <ul>
                        <li><a href="https://github.com/MartinycoDev" target="_blank" rel="noopener">GitHub</a></li>
                        <li><a href="https://www.linkedin.com/in/martincarmona/" target="_blank" rel="noopener">LinkedIn</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> Martín Carmona — Hecho con cuidado en Jaén</span>

                <!-- Acceso al dashboard (login en producción) -->
                <a href="/planner/login" class="dashboard-link" rel="nofollow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Acceso dashboard
                </a>
            </div>
        </div>
    </footer>

    <script>
        // ─── Header scroll state ───────────────────────────
        // Añade clase 'is-scrolled' cuando bajamos un poco
        // para activar el fondo blur del header.
        const header = document.getElementById('siteHeader');
        const onScroll = () => {
            if (window.scrollY > 30) header.classList.add('is-scrolled');
            else header.classList.remove('is-scrolled');
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        // ─── Reveal on scroll ──────────────────────────────
        // IntersectionObserver: los elementos con [data-reveal]
        // se animan al entrar en el viewport. Es ligero y
        // funciona sin librerías externas.
        const revealElements = document.querySelectorAll('[data-reveal]');
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -80px 0px' });

        revealElements.forEach(el => io.observe(el));
    </script>
</body>
</html>
