#!/usr/bin/env php
<?php
/**
 * Seed de la base de datos con datos exportados de Notion
 *
 * Este script importa tareas históricas, trabajadores, parcelas, trabajos y vehículos
 * desde los CSVs exportados de Notion a la base de datos MySQL.
 *
 * REGLAS:
 * - No duplica registros: busca por nombre antes de insertar
 * - Las tareas se importan como "realizada" con fecha
 * - Las relaciones N:M (tarea_trabajadores, tarea_parcelas, tarea_trabajos)
 *   se crean con horas=0 y precio=0 para no generar deuda ni gastos
 * - Los nombres de Notion vienen con URLs que se limpian automáticamente
 *
 * USO:
 *   php database/seed_notion.php
 *
 * Requiere conexión a la base de datos configurada en .env
 */

// ── Cargar el entorno de la aplicación ──────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Conectar a la base de datos
require BASE_PATH . '/config/database.php';
$db = \Database::connect();
$db->set_charset('utf8mb4');

// ── Configuración ───────────────────────────────────────────────────────
// ID del usuario propietario (empresa). Cambiar si es diferente.
// Intentar detectarlo automáticamente buscando un usuario con rol 'empresa'
$stmt = $db->prepare("SELECT id FROM usuarios WHERE rol = 'empresa' LIMIT 1");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    echo "ERROR: No se encontró ningún usuario con rol 'empresa'.\n";
    echo "Asegúrate de tener al menos un usuario empresa en la BD.\n";
    exit(1);
}

$USER_ID = $result['id'];
echo "=== Seed Notion → Base de datos ===\n";
echo "Usuario empresa detectado: ID={$USER_ID}\n\n";

// Carpeta con los CSVs de Notion
$NOTION_DIR = BASE_PATH . '/notion';

// ── Funciones auxiliares ────────────────────────────────────────────────

/**
 * Limpia un nombre de Notion quitando la URL entre paréntesis.
 * "Pedro Carmona Díaz (https://www.notion.so/...)" → "Pedro Carmona Díaz"
 */
function limpiarNombre($texto) {
    $texto = trim($texto);
    // Quitar URL de Notion entre paréntesis
    $texto = preg_replace('/\s*\(https?:\/\/www\.notion\.so\/[^)]+\)/', '', $texto);
    // Limpiar comillas y espacios extra
    $texto = trim($texto, " \t\n\r\0\x0B\"");
    return $texto;
}

/**
 * Parsea un campo CSV de Notion que puede contener múltiples valores separados por coma.
 * Cada valor puede tener una URL de Notion entre paréntesis.
 * Devuelve un array de nombres limpios.
 */
function parsearMultiple($campo) {
    if (empty(trim($campo))) return [];

    $nombres = [];
    // Dividir por comas, pero respetando las URLs entre paréntesis
    // Patrón: nombre (url), nombre (url), ...
    preg_match_all('/([^,]+(?:\([^)]*\))?)/', $campo, $matches);

    foreach ($matches[1] as $match) {
        $nombre = limpiarNombre($match);
        if (!empty($nombre)) {
            $nombres[] = $nombre;
        }
    }

    return $nombres;
}

/**
 * Lee un archivo CSV y devuelve un array de arrays asociativos.
 */
function leerCSV($ruta) {
    if (!file_exists($ruta)) {
        echo "  AVISO: No se encontró el archivo: {$ruta}\n";
        return [];
    }

    $filas = [];
    $handle = fopen($ruta, 'r');
    if (!$handle) return [];

    // Leer cabecera
    $cabecera = fgetcsv($handle);
    if (!$cabecera) { fclose($handle); return []; }

    // Limpiar BOM de UTF-8 si existe
    $cabecera[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cabecera[0]);

    while (($fila = fgetcsv($handle)) !== false) {
        if (count($fila) === count($cabecera)) {
            $filas[] = array_combine($cabecera, $fila);
        }
    }

    fclose($handle);
    return $filas;
}

/**
 * Busca o crea un registro en una tabla por nombre.
 * Devuelve el ID (existente o nuevo).
 */
