<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto — MartinCarmona.com</title>
    <meta name="description" content="Contacta con MartinCarmona.com. Resolvemos tus dudas sobre nuestra app de gestion agricola para olivar en menos de 24 horas.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://martincarmona.com/contacto">
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $this->url('/public/css/landing.css') ?>">
</head>
<body>

    <?php $activePage = 'contacto'; include __DIR__ . '/public/_nav.php'; ?>

    <main class="lp-page-content">
        <section class="lp-section" style="padding-top: 60px;">
            <div class="lp-section-header" style="margin-bottom: 40px;">
                <span class="lp-label">Contacto</span>
                <h1 class="lp-title">Hablemos</h1>
                <p class="lp-sub">Tienes preguntas o quieres una demo? Escribenos y te respondemos en menos de 24h.</p>
            </div>

            <div class="lp-form-card">
                <form id="contactForm" method="POST" action="<?= $this->url('/contacto/enviar') ?>">
                    <?= \Core\CsrfMiddleware::getTokenField() ?>

                    <!-- Honeypot: campo invisible para humanos, los bots lo rellenan -->
                    <div style="position:absolute; left:-9999px; opacity:0; height:0; overflow:hidden;" aria-hidden="true">
                        <label for="website">No rellenar este campo</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <!-- Timestamp de carga para deteccion de bots -->
                    <input type="hidden" name="_t" id="contact_timestamp" value="">

                    <div class="form-group">
                        <label for="contact_name">Nombre</label>
                        <input type="text" id="contact_name" name="nombre" placeholder="Tu nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_email">Email</label>
                        <input type="email" id="contact_email" name="email" placeholder="tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_message">Mensaje</label>
                        <textarea id="contact_message" name="mensaje" placeholder="Cuentanos que necesitas..." required></textarea>
                    </div>
                    <button type="submit" class="btn" id="contactSubmitBtn">Enviar mensaje →</button>
                </form>

                <div class="lp-contact-success" id="contactSuccess">
                    Mensaje enviado correctamente. Te responderemos pronto.
                </div>
            </div>

            <!-- Info adicional de contacto -->
            <div style="text-align: center; margin-top: 48px; color: var(--text-muted); font-size: 0.9rem;">
                <p>Tambien puedes escribirnos a <a href="mailto:info@martincarmona.com" style="color: var(--green-light);">info@martincarmona.com</a></p>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/public/_footer.php'; ?>

    <script>
    /* Formulario de contacto con proteccion anti-spam */
    (function () {
        var form = document.getElementById('contactForm');
        var success = document.getElementById('contactSuccess');
        var tsField = document.getElementById('contact_timestamp');
        if (!form) return;

        /* Timestamp de carga para deteccion de bots */
        tsField.value = Math.floor(Date.now() / 1000);

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = document.getElementById('contactSubmitBtn');
            var nombre = document.getElementById('contact_name').value.trim();
            var email = document.getElementById('contact_email').value.trim();
            var mensaje = document.getElementById('contact_message').value.trim();

            if (!nombre || !email || !mensaje) return;

            btn.disabled = true;
            btn.textContent = 'Enviando...';

            var formData = new FormData(form);

            fetch(form.action, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    form.style.display = 'none';
                    success.classList.add('active');
                } else {
                    alert(data.error || 'Error al enviar. Intentalo de nuevo.');
                    btn.disabled = false;
                    btn.textContent = 'Enviar mensaje →';
                }
            })
            .catch(function () {
                alert('Error de conexion. Intentalo de nuevo.');
                btn.disabled = false;
                btn.textContent = 'Enviar mensaje →';
            });
        });
    })();
    </script>
</body>
</html>
