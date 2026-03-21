<?php

namespace App\Models;

/**
 * Modelo para gestión de recordatorios y notificaciones.
 * Genera alertas automáticas (ITV, cuentas, fitosanitarios)
 * y permite crear recordatorios personalizados.
 */
class Recordatorio
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    /**
     * Obtener recordatorios activos y no leídos del usuario
     * (hasta 30 días en el futuro)
     */
    public function getActivos($userId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM recordatorios
            WHERE id_user = ? AND activo = 1 AND leido = 0
              AND fecha_aviso <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY fecha_aviso ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Obtener todos los recordatorios del usuario (para el perfil)
     */
    public function getAll($userId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM recordatorios
            WHERE id_user = ?
            ORDER BY fecha_aviso DESC
            LIMIT 100
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Crear un recordatorio personalizado
     */
    public function crear($userId, $titulo, $descripcion, $fechaAviso, $repeticion = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO recordatorios (id_user, tipo, titulo, descripcion, fecha_aviso, repeticion)
            VALUES (?, 'personalizado', ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $userId, $titulo, $descripcion, $fechaAviso, $repeticion);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Marcar como leído. Si tiene repetición, genera el siguiente automáticamente.
     */
    public function marcarLeido($id, $userId)
    {
        // Obtener datos del recordatorio antes de marcarlo
        $stmt = $this->db->prepare("SELECT titulo, descripcion, fecha_aviso, repeticion FROM recordatorios WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $rec = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Marcar como leído
        $stmt = $this->db->prepare("UPDATE recordatorios SET leido = 1 WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        // Si tiene repetición, crear el siguiente recordatorio
        if ($ok && $rec && !empty($rec['repeticion'])) {
            $fechaBase = $rec['fecha_aviso'];
            $rep = $rec['repeticion'];

            if ($rep === 'mensual') {
                $nuevaFecha = date('Y-m-d', strtotime($fechaBase . ' +1 month'));
            } elseif ($rep === 'anual') {
                $nuevaFecha = date('Y-m-d', strtotime($fechaBase . ' +1 year'));
            } elseif (ctype_digit($rep)) {
                $nuevaFecha = date('Y-m-d', strtotime($fechaBase . ' +' . intval($rep) . ' days'));
            } else {
                return $ok;
            }

            $this->crear($userId, $rec['titulo'], $rec['descripcion'], $nuevaFecha, $rep);
        }

        return $ok;
    }

    /**
     * Eliminar recordatorio
     */
    public function eliminar($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM recordatorios WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $this->db->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Contar recordatorios pendientes (para el badge del header)
     */
    public function contarPendientes($userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM recordatorios
            WHERE id_user = ? AND activo = 1 AND leido = 0
              AND fecha_aviso <= CURDATE()
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return intval($row['total'] ?? 0);
    }

    // ── Generación automática de recordatorios ──────────────────────────

    /**
     * Generar recordatorios automáticos de ITV de vehículos.
     * Crea recordatorios para vehículos cuya ITV vence en los próximos N días
     * si no existe ya un recordatorio para ese vehículo y fecha.
     */
    public function generarITV($userId, $diasAntelacion = 15)
    {
        $creados = 0;

        $stmt = $this->db->prepare("
            SELECT id, nombre, pasa_itv FROM vehiculos
            WHERE id_user = ? AND pasa_itv IS NOT NULL
              AND pasa_itv BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ");
        $stmt->bind_param("ii", $userId, $diasAntelacion);
        $stmt->execute();
        $vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($vehiculos as $v) {
            // Comprobar si ya existe recordatorio para este vehículo y fecha
            $check = $this->db->prepare("
                SELECT id FROM recordatorios
                WHERE id_user = ? AND tipo = 'itv' AND entidad_id = ? AND fecha_referencia = ?
            ");
            $check->bind_param("iis", $userId, $v['id'], $v['pasa_itv']);
            $check->execute();
            $existe = $check->get_result()->fetch_assoc();
            $check->close();

            if (!$existe) {
                $titulo = 'ITV ' . $v['nombre'];
                $desc = 'La ITV de ' . $v['nombre'] . ' vence el ' . date('d/m/Y', strtotime($v['pasa_itv']));
                // Avisar con antelación
                $fechaAviso = date('Y-m-d', strtotime($v['pasa_itv'] . " -{$diasAntelacion} days"));
                if ($fechaAviso < date('Y-m-d')) $fechaAviso = date('Y-m-d');

                $ins = $this->db->prepare("
                    INSERT INTO recordatorios (id_user, tipo, titulo, descripcion, fecha_aviso, fecha_referencia, entidad_id)
                    VALUES (?, 'itv', ?, ?, ?, ?, ?)
                ");
                $ins->bind_param("issssi", $userId, $titulo, $desc, $fechaAviso, $v['pasa_itv'], $v['id']);
                $ins->execute();
                $ins->close();
                $creados++;
            }
        }

        return $creados;
    }

    /**
     * Generar recordatorio de cierre de cuentas del mes anterior
     * si no se ha cerrado aún (no hay registros en pagos_mensuales_trabajadores)
     */
    public function generarCuentas($userId)
    {
        $mesAnterior = (int) date('n', strtotime('first day of last month'));
        $anioAnterior = (int) date('Y', strtotime('first day of last month'));
        $nombreMes = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'
        ];

        // Comprobar si hay pagos del mes anterior
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM pagos_mensuales_trabajadores
            WHERE id_user = ? AND month = ? AND year = ?
        ");
        $stmt->bind_param("iii", $userId, $mesAnterior, $anioAnterior);
        $stmt->execute();
        $hay = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();

        if ($hay > 0) return 0; // Ya está cerrado

        // Comprobar si ya existe este recordatorio
        $clave = "cuentas-{$mesAnterior}-{$anioAnterior}";
        $stmt = $this->db->prepare("
            SELECT id FROM recordatorios
            WHERE id_user = ? AND tipo = 'cuentas' AND titulo = ?
        ");
        $stmt->bind_param("is", $userId, $clave);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existe) return 0;

        $titulo = $clave;
        $desc = "Las cuentas de {$nombreMes[$mesAnterior]} {$anioAnterior} no se han cerrado todavía.";
        $fechaAviso = date('Y-m-d', strtotime('first day of this month'));

        $ins = $this->db->prepare("
            INSERT INTO recordatorios (id_user, tipo, titulo, descripcion, fecha_aviso)
            VALUES (?, 'cuentas', ?, ?, ?)
        ");
        $ins->bind_param("isss", $userId, $titulo, $desc, $fechaAviso);
        $ins->execute();
        $ins->close();
        return 1;
    }

    /**
     * Generar recordatorio de enviar jornadas reales a la gestoría.
     * Se crea al inicio de cada mes si hubo tareas con trabajadores el mes anterior.
     */
    public function generarJornadas($userId)
    {
        $diaActual = (int) date('j');
        $diasEnMes = (int) date('t');
        $nombreMes = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'
        ];

        // Ventana de aviso: últimos 2 días del mes → aviso del mes actual
        //                   primeros 5 días del mes → aviso del mes anterior
        //                   fuera de esta ventana → no generar nada
        if ($diaActual >= ($diasEnMes - 1)) {
            // Últimos 2 días: recordatorio del mes actual (preaviso)
            $mesObjetivo = (int) date('n');
            $anioObjetivo = (int) date('Y');
        } elseif ($diaActual <= 5) {
            // Primeros 5 días: recordatorio del mes anterior
            $mesObjetivo = (int) date('n', strtotime('first day of last month'));
            $anioObjetivo = (int) date('Y', strtotime('first day of last month'));
        } else {
            // Fuera de ventana, no generar recordatorio
            return 0;
        }

        // Comprobar si hubo tareas con trabajadores asignados en el mes objetivo
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM tareas t
            INNER JOIN tarea_trabajadores tt ON t.id = tt.tarea_id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mesObjetivo, $anioObjetivo);
        $stmt->execute();
        $hayTareas = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();

        if ($hayTareas === 0) return 0;

        // Comprobar si ya existe este recordatorio
        $clave = "jornadas-{$mesObjetivo}-{$anioObjetivo}";
        $stmt = $this->db->prepare("
            SELECT id FROM recordatorios
            WHERE id_user = ? AND tipo = 'jornadas' AND titulo = ?
        ");
        $stmt->bind_param("is", $userId, $clave);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existe) return 0;

        $titulo = $clave;
        $desc = "Enviar las jornadas reales de {$nombreMes[$mesObjetivo]} {$anioObjetivo} a la gestoría.";
        $fechaAviso = date('Y-m-d');

        $ins = $this->db->prepare("
            INSERT INTO recordatorios (id_user, tipo, titulo, descripcion, fecha_aviso)
            VALUES (?, 'jornadas', ?, ?, ?)
        ");
        $ins->bind_param("isss", $userId, $titulo, $desc, $fechaAviso);
        $ins->execute();
        $ins->close();
        return 1;
    }

    // ── Configuración de notificaciones ─────────────────────────────────

    /**
     * Obtener la configuración del usuario (o crear defaults)
     */
    public function getConfig($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM notificaciones_config WHERE id_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Si no hay config, crear defaults
        if (empty($rows)) {
            $defaults = [
                ['itv', 1, 15],
                ['cuentas', 1, 1],
                ['fitosanitario', 1, 7],
                ['jornadas', 1, 1],
                ['personalizado', 1, 0],
            ];
            foreach ($defaults as $d) {
                $ins = $this->db->prepare("
                    INSERT IGNORE INTO notificaciones_config (id_user, tipo, activo, dias_antelacion)
                    VALUES (?, ?, ?, ?)
                ");
                $ins->bind_param("isii", $userId, $d[0], $d[1], $d[2]);
                $ins->execute();
                $ins->close();
            }
            // Recargar
            return $this->getConfig($userId);
        }

        // Indexar por tipo
        $config = [];
        foreach ($rows as $r) {
            $config[$r['tipo']] = $r;
        }
        return $config;
    }

    /**
     * Activar/desactivar un tipo de notificación
     */
    public function toggleConfig($userId, $tipo, $activo)
    {
        $stmt = $this->db->prepare("
            UPDATE notificaciones_config
            SET activo = ?
            WHERE id_user = ? AND tipo = ?
        ");
        $activo = $activo ? 1 : 0;
        $stmt->bind_param("iis", $activo, $userId, $tipo);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
