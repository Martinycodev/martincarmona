<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politica de cookies — MiOlivar.es</title>
    <meta name="description" content="Politica de cookies de MiOlivar.es. Informacion sobre las cookies que utilizamos y como gestionarlas.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://miolivar.es/cookies">
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $this->url('/public/css/landing.css') ?>">
</head>
<body>

    <?php $activePage = ''; include __DIR__ . '/public/_nav.php'; ?>

    <main class="lp-page-content">
        <div class="lp-legal">
            <span class="lp-label">Legal</span>
            <h1>Politica de cookies</h1>
            <p class="lp-legal-updated">Ultima actualizacion: marzo 2026</p>

            <h2>1. Que son las cookies</h2>
            <p>Las cookies son pequenos archivos de texto que los sitios web almacenan en tu navegador. Se utilizan para recordar preferencias, mantener sesiones activas y mejorar la experiencia de navegacion.</p>

            <h2>2. Cookies que utilizamos</h2>
            <p>MiOlivar.es utiliza exclusivamente cookies tecnicas y de sesion, necesarias para el funcionamiento de la plataforma:</p>
            <ul>
                <li><strong>PHPSESSID:</strong> cookie de sesion de PHP. Mantiene tu sesion activa mientras navegas por la aplicacion. Se elimina al cerrar el navegador.</li>
                <li><strong>user_id:</strong> cookie opcional que se activa solo si marcas "Mantener sesion iniciada" al iniciar sesion. Caduca a los 30 dias.</li>
            </ul>

            <h2>3. Cookies de terceros</h2>
            <p>Este sitio web <strong>no utiliza cookies de terceros</strong>, cookies de seguimiento, cookies publicitarias ni cookies analiticas. No utilizamos Google Analytics ni herramientas similares de rastreo.</p>

            <h2>4. Finalidad</h2>
            <p>Las cookies utilizadas tienen una finalidad exclusivamente tecnica:</p>
            <ul>
                <li>Mantener la sesion del usuario autenticado.</li>
                <li>Proteger contra ataques CSRF (falsificacion de peticiones).</li>
                <li>Recordar la preferencia de sesion persistente (si el usuario lo solicita).</li>
            </ul>

            <h2>5. Como gestionar las cookies</h2>
            <p>Puedes configurar tu navegador para bloquear o eliminar cookies en cualquier momento. Ten en cuenta que si desactivas las cookies de sesion, no podras utilizar las funciones que requieren autenticacion.</p>
            <p>Instrucciones por navegador:</p>
            <ul>
                <li><strong>Chrome:</strong> Configuracion → Privacidad y seguridad → Cookies</li>
                <li><strong>Firefox:</strong> Opciones → Privacidad y seguridad → Cookies</li>
                <li><strong>Safari:</strong> Preferencias → Privacidad → Cookies</li>
                <li><strong>Edge:</strong> Configuracion → Cookies y permisos del sitio</li>
            </ul>

            <h2>6. Cambios en esta politica</h2>
            <p>Si en el futuro incorporamos nuevas cookies, actualizaremos esta pagina con la informacion correspondiente.</p>
        </div>
    </main>

    <?php include __DIR__ . '/public/_footer.php'; ?>

</body>
</html>
