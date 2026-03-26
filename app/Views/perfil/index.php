<?php 
$title = 'Mi Perfil - MiOlivar.es';
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

        <!-- Sección de gestión de usuarios (solo para rol empresa) -->
        <?php if (($user['rol'] ?? '') === 'empresa'): ?>
        <div class="profile-card" id="gestion-usuarios">
            <div class="profile-header">
                <h2>👥 Gestión de Usuarios</h2>
            </div>
            <p style="color:#aaa; font-size:0.85rem; margin-bottom:16px;">
                Crea cuentas de acceso para tus trabajadores y propietarios. El usuario tendrá el formato <strong style="color:#a8d5ab;">nombre@miolivar.es</strong>
            </p>

            <!-- Combobox para añadir nuevo usuario -->
            <h3 style="color:#ccc; font-size:0.95rem; margin:0 0 10px;">Crear cuenta de acceso</h3>
            <div class="combobox-usuarios-wrapper" style="position:relative; margin-bottom:20px;">
                <input type="text" id="buscar-persona-sin-cuenta" placeholder="Buscar trabajador o propietario..."
                    autocomplete="off"
                    style="width:100%; padding:12px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff; font-size:16px; box-sizing:border-box;">
                <div id="combobox-personas-dropdown" class="combobox-dropdown" style="display:none;"></div>
            </div>

            <!-- Usuarios con cuenta creada -->
            <h3 style="color:#ccc; font-size:0.95rem; margin:20px 0 10px;">Usuarios activos</h3>
            <div id="lista-usuarios-activos">
                <div class="notif-empty" style="color:#888; text-align:center; padding:12px;">Cargando...</div>
            </div>
        </div>

        <!-- Modal: Crear usuario vinculado -->
        <div class="modal" id="modalCrearUsuario" style="display:none;">
            <div class="modal-content" style="max-width:480px;">
                <div class="modal-header">
                    <h3 id="modalCrearUsuarioTitulo">Crear usuario</h3>
                    <button class="modal-close" onclick="cerrarModalCrearUsuario()" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="crear-usuario-tipo">
                    <input type="hidden" id="crear-usuario-vinculado-id">

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:6px;">Nombre de usuario:</label>
                        <div style="display:flex; align-items:center; gap:0;">
                            <input type="text" id="crear-usuario-nickname" placeholder="nombre.usuario"
                                style="flex:1; padding:12px; border-radius:8px 0 0 8px; border:1px solid #404040; border-right:none; background:#333; color:#fff; font-size:16px;"
                                pattern="[a-zA-Z0-9._-]+" title="Solo letras, números, puntos, guiones y guiones bajos">
                            <span style="padding:12px 14px; background:#404040; color:#a8d5ab; border-radius:0 8px 8px 0; border:1px solid #404040; font-size:14px; white-space:nowrap;">@miolivar.es</span>
                        </div>
                        <small id="crear-usuario-dni-hint" style="color:#888; font-size:0.78rem; margin-top:4px; display:block;"></small>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:6px;">Contraseña:</label>
                        <input type="password" id="crear-usuario-password" placeholder="Mínimo 6 caracteres" minlength="6"
                            style="width:100%; padding:12px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff; font-size:16px; box-sizing:border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:6px;">Confirmar contraseña:</label>
                        <input type="password" id="crear-usuario-password-confirm" placeholder="Repite la contraseña" minlength="6"
                            style="width:100%; padding:12px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff; font-size:16px; box-sizing:border-box;">
                    </div>
                    <div id="crear-usuario-msg" class="modal-msg" style="display:none;"></div>
                </div>
                <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end;">
                    <button class="btn btn-secondary" onclick="cerrarModalCrearUsuario()">Cancelar</button>
                    <button class="btn btn-primary" id="btnConfirmarCrearUsuario" onclick="confirmarCrearUsuario()">Crear usuario</button>
                </div>
            </div>
        </div>

        <!-- Modal: Cambiar contraseña de usuario vinculado -->
        <div class="modal" id="modalCambiarPassUsuario" style="display:none;">
            <div class="modal-content" style="max-width:440px;">
                <div class="modal-header">
                    <h3 id="modalCambiarPassTitulo">Cambiar contraseña</h3>
                    <button class="modal-close" onclick="cerrarModalCambiarPass()" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cambiar-pass-usuario-id">

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:6px;">Nueva contraseña:</label>
                        <input type="password" id="cambiar-pass-nueva" placeholder="Mínimo 6 caracteres" minlength="6"
                            style="width:100%; padding:12px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff; font-size:16px; box-sizing:border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:6px;">Confirmar contraseña:</label>
                        <input type="password" id="cambiar-pass-confirmar" placeholder="Repite la contraseña" minlength="6"
                            style="width:100%; padding:12px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff; font-size:16px; box-sizing:border-box;">
                    </div>
                    <div id="cambiar-pass-msg" class="modal-msg" style="display:none;"></div>
                </div>
                <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end;">
                    <button class="btn btn-secondary" onclick="cerrarModalCambiarPass()">Cancelar</button>
                    <button class="btn btn-primary" id="btnConfirmarCambiarPass" onclick="confirmarCambiarPassword()">Cambiar contraseña</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

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
                <div style="display:flex; gap:8px; align-items:center;">
                    <label style="color:#aaa; font-size:0.85rem; white-space:nowrap;">🔁 Repetición:</label>
                    <select id="rec-repeticion"
                        style="flex:1; padding:10px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff;"
                        onchange="document.getElementById('rec-dias-custom').style.display = this.value === 'custom' ? 'flex' : 'none';">
                        <option value="">Sin repetición</option>
                        <option value="mensual">Cada mes</option>
                        <option value="anual">Cada año</option>
                        <option value="custom">Cada X días</option>
                    </select>
                </div>
                <div id="rec-dias-custom" style="display:none; gap:8px; align-items:center;">
                    <label style="color:#aaa; font-size:0.85rem; white-space:nowrap;">Cada</label>
                    <input type="number" id="rec-dias" min="1" max="365" placeholder="15"
                        style="width:80px; padding:10px; border-radius:8px; border:1px solid #404040; background:#333; color:#fff;">
                    <span style="color:#aaa; font-size:0.85rem;">días</span>
                </div>
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

