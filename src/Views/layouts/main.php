<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Martín Carmona — Desarrollador web, diseñador gráfico y creador visual. Jaén, España.">
    <meta name="robots" content="index, follow">

    <title><?= htmlspecialchars($title ?? 'Martín Carmona') ?></title>

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= htmlspecialchars($title ?? 'Martín Carmona') ?>">
    <meta property="og:description" content="Desarrollador web, diseñador gráfico y creador visual.">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="https://martincarmona.com">

    <!-- Fuentes: Clash Display + Satoshi (Fontshare) -->
    <link rel="preconnect" href="https://api.fontshare.com">
    <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=satoshi@400,500,700&display=swap">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <!-- Vite assets -->
    <?= \App\Core\ViteHelper::tags('resources/css/app.css', 'resources/js/app.js') ?>
</head>
<body class="bg-dark-900 text-light-50 font-body antialiased overflow-x-hidden">

    <!-- Cursor personalizado (sólo desktop) -->
    <div id="cursor"       class="hidden lg:block fixed w-3 h-3 bg-accent rounded-full pointer-events-none z-[9999] -translate-x-1/2 -translate-y-1/2 transition-transform duration-150 mix-blend-difference" aria-hidden="true"></div>
    <div id="cursor-trail" class="hidden lg:block fixed w-8 h-8 border border-accent/40 rounded-full pointer-events-none z-[9998] -translate-x-1/2 -translate-y-1/2 transition-all duration-300" aria-hidden="true"></div>

    <!-- Navegación -->
    <header id="site-header" class="fixed top-0 left-0 right-0 z-50 px-6 lg:px-12 transition-all duration-600"
            x-data="{ scrolled: false, menuOpen: false }"
            @scroll.window="scrolled = window.scrollY > 50">

        <nav class="flex items-center justify-between py-6"
             :class="scrolled ? 'py-4' : 'py-6'">

            <!-- Logo / Nombre -->
            <a href="/" class="font-display font-bold text-xl tracking-tight text-light-50 hover:text-accent transition-colors duration-300">
                MC
            </a>

            <!-- Nav desktop -->
            <ul class="hidden md:flex items-center gap-10 text-sm font-medium text-light-400 tracking-wide">
                <li><a href="/#sobre-mi"  class="nav-link hover:text-light-50 transition-colors duration-300">Sobre mí</a></li>
                <li><a href="/#servicios" class="nav-link hover:text-light-50 transition-colors duration-300">Servicios</a></li>
                <li><a href="/#portfolio"  class="nav-link hover:text-light-50 transition-colors duration-300">Portfolio</a></li>
                <li>
                    <a href="/contacto"
                       class="px-5 py-2.5 border border-accent/50 text-accent rounded hover:bg-accent hover:text-dark-900 transition-all duration-300 font-semibold">
                        Contacto
                    </a>
                </li>
            </ul>

            <!-- Hamburger mobile -->
            <button class="md:hidden flex flex-col gap-1.5 p-2" @click="menuOpen = !menuOpen" aria-label="Abrir menú">
                <span class="block w-6 h-px bg-light-50 transition-all duration-300" :class="menuOpen ? 'rotate-45 translate-y-2' : ''"></span>
                <span class="block w-6 h-px bg-light-50 transition-all duration-300" :class="menuOpen ? 'opacity-0' : ''"></span>
                <span class="block w-6 h-px bg-light-50 transition-all duration-300" :class="menuOpen ? '-rotate-45 -translate-y-2' : ''"></span>
            </button>
        </nav>

        <!-- Menú mobile -->
        <div x-show="menuOpen"
             x-transition:enter="transition duration-300 ease-expo-out"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition duration-200"
             x-transition:leave-end="opacity-0"
             class="md:hidden absolute inset-x-0 top-full bg-dark-800/95 backdrop-blur-md border-t border-dark-700 px-6 py-8"
             @click.away="menuOpen = false">
            <ul class="flex flex-col gap-6 text-lg font-medium">
                <li><a href="/#sobre-mi"  @click="menuOpen = false" class="block text-light-200 hover:text-accent transition-colors">Sobre mí</a></li>
                <li><a href="/#servicios" @click="menuOpen = false" class="block text-light-200 hover:text-accent transition-colors">Servicios</a></li>
                <li><a href="/#portfolio"  @click="menuOpen = false" class="block text-light-200 hover:text-accent transition-colors">Portfolio</a></li>
                <li><a href="/contacto"   @click="menuOpen = false" class="block text-accent font-semibold">Contacto</a></li>
            </ul>
        </div>
    </header>

    <!-- Contenido principal -->
    <main id="main-content">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="border-t border-dark-700 px-6 lg:px-12 py-12">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <p class="font-display font-bold text-lg text-light-50">Martín Carmona</p>

            <div class="flex items-center gap-6 text-light-400 text-sm">
                <a href="https://github.com/martincarmona" target="_blank" rel="noopener noreferrer"
                   class="hover:text-light-50 transition-colors duration-300">GitHub</a>
                <a href="https://linkedin.com/in/martincarmona" target="_blank" rel="noopener noreferrer"
                   class="hover:text-light-50 transition-colors duration-300">LinkedIn</a>
                <a href="mailto:hola@martincarmona.com"
                   class="hover:text-accent transition-colors duration-300">hola@martincarmona.com</a>
            </div>

            <p class="text-light-600 text-xs">
                &copy; <?= date('Y') ?> Martín Carmona. Hecho con cuidado.
            </p>
        </div>
    </footer>

</body>
</html>
