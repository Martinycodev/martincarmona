<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Página no encontrada — Martín Carmona</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #080a07; color: #e8ebe3;
            font-family: 'Helvetica Neue', Helvetica, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 2rem;
        }
        .code { font-size: 5rem; font-weight: 700; color: #3d4138; line-height: 1; }
        .msg  { color: #8a8f80; margin: 1rem 0 2rem; font-size: 0.95rem; }
        a { color: #8a8f80; text-decoration: none; font-size: 0.8rem; letter-spacing: 0.05em;
            border-bottom: 1px solid rgba(138,143,128,0.2); padding-bottom: 2px;
            transition: color 0.3s; }
        a:hover { color: #e8ebe3; }
    </style>
</head>
<body>
    <div>
        <p class="code"><?= $code ?></p>
        <p class="msg">Esta página no existe.</p>
        <a href="/">Volver</a>
    </div>
</body>
</html>
