<?php 
$title = 'Mi Perfil - MartinCarmona.com';
?>
<div class="container">
    <div class="welcome-section">
        <h1>👤 Mi Perfil</h1>
    </div>

    <div class="profile-section">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <span class="avatar-icon">👤</span>
                </div>
                <h2>Información del Usuario</h2>
            </div>
            
            <div class="profile-info">
                <div class="info-item">
                    <label>ID de Usuario:</label>
                    <span><?= htmlspecialchars($user['id'] ?? 'N/A') ?></span>
                </div>
                
                <div class="info-item">
                    <label>Nombre:</label>
                    <span id="userNameDisplay"><?= htmlspecialchars($user['name'] ?? 'Sin nombre') ?></span>
                </div>
                
                <div class="info-item">
                    <label>Email:</label>
                    <span><?= htmlspecialchars($user['email'] ?? 'Sin email') ?></span>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <h2>Cambiar Nombre</h2>
            </div>
            
            <form id="updateNameForm" class="profile-form">
                <div class="form-group">
                    <label for="nuevoNombre">Nuevo Nombre:</label>
                    <input 
                        type="text" 
                        id="nuevoNombre" 
                        name="nombre" 
                        value="<?= htmlspecialchars($user['name'] ?? '') ?>" 
                        required
                        maxlength="255"
                        placeholder="Ingresa tu nuevo nombre"
                    >
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="updateNameBtn">
                        💾 Guardar Cambios
                    </button>
                </div>
                
                <div id="updateMessage" class="message" style="display: none;"></div>
            </form>
        </div>

        <!-- Sección de notificaciones -->
        <div class="profile-card" id="notificaciones" style="background:#2a2a2a;">
            <div class="profile-header" style="border-bottom-color:#404040;">
                <h2 style="color:#fff;">🔔 Notificaciones</h2>
            </div>

            <div id="notif-config" style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
                <!-- Se rellena por JS -->
            </div>

            <h3 style="color:#ccc; font-size:0.95rem; margin:20px 0 12px;">Crear recordatorio personalizado</h3>
            <form id="formRecordatorio" style="display:flex; flex-direction:column; gap:10px;">
                <input type="text" id="rec-titulo" placeholder="Título del recordatorio" required
                    style="padding:10px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff;">
                <input type="text" id="rec-desc" placeholder="Descripción (opcional)"
                    style="padding:10px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff;">
                <input type="date" id="rec-fecha" required
                    style="padding:10px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff;">
                <button type="submit" class="btn btn-primary" style="align-self:flex-start;">+ Crear recordatorio</button>
            </form>

            <h3 style="color:#ccc; font-size:0.95rem; margin:20px 0 12px;">Recordatorios activos</h3>
            <div id="rec-list">
                <div class="notif-empty">Cargando...</div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-section {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.profile-card {
    background: white;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.profile-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
}

.avatar-icon {
    font-size: 30px;
}

.profile-header h2 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item span {
    color: #333;
    font-size: 16px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.profile-form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.form-actions {
    margin-top: 25px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:active {
    transform: translateY(0);
}

.message {
    margin-top: 15px;
    padding: 12px;
    border-radius: 6px;
    font-size: 14px;
}

.message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

#updateNameBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updateNameForm');
    const messageDiv = document.getElementById('updateMessage');
    const updateBtn = document.getElementById('updateNameBtn');
    const nameDisplay = document.getElementById('userNameDisplay');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nuevoNombre = document.getElementById('nuevoNombre').value.trim();
        
        if (!nuevoNombre) {
            showMessage('El nombre no puede estar vacío', 'error');
            return;
        }
        
        // Deshabilitar el botón mientras se procesa
        updateBtn.disabled = true;
        updateBtn.textContent = '⏳ Guardando...';
        
        try {
            const response = await fetch('<?= $this->url('/perfil/actualizarNombre') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    nombre: nuevoNombre
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar el nombre en la pantalla
                nameDisplay.textContent = data.nombre;
                showMessage(data.message || 'Nombre actualizado correctamente', 'success');
                
                // Actualizar también en el header si existe
                const headerUserName = document.querySelector('.user-info span strong');
                if (headerUserName) {
                    headerUserName.textContent = data.nombre;
                }
            } else {
                showMessage(data.message || 'Error al actualizar el nombre', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error de conexión. Por favor, intenta de nuevo.', 'error');
        } finally {
            // Rehabilitar el botón
            updateBtn.disabled = false;
            updateBtn.textContent = '💾 Guardar Cambios';
        }
    });
    
    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';

        // Ocultar el mensaje después de 5 segundos
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }

    // ── Notificaciones ───────────────────────────────────────────────────
    var basePath = window._APP_BASE_PATH || '';
    var tiposLabels = {
        'itv':            { icon: '🚗', label: 'ITV de vehículos' },
        'cuentas':        { icon: '💰', label: 'Cierre de cuentas mensuales' },
        'fitosanitario':  { icon: '🧪', label: 'Fitosanitarios (stock bajo)' },
        'personalizado':  { icon: '📌', label: 'Recordatorios personalizados' }
    };

    // Cargar configuración de tipos
    async function cargarNotifConfig() {
        try {
            var res = await fetch(basePath + '/notificaciones/config', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json();
            if (!data.success) return;

            var container = document.getElementById('notif-config');
            container.innerHTML = '';

            Object.keys(tiposLabels).forEach(function(tipo) {
                var cfg = data.config[tipo] || { activo: 1 };
                var info = tiposLabels[tipo];
                var div = document.createElement('div');
                div.style.cssText = 'display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#333; border-radius:8px;';
                div.innerHTML = '<span style="color:#ddd;">' + info.icon + ' ' + info.label + '</span>'
                    + '<label class="toggle-switch">'
                    + '<input type="checkbox" ' + (cfg.activo == 1 ? 'checked' : '') + ' onchange="toggleNotifTipo(\'' + tipo + '\', this.checked)">'
                    + '<span class="toggle-slider"></span>'
                    + '</label>';
                container.appendChild(div);
            });
        } catch(e) {}
    }

    window.toggleNotifTipo = async function(tipo, activo) {
        await fetch(basePath + '/notificaciones/toggleConfig', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ tipo: tipo, activo: activo })
        });
        if (typeof showToast === 'function') showToast('Configuración guardada', 'success');
    };

    // Cargar recordatorios activos
    async function cargarRecordatoriosPerfil() {
        try {
            var res = await fetch(basePath + '/notificaciones/pendientes', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json();
            if (!data.success) return;

            var list = document.getElementById('rec-list');
            var items = data.recordatorios || [];

            if (items.length === 0) {
                list.innerHTML = '<div class="notif-empty" style="color:#888;text-align:center;padding:16px;">Sin recordatorios pendientes</div>';
                return;
            }

            var html = '';
            items.forEach(function(r) {
                var icono = tiposLabels[r.tipo]?.icon || '📌';
                var titulo = r.tipo === 'cuentas' ? r.descripcion : r.titulo;
                html += '<div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#333; border-radius:8px; margin-bottom:6px;">'
                    + '<span>' + icono + '</span>'
                    + '<div style="flex:1; color:#ddd; font-size:0.85rem;">' + titulo
                    + (r.fecha_referencia ? '<br><span style="color:#888;font-size:0.75rem;">' + r.fecha_referencia + '</span>' : '')
                    + '</div>'
                    + '<button onclick="eliminarRecordatorio(' + r.id + ', this)" style="background:none;border:none;color:#666;cursor:pointer;font-size:1rem;" title="Eliminar">🗑</button>'
                    + '</div>';
            });
            list.innerHTML = html;
        } catch(e) {}
    }

    window.eliminarRecordatorio = async function(id, btn) {
        await fetch(basePath + '/notificaciones/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ id: id })
        });
        btn.closest('div').remove();
        if (typeof showToast === 'function') showToast('Recordatorio eliminado', 'success');
    };

    // Crear recordatorio personalizado
    document.getElementById('formRecordatorio').addEventListener('submit', async function(e) {
        e.preventDefault();
        var titulo = document.getElementById('rec-titulo').value.trim();
        var desc = document.getElementById('rec-desc').value.trim();
        var fecha = document.getElementById('rec-fecha').value;

        if (!titulo || !fecha) return;

        var res = await fetch(basePath + '/notificaciones/crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ titulo: titulo, descripcion: desc, fecha_aviso: fecha })
        });
        var data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') showToast('Recordatorio creado', 'success');
            document.getElementById('rec-titulo').value = '';
            document.getElementById('rec-desc').value = '';
            document.getElementById('rec-fecha').value = '';
            cargarRecordatoriosPerfil();
        }
    });

    cargarNotifConfig();
    cargarRecordatoriosPerfil();
});
</script>

