<?php
/**
 * Navegacion compartida para paginas publicas.
 *
 * Variable disponible:
 *   $activePage — string: 'home' | 'about' | 'contacto' | 'login' (resalta el link activo)
 */
$activePage = $activePage ?? 'home';
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<!-- NAV -->
<nav class="lp-nav" role="navigation" aria-label="Navegacion principal">
    <a href="<?= $base ?>/" class="lp-nav-logo">🌳 Martin<span>Carmona</span>.com</a>

    <div class="lp-nav-links">
        <a href="<?= $base ?>/"<?= $activePage === 'home' ? ' class="active"' : '' ?>>Inicio</a>
        <a href="<?= $base ?>/sobre-nosotros"<?= $activePage === 'about' ? ' class="active"' : '' ?>>Sobre nosotros</a>
        <a href="<?= $base ?>/contacto"<?= $activePage === 'contacto' ? ' class="active"' : '' ?>>Contacto</a>
    </div>

    <a href="<?= $base ?>/login" class="lp-nav-cta">Ingresar →</a>

    <button class="lp-nav-hamburger" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobileMenu">☰</button>
</nav>

<!-- Menu movil (overlay) -->
<div class="lp-nav-mobile" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Menu de navegacion">
    <button class="lp-nav-mobile-close" aria-label="Cerrar menu">&times;</button>
    <a href="<?= $base ?>/">Inicio</a>
    <a href="<?= $base ?>/sobre-nosotros">Sobre nosotros</a>
    <a href="<?= $base ?>/contacto">Contacto</a>
    <a href="<?= $base ?>/login" style="color: var(--green);">Ingresar →</a>
</div>

<script>
/* Menu hamburguesa movil */
(function () {
    var btn = document.querySelector('.lp-nav-hamburger');
    var menu = document.getElementById('mobileMenu');
    var closeBtn = menu ? menu.querySelector('.lp-nav-mobile-close') : null;
    if (!btn || !menu) return;

    function open()  { menu.classList.add('active'); btn.setAttribute('aria-expanded', 'true'); }
    function close() { menu.classList.remove('active'); btn.setAttribute('aria-expanded', 'false'); }

    btn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    menu.addEventListener('click', function (e) { if (e.target === menu) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
