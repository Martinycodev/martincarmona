    </main>

    <footer class="site-footer">
        <div class="footer-content">
            &copy; <?= date('Y') ?> MartinCarmona.com <a href="https://auth-db699.hstgr.io/" target="_blank">bbdd</a>
        </div>
    </footer>

    <!-- ===== LIGHTBOX DE IMÁGENES ===== -->
    <div id="img-lightbox" onclick="closeLightbox()">
        <button id="img-lightbox-close" onclick="closeLightbox()" title="Cerrar" aria-label="Cerrar visor de imagen">✕</button>
        <img id="img-lightbox-img" src="" alt="" onclick="event.stopPropagation()">
        <button id="img-lightbox-delete" class="btn btn-danger btn-sm" onclick="event.stopPropagation()">
            🗑 Eliminar imagen
        </button>
    </div>

    <!-- ===== SIDEBAR DE TAREAS ===== -->
    <div id="task-sidebar-overlay" onclick="window.taskSidebar && window.taskSidebar.close()"></div>

    <div id="task-sidebar" role="dialog" aria-modal="true" aria-label="Editar tarea">
        <!-- Header con título editable y estado de guardado -->
        <div class="sidebar-header">
            <div class="sidebar-title-wrap">
                <div id="sidebar-title"
                     contenteditable="true"
                     data-field="titulo"
                     data-placeholder="Título de la tarea..."></div>
            </div>
            <div class="sidebar-header-right">
                <span id="sidebar-save-status" class="sidebar-save-status"></span>
                <button class="sidebar-close"
                        onclick="window.taskSidebar && window.taskSidebar.close()"
                        aria-label="Cerrar sidebar">✕</button>
            </div>
        </div>

        <!-- Cuerpo con scroll -->
        <div id="sidebar-body"></div>

        <!-- Footer con acciones secundarias -->
        <div class="sidebar-footer">
            <button id="sidebar-delete-btn" class="btn btn-danger btn-sm">
                🗑 Eliminar tarea
            </button>
        </div>
    </div>

    <script src="<?= $this->url('/public/js/task-sidebar.js') ?>?v=<?= filemtime(BASE_PATH . '/public/js/task-sidebar.js') ?>"></script>

    <!-- Registro del Service Worker para PWA -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= $this->url('/public/sw.js') ?>', { scope: '<?= APP_BASE_PATH ?>/' })
            .catch(function() { /* SW no soportado o error silencioso */ });
    }
    </script>

</body>
</html>