/* Gestión de usuarios vinculados */
.usuario-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #333;
    border-radius: 8px;
    margin-bottom: 6px;
}

.usuario-item .usuario-info {
    flex: 1;
    min-width: 0;
}

.usuario-item .usuario-nombre {
    color: #ddd;
    font-size: 0.9rem;
    font-weight: 500;
}

.usuario-item .usuario-email {
    color: #a8d5ab;
    font-size: 0.78rem;
    margin-top: 2px;
}

.usuario-item .usuario-sin-cuenta {
    color: #888;
    font-size: 0.78rem;
    font-style: italic;
    margin-top: 2px;
}

.usuario-item .usuario-acciones {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.usuario-item .usuario-acciones button {
    background: none;
    border: 1px solid #555;
    color: #ccc;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.78rem;
    transition: all 0.2s;
    white-space: nowrap;
}

.usuario-item .usuario-acciones button:hover {
    background: #444;
    border-color: #666;
}

.usuario-item .usuario-acciones .btn-crear-usuario {
    border-color: #4caf50;
    color: #a8d5ab;
}

.usuario-item .usuario-acciones .btn-crear-usuario:hover {
    background: rgba(76, 175, 80, 0.15);
}

.usuario-item .usuario-acciones .btn-eliminar-usuario {
    border-color: #f44336;
    color: #ef9a9a;
}

.usuario-item .usuario-acciones .btn-eliminar-usuario:hover {
    background: rgba(244, 67, 54, 0.15);
}

/* Mensaje inline dentro de modales */
.modal-msg {
    margin-top: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    line-height: 1.4;
}

.modal-msg.msg-error {
    background: rgba(244, 67, 54, 0.15);
    color: #ef9a9a;
    border: 1px solid rgba(244, 67, 54, 0.3);
}

.modal-msg.msg-success {
    background: rgba(76, 175, 80, 0.15);
    color: #a8d5ab;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

/* Combobox dropdown para buscar personas sin cuenta */
.combobox-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #333;
    border: 1px solid #555;
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 220px;
    overflow-y: auto;
    z-index: 100;
}

.combobox-dropdown .combobox-option {
    padding: 10px 14px;
    cursor: pointer;
    color: #ddd;
    font-size: 0.88rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s;
}

.combobox-dropdown .combobox-option:hover {
    background: #444;
}

.combobox-dropdown .combobox-option .combobox-tipo {
    font-size: 0.72rem;
    padding: 2px 6px;
    border-radius: 4px;
    flex-shrink: 0;
}

.combobox-dropdown .combobox-option .combobox-tipo.tipo-trabajador {
    background: rgba(76, 175, 80, 0.15);
    color: #a8d5ab;
}

.combobox-dropdown .combobox-option .combobox-tipo.tipo-propietario {
    background: rgba(33, 150, 243, 0.15);
    color: #90caf9;
}

.combobox-dropdown .combobox-empty {
    padding: 12px 14px;
    color: #888;
    font-size: 0.85rem;
    text-align: center;
}

@media (max-width: 768px) {
    .usuario-item {
        flex-wrap: wrap;
    }
    .usuario-item .usuario-acciones {
        width: 100%;
        justify-content: flex-end;
        margin-top: 6px;
    }
}
</style>

<script>
function initPerfil() {
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
        'itv':            { icon: '🚗', label: 'ITV de vehículos', desc: 'Te avisará 30 días antes del vencimiento de la ITV de tus vehículos.' },
        'cuentas':        { icon: '💰', label: 'Cierre de cuentas mensuales', desc: 'Recordatorio a principio de mes para revisar el cierre económico del mes anterior.' },
        'jornadas':       { icon: '📋', label: 'Jornadas reales a gestoría', desc: 'Aparece 2 días antes de fin de mes y permanece hasta el día 5 del mes siguiente.' },
        'fitosanitario':  { icon: '🧪', label: 'Fitosanitarios (stock bajo)', desc: 'Te avisa cuando algún producto fitosanitario tiene stock en 0.' },
        'personalizado':  { icon: '📌', label: 'Recordatorios personalizados', desc: 'Recordatorios que tú mismo creas con fecha y descripción.' }
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
                div.style.cssText = 'padding:10px 12px; background:#333; border-radius:8px;';
                div.innerHTML = '<div style="display:flex; align-items:center; justify-content:space-between;">'
                    + '<span style="color:#ddd;">' + info.icon + ' ' + info.label + '</span>'
                    + '<label class="toggle-switch">'
                    + '<input type="checkbox" ' + (cfg.activo == 1 ? 'checked' : '') + ' onchange="toggleNotifTipo(\'' + tipo + '\', this.checked)">'
                    + '<span class="toggle-slider"></span>'
                    + '</label>'
                    + '</div>'
                    + '<p style="margin:4px 0 0; font-size:0.78rem; color:#888; line-height:1.3;">' + info.desc + '</p>';
                container.appendChild(div);
            });
        } catch(e) {}
    }

    window.toggleNotifTipo = async function(tipo, activo) {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        await fetch(basePath + '/notificaciones/toggleConfig', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
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
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        await fetch(basePath + '/notificaciones/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
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

        // Obtener valor de repetición
        var repSelect = document.getElementById('rec-repeticion').value;
        var repeticion = null;
        if (repSelect === 'mensual' || repSelect === 'anual') {
            repeticion = repSelect;
        } else if (repSelect === 'custom') {
            var dias = parseInt(document.getElementById('rec-dias').value);
            if (!dias || dias < 1) {
                if (typeof showToast === 'function') showToast('Indica el número de días', 'error');
                return;
            }
            repeticion = String(dias);
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        var res = await fetch(basePath + '/notificaciones/crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ titulo: titulo, descripcion: desc, fecha_aviso: fecha, repeticion: repeticion })
        });
        var data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') showToast('Recordatorio creado', 'success');
            document.getElementById('rec-titulo').value = '';
            document.getElementById('rec-desc').value = '';
            document.getElementById('rec-fecha').value = '';
            document.getElementById('rec-repeticion').value = '';
            document.getElementById('rec-dias-custom').style.display = 'none';
            cargarRecordatoriosPerfil();
        }
    });

    cargarNotifConfig();
    cargarRecordatoriosPerfil();

    <?php if (($user['rol'] ?? '') === 'empresa'): ?>
    // ── Gestión de usuarios vinculados ─────────────────────────────────
    cargarUsuariosVinculados();
    <?php endif; ?>
}

