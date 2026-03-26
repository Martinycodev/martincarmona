<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion — MiOlivar.es</title>
    <meta name="description" content="Accede a tu panel de gestion agricola. Inicia sesion con tus credenciales.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="https://miolivar.es/login">
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $this->url('/public/css/landing.css') ?>">
</head>
<body>

    <?php $activePage = 'login'; include __DIR__ . '/public/_nav.php'; ?>

    <main class="lp-page-content">
        <section class="lp-section" style="padding-top: 60px;">
            <div class="lp-section-header" style="margin-bottom: 40px;">
                <span class="lp-label">Acceso privado</span>
                <h1 class="lp-title">Iniciar sesion</h1>
                <p class="lp-sub">Introduce tus credenciales para acceder a tu panel de gestion.</p>
            </div>

            <div class="lp-form-card">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <?php if ($error === 'missing_fields'): ?>
                            Por favor, completa todos los campos
                        <?php elseif ($error === 'invalid_credentials'): ?>
                            Email o contrasena incorrectos
                        <?php elseif ($error === 'too_many_attempts'): ?>
                            Demasiados intentos. Espera 15 minutos antes de volver a intentarlo
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
                        <label for="password">Contrasena</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="form-group-check">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Mantener sesion iniciada</label>
                    </div>

                    <button type="submit" class="btn">Iniciar sesion →</button>
                </form>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/public/_footer.php'; ?>

</body>
</html>