function buscarOCrear($db, $tabla, $nombre, $userId, $datosExtra = []) {
    $nombre = trim($nombre);
    if (empty($nombre)) return null;

    // Buscar existente por nombre (case-insensitive)
    $stmt = $db->prepare("SELECT id FROM {$tabla} WHERE LOWER(nombre) = LOWER(?) AND id_user = ? LIMIT 1");
    $stmt->bind_param("si", $nombre, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        return $row['id'];
    }

    // No existe → crear nuevo
    $campos = ['nombre', 'id_user'];
    $tipos  = 'si';
    $valores = [$nombre, $userId];
    $placeholders = ['?', '?'];

    foreach ($datosExtra as $campo => $valor) {
        $campos[] = $campo;
        $placeholders[] = '?';
        if (is_int($valor)) {
            $tipos .= 'i';
        } elseif (is_float($valor)) {
            $tipos .= 'd';
        } else {
            $tipos .= 's';
        }
        $valores[] = $valor;
    }

    $sql = "INSERT INTO {$tabla} (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($tipos, ...$valores);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    return $id;
}

// ── Contadores para el resumen ──────────────────────────────────────────
$stats = [
    'trabajadores_nuevos' => 0, 'trabajadores_existentes' => 0,
    'trabajos_nuevos' => 0, 'trabajos_existentes' => 0,
    'parcelas_nuevas' => 0, 'parcelas_existentes' => 0,
    'vehiculos_nuevos' => 0, 'vehiculos_existentes' => 0,
    'tareas_importadas' => 0, 'tareas_saltadas' => 0,
    'rel_trabajadores' => 0, 'rel_parcelas' => 0, 'rel_trabajos' => 0,
];

// ═══════════════════════════════════════════════════════════════════════
// PASO 1: Importar TRABAJADORES
// ═══════════════════════════════════════════════════════════════════════
echo "── Paso 1: Trabajadores ────────────────────────────────────\n";
$csvTrabajadores = glob($NOTION_DIR . '/Trabajadores/*_all.csv')[0] ?? '';
$trabajadores = leerCSV($csvTrabajadores);

// Cache: nombre → id
$cacheTrabajadores = [];

foreach ($trabajadores as $t) {
    $nombre = limpiarNombre($t['Nombre'] ?? '');
    if (empty($nombre)) continue;

    // Separar nombre y apellidos (primer token = nombre, resto = apellidos)
    $partes = explode(' ', $nombre, 2);
    $nombrePila = $partes[0];
    $apellidos = $partes[1] ?? '';

    $datosExtra = [];
    if (!empty($t['DNI'])) $datosExtra['dni'] = trim($t['DNI']);
    if (!empty($t['Seguridad Social'])) $datosExtra['ss'] = trim($t['Seguridad Social']);
    if (!empty($apellidos)) $datosExtra['apellidos'] = $apellidos;

    // Buscar por nombre completo (nombre + apellidos)
    $stmt = $db->prepare("SELECT id FROM trabajadores WHERE LOWER(CONCAT(nombre, ' ', COALESCE(apellidos, ''))) = LOWER(?) AND id_user = ? LIMIT 1");
    $stmt->bind_param("si", $nombre, $USER_ID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $cacheTrabajadores[mb_strtolower($nombre)] = $row['id'];
        $stats['trabajadores_existentes']++;
    } else {
        // También buscar solo por nombre de pila + apellidos parciales
        $stmt = $db->prepare("SELECT id FROM trabajadores WHERE LOWER(nombre) = LOWER(?) AND LOWER(COALESCE(apellidos,'')) = LOWER(?) AND id_user = ? LIMIT 1");
        $stmt->bind_param("ssi", $nombrePila, $apellidos, $USER_ID);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $cacheTrabajadores[mb_strtolower($nombre)] = $row['id'];
            $stats['trabajadores_existentes']++;
        } else {
            // Crear nuevo
            $stmt = $db->prepare("INSERT INTO trabajadores (nombre, apellidos, dni, ss, id_user, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
            $dni = !empty($t['DNI']) ? trim($t['DNI']) : null;
            $ss = !empty($t['Seguridad Social']) ? trim($t['Seguridad Social']) : null;
            $stmt->bind_param("ssssi", $nombrePila, $apellidos, $dni, $ss, $USER_ID);
            $stmt->execute();
            $cacheTrabajadores[mb_strtolower($nombre)] = $stmt->insert_id;
            $stmt->close();
            $stats['trabajadores_nuevos']++;
        }
    }
}

