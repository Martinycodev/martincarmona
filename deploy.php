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

// ─── Ejecutar comando con proc_open ──────────────────────
function run(string $command, string $cwd): array
{
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptors, $pipes, $cwd);

    if (!is_resource($process)) {
        return ['output' => 'Failed to start process', 'code' => 1];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $code = proc_close($process);

    $output = trim($stdout . "\n" . $stderr);
    return ['output' => $output, 'code' => $code];
}

// Deploy
$commands = [
    "git fetch origin {$branch}",
    "git reset --hard origin/{$branch}",
    "composer install --no-dev --optimize-autoloader",
];

$log = "[" . date('Y-m-d H:i:s') . "] Deploy started\n";
$failed = false;

foreach ($commands as $cmd) {
    $result = run($cmd, $repoPath);
    $log .= "> {$cmd}\n{$result['output']}\n";

    if ($result['code'] !== 0) {
        $log .= "FAILED (exit {$result['code']})\n";
        $failed = true;
        break;
    }
}

$log .= $failed ? "Deploy FAILED\n" : "Deploy OK\n";
$log .= str_repeat('-', 50) . "\n";

file_put_contents($logFile, $log, FILE_APPEND);

http_response_code($failed ? 500 : 200);
echo $failed ? 'Deploy failed — check log' : 'Deploy OK';
