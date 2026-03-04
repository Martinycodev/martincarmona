<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MartinCarmona.com — Gestión de Olivar</title>
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:           #0c160c;
            --surface:      #131f13;
            --surface-2:    #1a2a1a;
            --border:       rgba(76, 175, 80, 0.14);
            --border-hover: rgba(76, 175, 80, 0.38);
            --green:        #4CAF50;
            --green-light:  #81c784;
            --green-dim:    rgba(76, 175, 80, 0.10);
            --text:         #e8f5e9;
            --text-muted:   #7a977a;
            --text-dim:     #3d5c3d;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        /* ===== NAV ===== */
        .lp-nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            padding: 18px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(12, 22, 12, 0.82);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
        }

        .lp-nav-logo {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            letter-spacing: 0.02em;
            text-decoration: none;
        }
        .lp-nav-logo span { color: var(--green); }

        .lp-nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 22px;
            background: var(--green-dim);
            color: var(--green);
            border: 1px solid rgba(76,175,80,0.4);
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        .lp-nav-cta:hover {
            background: var(--green);
            color: #0c160c;
        }

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

        .lp-btn-primary {
            padding: 14px 34px;
            background: var(--green);
            color: #0c160c;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .lp-btn-primary:hover {
            background: var(--green-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(76, 175, 80, 0.28);
        }

        .lp-btn-ghost {
            padding: 14px 34px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .lp-btn-ghost:hover {
            color: var(--text);
            border-color: var(--text-muted);
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
        .lp-section {
            padding: 100px 24px;
            max-width: 1080px;
            margin: 0 auto;
        }

        .lp-section-header {
            text-align: center;
            margin-bottom: 64px;
        }
        .lp-label {
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--green);
            text-transform: uppercase;
            letter-spacing: 0.14em;
            display: block;
            margin-bottom: 14px;
        }
        .lp-title {
            font-size: clamp(1.9rem, 3.5vw, 2.9rem);
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .lp-sub {
            font-size: 1rem;
            color: var(--text-muted);
            margin-top: 16px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

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
        .lp-feature-title {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .lp-feature-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* ===== HOW IT WORKS ===== */
        .lp-how-bg {
            background: var(--surface);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
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

        /* ===== LOGIN ===== */
        .lp-login-wrap {
            padding: 100px 24px 80px;
            max-width: 460px;
            margin: 0 auto;
        }
        .lp-login-wrap .lp-section-header {
            margin-bottom: 40px;
        }

        .login-form {
            background: var(--surface);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 50px rgba(0, 0, 0, 0.45);
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-muted);
        }
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 13px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            background: var(--bg);
            color: var(--text);
            transition: all 0.2s;
        }
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.12);
            background: var(--surface-2);
        }

        .form-group-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        .form-group-check input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--green);
            cursor: pointer;
            flex-shrink: 0;
        }
        .form-group-check label {
            font-size: 0.88rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--green);
            color: #0c160c;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .btn:hover {
            background: var(--green-light);
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(76, 175, 80, 0.28);
        }

        .error-message {
            background: rgba(107, 27, 27, 0.35);
            color: #ffcdd2;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(141, 47, 47, 0.45);
            font-size: 0.88rem;
        }

        /* ===== FOOTER ===== */
        .lp-footer {
            padding: 28px 24px;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--text-dim);
            font-size: 0.82rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 680px) {
            .lp-nav { padding: 16px 20px; }
            .lp-stat { padding: 28px 24px; border-right: none; border-bottom: 1px solid var(--border); }
            .lp-stat:last-child { border-bottom: none; }
            .lp-stats { flex-direction: column; align-items: center; }
            .lp-steps-grid { grid-template-columns: 1fr; }
            .lp-steps-grid::before { display: none; }
            .login-form { padding: 28px 20px; }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav class="lp-nav">
        <a href="#" class="lp-nav-logo">🌳 Martin<span>Carmona</span>.com</a>
        <a href="#acceso" class="lp-nav-cta">Acceder →</a>
    </nav>


    <!-- HERO -->
    <section class="lp-hero">
        <div class="lp-badge">Gestión de olivar · Plataforma completa</div>
        <h1>Tu finca,<br><em>gestionada con precisión</em></h1>
        <p class="lp-hero-desc">
            Parcelas, personal, tareas y economía centralizados en una sola herramienta.
            Sin hojas de cálculo, sin papeles.
        </p>
        <div class="lp-hero-actions">
            <a href="#acceso" class="lp-btn-primary">Iniciar sesión</a>
            <a href="#funciones" class="lp-btn-ghost">Ver funciones ↓</a>
        </div>
    </section>


    <!-- STATS BAR -->
    <div class="lp-stats">
        <div class="lp-stat">
            <span class="lp-stat-value">4</span>
            <span class="lp-stat-label">Módulos integrados</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-value">360°</span>
            <span class="lp-stat-label">Visión del negocio</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-value">100%</span>
            <span class="lp-stat-label">Datos en tiempo real</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-value">∞</span>
            <span class="lp-stat-label">Temporadas sin límite</span>
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
            <div class="lp-feature-card">
                <span class="lp-feature-icon">🗺️</span>
                <div class="lp-feature-title">Gestión de Parcelas</div>
                <p class="lp-feature-desc">Organiza tus fincas con ficha detallada por parcela, propietario y datos de superficie.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">👷</span>
                <div class="lp-feature-title">Control de Personal</div>
                <p class="lp-feature-desc">Registra trabajadores, asigna roles y lleva el seguimiento de jornadas y contratos.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">📋</span>
                <div class="lp-feature-title">Planificación de Tareas</div>
                <p class="lp-feature-desc">Crea, asigna y monitoriza cada faena: poda, riego, recolección y tratamientos.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">💶</span>
                <div class="lp-feature-title">Control Económico</div>
                <p class="lp-feature-desc">Registra gastos e ingresos por temporada y genera reportes para tomar mejores decisiones.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">💧</span>
                <div class="lp-feature-title">Control de Riego</div>
                <p class="lp-feature-desc">Programa y registra los riegos por parcela, controlando consumos y fechas de cada intervención.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">🧪</span>
                <div class="lp-feature-title">Control Fitosanitario</div>
                <p class="lp-feature-desc">Registra tratamientos, productos y dosis aplicados. Mantén el historial fitosanitario de cada parcela.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">👥</span>
                <div class="lp-feature-title">Portal Propietarios y Trabajadores</div>
                <p class="lp-feature-desc">Acceso diferenciado por rol: los propietarios consultan sus parcelas y los trabajadores sus tareas asignadas.</p>
            </div>
            <div class="lp-feature-card">
                <span class="lp-feature-icon">🗓️</span>
                <div class="lp-feature-title">Gestión de Campañas</div>
                <p class="lp-feature-desc">Organiza cada temporada como una campaña independiente, con su propio equipo, tareas y balance económico.</p>
            </div>
        </div>
    </section>


    <!-- HOW IT WORKS -->
    <div class="lp-how-bg">
        <section class="lp-section">
            <div class="lp-section-header">
                <span class="lp-label">Cómo funciona</span>
                <h2 class="lp-title">Simple desde el primer día</h2>
            </div>

            <div class="lp-steps-grid">
                <div class="lp-step">
                    <div class="lp-step-num">1</div>
                    <div class="lp-step-title">Accede</div>
                    <p class="lp-step-desc">Inicia sesión con tus credenciales y accede al panel adaptado a tu rol en la empresa.</p>
                </div>
                <div class="lp-step">
                    <div class="lp-step-num">2</div>
                    <div class="lp-step-title">Organiza</div>
                    <p class="lp-step-desc">Registra parcelas, equipo de trabajo y planifica las tareas de cada temporada.</p>
                </div>
                <div class="lp-step">
                    <div class="lp-step-num">3</div>
                    <div class="lp-step-title">Controla</div>
                    <p class="lp-step-desc">Consulta el estado de la finca en tiempo real y exporta reportes económicos al instante.</p>
                </div>
            </div>
        </section>
    </div>


    <!-- LOGIN -->
    <section class="lp-login-wrap" id="acceso">
        <div class="lp-section-header">
            <span class="lp-label">Acceso privado</span>
            <h2 class="lp-title">Bienvenido</h2>
            <p class="lp-sub" style="font-size:0.95rem; margin-top:14px;">Introduce tus credenciales para acceder a tu panel de gestión.</p>
        </div>

        <div class="login-form">

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php if ($error === 'missing_fields'): ?>
                        ❌ Por favor, completa todos los campos
                    <?php elseif ($error === 'invalid_credentials'): ?>
                        ❌ Email o contraseña incorrectos
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $this->url('/login') ?>">
                <?= \Core\CsrfMiddleware::getTokenField() ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-group-check">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Mantener sesión iniciada</label>
                </div>

                <button type="submit" class="btn">Iniciar sesión →</button>
            </form>

        </div>
    </section>


    <!-- FOOTER -->
    <footer class="lp-footer">
        &copy; <?= date('Y') ?> MartinCarmona.com — Sistema de Gestión de Olivar
    </footer>

</body>
</html>
