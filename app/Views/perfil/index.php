<?php 
$title = 'Mi Perfil - MartinCarmona.com';
?>
<div class="container">
    <h2 style="text-align:center; margin:1.5rem 0;">👤 Mi Perfil</h2>

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

        <!-- Sección de cambiar contraseña -->
        <div class="profile-card">
            <div class="profile-header">
                <h2>🔒 Cambiar Contraseña</h2>
            </div>

            <form id="changePasswordForm" class="profile-form">
                <div class="form-group">
                    <label for="passwordActual">Contraseña actual:</label>
                    <input
                        type="password"
                        id="passwordActual"
                        required
                        placeholder="Introduce tu contraseña actual"
                    >
                </div>
                <div class="form-group">
                    <label for="passwordNueva">Nueva contraseña:</label>
                    <input
                        type="password"
                        id="passwordNueva"
                        required
                        minlength="6"
                        placeholder="Mínimo 6 caracteres"
                    >
                </div>
                <div class="form-group">
                    <label for="passwordConfirmar">Confirmar nueva contraseña:</label>
                    <input
                        type="password"
                        id="passwordConfirmar"
                        required
                        minlength="6"
                        placeholder="Repite la nueva contraseña"
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                        🔒 Cambiar Contraseña
                    </button>
                </div>

                <div id="passwordMessage" class="message" style="display: none;"></div>
            </form>
        </div>

        <!-- Sección de notificaciones -->
        <div class="profile-card" id="notificaciones">
            <div class="profile-header">
                <h2>🔔 Notificaciones</h2>
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
    background: #2a2a2a;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 20px;
    border: 1px solid #3a3a3a;
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #3a3a3a;
}

.profile-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
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
    color: #fff;
    font-size: 22px;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-item label {
    font-weight: 600;
    color: #a8d5ab;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item span {
    color: #ddd;
    font-size: 16px;
    padding: 10px;
    background: #333;
    border-radius: 8px;
    border: 1px solid #404040;
}

.profile-form {
    margin-top: 20px;
}

.profile-form .form-group {
    margin-bottom: 18px;
}

.profile-form .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #ccc;
}

.profile-form .form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #404040;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
    box-sizing: border-box;
    background: #333;
    color: #fff;
}

.profile-form .form-group input:focus {
    outline: none;
    border-color: #4caf50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.profile-form .form-actions {
    margin-top: 25px;
}

.profile-form .btn-primary {
    background: #4caf50;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.profile-form .btn-primary:hover {
    background: #43a047;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.profile-form .btn-primary:active {
    transform: translateY(0);
}

.message {
    margin-top: 15px;
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
}

.message.success {
    background: rgba(76, 175, 80, 0.15);
    color: #a8d5ab;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

.message.error {
    background: rgba(244, 67, 54, 0.15);
    color: #ef9a9a;
    border: 1px solid rgba(244, 67, 54, 0.3);
}

#updateNameBtn:disabled,
#changePasswordBtn:disabled {
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

    // ── Cambiar contraseña ─────────────────────────────────────────────
    const pwForm = document.getElementById('changePasswordForm');
    const pwMessage = document.getElementById('passwordMessage');
    const pwBtn = document.getElementById('changePasswordBtn');

    pwForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const actual = document.getElementById('passwordActual').value;
        const nueva = document.getElementById('passwordNueva').value;
        const confirmar = document.getElementById('passwordConfirmar').value;

        if (nueva !== confirmar) {
            showPwMessage('Las contraseñas no coinciden', 'error');
            return;
        }

        if (nueva.length < 6) {
            showPwMessage('La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }

        pwBtn.disabled = true;
        pwBtn.textContent = '⏳ Cambiando...';

        try {
            const res = await fetch('<?= $this->url('/perfil/cambiarPassword') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    password_actual: actual,
                    password_nueva: nueva,
                    password_confirmacion: confirmar
                })
            });
            const data = await res.json();

            if (data.success) {
                showPwMessage(data.message, 'success');
                pwForm.reset();
            } else {
                showPwMessage(data.message || 'Error al cambiar la contraseña', 'error');
            }
        } catch (err) {
            showPwMessage('Error de conexión', 'error');
        } finally {
            pwBtn.disabled = false;
            pwBtn.textContent = '🔒 Cambiar Contraseña';
        }
    });

    function showPwMessage(text, type) {
        pwMessage.textContent = text;
        pwMessage.className = 'message ' + type;
        pwMessage.style.display = 'block';
        setTimeout(() => { pwMessage.style.display = 'none'; }, 5000);
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

