<?php

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>👤 Crear Nuevo Usuario</h2>";

// Cargar la clase Database
require_once 'config/database.php';

// Datos del nuevo usuario
$nombre = 'Admin Sistema';
$email = 'admin@sistema.com';
$password = 'admin123';

echo "<h3>1. Datos del usuario a crear:</h3>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Nombre:</strong> {$nombre}<br>";
echo "<strong>Email:</strong> {$email}<br>";
echo "<strong>Contraseña:</strong> {$password}<br>";
echo "</div>";

try {
    $db = \Database::connect();
    
    echo "<h3>2. Verificando si el usuario ya existe...</h3>";
    
    // Verificar si el usuario ya existe
    $stmt = $db->prepare("SELECT id, name, email FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $existingUser = $result->fetch_assoc();
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ El usuario ya existe:<br>";
        echo "<strong>ID:</strong> " . $existingUser['id'] . "<br>";
        echo "<strong>Nombre:</strong> " . $existingUser['name'] . "<br>";
        echo "<strong>Email:</strong> " . $existingUser['email'] . "<br>";
        echo "</div>";
        
        echo "<h3>3. Actualizando contraseña del usuario existente...</h3>";
        
        // Generar nuevo hash para la contraseña
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Actualizar la contraseña
        $updateStmt = $db->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
        $updateStmt->bind_param("ss", $newHash, $email);
        
        if ($updateStmt->execute()) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Contraseña actualizada correctamente<br>";
            echo "El usuario ahora puede hacer login con 'admin123'";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Error al actualizar la contraseña: " . $updateStmt->error;
            echo "</div>";
        }
        
        $updateStmt->close();
        
    } else {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ El usuario no existe, procediendo a crearlo...";
        echo "</div>";
        
        echo "<h3>3. Creando nuevo usuario...</h3>";
        
        // Generar hash para la contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $insertStmt = $db->prepare("INSERT INTO usuarios (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $insertStmt->bind_param("sss", $nombre, $email, $hash);
        
        if ($insertStmt->execute()) {
            $userId = $db->insert_id;
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Usuario creado correctamente<br>";
            echo "<strong>ID:</strong> {$userId}<br>";
            echo "<strong>Nombre:</strong> {$nombre}<br>";
            echo "<strong>Email:</strong> {$email}<br>";
            echo "<strong>Contraseña:</strong> {$password}<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Error al crear el usuario: " . $insertStmt->error;
            echo "</div>";
        }
        
        $insertStmt->close();
    }
    
    $stmt->close();
    $db->close();
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>4. Credenciales para el login:</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Email:</strong> {$email}<br>";
echo "<strong>Contraseña:</strong> {$password}<br>";
echo "</div>";

echo "<h3>5. Próximos pasos:</h3>";
echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "1. <strong>Probar el login</strong> con las credenciales de arriba<br>";
echo "2. <strong>Verificar que funciona</strong> la autenticación<br>";
echo "3. <strong>Crear el dashboard</strong> para usuarios autenticados<br>";
echo "4. <strong>Implementar el sistema de tareas</strong>";
echo "</div>";

echo "<p>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 Ir al Login</a>";
echo "<a href='test_password.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Verificar Contraseña</a>";
echo "</p>";