echo "  Existentes: {$stats['trabajadores_existentes']}, Nuevos: {$stats['trabajadores_nuevos']}\n\n";

// ═══════════════════════════════════════════════════════════════════════
// PASO 2: Importar TRABAJOS
// ═══════════════════════════════════════════════════════════════════════
echo "── Paso 2: Trabajos ─────────────────────────────────────────\n";
$csvTrabajos = glob($NOTION_DIR . '/Trabajos/*_all.csv')[0] ?? '';
$trabajos = leerCSV($csvTrabajos);

// Cache: nombre → id
$cacheTrabajos = [];

foreach ($trabajos as $t) {
    $nombre = limpiarNombre($t['Nombre'] ?? '');
    if (empty($nombre)) continue;

    $stmt = $db->prepare("SELECT id FROM trabajos WHERE LOWER(nombre) = LOWER(?) AND id_user = ? LIMIT 1");
    $stmt->bind_param("si", $nombre, $USER_ID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $cacheTrabajos[mb_strtolower($nombre)] = $row['id'];
        $stats['trabajos_existentes']++;
    } else {
        $desc = trim($t['Descripcion'] ?? '');
        $stmt = $db->prepare("INSERT INTO trabajos (nombre, descripcion, precio_hora, id_user) VALUES (?, ?, 0, ?)");
        $stmt->bind_param("ssi", $nombre, $desc, $USER_ID);
        $stmt->execute();
        $cacheTrabajos[mb_strtolower($nombre)] = $stmt->insert_id;
        $stmt->close();
        $stats['trabajos_nuevos']++;
    }
}

echo "  Existentes: {$stats['trabajos_existentes']}, Nuevos: {$stats['trabajos_nuevos']}\n\n";

// ═══════════════════════════════════════════════════════════════════════
// PASO 3: Importar PARCELAS
// ═══════════════════════════════════════════════════════════════════════
echo "── Paso 3: Parcelas ─────────────────────────────────────────\n";
$csvParcelas = glob($NOTION_DIR . '/Parcelas/*_all.csv')[0] ?? '';
$parcelas = leerCSV($csvParcelas);

// Cache: nombre → id
$cacheParcelas = [];

foreach ($parcelas as $p) {
    $nombre = limpiarNombre($p['Nombre'] ?? '');
    if (empty($nombre)) continue;

    $stmt = $db->prepare("SELECT id FROM parcelas WHERE LOWER(nombre) = LOWER(?) AND id_user = ? LIMIT 1");
    $stmt->bind_param("si", $nombre, $USER_ID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $cacheParcelas[mb_strtolower($nombre)] = $row['id'];
        $stats['parcelas_existentes']++;
    } else {
        // Extraer datos del CSV
        $olivos = intval($p['Nª Olivos'] ?? 0);
        $hidrante = intval($p['Hidrante'] ?? 0);
        $ubicacion = trim($p['Ubicación'] ?? '');
        $propietario = trim($p['Dueño'] ?? '');
        $refCatastral = trim($p['Referencia Sigpac'] ?? '');
        $variedad = trim($p['Variedad'] ?? '');

        // Mapear "corta" del CSV a enum de la BD
        $cortaCsv = mb_strtolower(trim($p['corta'] ?? ''));
        $corta = null;
        if ($cortaCsv === 'par') $corta = 'par';
        elseif ($cortaCsv === 'impar') $corta = 'impar';
        elseif ($cortaCsv === 'no' || $cortaCsv === 'si') $corta = 'siempre';

        $stmt = $db->prepare("INSERT INTO parcelas (nombre, olivos, hidrante, ubicacion, propietario, referencia_catastral, tipo_olivos, corta, id_user, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '')");
        $stmt->bind_param("siisssssi", $nombre, $olivos, $hidrante, $ubicacion, $propietario, $refCatastral, $variedad, $corta, $USER_ID);
        $stmt->execute();
        $cacheParcelas[mb_strtolower($nombre)] = $stmt->insert_id;
        $stmt->close();
        $stats['parcelas_nuevas']++;
    }
}

