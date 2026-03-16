#!/usr/bin/env php
<?php
/**
 * Backup automático de la base de datos
 *
 * Genera un dump SQL de la base de datos y lo guarda en database/backups/
 * con fecha en el nombre. Mantiene solo los últimos 10 backups.
 *
 * USO:
 *   php database/backup.php
 *
 * Se puede programar como tarea cron (Linux) o Tarea Programada (Windows):
 *   # Cada día a las 3:00 AM
 *   0 3 * * * cd /path/to/martincarmona && php database/backup.php >> logs/backup.log 2>&1
 *
 * En Hostinger (cron jobs del panel):
 *   php /home/u873002419/public_html/martincarmona/database/backup.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// ── Configuración ───────────────────────────────────────────────────────
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_DATABASE'] ?? '';
$dbUser = $_ENV['DB_USERNAME'] ?? '';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';
$dbPort = $_ENV['DB_PORT'] ?? '3306';

$backupDir = BASE_PATH . '/database/backups';
$maxBackups = 10; // Número máximo de backups a mantener
$fecha = date('Y-m-d_H-i-s');
$archivo = "{$backupDir}/backup_{$dbName}_{$fecha}.sql";

echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup de '{$dbName}'...\n";

// ── Crear carpeta de backups si no existe ───────────────────────────────
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    // Crear .gitignore para no subir los backups al repo
    file_put_contents($backupDir . '/.gitignore', "*\n!.gitignore\n");
}

// ── Método 1: mysqldump (preferido, más fiable) ────────────────────────
$mysqldumpPath = '';

// Detectar ruta de mysqldump según el entorno
$possiblePaths = [
    'mysqldump',                              // En PATH del sistema
    'C:/xampp/mysql/bin/mysqldump.exe',        // XAMPP Windows
    '/usr/bin/mysqldump',                      // Linux
    '/usr/local/bin/mysqldump',                // macOS / Homebrew
];

foreach ($possiblePaths as $path) {
    $testCmd = (PHP_OS_FAMILY === 'Windows')
        ? "where " . escapeshellarg($path) . " 2>NUL"
        : "which " . escapeshellarg($path) . " 2>/dev/null";

    // Para rutas absolutas, verificar si el archivo existe
    if (str_contains($path, '/') || str_contains($path, '\\')) {
        if (file_exists($path)) {
            $mysqldumpPath = $path;
            break;
        }
    } else {
        exec($testCmd, $output, $returnCode);
        if ($returnCode === 0) {
            $mysqldumpPath = $path;
            break;
        }
    }
}

$success = false;

if ($mysqldumpPath) {
    echo "  Usando mysqldump: {$mysqldumpPath}\n";

    // Construir comando mysqldump
    $cmd = escapeshellarg($mysqldumpPath)
        . " --host=" . escapeshellarg($dbHost)
        . " --port=" . escapeshellarg($dbPort)
        . " --user=" . escapeshellarg($dbUser)
        . " --password=" . escapeshellarg($dbPass)
        . " --single-transaction"
        . " --routines"
        . " --triggers"
        . " " . escapeshellarg($dbName)
        . " > " . escapeshellarg($archivo)
        . " 2>&1";

    exec($cmd, $output, $returnCode);

    if ($returnCode === 0 && file_exists($archivo) && filesize($archivo) > 100) {
        $success = true;
    } else {
        echo "  AVISO: mysqldump falló (código {$returnCode}), intentando método PHP...\n";
        if (file_exists($archivo)) unlink($archivo);
    }
}

// ── Método 2: Backup via PHP/mysqli (fallback) ─────────────────────────
if (!$success) {
    echo "  Usando backup por PHP (sin mysqldump)...\n";

    require BASE_PATH . '/config/database.php';
    $db = \Database::connect();
    $db->set_charset('utf8mb4');

    $dump = "-- Backup generado por PHP\n";
    $dump .= "-- Fecha: {$fecha}\n";
    $dump .= "-- Base de datos: {$dbName}\n";
    $dump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $dump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

    // Obtener todas las tablas
    $tablas = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tablas[] = $row[0];
    }

    foreach ($tablas as $tabla) {
        // Estructura
        $createResult = $db->query("SHOW CREATE TABLE `{$tabla}`");
        $createRow = $createResult->fetch_row();
        $dump .= "-- Estructura de tabla `{$tabla}`\n";
        $dump .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
        $dump .= $createRow[1] . ";\n\n";

        // Datos
        $dataResult = $db->query("SELECT * FROM `{$tabla}`");
        if ($dataResult && $dataResult->num_rows > 0) {
            $dump .= "-- Datos de tabla `{$tabla}`\n";

            while ($row = $dataResult->fetch_assoc()) {
                $valores = [];
                foreach ($row as $valor) {
                    if ($valor === null) {
                        $valores[] = 'NULL';
                    } else {
                        $valores[] = "'" . $db->real_escape_string($valor) . "'";
                    }
                }
                $dump .= "INSERT INTO `{$tabla}` VALUES (" . implode(', ', $valores) . ");\n";
            }
            $dump .= "\n";
        }
    }

    $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

    if (file_put_contents($archivo, $dump)) {
        $success = true;
    }
}

// ── Resultado ───────────────────────────────────────────────────────────
if ($success) {
    $size = filesize($archivo);
    $sizeKB = round($size / 1024, 1);
    echo "  Backup guardado: {$archivo} ({$sizeKB} KB)\n";

    // Comprimir si gzip está disponible
    if (function_exists('gzencode') && $size > 0) {
        $contenido = file_get_contents($archivo);
        $comprimido = gzencode($contenido, 9);
        $archivoGz = $archivo . '.gz';
        file_put_contents($archivoGz, $comprimido);
        unlink($archivo); // Borrar el .sql sin comprimir
        $sizeGz = round(strlen($comprimido) / 1024, 1);
        echo "  Comprimido: {$archivoGz} ({$sizeGz} KB)\n";
        $archivo = $archivoGz;
    }

    // ── Limpiar backups antiguos (mantener solo los últimos N) ───────
    $backups = glob($backupDir . '/backup_*');
    usort($backups, function($a, $b) { return filemtime($b) - filemtime($a); });

    if (count($backups) > $maxBackups) {
        $eliminados = 0;
        for ($i = $maxBackups; $i < count($backups); $i++) {
            unlink($backups[$i]);
            $eliminados++;
        }
        echo "  Limpieza: eliminados {$eliminados} backups antiguos (se mantienen los últimos {$maxBackups})\n";
    }

    echo "[" . date('Y-m-d H:i:s') . "] Backup completado con éxito.\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: No se pudo crear el backup.\n";
    exit(1);
}
