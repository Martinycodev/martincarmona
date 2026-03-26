<?php
/**
 * Footer compartido para paginas publicas.
 * Incluye enlaces legales (privacidad, aviso legal, cookies).
 */
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<!-- FOOTER -->
<footer class="lp-footer">
    <div class="lp-footer-links">
        <a href="<?= $base ?>/privacidad">Politica de privacidad</a>
        <a href="<?= $base ?>/aviso-legal">Aviso legal</a>
        <a href="<?= $base ?>/cookies">Politica de cookies</a>
    </div>
    <p>&copy; <?= date('Y') ?> MiOlivar.es — Sistema de Gestión de Olivar</p>
</footer>
