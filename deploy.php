<?php
/**
 * GitHub Webhook — Auto deploy
 *
 * Subir este archivo a una ubicación accesible por web en Hostinger.
 * Configurar el webhook en GitHub apuntando a:
 * https://martincarmona.com/deploy.php
 */

// ─── Configuración ───────────────────────────────────────
$secret    = '66db4530fbf51481fef70205f1f88d9cdd1644ddbd2a531d3517e1415e2b3a57'; // El mismo que pongas en GitHub
$repoPath  = '/home/u873002419/domains/martincarmona.com/public_html';
$branch    = 'main';
$logFile   = __DIR__ . '/deploy.log';
// ─────────────────────────────────────────────────────────

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Verificar firma de GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload   = file_get_contents('php://input');

if (empty($signature)) {
    http_response_code(403);
    exit('No signature');
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}

// Verificar que es un push a la rama correcta
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';

if ($ref !== "refs/heads/{$branch}") {
    http_response_code(200);
    exit("Ignored: push to {$ref}");
}

// Ejecutar deploy
$commands = [
    "cd {$repoPath}",
    "git fetch origin {$branch}",
    "git reset --hard origin/{$branch}",
    "composer install --no-dev --optimize-autoloader 2>&1",
];

$fullCommand = implode(' && ', $commands);
$output = [];
$returnCode = 0;

exec($fullCommand, $output, $returnCode);

// Log
$log = sprintf(
    "[%s] Deploy %s (branch: %s, exit: %d)\n%s\n%s\n",
    date('Y-m-d H:i:s'),
    $returnCode === 0 ? 'OK' : 'FAIL',
    $branch,
    $returnCode,
    str_repeat('-', 50),
    implode("\n", $output)
);

file_put_contents($logFile, $log, FILE_APPEND);

http_response_code($returnCode === 0 ? 200 : 500);
echo $returnCode === 0 ? 'Deploy OK' : 'Deploy failed — check log';
