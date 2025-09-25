<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MartinCarmona.com' ?></title>
    <!-- Favicons -->
    <link rel="icon" href="<?= $this->url('/public/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= $this->url('/public/img/favicon.svg') ?>">
    <!-- Fallback para navegadores que no soportan SVG -->
    <link rel="alternate icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A" type="image/x-icon">
    <!-- Estilos -->
    <link rel="stylesheet" href="<?= $this->url('/public/css/styles.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/public/css/autocomplete.css') ?>">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><a href="<?= $this->url('/') ?>" style="text-decoration: none; color:white"> 🌳</a></h1>
            <div class="user-info">
                <span><strong><?= isset($user) && isset($user['name']) ? htmlspecialchars($user['name']) : 'Invitado' ?></strong></span>
                <div class="hamburger-menu" onclick="toggleMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú de navegación -->
    <nav class="nav-menu" id="navMenu">
        <br>
        <a href="<?= $this->url('/datos') ?>">📚 Bases de datos</a>
        <a href="<?= $this->url('/economia') ?>">💶 Economía</a>
        <a href="<?= $this->url('/reportes') ?>">📊 Reportes</a>
        <a href="<?= $this->url('/perfil') ?>">👤 Mi Perfil</a>
        <a href="<?= $this->url('/logout') ?>" style="color: #ff4444;">🚪 Cerrar Sesión</a>
    </nav>

    <!-- Overlay para cerrar el menú -->
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <script>
        function toggleMenu() {
            const navMenu = document.getElementById('navMenu');
            const overlay = document.getElementById('overlay');
            const spans = document.querySelectorAll('.hamburger-menu span');
            
            navMenu.classList.toggle('active');
            
            if (navMenu.classList.contains('active')) {
                overlay.style.display = 'block';
                // Animación del icono hamburguesa a X
                spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
            } else {
                overlay.style.display = 'none';
                // Restaurar icono hamburguesa
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        }
    </script>