echo "  Existentes: {$stats['parcelas_existentes']}, Nuevas: {$stats['parcelas_nuevas']}\n\n";

// ═══════════════════════════════════════════════════════════════════════
// PASO 4: Importar VEHÍCULOS
// ═══════════════════════════════════════════════════════════════════════
echo "── Paso 4: Vehículos ────────────────────────────────────────\n";
$csvVehiculos = glob($NOTION_DIR . '/Vehiculos/*_all.csv')[0] ?? '';
$vehiculos = leerCSV($csvVehiculos);

foreach ($vehiculos as $v) {
    $nombre = limpiarNombre($v['Nombre'] ?? '');
    if (empty($nombre)) continue;

    $stmt = $db->prepare("SELECT id FROM vehiculos WHERE LOWER(nombre) = LOWER(?) AND id_user = ? LIMIT 1");
    $stmt->bind_param("si", $nombre, $USER_ID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $stats['vehiculos_existentes']++;
    } else {
        $matricula = trim($v['Matricula'] ?? '') ?: null;

        // Parsear precio seguro: "108,15 €" → 108.15
        $precioSeguro = null;
        if (!empty($v['Precio seguro'])) {
            $precioStr = str_replace(['€', ' ', '.'], '', $v['Precio seguro']);
            $precioStr = str_replace(',', '.', $precioStr);
            $precioSeguro = floatval($precioStr) ?: null;
        }

        $seguro = trim($v['Seguro'] ?? '') ?: null;

        // Parsear fecha matriculación: "30/12/1994" → "1994-12-30"
        $fechaMatr = null;
        if (!empty($v['Fecha de matriculacion'])) {
            $dt = DateTime::createFromFormat('d/m/Y', trim($v['Fecha de matriculacion']));
            if ($dt) $fechaMatr = $dt->format('Y-m-d');
        }

        // Parsear fecha ITV: "15 de noviembre de 2024" → "2024-11-15"
        $fechaItv = null;
        if (!empty($v['pasa la itv'])) {
            $meses = [
                'enero'=>1,'febrero'=>2,'marzo'=>3,'abril'=>4,'mayo'=>5,'junio'=>6,
                'julio'=>7,'agosto'=>8,'septiembre'=>9,'octubre'=>10,'noviembre'=>11,'diciembre'=>12
            ];
            $itvStr = mb_strtolower(trim($v['pasa la itv']));
            if (preg_match('/(\d+)\s+de\s+(\w+)\s+de\s+(\d{4})/', $itvStr, $m)) {
                $mesNum = $meses[$m[2]] ?? null;
                if ($mesNum) {
                    $fechaItv = sprintf('%04d-%02d-%02d', $m[3], $mesNum, $m[1]);
                }
            }
        }

        $stmt = $db->prepare("INSERT INTO vehiculos (nombre, matricula, precio_seguro, fecha_matriculacion, seguro, pasa_itv, id_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssi", $nombre, $matricula, $precioSeguro, $fechaMatr, $seguro, $fechaItv, $USER_ID);
        $stmt->execute();
        $stmt->close();
        $stats['vehiculos_nuevos']++;
    }
}

echo "  Existentes: {$stats['vehiculos_existentes']}, Nuevos: {$stats['vehiculos_nuevos']}\n\n";

// ═══════════════════════════════════════════════════════════════════════
// PASO 5: Importar TAREAS (lo más complejo)
// ═══════════════════════════════════════════════════════════════════════
echo "── Paso 5: Tareas (esto tardará un poco) ────────────────────\n";
$csvTareas = glob($NOTION_DIR . '/tareas/*_all.csv')[0] ?? '';
$tareas = leerCSV($csvTareas);

