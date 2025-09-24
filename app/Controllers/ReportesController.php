<?php

namespace App\Controllers;

class ReportesController extends BaseController {
    
    public function index() {
        // Datos de ejemplo para los reportes
        $data = [
            'title' => 'Reportes y Estadísticas - MartinCarmona.com',
            
            // KPIs principales
            'kpis' => [
                'total_horas_mes' => 247.5,
                'tareas_completadas' => 58,
                'trabajadores_activos' => 12,
                'parcelas_trabajadas' => 8,
                'eficiencia_promedio' => 87.3,
                'costo_total_mes' => 15420.50
            ],
            
            // Datos para gráficos (datos de ejemplo)
            'productividad_semanal' => [
                ['semana' => 'Sem 1', 'horas' => 62.5, 'tareas' => 14],
                ['semana' => 'Sem 2', 'horas' => 58.2, 'tareas' => 12],
                ['semana' => 'Sem 3', 'horas' => 71.8, 'tareas' => 16],
                ['semana' => 'Sem 4', 'horas' => 55.0, 'tareas' => 16]
            ],
            
            // Top trabajadores
            'top_trabajadores' => [
                ['nombre' => 'Juan Pérez', 'horas' => 42.5, 'tareas' => 8, 'eficiencia' => 94.2],
                ['nombre' => 'María García', 'horas' => 38.0, 'tareas' => 9, 'eficiencia' => 91.8],
                ['nombre' => 'Carlos López', 'horas' => 35.5, 'tareas' => 7, 'eficiencia' => 89.1],
                ['nombre' => 'Ana Martín', 'horas' => 33.2, 'tareas' => 6, 'eficiencia' => 87.5],
                ['nombre' => 'Pedro Sánchez', 'horas' => 31.8, 'tareas' => 5, 'eficiencia' => 85.3]
            ],
            
            // Parcelas más productivas
            'top_parcelas' => [
                ['nombre' => 'Pocico (Loli)', 'horas' => 45.2, 'tareas' => 12, 'olivos' => 150, 'roi' => 125.8],
                ['nombre' => 'Pocico (Paco)', 'horas' => 38.5, 'tareas' => 10, 'olivos' => 120, 'roi' => 118.3],
                ['nombre' => 'Pocico (Pedro) plantones', 'horas' => 42.1, 'tareas' => 8, 'olivos' => 200, 'roi' => 112.7],
                ['nombre' => 'Pocico tierra', 'horas' => 28.3, 'tareas' => 6, 'olivos' => 0, 'roi' => 95.2]
            ],
            
            // Tipos de trabajo más frecuentes
            'trabajos_frecuentes' => [
                ['tipo' => 'Carga paja', 'cantidad' => 15, 'horas_total' => 48.5, 'promedio' => 3.2],
                ['tipo' => 'Carga palos', 'cantidad' => 12, 'horas_total' => 36.0, 'promedio' => 3.0],
                ['tipo' => 'Poda olivos', 'cantidad' => 8, 'horas_total' => 56.8, 'promedio' => 7.1],
                ['tipo' => 'Recolección', 'cantidad' => 6, 'horas_total' => 42.3, 'promedio' => 7.0]
            ],
            
            // Análisis de costos
            'costos_categorias' => [
                ['categoria' => 'Personal', 'monto' => 8420.50, 'porcentaje' => 54.6],
                ['categoria' => 'Herramientas', 'monto' => 2150.30, 'porcentaje' => 13.9],
                ['categoria' => 'Vehículos', 'monto' => 1890.75, 'porcentaje' => 12.3],
                ['categoria' => 'Proveedores', 'monto' => 2958.95, 'porcentaje' => 19.2]
            ],
            
            // Alertas del sistema
            'alertas' => [
                ['tipo' => 'warning', 'mensaje' => 'Herramienta "Tractor John Deere" requiere mantenimiento en 5 días'],
                ['tipo' => 'info', 'mensaje' => 'Parcela "Pocico tierra" no ha tenido actividad en 10 días'],
                ['tipo' => 'success', 'mensaje' => 'Meta mensual de productividad alcanzada (87.3%)'],
                ['tipo' => 'warning', 'mensaje' => 'Presupuesto mensual al 78% con 5 días restantes']
            ]
        ];
        
        $this->render('reportes/index', $data);
    }
    
    public function personal() {
        // Página específica de análisis de personal
        $this->render('reportes/personal');
    }
    
    public function parcelas() {
        // Página específica de análisis de parcelas
        $this->render('reportes/parcelas');
    }
    
    public function trabajos() {
        // Página específica de análisis de trabajos
        $this->render('reportes/trabajos');
    }
    
    public function economia() {
        // Página específica de análisis económico
        $this->render('reportes/economia');
    }
    
    public function recursos() {
        // Página específica de gestión de recursos
        $this->render('reportes/recursos');
    }
    
    public function proveedores() {
        // Página específica de análisis de proveedores
        $this->render('reportes/proveedores');
    }
}
