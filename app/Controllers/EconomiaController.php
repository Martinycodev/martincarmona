<?php

namespace App\Controllers;

use App\Models\Movimiento;
use App\Models\PagoMensual;
use App\Models\Proveedor;
use App\Models\Trabajador;
use App\Models\Vehiculo;
use App\Models\Parcela;

class EconomiaController extends BaseController
{
    private Movimiento  $movimiento;
    private PagoMensual $pagoMensual;
    private Proveedor   $proveedor;
    private Trabajador  $trabajador;
    private Vehiculo    $vehiculo;
    private Parcela     $parcela;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        $this->movimiento  = new Movimiento();
        $this->pagoMensual = new PagoMensual();
        $this->proveedor   = new Proveedor();
        $this->trabajador  = new Trabajador();
        $this->vehiculo    = new Vehiculo();
        $this->parcela     = new Parcela();
    }

    private function userId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    private function userName(): string
    {
        return $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuario';
    }

    private function user(): array
    {
        return ['id' => $this->userId(), 'name' => $this->userName()];
    }

    // ─── Vistas ───────────────────────────────────────────────────────────────

    /**
     * Dashboard: saldo banco, saldo efectivo, deuda total trabajadores
     */
    public function index(): void
    {
        $resumen        = $this->movimiento->getResumen();
        $deudaPendiente = $this->pagoMensual->getTotalPendiente($this->userId());
        $ultimosGastos  = array_slice($this->movimiento->getAllGastos(), 0, 5);
        $ultimosIngresos = array_slice($this->movimiento->getAllIngresos(), 0, 5);

        $this->render('economia/index', [
            'title'          => 'Economía — Dashboard',
            'user'           => $this->user(),
            'resumen'        => $resumen,
            'deudaPendiente' => $deudaPendiente,
            'ultimosGastos'  => $ultimosGastos,
            'ultimosIngresos' => $ultimosIngresos,
        ]);
    }

    /**
     * CRUD de gastos
     */
    public function gastos(): void
    {
        $gastos      = $this->movimiento->getAllGastos();
        $proveedores = $this->proveedor->getAll($this->userId());
        $vehiculos   = $this->vehiculo->getAll($this->userId());
        $parcelas    = $this->parcela->getAll($this->userId());

        $this->render('economia/gastos', [
            'title'      => 'Economía — Gastos',
            'user'       => $this->user(),
            'gastos'     => $gastos,
            'proveedores'=> $proveedores,
            'vehiculos'  => $vehiculos,
            'parcelas'   => $parcelas,
            'categorias' => Movimiento::CATEGORIAS_GASTO,
            'labels'     => Movimiento::LABELS_CATEGORIA,
        ]);
    }

    /**
     * CRUD de ingresos
     */
    public function ingresos(): void
    {
        $ingresos = $this->movimiento->getAllIngresos();

        $this->render('economia/ingresos', [
            'title'      => 'Economía — Ingresos',
            'user'       => $this->user(),
            'ingresos'   => $ingresos,
            'categorias' => Movimiento::CATEGORIAS_INGRESO,
            'labels'     => Movimiento::LABELS_CATEGORIA,
        ]);
    }

    /**
     * Deuda acumulada por trabajador (mes actual) + historial de pagos mensuales
     */
    public function deudas_trabajadores(): void
    {
        $deudaMesActual = $this->pagoMensual->getDeudaMesActual($this->userId());
        $pagosPendientes = $this->pagoMensual->getPendientes($this->userId());
        $todosLosPagos   = $this->pagoMensual->getAll($this->userId());

        $this->render('economia/deudas', [
            'title'          => 'Economía — Deudas trabajadores',
            'user'           => $this->user(),
            'deudaMesActual' => $deudaMesActual,
            'pagosPendientes'=> $pagosPendientes,
            'todosLosPagos'  => $todosLosPagos,
            'mesActual'      => (int) date('n'),
            'anioActual'     => (int) date('Y'),
        ]);
    }

    // ─── API JSON ─────────────────────────────────────────────────────────────

    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->validateCsrf();

        $data = $this->extractMovimientoData($_POST);

        if ($this->movimiento->create($data)) {
            $this->json(['success' => true, 'message' => 'Guardado correctamente']);
        } else {
            http_response_code(500);
            $this->json(['success' => false, 'message' => 'Error al guardar']);
        }
    }

    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->validateCsrf();

        $id   = (int) ($_POST['id'] ?? 0);
        $data = $this->extractMovimientoData($_POST);

        if ($this->movimiento->update($id, $data)) {
            $this->json(['success' => true, 'message' => 'Actualizado correctamente']);
        } else {
            http_response_code(500);
            $this->json(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->validateCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($this->movimiento->delete($id)) {
            $this->json(['success' => true, 'message' => 'Eliminado correctamente']);
        } else {
            http_response_code(500);
            $this->json(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    public function obtener(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $row = $this->movimiento->getById($id);

        if ($row) {
            $this->json(['success' => true, 'data' => $row]);
        } else {
            http_response_code(404);
            $this->json(['success' => false, 'message' => 'No encontrado']);
        }
    }

    /**
     * Cerrar mes: genera registros en pagos_mensuales_trabajadores para el mes/año dado
     */
    public function cerrar_mes(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->validateCsrf();

        $month = (int) ($_POST['month'] ?? date('n'));
        $year  = (int) ($_POST['year']  ?? date('Y'));

        $trabajadores = $this->pagoMensual->calcularDeudaMes($month, $year, $this->userId());

        if (empty($trabajadores)) {
            $this->json(['success' => false, 'message' => 'No hay tareas con coste en ese mes']);
            return;
        }

        $creados = 0;
        foreach ($trabajadores as $t) {
            $ok = $this->pagoMensual->upsert(
                $t['trabajador_id'],
                $month,
                $year,
                $t['deuda_calculada'],
                $this->userId()
            );
            if ($ok) $creados++;
        }

        $this->json([
            'success' => true,
            'message' => "Mes cerrado: {$creados} registro(s) generado(s)",
        ]);
    }

    /**
     * Registrar pago: marca un pago mensual como pagado
     */
    public function registrar_pago(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->validateCsrf();

        $id        = (int) ($_POST['id'] ?? 0);
        $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if ($this->pagoMensual->marcarPagado($id, $fechaPago)) {
            $this->json(['success' => true, 'message' => 'Pago registrado correctamente']);
        } else {
            http_response_code(500);
            $this->json(['success' => false, 'message' => 'Error al registrar el pago']);
        }
    }

    // ─── Helper privado ───────────────────────────────────────────────────────

    private function extractMovimientoData(array $post): array
    {
        return [
            'fecha'        => $post['fecha']        ?? date('Y-m-d'),
            'tipo'         => $post['tipo']          ?? 'gasto',
            'concepto'     => htmlspecialchars(trim($post['concepto'] ?? '')),
            'categoria'    => $post['categoria']     ?? 'otros',
            'importe'      => (float) ($post['importe'] ?? 0),
            'cuenta'       => in_array($post['cuenta'] ?? '', ['banco', 'efectivo']) ? $post['cuenta'] : 'banco',
            'proveedor_id' => !empty($post['proveedor_id'])  ? (int) $post['proveedor_id']  : null,
            'trabajador_id'=> !empty($post['trabajador_id']) ? (int) $post['trabajador_id'] : null,
            'vehiculo_id'  => !empty($post['vehiculo_id'])   ? (int) $post['vehiculo_id']   : null,
            'parcela_id'   => !empty($post['parcela_id'])    ? (int) $post['parcela_id']    : null,
            'estado'       => in_array($post['estado'] ?? '', ['pendiente', 'pagado']) ? $post['estado'] : 'pendiente',
        ];
    }
}
