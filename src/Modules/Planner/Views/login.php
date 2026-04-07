<?php
/**
 * Vista de login del módulo Planner.
 *
 * Variables disponibles (inyectadas por AuthController::renderPlannerView):
 *   $error : string|null  Mensaje de error a mostrar.
 *   $csrf  : string       HTML del input oculto con el token CSRF.
 *
 * Diseño deliberadamente austero: sin assets externos, sin Tailwind del
 * sitio público, sin JS. Una sola pantalla, foco directo en el formulario.
 * Es coherente con el espíritu minimalista del módulo Planner.
 */
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Planner · Acceso</title>
  <style>
    :root { color-scheme: dark; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
      background: #0a0a0a;
      color: #f5f5f5;
    }
    .card {
      width: min(360px, 90vw);
      padding: 2.5rem 2rem;
      border: 1px solid #1f1f1f;
      border-radius: 4px;
      background: #0f0f0f;
    }
    h1 {
      margin: 0 0 0.25rem;
      font-size: 1.1rem;
      font-weight: 500;
      letter-spacing: 0.02em;
    }
    p.sub {
      margin: 0 0 2rem;
      font-size: 0.8rem;
      color: #6a6a6a;
    }
    label {
      display: block;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #8a8a8a;
      margin-bottom: 0.4rem;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 0.7rem 0.8rem;
      margin-bottom: 1.2rem;
      background: #050505;
      border: 1px solid #222;
      color: #f5f5f5;
      font-size: 0.9rem;
      border-radius: 2px;
      outline: none;
      transition: border-color 0.15s;
    }
    input:focus { border-color: #555; }
    button {
      width: 100%;
      padding: 0.8rem;
      background: #f5f5f5;
      color: #0a0a0a;
      border: none;
      font-size: 0.85rem;
      font-weight: 500;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: background 0.15s;
    }
    button:hover { background: #ffffff; }
    .error {
      margin-bottom: 1.2rem;
      padding: 0.7rem 0.8rem;
      border-left: 2px solid #c0392b;
      background: #1a0707;
      color: #e8a8a8;
      font-size: 0.8rem;
    }
  </style>
</head>
<body>
  <main class="card">
    <h1>Planner</h1>
    <p class="sub">Acceso restringido</p>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="login" autocomplete="off">
      <?= $csrf ?>

      <label for="username">Usuario</label>
      <input type="text" id="username" name="username" required autofocus>

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Entrar</button>
    </form>
  </main>
</body>
</html>