// Patrón AJAX-safe: funciona tanto en carga normal como en navegación AJAX
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPerfil);
} else {
    initPerfil();
}

<?php if (($user['rol'] ?? '') === 'empresa'): ?>
// Almacén de datos: personas sin cuenta (para el combobox) y con cuenta (para la lista)
var _personasSinCuenta = [];
var _personasConCuenta = [];

/**
 * Carga la lista de trabajadores y propietarios con info de sus cuentas de usuario.
 */
async function cargarUsuariosVinculados() {
    var basePath = window._APP_BASE_PATH || '';
    try {
        var res = await fetch(basePath + '/perfil/usuarios', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        var data = await res.json();
        if (!data.success) return;

        // Separar: con cuenta y sin cuenta
        _personasConCuenta = [];
        _personasSinCuenta = [];

        var todas = (data.trabajadores || []).concat(data.propietarios || []);
        todas.forEach(function(p) {
            p._nombreCompleto = p.nombre + (p.apellidos ? ' ' + p.apellidos : '');
            if (p.usuario_id) {
                _personasConCuenta.push(p);
            } else {
                _personasSinCuenta.push(p);
            }
        });

        renderUsuariosActivos();
        initComboboxPersonas();
    } catch (e) {
        console.error('Error cargando usuarios vinculados:', e);
    }
}

/**
 * Renderiza solo los usuarios que ya tienen cuenta.
 */
function renderUsuariosActivos() {
    var container = document.getElementById('lista-usuarios-activos');

    if (_personasConCuenta.length === 0) {
        container.innerHTML = '<div style="color:#888; text-align:center; padding:12px; font-size:0.85rem;">No hay usuarios creados todavía</div>';
        return;
    }

    var html = '';
    _personasConCuenta.forEach(function(p) {
        var tipoLabel = p.tipo === 'trabajador' ? '🧑‍🌾' : '🏠';
        html += '<div class="usuario-item">';
        html += '<div class="usuario-info">';
        html += '<div class="usuario-nombre">' + tipoLabel + ' ' + escapeHtml(p._nombreCompleto) + '</div>';
        html += '<div class="usuario-email">✓ ' + escapeHtml(p.usuario_email) + '</div>';
        html += '</div>';
        html += '<div class="usuario-acciones">';
        html += '<button onclick="abrirModalCambiarPass(' + p.usuario_id + ', \'' + escapeHtml(p._nombreCompleto).replace(/'/g, "\\'") + '\')" title="Cambiar contraseña">🔑</button>';
        html += '<button class="btn-eliminar-usuario" onclick="eliminarUsuarioVinculado(' + p.usuario_id + ', \'' + escapeHtml(p._nombreCompleto).replace(/'/g, "\\'") + '\')" title="Eliminar usuario">🗑</button>';
        html += '</div></div>';
    });

    container.innerHTML = html;
}

/**
 * Escapa HTML para evitar XSS.
 */
function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// ── Combobox: buscar persona sin cuenta ────────────────────────────────

function initComboboxPersonas() {
    var input = document.getElementById('buscar-persona-sin-cuenta');
    var dropdown = document.getElementById('combobox-personas-dropdown');

    // Limpiar listeners previos clonando el input
    var nuevoInput = input.cloneNode(true);
    input.parentNode.replaceChild(nuevoInput, input);
    input = nuevoInput;

    input.addEventListener('input', function() {
        var texto = this.value.trim().toLowerCase();
        if (texto.length === 0) {
            dropdown.style.display = 'none';
            return;
        }

        var filtradas = _personasSinCuenta.filter(function(p) {
            return p._nombreCompleto.toLowerCase().indexOf(texto) !== -1 ||
                   (p.dni && p.dni.toLowerCase().indexOf(texto) !== -1);
        });

        if (filtradas.length === 0) {
            dropdown.innerHTML = '<div class="combobox-empty">No se encontraron resultados</div>';
            dropdown.style.display = 'block';
            return;
        }

        var html = '';
        filtradas.forEach(function(p) {
            var tipoClase = 'tipo-' + p.tipo;
            var tipoTexto = p.tipo === 'trabajador' ? 'Trabajador' : 'Propietario';
            html += '<div class="combobox-option" data-tipo="' + p.tipo + '" data-id="' + p.id + '" data-nombre="' + escapeHtml(p._nombreCompleto) + '" data-dni="' + escapeHtml(p.dni || '') + '">';
            html += '<span class="combobox-tipo ' + tipoClase + '">' + tipoTexto + '</span>';
            html += '<span>' + escapeHtml(p._nombreCompleto) + '</span>';
            html += '</div>';
        });
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';

        // Añadir listeners a cada opción
        dropdown.querySelectorAll('.combobox-option').forEach(function(opt) {
            opt.addEventListener('click', function() {
                var tipo = this.getAttribute('data-tipo');
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre');
                var dni = this.getAttribute('data-dni');
                dropdown.style.display = 'none';
                input.value = '';
                abrirModalCrearUsuario(tipo, id, nombre, dni);
            });
        });
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.combobox-usuarios-wrapper')) {
            dropdown.style.display = 'none';
        }
    });

    // Al enfocar sin texto, mostrar todas las opciones disponibles
    input.addEventListener('focus', function() {
        if (this.value.trim().length === 0 && _personasSinCuenta.length > 0) {
            var html = '';
            _personasSinCuenta.forEach(function(p) {
                var tipoClase = 'tipo-' + p.tipo;
                var tipoTexto = p.tipo === 'trabajador' ? 'Trabajador' : 'Propietario';
                html += '<div class="combobox-option" data-tipo="' + p.tipo + '" data-id="' + p.id + '" data-nombre="' + escapeHtml(p._nombreCompleto) + '" data-dni="' + escapeHtml(p.dni || '') + '">';
                html += '<span class="combobox-tipo ' + tipoClase + '">' + tipoTexto + '</span>';
                html += '<span>' + escapeHtml(p._nombreCompleto) + '</span>';
                html += '</div>';
            });
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';

            dropdown.querySelectorAll('.combobox-option').forEach(function(opt) {
                opt.addEventListener('click', function() {
                    var tipo = this.getAttribute('data-tipo');
                    var id = this.getAttribute('data-id');
                    var nombre = this.getAttribute('data-nombre');
                    var dni = this.getAttribute('data-dni');
                    dropdown.style.display = 'none';
                    input.value = '';
                    abrirModalCrearUsuario(tipo, id, nombre, dni);
                });
            });
        }
    });
}

// ── Modal: Crear usuario ───────────────────────────────────────────────

/**
 * Muestra un mensaje inline dentro de un modal.
 */
function showModalMsg(elementId, text, type) {
    var el = document.getElementById(elementId);
    el.textContent = text;
    el.className = 'modal-msg msg-' + type;
    el.style.display = 'block';
}

function hideModalMsg(elementId) {
    var el = document.getElementById(elementId);
    el.style.display = 'none';
    el.textContent = '';
}

function abrirModalCrearUsuario(tipo, vinculadoId, nombre, dni) {
    document.getElementById('crear-usuario-tipo').value = tipo;
    document.getElementById('crear-usuario-vinculado-id').value = vinculadoId;
    document.getElementById('crear-usuario-nickname').value = '';
    document.getElementById('crear-usuario-password').value = '';
    document.getElementById('crear-usuario-password-confirm').value = '';
    document.getElementById('modalCrearUsuarioTitulo').textContent = 'Crear usuario para ' + nombre;
    hideModalMsg('crear-usuario-msg');

    // Sugerir el DNI como nombre de usuario si existe
    var hintEl = document.getElementById('crear-usuario-dni-hint');
    if (dni) {
        var dniLimpio = dni.replace(/[\s-]/g, '').toLowerCase();
        hintEl.textContent = 'Sugerencia: ' + dniLimpio + '@miolivar.es (basado en DNI)';
        hintEl.style.cursor = 'pointer';
        hintEl.onclick = function() {
            document.getElementById('crear-usuario-nickname').value = dniLimpio;
            hintEl.textContent = '✓ DNI aplicado como nombre de usuario';
            hintEl.onclick = null;
        };
    } else {
        hintEl.textContent = '';
        hintEl.onclick = null;
    }

    document.getElementById('modalCrearUsuario').style.display = 'flex';
}

function cerrarModalCrearUsuario() {
    document.getElementById('modalCrearUsuario').style.display = 'none';
    hideModalMsg('crear-usuario-msg');
}

async function confirmarCrearUsuario() {
    var msgId = 'crear-usuario-msg';
    hideModalMsg(msgId);

    var tipo = document.getElementById('crear-usuario-tipo').value;
    var vinculadoId = document.getElementById('crear-usuario-vinculado-id').value;
    var nickname = document.getElementById('crear-usuario-nickname').value.trim();
    var password = document.getElementById('crear-usuario-password').value;
    var passwordConfirm = document.getElementById('crear-usuario-password-confirm').value;

    if (!nickname) {
        showModalMsg(msgId, 'El nombre de usuario es obligatorio', 'error');
        return;
    }

    if (!/^[a-zA-Z0-9._-]+$/.test(nickname)) {
        showModalMsg(msgId, 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos', 'error');
        return;
    }

    if (!password || password.length < 6) {
        showModalMsg(msgId, 'La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }

    if (password !== passwordConfirm) {
        showModalMsg(msgId, 'Las contraseñas no coinciden', 'error');
        return;
    }

    var btn = document.getElementById('btnConfirmarCrearUsuario');
    if (typeof setButtonLoading === 'function') setButtonLoading(btn, true);

    var basePath = window._APP_BASE_PATH || '';
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    try {
        var res = await fetch(basePath + '/perfil/usuarios/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                tipo: tipo,
                vinculado_id: vinculadoId,
                nickname: nickname,
                password: password
            })
        });
        var data = await res.json();

        if (data.success) {
            cerrarModalCrearUsuario();
            if (typeof showToast === 'function') showToast(data.message || 'Usuario creado', 'success');
            cargarUsuariosVinculados();
        } else {
            showModalMsg(msgId, data.message || 'Error al crear usuario', 'error');
        }
    } catch (e) {
        showModalMsg(msgId, 'Error de conexión', 'error');
    } finally {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
    }
}

// ── Modal: Cambiar contraseña ──────────────────────────────────────────

function abrirModalCambiarPass(usuarioId, nombre) {
    document.getElementById('cambiar-pass-usuario-id').value = usuarioId;
    document.getElementById('cambiar-pass-nueva').value = '';
    document.getElementById('cambiar-pass-confirmar').value = '';
    document.getElementById('modalCambiarPassTitulo').textContent = 'Cambiar contraseña de ' + nombre;
    hideModalMsg('cambiar-pass-msg');
    document.getElementById('modalCambiarPassUsuario').style.display = 'flex';
}

function cerrarModalCambiarPass() {
    document.getElementById('modalCambiarPassUsuario').style.display = 'none';
    hideModalMsg('cambiar-pass-msg');
}

async function confirmarCambiarPassword() {
    var msgId = 'cambiar-pass-msg';
    hideModalMsg(msgId);

    var usuarioId = document.getElementById('cambiar-pass-usuario-id').value;
    var nueva = document.getElementById('cambiar-pass-nueva').value;
    var confirmar = document.getElementById('cambiar-pass-confirmar').value;

    if (!nueva || nueva.length < 6) {
        showModalMsg(msgId, 'La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }

    if (nueva !== confirmar) {
        showModalMsg(msgId, 'Las contraseñas no coinciden', 'error');
        return;
    }

    var btn = document.getElementById('btnConfirmarCambiarPass');
    if (typeof setButtonLoading === 'function') setButtonLoading(btn, true);

    var basePath = window._APP_BASE_PATH || '';
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    try {
        var res = await fetch(basePath + '/perfil/usuarios/cambiarPassword', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                usuario_id: usuarioId,
                password: nueva
            })
        });
        var data = await res.json();

        if (data.success) {
            cerrarModalCambiarPass();
            if (typeof showToast === 'function') showToast(data.message || 'Contraseña cambiada', 'success');
        } else {
            showModalMsg(msgId, data.message || 'Error al cambiar la contraseña', 'error');
        }
    } catch (e) {
        showModalMsg(msgId, 'Error de conexión', 'error');
    } finally {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
    }
}

// ── Eliminar usuario vinculado ─────────────────────────────────────────

async function eliminarUsuarioVinculado(usuarioId, nombre) {
    // Usar showConfirm global en vez de confirm() nativo
    if (typeof showConfirm === 'function') {
        var confirmado = await showConfirm('¿Eliminar la cuenta de usuario de ' + nombre + '? Ya no podrá acceder a la aplicación.');
        if (!confirmado) return;
    }

    var basePath = window._APP_BASE_PATH || '';
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    try {
        var res = await fetch(basePath + '/perfil/usuarios/eliminar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ usuario_id: usuarioId })
        });
        var data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') showToast(data.message || 'Usuario eliminado', 'success');
            cargarUsuariosVinculados();
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Error al eliminar', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error de conexión', 'error');
    }
}

// Cerrar modales con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('modalCrearUsuario').style.display !== 'none') {
            cerrarModalCrearUsuario();
        }
        if (document.getElementById('modalCambiarPassUsuario').style.display !== 'none') {
            cerrarModalCambiarPass();
        }
    }
});
<?php endif; ?>
</script>

