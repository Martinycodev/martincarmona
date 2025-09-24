<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Tareas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e1e1e;
            color: white;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }
        
        h1 {
            color: white;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }
        
        .status {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            color: #4CAF50;
            border: 1px solid #444;
        }
        
        .login-form {
            background: #2a2a2a;
            padding: 40px;
            border-radius: 12px;
            margin: 40px 0;
            border: 1px solid #444;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .login-form h3 {
            color: white;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #e0e0e0;
            font-size: 1rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid #555;
            border-radius: 8px;
            font-size: 16px;
            background: #1e1e1e;
            color: white;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background: #252525;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .demo-credentials {
            background: #1b3b6b;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border: 1px solid #2f4f4f;
        }
        
        .demo-credentials p {
            margin: 8px 0;
            color: #e0e0e0;
        }
        
        .demo-credentials code {
            background: #2a2a2a;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #4CAF50;
            border: 1px solid #444;
        }
        
        .demo-credentials a {
            color: #4CAF50;
            text-decoration: none;
        }
        
        .demo-credentials a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #6b1b1b;
            color: #ffcdd2;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #8d2f2f;
            text-align: center;
        }
    </style>
</head>
<body>
            <div class="container">
            <h1>🌳 MartinCarmona.com</h1>
            
            <div class="login-form">
                <h3>🔐 Iniciar Sesión</h3>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <?php if ($error === 'missing_fields'): ?>
                            ❌ Por favor, completa todos los campos
                        <?php elseif ($error === 'invalid_credentials'): ?>
                            ❌ Email o contraseña incorrectos
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= $this->url('/login') ?>">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="remember">
                            Recuérdame
                            <input type="checkbox" id="remember" name="remember" style="display: inline-block; width: auto; margin-left: 10px; scale: 1.5;">
                            
                        </label>
                    </div>
                    
                    <button type="submit" class="btn">Iniciar Sesión</button>
                </form>
    
            </div>
        </div>
</body>
</html>
