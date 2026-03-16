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

    <!-- Sistema de notificaciones (campanita) -->
    <script>
    var _notifBasePath = '<?= $this->url("") ?>';

    function toggleNotifPanel() {
        var panel = document.getElementById('notif-panel');
        if (!panel) return;
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) cargarNotificaciones();
    }

    // Cerrar panel al hacer click fuera
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('notif-panel');
        var bell = document.getElementById('notif-bell');
        if (panel && panel.classList.contains('open') && !panel.contains(e.target) && e.target !== bell && !bell.contains(e.target)) {
            panel.classList.remove('open');
        }
    });

    async function cargarNotificaciones() {
        try {
            var res = await fetch(_notifBasePath + '/notificaciones/pendientes', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json();
            if (!data.success) return;

            // Badge
            var badge = document.getElementById('notif-badge');
            if (badge) {
                if (data.total > 0) {
                    badge.textContent = data.total;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            // Panel body
            var body = document.getElementById('notif-panel-body');
            if (!body) return;

            var items = data.recordatorios || [];
            if (items.length === 0) {
                body.innerHTML = '<div class="notif-empty">Sin recordatorios pendientes</div>';
                return;
            }

            var html = '';
            items.forEach(function(r) {
                var icono = r.tipo === 'itv' ? '🚗' : r.tipo === 'cuentas' ? '💰' : r.tipo === 'fitosanitario' ? '🧪' : '📌';
                var titulo = r.tipo === 'cuentas'
                    ? r.descripcion
                    : r.titulo;
                html += '<div class="notif-item" data-id="' + r.id + '">'
                    + '<span class="notif-icon">' + icono + '</span>'
                    + '<div class="notif-content">'
                    + '<div class="notif-title">' + titulo + '</div>'
                    + (r.fecha_referencia ? '<div class="notif-date">' + r.fecha_referencia + '</div>' : '')
                    + '</div>'
                    + '<button class="notif-dismiss" onclick="dismissNotif(' + r.id + ', this)" title="Marcar como leído">✕</button>'
                    + '</div>';
            });
            body.innerHTML = html;
        } catch (e) { /* silencioso */ }
    }

    async function dismissNotif(id, btn) {
        try {
            await fetch(_notifBasePath + '/notificaciones/leido', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ id: id })
            });
            var item = btn.closest('.notif-item');
            if (item) item.remove();
            // Recargar badge
            cargarNotificaciones();
        } catch (e) { /* silencioso */ }
    }

    // Cargar badge al inicio
    (function() {
        function initNotif() { cargarNotificaciones(); }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNotif);
        } else {
            initNotif();
        }
    })();
    </script>

    <!-- Registro del Service Worker para PWA -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= $this->url('/public/sw.js') ?>', { scope: '<?= APP_BASE_PATH ?>/' })
            .catch(function() { /* SW no soportado o error silencioso */ });
    }
    </script>

</body>
</html>

