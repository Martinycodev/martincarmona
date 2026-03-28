<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Mi Olivar — MiOlivar.es</title>
    <meta name="description" content="Mi Olivar nace en Arjonilla (Jaén) de la mano de Martín Carmona, agricultor y desarrollador. Tradición y tecnología para gestionar tu olivar de forma integral.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://miolivar.es/sobre-nosotros">
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://miolivar.es/sobre-nosotros">
    <meta property="og:title" content="Sobre Mi Olivar — MiOlivar.es">
    <meta property="og:description" content="Mi Olivar nace en Arjonilla (Jaén). Tradición y tecnología para gestionar tu olivar de forma integral.">
    <meta property="og:image" content="https://miolivar.es/public/img/og-cover.jpg">
    <meta property="og:locale" content="es_ES">
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $this->url('/public/css/landing.css') ?>">
    <style>
        /* Estilos especificos de la pagina Sobre Nosotros */
        .about-hero {
            text-align: center;
            padding: 60px 24px 40px;
            max-width: 760px;
            margin: 0 auto;
        }
        .about-hero h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 16px;
        }
        .about-hero h1 em {
            font-style: normal;
            color: var(--green);
        }
        .about-hero p {
            font-size: 1.05rem;
            color: var(--text-muted);
            max-width: 540px;
            margin: 0 auto;
            line-height: 1.7;
        }
        .about-photo {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-hover);
            margin-top: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .about-content {
            max-width: 760px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }

        .about-section {
            margin-bottom: 56px;
        }
        .about-section h2 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--green-light);
            letter-spacing: -0.01em;
        }
        .about-section p {
            color: var(--text-muted);
            line-height: 1.75;
            margin-bottom: 14px;
            font-size: 0.95rem;
        }

        .about-values {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 24px;
        }
        @media (max-width: 768px) {
            .about-values { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .about-values { grid-template-columns: 1fr; }
        }
        .about-value-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px 24px;
            text-align: center;
        }
        .about-value-card .icon {
            font-size: 2rem;
            display: block;
            margin-bottom: 14px;
        }
        .about-value-card h3 {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .about-value-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .about-cta {
            text-align: center;
            padding: 48px 24px;
            border-top: 1px solid var(--border);
        }
        .about-cta p {
            color: var(--text-muted);
            margin-bottom: 20px;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <?php $activePage = 'about'; include __DIR__ . '/public/_nav.php'; ?>

    <main class="lp-page-content">

        <!-- Hero -->
        <div class="about-hero">
            <span class="lp-label">Sobre Mi Olivar</span>
            <h1>Tradición y tecnología <em>trabajando juntas</em></h1>
            <p>Mi Olivar nace en 2026 en Arjonilla (Jaén), en el corazón de la campiña norte, donde el olivar no es solo un cultivo, sino una forma de vida.</p>

            <!-- Foto de Martín -->
            <img src="<?= $this->url('/public/img/about.png') ?>"
                 alt="Martín Carmona — agricultor y desarrollador de Mi Olivar"
                 class="about-photo">
        </div>

        <div class="about-content">

            <!-- Origen -->
            <div class="about-section">
                <h2>El origen</h2>
                <p>Detrás del proyecto está Martín Carmona, agricultor y desarrollador, que ha unido tradición familiar y tecnología para dar respuesta a una necesidad real del campo.</p>
                <p>Este proyecto surge en un momento clave: el relevo generacional. Tras toda una vida dedicada al olivar, su padre se jubila y Martín hereda la explotación con un gran reto por delante. Con conocimientos en programación, pero menos experiencia en la gestión agrícola tradicional, decide transformar esa dificultad en una oportunidad.</p>
                <p>Así nace Mi Olivar: una plataforma digital pensada para gestionar el olivar de forma integral, desde la siembra hasta la venta del aceite de oliva virgen extra.</p>
            </div>

            <!-- El problema que resuelve -->
            <div class="about-section">
                <h2>El problema que resuelve</h2>
                <p>Durante años, la gestión del campo se ha llevado con libretas, papeles o procesos poco optimizados. Mi Olivar digitaliza y automatiza todo ese trabajo diario: jornales, riegos, tratamientos fitosanitarios, costes y productividad, permitiendo tener toda la información clara, accesible y actualizada en un solo lugar.</p>
                <p>Nuestro objetivo es claro: ayudar a los agricultores, propietarios y encargados de explotaciones a tomar mejores decisiones, ahorrar tiempo y entender de forma sencilla cómo está funcionando su olivar.</p>
            </div>

            <!-- Qué nos diferencia -->
            <div class="about-section">
                <h2>Qué nos diferencia</h2>
                <p>Mi Olivar no es solo tecnología. Es la combinación de conocimiento real del campo con herramientas digitales diseñadas específicamente para el sector del aceite de oliva. Esto permite ofrecer una experiencia sencilla, intuitiva y adaptada a quienes, muchas veces, son reacios al cambio tecnológico.</p>
                <p>Creemos en una agricultura más eficiente, más controlada y mejor preparada para el futuro. Por eso, la plataforma está en constante evolución, adaptándose a nuevas normativas como el cuaderno de campo digital o sistemas como Verifactu, y siempre centrada en mejorar la productividad del olivar.</p>
            </div>

            <!-- Esencia -->
            <div class="about-section" style="text-align: center; padding: 32px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
                <p style="font-size: 1.1rem; color: var(--text); font-weight: 500; font-style: italic; max-width: 600px; margin: 0 auto;">Mi Olivar es, en esencia, tradición y tecnología trabajando juntos para hacer el campo más rentable, más comprensible y más sostenible.</p>
            </div>

            <!-- Por qué Mi Olivar -->
            <div class="about-section" style="margin-top: 56px;">
                <h2>¿Por qué usar Mi Olivar?</h2>
                <p>Mi Olivar es una de las pocas soluciones creadas desde dentro del propio sector agrícola. No es solo software: es una herramienta desarrollada por alguien que entiende el día a día del olivar.</p>
                <div class="about-values">
                    <div class="about-value-card">
                        <span class="icon">🌱</span>
                        <h3>Facilidad de uso real</h3>
                        <p>Diseñada para que cualquier agricultor pueda usarla desde el primer día, sin formación previa.</p>
                    </div>
                    <div class="about-value-card">
                        <span class="icon">🛠️</span>
                        <h3>Enfoque práctico</h3>
                        <p>Cada funcionalidad responde a una necesidad real del campo, no a una idea de despacho.</p>
                    </div>
                    <div class="about-value-card">
                        <span class="icon">🔄</span>
                        <h3>Adaptación constante</h3>
                        <p>La plataforma evoluciona con las necesidades del campo y las normativas del sector.</p>
                    </div>
                    <div class="about-value-card">
                        <span class="icon"><?= emoji('olive') ?></span>
                        <h3>Especialistas en olivar</h3>
                        <p>Centrados en el sector del aceite de oliva, con conocimiento directo de la realidad del cultivo.</p>
                    </div>
                </div>
            </div>

            <!-- Ubicación -->
            <div class="about-section">
                <h2>¿Dónde estamos?</h2>
                <p>Operamos desde Arjonilla, Jaén — en el corazón de la mayor zona productora de aceite de oliva del mundo. Esta ubicación nos da una perspectiva única sobre las necesidades reales del sector olivarero.</p>
            </div>
        </div>

        <!-- CTA -->
        <div class="about-cta">
            <p>¿Quieres saber más o probar la plataforma?</p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= $this->url('/contacto') ?>" class="lp-btn-ghost">Contáctanos</a>
                <a href="<?= $this->url('/login') ?>" class="lp-btn-primary">Ingresar →</a>
            </div>
        </div>

    </main>

    <?php include __DIR__ . '/public/_footer.php'; ?>

</body>
</html>