echo "  Tareas a procesar: " . count($tareas) . "\n";

// Función auxiliar para buscar en cache con coincidencia parcial
function buscarEnCache($nombre, &$cache) {
    $nombreLower = mb_strtolower(trim($nombre));
    if (empty($nombreLower)) return null;

    // Coincidencia exacta
    if (isset($cache[$nombreLower])) return $cache[$nombreLower];

    // Coincidencia parcial (por si hay espacios extra o tildes diferentes)
    foreach ($cache as $key => $id) {
        if (similar_text($nombreLower, $key) / max(mb_strlen($nombreLower), mb_strlen($key)) > 0.85) {
            return $id;
        }
    }

    return null;
}

// Preparar statements para las relaciones
$stmtTarea = $db->prepare("INSERT INTO tareas (titulo, descripcion, fecha, horas, estado, id_user) VALUES (?, ?, ?, ?, 'realizada', ?)");
$stmtRelTrab = $db->prepare("INSERT INTO tarea_trabajadores (tarea_id, trabajador_id, horas_asignadas) VALUES (?, ?, 0)");
$stmtRelParc = $db->prepare("INSERT INTO tarea_parcelas (tarea_id, parcela_id) VALUES (?, ?)");
$stmtRelTrabajo = $db->prepare("INSERT INTO tarea_trabajos (tarea_id, trabajo_id, horas_trabajo, precio_hora) VALUES (?, ?, 0, 0)");

// Verificar duplicados: no importar tareas con misma fecha + titulo
$stmtCheckDup = $db->prepare("SELECT id FROM tareas WHERE titulo = ? AND fecha = ? AND id_user = ? LIMIT 1");

$batchSize = 100;
$processed = 0;

