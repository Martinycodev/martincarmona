<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MartinCarmona.com' ?></title>
    <!-- Favicons -->
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= $this->url('/public/img/icons/icon-152x152.png') ?>">
    <!-- Fallback para navegadores que no soportan SVG -->
    <link rel="alternate icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A" type="image/x-icon">
    <!-- Ruta base de la aplicación para JS -->
    <script>window._APP_BASE_PATH = '<?= APP_BASE_PATH ?>';</script>
    <!-- Token CSRF para peticiones AJAX -->
    <?= \Core\CsrfMiddleware::getMetaTag() ?>
    <!-- PWA: manifest + theme-color -->
    <link rel="manifest" href="<?= $this->url('/public/manifest.php') ?>">
    <meta name="theme-color" content="#4caf50">
    <!-- iOS PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MartinCarmona">
    <link rel="apple-touch-icon" href="<?= $this->url('/public/img/icons/icon-152x152.png') ?>">
    <!-- Estilos -->
    <link rel="stylesheet" href="<?= $this->url('/public/css/styles.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/public/css/autocomplete.css') ?>">
    
</head>
<body>
    <!-- Skip-nav: visible solo con focus (Tab) para usuarios de teclado -->
    <a href="#main-content" class="skip-nav">Saltar al contenido</a>

    <div class="header">
        <div class="header-content">
            <h1><a href="<?= $this->url('/') ?>" style="text-decoration: none; color:white"> 🌳</a></h1>
            <div class="user-info">
                <span><strong><?= isset($user) && isset($user['name']) ? htmlspecialchars($user['name']) : 'Invitado' ?></strong></span>
                <span id="offline-queue-badge" class="offline-queue-badge" style="display:none" title="Formularios pendientes de enviar">0</span>
                <!-- Campanita de notificaciones -->
                <button id="notif-bell" class="notif-bell" onclick="toggleNotifPanel()" title="Notificaciones">
                    🔔 <span id="notif-badge" class="notif-badge" style="display:none">0</span>
                </button>
                <div id="notif-panel" class="notif-panel">
                    <div class="notif-panel-header">
                        <strong>Recordatorios</strong>
                        <a href="<?= $this->url('/perfil') ?>#notificaciones" style="font-size:0.8rem;color:#4caf50;">Configurar</a>
                    </div>
                    <div id="notif-panel-body" class="notif-panel-body">
                        <div class="notif-empty">Cargando...</div>
                    </div>
                </div>
                <button class="hamburger-menu" onclick="toggleMenu()" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="navMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú de navegación -->
    <?php $rolActual = $_SESSION['user_rol'] ?? 'empresa'; ?>
    <nav class="nav-menu" id="navMenu" role="navigation" aria-label="Menú principal">
        <br>
        <?php if ($rolActual === 'empresa'): ?>
        <a href="<?= $this->url('/datos') ?>">📚 Bases de datos</a>
        <a href="<?= $this->url('/tareas/pendientes') ?>">📋 Tareas Pendientes</a>
        <a href="<?= $this->url('/economia') ?>">💶 Economía</a>
        <a href="<?= $this->url('/reportes') ?>">📊 Reportes</a>
        <a href="<?= $this->url('/enlaces') ?>">🔗 Enlaces de interés</a>
        <a href="<?= $this->url('/perfil') ?>">👤 Mi Perfil</a>
        <?php elseif ($rolActual === 'admin'): ?>
        <a href="<?= $this->url('/admin/usuarios') ?>">👥 Gestión de Usuarios</a>
        <?php elseif ($rolActual === 'propietario'): ?>
        <a href="<?= $this->url('/propietario') ?>">🌳 Mis Parcelas</a>
        <?php elseif ($rolActual === 'trabajador'): ?>
        <a href="<?= $this->url('/trabajador') ?>">👷 Mi Cuenta</a>
        <?php endif; ?>
        <a href="<?= $this->url('/logout') ?>" style="color: #ff4444;">🚪 Cerrar Sesión</a>
    </nav>

    <!-- Overlay para cerrar el menú -->
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <script>
        function toggleMenu() {
            const navMenu = document.getElementById('navMenu');
            const overlay = document.getElementById('overlay');
            const hamburger = document.querySelector('.hamburger-menu');
            const spans = hamburger.querySelectorAll('span');

            navMenu.classList.toggle('active');
            var isOpen = navMenu.classList.contains('active');

            // Actualizar ARIA
            hamburger.setAttribute('aria-expanded', isOpen);
            hamburger.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');

            if (isOpen) {
                overlay.style.display = 'block';
                spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
            } else {
                overlay.style.display = 'none';
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        }

        // Cerrar menú al pulsar cualquier enlace
        document.getElementById('navMenu').querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                const navMenu = document.getElementById('navMenu');
                if (navMenu.classList.contains('active')) toggleMenu();
            });
        });
    </script>

    <!-- Scripts principales -->
    <script src="<?= $this->url('/public/js/offline-queue.js') ?>"></script>
    <script src="<?= $this->url('/public/js/modal-functions.js') ?>"></script>
    <script src="<?= $this->url('/public/js/ajax-navigation.js') ?>"></script>

    <main id="main-content">

