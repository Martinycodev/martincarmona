#!/usr/bin/env php
<?php
/**
 * Seed de riegos desde Notion
 *
 * Importa los registros de riego del CSV de Notion a la tabla `riegos`.
 * Resuelve parcela_id buscando por nombre en la BD.
 * Si un riego tiene múltiples parcelas, las vincula via `riego_parcelas`.
 * Evita duplicados comprobando hidrante + fecha_ini + fecha_fin.
 *
 * USO: php database/seed_notion_riego.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

require BASE_PATH . '/config/database.php';
$db = \Database::connect();
$db->set_charset('utf8mb4');

// Detectar usuario empresa
$stmt = $db->prepare("SELECT id FROM usuarios WHERE rol = 'empresa' LIMIT 1");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$result) { echo "ERROR: No hay usuario empresa.\n"; exit(1); }
$USER_ID = $result['id'];

echo "=== Seed Riego (Notion → BD) ===\n";
echo "Usuario empresa: ID={$USER_ID}\n\n";

// ── Cargar cache de parcelas (nombre → id) ──────────────────────────────
$cacheParcelas = [];
$res = $db->query("SELECT id, LOWER(nombre) as nombre FROM parcelas WHERE id_user = {$USER_ID}");
while ($row = $res->fetch_assoc()) {
    $cacheParcelas[$row['nombre']] = $row['id'];
}
echo "Parcelas en BD: " . count($cacheParcelas) . "\n";

// ── Función para limpiar nombre de Notion ───────────────────────────────
function limpiarNombre($texto) {
    $texto = trim($texto);
    $texto = preg_replace('/\s*\(https?:\/\/www\.notion\.so\/[^)]+\)/', '', $texto);
    return trim($texto, " \t\n\r\0\x0B\"");
}

// ── Función para buscar parcela por nombre ──────────────────────────────
function buscarParcela($nombre, &$cache) {
    $nombreLower = mb_strtolower(trim($nombre));
    if (empty($nombreLower)) return null;
    if (isset($cache[$nombreLower])) return $cache[$nombreLower];

    // Coincidencia parcial
    foreach ($cache as $key => $id) {
        if (similar_text($nombreLower, $key) / max(mb_strlen($nombreLower), mb_strlen($key)) > 0.85) {
            return $id;
        }
    }
    return null;
}

// ── Parsear múltiples parcelas ──────────────────────────────────────────
function parsearParcelas($campo) {
    if (empty(trim($campo))) return [];
    $nombres = [];
    preg_match_all('/([^,]+(?:\([^)]*\))?)/', $campo, $matches);
    foreach ($matches[1] as $match) {
        $nombre = limpiarNombre($match);
        if (!empty($nombre)) $nombres[] = $nombre;
    }
    return $nombres;
}

// ── Leer CSV ────────────────────────────────────────────────────────────
$csvFile = glob(BASE_PATH . '/notion/Riego/*_all.csv')[0] ?? '';
if (!$csvFile || !file_exists($csvFile)) {
    echo "ERROR: No se encontró el CSV de riego.\n";
    exit(1);
}

$handle = fopen($csvFile, 'r');
$cabecera = fgetcsv($handle);
$cabecera[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cabecera[0]);

$filas = [];
while (($fila = fgetcsv($handle)) !== false) {
    if (count($fila) === count($cabecera)) {
        $filas[] = array_combine($cabecera, $fila);
    }
}
fclose($handle);

echo "Registros de riego en CSV: " . count($filas) . "\n\n";

// ── Preparar statements ─────────────────────────────────────────────────
$stmtCheck = $db->prepare("SELECT id FROM riegos WHERE hidrante = ? AND fecha_ini = ? AND fecha_fin = ? AND id_user = ? LIMIT 1");
$stmtInsert = $db->prepare("INSERT INTO riegos (hidrante, propiedad, parcela_id, cantidad_fin, cantidad_ini, dias, fecha_fin, fecha_ini, total_m3, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtRelParc = $db->prepare("INSERT INTO riego_parcelas (riego_id, parcela_id) VALUES (?, ?)");

$importados = 0;
$saltados = 0;
$sinParcela = 0;

foreach ($filas as $r) {
    $hidrante    = trim($r['Hidrante'] ?? '');
    $propiedad   = trim($r['Nombre'] ?? '');
    $cantidadFin = floatval($r['cantidad fin'] ?? 0);
    $cantidadIni = floatval($r['cantidad ini'] ?? 0);
    $dias        = intval($r['dias'] ?? 0);
    $totalM3     = floatval($r['total m3'] ?? 0);

    // Parsear fechas: "25/06/2024" → "2024-06-25"
    $fechaIni = null;
    $fechaFin = null;
    if (!empty($r['fecha ini'])) {
        $dt = DateTime::createFromFormat('d/m/Y', trim($r['fecha ini']));
        if ($dt) $fechaIni = $dt->format('Y-m-d');
    }
    if (!empty($r['fecha fin'])) {
        $dt = DateTime::createFromFormat('d/m/Y', trim($r['fecha fin']));
        if ($dt) $fechaFin = $dt->format('Y-m-d');
    }

    if (!$fechaIni || !$fechaFin) {
        $saltados++;
        continue;
    }

    // Verificar duplicado
    $stmtCheck->bind_param("sssi", $hidrante, $fechaIni, $fechaFin, $USER_ID);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->fetch_assoc()) {
        $saltados++;
        continue;
    }

    // Resolver parcelas
    $nombresParcelas = parsearParcelas($r['Parcela'] ?? '');
    $parcelaIds = [];
    foreach ($nombresParcelas as $nomParc) {
        $pid = buscarParcela($nomParc, $cacheParcelas);
        if ($pid) $parcelaIds[] = $pid;
    }

    // La primera parcela va como parcela_id principal del riego
    $parcelaIdPrincipal = !empty($parcelaIds) ? $parcelaIds[0] : null;

    if (!$parcelaIdPrincipal) {
        $sinParcela++;
    }

    // Insertar riego
    $stmtInsert->bind_param("ssiddissdi",
        $hidrante, $propiedad, $parcelaIdPrincipal,
        $cantidadFin, $cantidadIni, $dias,
        $fechaFin, $fechaIni, $totalM3, $USER_ID
    );
    $stmtInsert->execute();
    $riegoId = $stmtInsert->insert_id;

    // Si hay múltiples parcelas, vincular via riego_parcelas
    if (count($parcelaIds) > 1) {
        foreach ($parcelaIds as $pid) {
            $stmtRelParc->bind_param("ii", $riegoId, $pid);
            $stmtRelParc->execute();
        }
    }

    $importados++;
}

$stmtCheck->close();
$stmtInsert->close();
$stmtRelParc->close();

echo "═══════════════════════════════════════════════════════════════\n";
echo "  RESUMEN\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Riegos importados: {$importados}\n";
echo "  Saltados (duplicados/sin fecha): {$saltados}\n";
echo "  Sin parcela encontrada: {$sinParcela}\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n¡Importación de riegos completada!\n";