foreach ($tareas as $t) {
    $titulo = limpiarNombre($t['Nombre'] ?? '');
    $descripcion = trim($t['Description'] ?? '');
    $horasStr = trim($t['Horas'] ?? '0');
    $horas = floatval(str_replace(',', '.', $horasStr));

    if (empty($titulo)) {
        $stats['tareas_saltadas']++;
        continue;
    }

    // Parsear fecha: "25/06/2024" → "2024-06-25"
    $fecha = null;
    if (!empty($t['Fecha'])) {
        $dt = DateTime::createFromFormat('d/m/Y', trim($t['Fecha']));
        if ($dt) $fecha = $dt->format('Y-m-d');
    }

    // Verificar duplicado
    if ($fecha) {
        $stmtCheckDup->bind_param("ssi", $titulo, $fecha, $USER_ID);
        $stmtCheckDup->execute();
        $dupRow = $stmtCheckDup->get_result()->fetch_assoc();
        if ($dupRow) {
            $stats['tareas_saltadas']++;
            continue;
        }
    }

    // Crear la tarea (horas se guardan para referencia histórica, pero no generan deuda)
    $stmtTarea->bind_param("sssdi", $titulo, $descripcion, $fecha, $horas, $USER_ID);
    $stmtTarea->execute();
    $tareaId = $stmtTarea->insert_id;

    if (!$tareaId) {
        $stats['tareas_saltadas']++;
        continue;
    }

    $stats['tareas_importadas']++;

    // ── Relaciones: Trabajadores ──
    $nombresTrabajadores = parsearMultiple($t['Trabajador'] ?? '');
    foreach ($nombresTrabajadores as $nomTrab) {
        $trabId = buscarEnCache($nomTrab, $cacheTrabajadores);
        if (!$trabId) {
            // Crear trabajador al vuelo
            $partes = explode(' ', $nomTrab, 2);
            $stmt2 = $db->prepare("INSERT INTO trabajadores (nombre, apellidos, id_user, estado) VALUES (?, ?, ?, 'activo')");
            $ap = $partes[1] ?? '';
            $stmt2->bind_param("ssi", $partes[0], $ap, $USER_ID);
            $stmt2->execute();
            $trabId = $stmt2->insert_id;
            $stmt2->close();
            $cacheTrabajadores[mb_strtolower($nomTrab)] = $trabId;
        }
        $stmtRelTrab->bind_param("ii", $tareaId, $trabId);
        $stmtRelTrab->execute();
        $stats['rel_trabajadores']++;
    }

    // ── Relaciones: Parcelas ──
    $nombresParcelas = parsearMultiple($t['Parcela'] ?? '');
    foreach ($nombresParcelas as $nomParc) {
        $parcId = buscarEnCache($nomParc, $cacheParcelas);
        if (!$parcId) {
            // Crear parcela al vuelo
            $stmt2 = $db->prepare("INSERT INTO parcelas (nombre, olivos, hidrante, ubicacion, propietario, id_user, descripcion) VALUES (?, 0, 0, '', '', ?, '')");
            $stmt2->bind_param("si", $nomParc, $USER_ID);
            $stmt2->execute();
            $parcId = $stmt2->insert_id;
            $stmt2->close();
            $cacheParcelas[mb_strtolower($nomParc)] = $parcId;
        }
        $stmtRelParc->bind_param("ii", $tareaId, $parcId);
        $stmtRelParc->execute();
        $stats['rel_parcelas']++;
    }

    // ── Relaciones: Trabajos ──
    $nombresTrabajos = parsearMultiple($t['Trabajo'] ?? '');
    foreach ($nombresTrabajos as $nomTrabajo) {
        $trabajoId = buscarEnCache($nomTrabajo, $cacheTrabajos);
        if (!$trabajoId) {
            // Crear trabajo al vuelo
            $stmt2 = $db->prepare("INSERT INTO trabajos (nombre, descripcion, precio_hora, id_user) VALUES (?, '', 0, ?)");
            $stmt2->bind_param("si", $nomTrabajo, $USER_ID);
            $stmt2->execute();
            $trabajoId = $stmt2->insert_id;
            $stmt2->close();
            $cacheTrabajos[mb_strtolower($nomTrabajo)] = $trabajoId;
        }
        $stmtRelTrabajo->bind_param("ii", $tareaId, $trabajoId);
        $stmtRelTrabajo->execute();
        $stats['rel_trabajos']++;
    }

    // Progreso
    $processed++;
    if ($processed % $batchSize === 0) {
        echo "  Procesadas: {$processed}/" . count($tareas) . "\n";
    }
}

$stmtTarea->close();
$stmtRelTrab->close();
$stmtRelParc->close();
$stmtRelTrabajo->close();
$stmtCheckDup->close();

echo "  Tareas importadas: {$stats['tareas_importadas']}, Saltadas (duplicadas/vacías): {$stats['tareas_saltadas']}\n";
echo "  Relaciones creadas → Trabajadores: {$stats['rel_trabajadores']}, Parcelas: {$stats['rel_parcelas']}, Trabajos: {$stats['rel_trabajos']}\n\n";

// ═══════════════════════════════════════════════════════════════════════
// RESUMEN FINAL
// ═══════════════════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RESUMEN DE IMPORTACIÓN\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Trabajadores: {$stats['trabajadores_existentes']} existentes + {$stats['trabajadores_nuevos']} nuevos\n";
echo "  Trabajos:     {$stats['trabajos_existentes']} existentes + {$stats['trabajos_nuevos']} nuevos\n";
echo "  Parcelas:     {$stats['parcelas_existentes']} existentes + {$stats['parcelas_nuevas']} nuevas\n";
echo "  Vehículos:    {$stats['vehiculos_existentes']} existentes + {$stats['vehiculos_nuevos']} nuevos\n";
echo "  Tareas:       {$stats['tareas_importadas']} importadas, {$stats['tareas_saltadas']} saltadas\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n¡Importación completada!\n";
echo "NOTA: Las tareas se importaron como 'realizada' con horas/precios=0\n";
echo "      para no generar deuda ni gastos en la economía actual.\n";
