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
});
</script>

