<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #1e1e1e;
            color: white;
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            background: #2a2a2a;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: 1px solid #444;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #e0e0e0;
            font-size: 1rem;
        }
        
        input[type="text"], textarea, select, input[type="date"] {
            width: 100%;
            padding: 15px;
            border: 1px solid #555;
            border-radius: 8px;
            font-size: 16px;
            background: #1e1e1e;
            color: white;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus, textarea:focus, select:focus, input[type="date"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background: #252525;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 8px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $titulo ?></h1>
        
        <form method="POST" action="<?= $this->url('/tareas/crear') ?>">
            <?= \Core\CsrfMiddleware::getTokenField() ?>
            <div class="form-group">
                <label for="titulo">Título de la Tarea:</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="prioridad">Prioridad:</label>
                <select id="prioridad" name="prioridad">
                    <option value="baja">Baja</option>
                    <option value="media" selected>Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_limite">Fecha Límite:</label>
                <input type="date" id="fecha_limite" name="fecha_limite">
            </div>
            
            <div style="text-align: center;">
                <button type="submit" class="btn">Crear Tarea</button>
                <a href="<?= $this->url('/tareas') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
