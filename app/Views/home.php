<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Martin Carmona - App de gestión para olivar y fincas agrícolas</title>
    <meta name="description" content="Aplicación agrícola para organizar tu olivar: controla parcelas, trabajadores, tareas, economía y campañas de aceituna desde una sola plataforma. Sin papeles, sin hojas de cálculo.">
    <meta name="keywords" content="app gestión olivar, aplicación agrícola, gestión de fincas, control de parcelas, olivar Jaén, MartinCarmona, tareas agrícolas, campañas aceituna">
    <link rel="canonical" href="https://martincarmona.com/">
    <meta name="author" content="Martín Carmona">
    <meta name="robots" content="index, follow">
    <!-- Open Graph (Facebook, WhatsApp, Telegram) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://martincarmona.com/">
    <meta property="og:title" content="Martin Carmona - App de gestión para olivar y fincas agrícolas">
    <meta property="og:description" content="Aplicación agrícola para organizar tu olivar: controla parcelas, trabajadores, tareas, economía y campañas de aceituna desde una sola plataforma. Sin papeles, sin hojas de cálculo.">
    <meta property="og:image" content="https://martincarmona.com/public/img/og-cover.jpg">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="MartinCarmona.com">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Martin Carmona - App de gestión para olivar y fincas agrícolas">
    <meta name="twitter:description" content="Aplicación agrícola para organizar tu olivar: controla parcelas, trabajadores, tareas, economía y campañas de aceituna desde una sola plataforma. Sin papeles, sin hojas de cálculo.">
    <meta name="twitter:image" content="https://martincarmona.com/public/img/og-cover.jpg">
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "MartinCarmona.com",
        "description": "Aplicación web de gestión integral de olivar y fincas agrícolas",
        "url": "https://martincarmona.com",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "author": {
            "@type": "Person",
            "name": "Martín Carmona",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Arjonilla",
                "addressRegion": "Jaén",
                "addressCountry": "ES"
            }
        }
    }
    </script>
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <!-- PWA -->
    <link rel="manifest" href="<?= $this->url('/manifest.json') ?>">
    <meta name="theme-color" content="#4caf50">
    <!-- Estilos compartidos de paginas publicas -->
    <link rel="stylesheet" href="<?= $this->url('/public/css/landing.css') ?>">
    <style>
        /* ===== HERO ===== */
        .lp-hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 140px 24px 100px;
            position: relative;
            overflow: hidden;
        }

        /* Video de fondo */
        .lp-hero-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
            z-index: 0;
            opacity: 0.22;
            filter: brightness(0.55) grayscale(15%);
        }

        /* Grid background */
        .lp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(76, 175, 80, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(76, 175, 80, 0.035) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
        }

        /* Radial glow */
        .lp-hero::after {
            content: '';
            position: absolute;
            top: 25%;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 500px;
            background: radial-gradient(ellipse, rgba(76, 175, 80, 0.07) 0%, transparent 65%);
            pointer-events: none;
        }

        .lp-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 6px 18px;
            background: var(--green-dim);
            border: 1px solid var(--border);
            border-radius: 100px;
            font-size: 0.78rem;
            color: var(--green-light);
            margin-bottom: 36px;
            position: relative;
            z-index: 1;
            letter-spacing: 0.04em;
        }
        .lp-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--green);
            border-radius: 50%;
            animation: lp-pulse 2.4s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes lp-pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(76,175,80,0.5); }
            50%       { opacity: 0.7; box-shadow: 0 0 0 5px rgba(76,175,80,0); }
        }

        .lp-hero h1 {
            font-size: clamp(2.6rem, 6.5vw, 4.8rem);
            font-weight: 700;
            line-height: 1.12;
            letter-spacing: -0.03em;
            max-width: 820px;
            position: relative;
            z-index: 1;
            margin-bottom: 24px;
        }
        .lp-hero h1 em {
            font-style: normal;
            color: var(--green);
        }

        .lp-hero-desc {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 500px;
            position: relative;
            z-index: 1;
            margin-bottom: 44px;
        }

        .lp-hero-actions {
            display: flex;
            gap: 12px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* ===== STATS BAR ===== */
        .lp-stats {
            display: flex;
            justify-content: center;
            gap: 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .lp-stat {
            text-align: center;
            padding: 36px 52px;
            border-right: 1px solid var(--border);
        }
        .lp-stat:last-child { border-right: none; }
        .lp-stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--green);
            display: block;
            letter-spacing: -0.02em;
        }
        .lp-stat-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* ===== FEATURES ===== */
        .lp-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
        }

        .lp-feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 34px 28px;
            transition: all 0.22s;
            position: relative;
            overflow: hidden;
        }
        .lp-feature-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--green) 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.22s;
        }
        .lp-feature-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-5px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
        }
        .lp-feature-card:hover::after { opacity: 1; }

        .lp-feature-icon {
            font-size: 2.2rem;
            display: block;
            margin-bottom: 18px;
        }
        .lp-feature-card { cursor: pointer; }
        .lp-feature-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }
        .lp-feature-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* ===== FEATURE MODAL ===== */
        .lp-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(6px);
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .lp-modal-overlay.active { display: flex; }
        .lp-modal {
            background: var(--surface);
            border: 1px solid var(--border-hover);
            border-radius: 18px;
            padding: 44px 38px;
            max-width: 520px;
            width: 100%;
            position: relative;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6);
            animation: lp-modal-in 0.25s ease-out;
        }
        @keyframes lp-modal-in {
            from { opacity: 0; transform: translateY(16px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .lp-modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.4rem;
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lp-modal-close:hover { color: var(--text); }
        .lp-modal-icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 18px;
        }
        .lp-modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .lp-modal-body {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.75;
        }
        .lp-modal-body ul {
            margin-top: 12px;
            padding-left: 20px;
        }
        .lp-modal-body li {
            margin-bottom: 6px;
        }

        /* ===== HOW IT WORKS — CAROUSEL ===== */
        .lp-how-bg {
            background: var(--surface);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        /* Tabs de roles */
        .lp-role-tabs {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 48px;
        }
        .lp-role-tab {
            padding: 10px 24px;
            border: 1px solid var(--border);
            border-radius: 100px;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s;
        }
        .lp-role-tab:hover {
            border-color: var(--border-hover);
            color: var(--text);
        }
        .lp-role-tab.active {
            background: var(--green);
            color: #0c160c;
            border-color: var(--green);
            font-weight: 600;
        }

        /* Contenedor del carrusel */
        .lp-carousel {
            position: relative;
            overflow: hidden;
        }
        .lp-carousel-slide {
            display: none;
            animation: lp-slide-in 0.4s ease-out;
        }
        .lp-carousel-slide.active { display: block; }
        @keyframes lp-slide-in {
            from { opacity: 0; transform: translateX(30px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .lp-steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            position: relative;
        }
        .lp-steps-grid::before {
            content: '';
            position: absolute;
            top: 24px;
            left: calc(16.66% + 12px);
            right: calc(16.66% + 12px);
            height: 1px;
            background: linear-gradient(90deg, var(--border), var(--border-hover), var(--border));
        }

        .lp-step { text-align: center; }

        .lp-step-num {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--bg);
            border: 1px solid var(--green);
            color: var(--green);
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            position: relative;
            z-index: 1;
        }
        .lp-step-title {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .lp-step-desc {
            font-size: 0.87rem;
            color: var(--text-muted);
        }

        /* Indicadores de progreso del carousel */
        .lp-carousel-dots {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 36px;
        }
        .lp-carousel-dot {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            padding: 0;
            position: relative;
        }
        .lp-carousel-dot::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--border);
            transition: all 0.25s;
        }
        .lp-carousel-dot.active::after {
            background: var(--green);
            width: 24px;
            border-radius: 4px;
        }

        /* ===== RESPONSIVE (home-specific) ===== */
        @media (max-width: 680px) {
            .lp-stat { padding: 28px 24px; border-right: none; border-bottom: 1px solid var(--border); }
            .lp-stat:last-child { border-bottom: none; }
            .lp-stats { flex-direction: column; align-items: center; }
            .lp-steps-grid { grid-template-columns: 1fr; }
            .lp-steps-grid::before { display: none; }
            .lp-role-tabs { flex-wrap: wrap; gap: 8px; }
            .lp-role-tab { padding: 8px 18px; font-size: 0.84rem; }
            .lp-modal { padding: 32px 24px; }
        }
    </style>
</head>
<body>

    <?php $activePage = 'home'; include __DIR__ . '/public/_nav.php'; ?>

    <main>
    <!-- HERO -->
    <section class="lp-hero">

        <!-- Video de fondo: se elimina automáticamente en conexiones lentas o con ahorro de datos -->
        <video class="lp-hero-video" autoplay muted loop playsinline preload="none" aria-hidden="true">
            <source src="<?= $this->url('/public/video/campo.mp4') ?>" type="video/mp4">
        </video>

        <div class="lp-badge">App de gestión agrícola · Plataforma digital para olivar</div>
        <h1>Tu olivar,<br><em>organizado desde una sola app</em></h1>
        <p class="lp-hero-desc">
            Organiza parcelas, personal, tareas y economía de tu explotación desde el móvil o el ordenador.
            Sin papeles, sin hojas de cálculo.
        </p>
        <div class="lp-hero-actions">
            <a href="<?= $this->url('/login') ?>" class="lp-btn-primary">Iniciar sesion</a>
            <a href="#funciones" class="lp-btn-ghost">Ver funciones ↓</a>
        </div>
    </section>


    <!-- STATS BAR -->
    <div class="lp-stats">
        <div class="lp-stat">
            <span class="lp-stat-value" data-count="<?= (int) ($stats['parcelas'] ?? 0) ?>">0</span>
            <span class="lp-stat-label">Parcelas registradas</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-value" data-count="<?= (int) ($stats['empresas'] ?? 0) ?>">0</span>
            <span class="lp-stat-label">Empresas activas</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-value" data-count="<?= (int) ($stats['trabajadores'] ?? 0) ?>">0</span>
            <span class="lp-stat-label">Trabajadores activos</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-value" data-count="0">♻️ 0</span>
            <span class="lp-stat-label">Papel usado</span>
        </div>
    </div>


    <!-- FEATURES -->
    <section class="lp-section" id="funciones">
        <div class="lp-section-header">
            <span class="lp-label">Funcionalidades</span>
            <h2 class="lp-title">Todo lo que necesita tu finca</h2>
            <p class="lp-sub">Desde el catastro hasta el balance económico, cubierto en un solo lugar.</p>
        </div>

        <div class="lp-features-grid">
            <div class="lp-feature-card" data-feature="parcelas">
                <span class="lp-feature-icon">🗺️</span>
                <div class="lp-feature-title">Gestión de Parcelas</div>
                <p class="lp-feature-desc">Organiza tus fincas con ficha detallada por parcela, propietario y datos de superficie.</p>
            </div>
            <div class="lp-feature-card" data-feature="personal">
                <span class="lp-feature-icon">👷</span>
                <div class="lp-feature-title">Control de Personal</div>
                <p class="lp-feature-desc">Registra trabajadores, asigna roles y lleva el seguimiento de jornadas y contratos.</p>
            </div>
            <div class="lp-feature-card" data-feature="tareas">
                <span class="lp-feature-icon">📋</span>
                <div class="lp-feature-title">Planificación de Tareas</div>
                <p class="lp-feature-desc">Crea, asigna y monitoriza cada faena: poda, riego, recolección y tratamientos.</p>
            </div>
            <div class="lp-feature-card" data-feature="economia">
                <span class="lp-feature-icon">💶</span>
                <div class="lp-feature-title">Control Económico</div>
                <p class="lp-feature-desc">Registra gastos e ingresos por temporada y genera reportes para tomar mejores decisiones.</p>
            </div>
            <div class="lp-feature-card" data-feature="riego">
                <span class="lp-feature-icon">💧</span>
                <div class="lp-feature-title">Control de Riego</div>
                <p class="lp-feature-desc">Programa y registra los riegos por parcela, controlando consumos y fechas de cada intervención.</p>
            </div>
            <div class="lp-feature-card" data-feature="fitosanitario">
                <span class="lp-feature-icon">🧪</span>
                <div class="lp-feature-title">Control Fitosanitario</div>
                <p class="lp-feature-desc">Registra tratamientos, productos y dosis aplicados. Mantén el historial fitosanitario de cada parcela.</p>
            </div>
            <div class="lp-feature-card" data-feature="portales">
                <span class="lp-feature-icon">👥</span>
                <div class="lp-feature-title">Portal Propietarios y Trabajadores</div>
                <p class="lp-feature-desc">Acceso diferenciado por rol: los propietarios consultan sus parcelas y los trabajadores sus tareas asignadas.</p>
            </div>
            <div class="lp-feature-card" data-feature="campanas">
                <span class="lp-feature-icon">🗓️</span>
                <div class="lp-feature-title">Gestión de Campañas</div>
                <p class="lp-feature-desc">Organiza cada temporada como una campaña independiente, con su propio equipo, tareas y balance económico.</p>
            </div>
        </div>
    </section>

    <!-- MODAL de funcionalidades (se rellena dinámicamente con JS) -->
    <div class="lp-modal-overlay" id="featureModal">
        <div class="lp-modal">
            <button class="lp-modal-close" id="featureModalClose" aria-label="Cerrar">&times;</button>
            <span class="lp-modal-icon" id="featureModalIcon"></span>
            <div class="lp-modal-title" id="featureModalTitle"></div>
            <div class="lp-modal-body" id="featureModalBody"></div>
        </div>
    </div>


    <!-- HOW IT WORKS — CAROUSEL POR ROL -->
    <div class="lp-how-bg">
        <section class="lp-section">
            <div class="lp-section-header">
                <span class="lp-label">Cómo funciona</span>
                <h2 class="lp-title">Simple desde el primer día</h2>
                <p class="lp-sub">Cada rol tiene su propia experiencia adaptada.</p>
            </div>

            <!-- Tabs de roles -->
            <div class="lp-role-tabs">
                <button class="lp-role-tab active" data-role="empresa">Empresa</button>
                <button class="lp-role-tab" data-role="trabajador">Trabajador</button>
                <button class="lp-role-tab" data-role="propietario">Propietario</button>
            </div>

            <!-- Slides del carrusel -->
            <div class="lp-carousel" id="roleCarousel">
                <!-- Slide: Empresa -->
                <div class="lp-carousel-slide active" data-role="empresa">
                    <div class="lp-steps-grid">
                        <div class="lp-step">
                            <div class="lp-step-num">1</div>
                            <div class="lp-step-title">Accede al panel</div>
                            <p class="lp-step-desc">Inicia sesión y accede al dashboard completo con calendario, meteorología y KPIs de tu explotación.</p>
                        </div>
                        <div class="lp-step">
                            <div class="lp-step-num">2</div>
                            <div class="lp-step-title">Organiza tu finca</div>
                            <p class="lp-step-desc">Registra parcelas, trabajadores y proveedores. Planifica tareas, riegos y tratamientos fitosanitarios.</p>
                        </div>
                        <div class="lp-step">
                            <div class="lp-step-num">3</div>
                            <div class="lp-step-title">Controla todo</div>
                            <p class="lp-step-desc">Consulta reportes económicos, campañas de aceituna y exporta datos en tiempo real.</p>
                        </div>
                    </div>
                </div>

                <!-- Slide: Trabajador -->
                <div class="lp-carousel-slide" data-role="trabajador">
                    <div class="lp-steps-grid">
                        <div class="lp-step">
                            <div class="lp-step-num">1</div>
                            <div class="lp-step-title">Consulta tus tareas</div>
                            <p class="lp-step-desc">Accede a tu panel personal y consulta las tareas que te han sido asignadas para hoy y la semana.</p>
                        </div>
                        <div class="lp-step">
                            <div class="lp-step-num">2</div>
                            <div class="lp-step-title">Revisa tu calendario</div>
                            <p class="lp-step-desc">Visualiza tu calendario de trabajo con todas las faenas programadas y las horas asignadas.</p>
                        </div>
                        <div class="lp-step">
                            <div class="lp-step-num">3</div>
                            <div class="lp-step-title">Controla tu deuda</div>
                            <p class="lp-step-desc">Consulta tu balance mensual, horas trabajadas y pagos pendientes en cualquier momento.</p>
                        </div>
                    </div>
                </div>

                <!-- Slide: Propietario -->
                <div class="lp-carousel-slide" data-role="propietario">
                    <div class="lp-steps-grid">
                        <div class="lp-step">
                            <div class="lp-step-num">1</div>
                            <div class="lp-step-title">Accede a tus parcelas</div>
                            <p class="lp-step-desc">Inicia sesión y consulta directamente las parcelas que tienes registradas a tu nombre.</p>
                        </div>
                        <div class="lp-step">
                            <div class="lp-step-num">2</div>
                            <div class="lp-step-title">Revisa las tareas</div>
                            <p class="lp-step-desc">Consulta qué trabajos se han realizado en tus fincas sin ver precios ni datos económicos.</p>
                        </div>
                        <div class="lp-step">
                            <div class="lp-step-num">3</div>
                            <div class="lp-step-title">Sigue las campañas</div>
                            <p class="lp-step-desc">Revisa los kilos, rendimiento y resultados de cada campaña de aceituna en tus parcelas.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dots indicadores -->
            <div class="lp-carousel-dots">
                <button class="lp-carousel-dot active" data-role="empresa" aria-label="Ver panel empresa"></button>
                <button class="lp-carousel-dot" data-role="trabajador" aria-label="Ver panel trabajador"></button>
                <button class="lp-carousel-dot" data-role="propietario" aria-label="Ver panel propietario"></button>
            </div>
        </section>
    </div>


    <!-- CTA final antes del footer -->
    <section class="lp-section" style="text-align: center; padding-bottom: 60px;">
        <div class="lp-section-header" style="margin-bottom: 32px;">
            <span class="lp-label">Empieza hoy</span>
            <h2 class="lp-title">Tu finca merece estar organizada</h2>
            <p class="lp-sub">Digitaliza tu explotacion y olvidate del papel. Contactanos o accede directamente.</p>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= $this->url('/contacto') ?>" class="lp-btn-ghost">Contacto</a>
            <a href="<?= $this->url('/login') ?>" class="lp-btn-primary">Ingresar →</a>
        </div>
    </section>

    </main>

    <?php include __DIR__ . '/public/_footer.php'; ?>

    <script>
        // === Elimina el video en conexiones lentas ===
        (function () {
            var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (conn && (conn.saveData || conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g')) {
                var v = document.querySelector('.lp-hero-video');
                if (v) v.remove();
            }
        })();

        // === ANIMACIÓN CONTADORES (stats bar) ===
        // Los números suben de 0 al valor real con efecto de conteo
        (function () {
            var counters = document.querySelectorAll('.lp-stat-value[data-count]');
            var animated = false;

            function animateCounters() {
                if (animated) return;
                animated = true;
                counters.forEach(function (el) {
                    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                    if (target === 0) return; // No animar stats con valor 0 (preserva contenido original)
                    var duration = 1600;
                    var start = performance.now();
                    function tick(now) {
                        var progress = Math.min((now - start) / duration, 1);
                        // easeOutExpo para un efecto más natural
                        var ease = 1 - Math.pow(2, -10 * progress);
                        el.textContent = Math.round(target * ease);
                        if (progress < 1) requestAnimationFrame(tick);
                        else el.textContent = target;
                    }
                    requestAnimationFrame(tick);
                });
            }

            // Iniciar animación cuando la barra de stats entra en viewport
            var statsBar = document.querySelector('.lp-stats');
            if (statsBar && 'IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) {
                        animateCounters();
                        observer.disconnect();
                    }
                }, { threshold: 0.3 });
                observer.observe(statsBar);
            } else {
                animateCounters();
            }
        })();

        // === MODALES DE FUNCIONALIDADES ===
        (function () {
            // Datos extendidos para cada feature
            var featureData = {
                parcelas: {
                    icon: '🗺️',
                    title: 'Gestión de Parcelas',
                    body: '<p>Controla cada parcela con una ficha completa: referencia catastral, superficie, número de olivos, tipo de plantación y propietario asignado.</p><ul><li>Vincula parcelas a propietarios con DNI y datos de contacto</li><li>Consulta qué tareas se han realizado en cada finca</li><li>Filtra parcelas inactivas o con alerta de mantenimiento</li><li>Integración con SIGPAC para verificar datos catastrales</li></ul>'
                },
                personal: {
                    icon: '👷',
                    title: 'Control de Personal',
                    body: '<p>Gestiona tu equipo de trabajo completo: altas, bajas, documentación y control de jornadas.</p><ul><li>Registro de trabajadores con datos de Seguridad Social</li><li>Control de horas trabajadas por tarea y mes</li><li>Generación de deuda mensual automática por trabajador</li><li>Historial completo de pagos y saldos pendientes</li></ul>'
                },
                tareas: {
                    icon: '📋',
                    title: 'Planificación de Tareas',
                    body: '<p>Crea, asigna y monitoriza cada faena del campo con calendario visual y asignación múltiple.</p><ul><li>Calendario interactivo con vista diaria, semanal y mensual</li><li>Asignación de trabajadores, parcelas y tipos de trabajo por tarea</li><li>Estados: pendiente, en curso y completada</li><li>Sidebar lateral para edición rápida sin cambiar de pantalla</li></ul>'
                },
                economia: {
                    icon: '💶',
                    title: 'Control Económico',
                    body: '<p>Registra cada euro que entra y sale de tu explotación agrícola con categorización automática.</p><ul><li>Movimientos de tipo gasto e ingreso por categoría</li><li>Diferenciación entre cuenta bancaria y efectivo</li><li>Dashboard con gráficos de evolución mensual</li><li>Reportes exportables en CSV y PDF</li></ul>'
                },
                riego: {
                    icon: '💧',
                    title: 'Control de Riego',
                    body: '<p>Programa y registra los riegos por parcela, manteniendo un historial detallado de consumos.</p><ul><li>Registro de fecha, duración y caudal por intervención</li><li>Historial de riego por parcela con filtros por fecha</li><li>Alertas de parcelas que llevan mucho tiempo sin riego</li><li>Resumen mensual de consumo total</li></ul>'
                },
                fitosanitario: {
                    icon: '🧪',
                    title: 'Control Fitosanitario',
                    body: '<p>Cumple con la normativa manteniendo un registro completo de productos y aplicaciones.</p><ul><li>Inventario de productos fitosanitarios con control de stock</li><li>Registro de aplicaciones: parcela, producto, dosis y fecha</li><li>Historial por parcela para inspecciones y auditorías</li><li>Alertas de productos sin stock o próximos a caducar</li></ul>'
                },
                portales: {
                    icon: '👥',
                    title: 'Portal Propietarios y Trabajadores',
                    body: '<p>Cada usuario accede solo a lo que necesita, con una experiencia adaptada a su rol.</p><ul><li><strong>Propietario:</strong> consulta sus parcelas, tareas realizadas y campañas (sin precios)</li><li><strong>Trabajador:</strong> ve sus tareas asignadas, calendario personal y deuda mensual</li><li>Acceso seguro con credenciales individuales</li><li>Interfaz simplificada para cada perfil</li></ul>'
                },
                campanas: {
                    icon: '🗓️',
                    title: 'Gestión de Campañas',
                    body: '<p>Organiza cada temporada de aceituna como una unidad independiente con sus propios resultados.</p><ul><li>Campañas de noviembre a febrero con datos por parcela</li><li>Registro de kilos recogidos, rendimiento graso y precio</li><li>Comparativa entre campañas y evolución histórica</li><li>Balance económico por campaña</li></ul>'
                }
            };

            var overlay = document.getElementById('featureModal');
            var modalIcon = document.getElementById('featureModalIcon');
            var modalTitle = document.getElementById('featureModalTitle');
            var modalBody = document.getElementById('featureModalBody');
            var closeBtn = document.getElementById('featureModalClose');

            // Click en las cards para abrir modal
            document.querySelectorAll('.lp-feature-card[data-feature]').forEach(function (card) {
                card.addEventListener('click', function () {
                    var key = this.getAttribute('data-feature');
                    var data = featureData[key];
                    if (!data) return;
                    modalIcon.textContent = data.icon;
                    modalTitle.textContent = data.title;
                    modalBody.innerHTML = data.body;
                    overlay.classList.add('active');
                });
            });

            // Cerrar modal
            closeBtn.addEventListener('click', function () { overlay.classList.remove('active'); });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) overlay.classList.remove('active');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') overlay.classList.remove('active');
            });
        })();

        // === CARRUSEL DE ROLES ===
        (function () {
            var tabs = document.querySelectorAll('.lp-role-tab');
            var slides = document.querySelectorAll('.lp-carousel-slide');
            var dots = document.querySelectorAll('.lp-carousel-dot');
            var roles = ['empresa', 'trabajador', 'propietario'];
            var currentIndex = 0;
            var autoInterval = null;

            function switchTo(role) {
                currentIndex = roles.indexOf(role);
                tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-role') === role); });
                slides.forEach(function (s) { s.classList.toggle('active', s.getAttribute('data-role') === role); });
                dots.forEach(function (d) { d.classList.toggle('active', d.getAttribute('data-role') === role); });
            }

            function next() {
                currentIndex = (currentIndex + 1) % roles.length;
                switchTo(roles[currentIndex]);
            }

            // Click en tabs
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    switchTo(this.getAttribute('data-role'));
                    resetAuto();
                });
            });

            // Click en dots
            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    switchTo(this.getAttribute('data-role'));
                    resetAuto();
                });
            });

            // Auto-rotación cada 5 segundos
            function startAuto() { autoInterval = setInterval(next, 5000); }
            function resetAuto() { clearInterval(autoInterval); startAuto(); }
            startAuto();
        })();

    </script>
</body>
</html>
