<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Martín Carmona — Aprendiz de Fotógrafo, Videógrafo, Diseñador y Desarrollador. Jaén, España.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://martincarmona.com">

    <title><?= htmlspecialchars($title ?? 'Martín Carmona') ?></title>

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= htmlspecialchars($title ?? 'Martín Carmona') ?>">
    <meta property="og:description" content="Aprendiz de Fotógrafo, Videógrafo, Diseñador y Desarrollador.">
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
        "jobTitle": "Fotógrafo, Videógrafo, Diseñador y Desarrollador",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Jaén",
            "addressCountry": "ES"
        }
    }
    </script>

    <!-- Fuente: Switzer (Fontshare) — neo-grotesque tipo Helvética -->
    <link rel="preconnect" href="https://api.fontshare.com">
    <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700,800&display=swap">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #080a07;
            --text-primary: #e8ebe3;
            --text-secondary: #8a8f80;
            --text-dim: #3d4138;
            --green-bright: #9bc53d;
            --green-mid: #5a8a1e;
            --green-deep: #2d5a0e;
            --green-muted: #3a6b18;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Switzer', 'Helvetica Neue', Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Background orbs ── */
        .scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(130px);
            will-change: transform;
        }

        .orb--1 {
            width: clamp(350px, 55vw, 700px);
            height: clamp(350px, 55vw, 700px);
            background: radial-gradient(circle, var(--green-mid), transparent 70%);
            top: -18%;
            right: -12%;
            opacity: 0.13;
            animation: orbDrift1 28s ease-in-out infinite;
        }

        .orb--2 {
            width: clamp(300px, 45vw, 550px);
            height: clamp(300px, 45vw, 550px);
            background: radial-gradient(circle, var(--green-deep), transparent 70%);
            bottom: -22%;
            left: -8%;
            opacity: 0.10;
            animation: orbDrift2 32s ease-in-out infinite;
        }

        .orb--3 {
            width: clamp(200px, 30vw, 380px);
            height: clamp(200px, 30vw, 380px);
            background: radial-gradient(circle, var(--green-muted), transparent 65%);
            top: 45%;
            left: 55%;
            opacity: 0.07;
            animation: orbDrift3 24s ease-in-out infinite;
        }

        @keyframes orbDrift1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(-25px, 30px) scale(1.06); }
            66%      { transform: translate(20px, -20px) scale(0.96); }
        }

        @keyframes orbDrift2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(30px, -25px) scale(1.04); }
            66%      { transform: translate(-18px, 18px) scale(0.97); }
        }

        @keyframes orbDrift3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-20px, -15px) scale(1.08); }
        }

        /* ── Grain overlay ── */
        .grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: 0.035;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-repeat: repeat;
            background-size: 200px 200px;
        }

        /* ── Content ── */
        .page {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            height: 100dvh;
            padding: 2rem;
            text-align: center;
        }

        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Typography ── */
        .greeting {
            font-size: clamp(1.1rem, 2.5vw, 1.65rem);
            font-weight: 300;
            color: var(--text-secondary);
            letter-spacing: 0.02em;
            line-height: 1.3;
            opacity: 0;
            animation: fadeInUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        }

        .name {
            font-size: clamp(2.8rem, 8.5vw, 7rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.035em;
            color: var(--text-primary);
            margin-top: 0.1em;
            opacity: 0;
            animation: fadeInUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
        }

        .subtitle-wrap {
            margin-top: clamp(1.2rem, 3vw, 2rem);
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: center;
            gap: 0.6em;
            opacity: 0;
            animation: fadeInUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.8s forwards;
        }

        .subtitle {
            font-size: clamp(0.85rem, 1.8vw, 1.15rem);
            font-weight: 400;
            color: var(--text-secondary);
            letter-spacing: 0.01em;
            line-height: 1.5;
        }

        .ai-tag {
            font-size: clamp(0.6rem, 1vw, 0.72rem);
            font-weight: 500;
            color: var(--green-bright);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.25em 0.7em;
            border: 1px solid rgba(155, 197, 61, 0.25);
            border-radius: 100px;
            white-space: nowrap;
            position: relative;
            top: -0.08em;
        }

        /* ── Email ── */
        .contact {
            position: fixed;
            bottom: clamp(1.2rem, 3vh, 2rem);
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            opacity: 0;
            animation: fadeInUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 1.4s forwards;
        }

        .contact__email {
            font-size: 0.68rem;
            font-weight: 400;
            color: var(--text-dim);
            text-decoration: none;
            letter-spacing: 0.06em;
            transition: color 0.4s ease;
        }

        .contact__email:hover {
            color: var(--text-secondary);
        }

        /* ── Animations ── */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Responsive fine-tuning ── */
        @media (max-width: 480px) {
            .subtitle-wrap {
                flex-direction: column;
                align-items: center;
                gap: 0.8em;
            }

            .ai-tag {
                top: 0;
            }
        }

        @media (min-width: 1600px) {
            .name {
                font-size: 7.5rem;
            }
        }
    </style>
</head>
<body>
    <?= $content ?>
</body>
</html>
