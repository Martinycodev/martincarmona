<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politica de privacidad — MiOlivar.es</title>
    <meta name="description" content="Politica de privacidad de MiOlivar.es. Informacion sobre el tratamiento de datos personales conforme al RGPD.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://miolivar.es/privacidad">
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $this->url('/public/css/landing.css') ?>">
</head>
<body>

    <?php $activePage = ''; include __DIR__ . '/public/_nav.php'; ?>

    <main class="lp-page-content">
        <div class="lp-legal">
            <span class="lp-label">Legal</span>
            <h1>Politica de privacidad</h1>
            <p class="lp-legal-updated">Ultima actualizacion: marzo 2026</p>

            <h2>1. Responsable del tratamiento</h2>
            <p>El responsable del tratamiento de tus datos personales es el titular de MiOlivar.es, con domicilio en Arjonilla, Jaen (Espana). Puedes contactar con nosotros a traves del correo electronico: <a href="mailto:info@miolivar.es">info@miolivar.es</a>.</p>

            <h2>2. Datos que recopilamos</h2>
            <p>Recopilamos los siguientes datos personales:</p>
            <ul>
                <li><strong>Datos de registro:</strong> nombre, email y contrasena (cifrada) al crear una cuenta.</li>
                <li><strong>Datos de uso:</strong> informacion introducida en la plataforma (parcelas, trabajadores, tareas, movimientos economicos, etc.).</li>
                <li><strong>Datos de contacto:</strong> nombre, email y mensaje cuando usas el formulario de contacto.</li>
                <li><strong>Datos tecnicos:</strong> direccion IP, fecha de ultimo acceso, navegador y dispositivo (para seguridad y logs).</li>
            </ul>

            <h2>3. Finalidad del tratamiento</h2>
            <p>Tratamos tus datos con las siguientes finalidades:</p>
            <ul>
                <li>Prestacion del servicio de gestion agricola contratado.</li>
                <li>Autenticacion y seguridad de la cuenta (control de accesos, rate limiting).</li>
                <li>Responder a consultas enviadas mediante el formulario de contacto.</li>
                <li>Mejora del servicio y resolucion de incidencias tecnicas.</li>
            </ul>

            <h2>4. Base legal</h2>
            <p>El tratamiento de datos se basa en:</p>
            <ul>
                <li><strong>Ejecucion de contrato:</strong> para prestar el servicio de gestion agricola.</li>
                <li><strong>Interes legitimo:</strong> para garantizar la seguridad de la plataforma.</li>
                <li><strong>Consentimiento:</strong> para el envio de mensajes mediante el formulario de contacto.</li>
            </ul>

            <h2>5. Conservacion de datos</h2>
            <p>Los datos se conservan mientras la cuenta este activa. Al solicitar la baja, los datos se eliminan en un plazo maximo de 30 dias, salvo obligacion legal de conservarlos.</p>

            <h2>6. Destinatarios</h2>
            <p>No compartimos tus datos con terceros, salvo obligacion legal. Los datos se almacenan en servidores de hosting proporcionados por Hostinger, dentro de la Union Europea.</p>

            <h2>7. Tus derechos</h2>
            <p>Tienes derecho a:</p>
            <ul>
                <li>Acceder a tus datos personales.</li>
                <li>Rectificar datos inexactos.</li>
                <li>Solicitar la supresion de tus datos.</li>
                <li>Oponerte al tratamiento o solicitar su limitacion.</li>
                <li>Portabilidad de los datos.</li>
            </ul>
            <p>Para ejercer estos derechos, contacta con nosotros en <a href="mailto:info@miolivar.es">info@miolivar.es</a>.</p>

            <h2>8. Seguridad</h2>
            <p>Aplicamos medidas de seguridad tecnicas y organizativas para proteger tus datos: contrasenas cifradas con bcrypt, proteccion CSRF, rate limiting, logs de seguridad y conexiones cifradas (HTTPS).</p>

            <h2>9. Cambios en esta politica</h2>
            <p>Nos reservamos el derecho de actualizar esta politica. Cualquier cambio se publicara en esta pagina con la fecha de actualizacion.</p>
        </div>
    </main>

    <?php include __DIR__ . '/public/_footer.php'; ?>

</body>
</html>
