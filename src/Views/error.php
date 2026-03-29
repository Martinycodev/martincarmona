<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $code ?> — Martín Carmona</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0a; color: #f5f5f5;
            font-family: system-ui, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 2rem;
        }
        .code { font-size: 6rem; font-weight: 700; color: #c8a97e; line-height: 1; }
        .msg  { color: #a0a0a0; margin: 1rem 0 2rem; }
        a { color: #c8a97e; text-decoration: none; border-bottom: 1px solid #c8a97e40; padding-bottom: 2px; }
    </style>
</head>
<body>
    <div>
        <p class="code"><?= $code ?></p>
        <p class="msg"><?= htmlspecialchars($message) ?></p>
        <a href="/">← Volver al inicio</a>
    </div>
</body>
</html>
