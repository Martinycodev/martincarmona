<?php

error_reporting(E_ALL);

// Definir la ruta base del proyecto
define('BASE_PATH', __DIR__);

// Cargar Composer y .env antes que nada (así config.php ya tiene acceso a $_ENV)
require_once BASE_PATH . '/vendor/autoload.php';
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Mostrar errores solo en desarrollo
ini_set('display_errors', ($_ENV['APP_ENV'] ?? 'production') === 'development' ? 1 : 0);

// Cargar configuración
$config = require_once BASE_PATH . '/config/config.php';
define('APP_BASE_PATH', $config['base_path']);

// Configurar e iniciar sesión de forma segura (timeout, cookies httponly, regeneración de ID)
require_once BASE_PATH . '/config/session.php';
SessionConfig::configure();

// Cargar el autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Crear instancia del autoloader
$autoloader = new Core\Autoloader();

// Registrar el autoloader
$autoloader->register();

// Configurar los namespaces
$autoloader->addNamespace('Core', BASE_PATH . '/core');
$autoloader->addNamespace('App', BASE_PATH . '/app');

// Crear instancia del router
$router = new Core\Router();

// Definir las rutas
$router->get('/', 'HomeController@index');

// Rutas de autenticación
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Rutas de perfil
$router->get('/perfil', 'PerfilController@index');
$router->post('/perfil/actualizarNombre', 'PerfilController@actualizarNombre');

// Rutas protegidas (requieren login)
$router->get('/datos/trabajadores', 'TrabajadoresController@index');
$router->get('/datos/trabajos', 'TrabajosController@index');
$router->get('/datos/vehiculos', 'VehiculosController@index');
$router->get('/datos/herramientas', 'HerramientasController@index');
$router->get('/datos/empresas', 'EmpresasController@index');
$router->get('/datos/parcelas', 'ParcelasController@index');
$router->get('/datos/proveedores', 'ProveedoresController@index');
$router->get('/dashboard', 'DashboardController@index');
$router->get('/datos', 'DatosController@index');
$router->get('/tareas', 'TareasController@index');
$router->get('/tareas/crear', 'TareasController@crear');
$router->post('/tareas/crear', 'TareasController@crear');
$router->post('/tareas/crearVacio', 'TareasController@crearVacio');
$router->post('/tareas/subirImagen', 'TareasController@subirImagen');
$router->post('/tareas/eliminarImagen', 'TareasController@eliminarImagen');
$router->get('/tareas/obtener', 'TareasController@obtener');
$router->post('/tareas/actualizar', 'TareasController@actualizar');
$router->post('/tareas/actualizarCampo', 'TareasController@actualizarCampo');
$router->post('/tareas/eliminar', 'TareasController@eliminar');
$router->get('/tareas/obtenerPorMes', 'TareasController@obtenerPorMes');

// Rutas para búsqueda avanzada
$router->get('/busqueda', 'BusquedaController@index');
$router->get('/busqueda/buscar', 'BusquedaController@buscar');

// Rutas para reportes y estadísticas
$router->get('/reportes', 'ReportesController@index');
$router->get('/reportes/personal', 'ReportesController@personal');
$router->get('/reportes/parcelas', 'ReportesController@parcelas');
$router->get('/reportes/trabajos', 'ReportesController@trabajos');
$router->get('/reportes/economia', 'ReportesController@economia');
$router->get('/reportes/recursos', 'ReportesController@recursos');
$router->get('/reportes/proveedores', 'ReportesController@proveedores');

// Rutas para economía
$router->get('/economia', 'EconomiaController@index');
$router->post('/economia/crear', 'EconomiaController@crear');
$router->post('/economia/editar', 'EconomiaController@editar');
$router->post('/economia/eliminar', 'EconomiaController@eliminar');
$router->get('/economia/obtener', 'EconomiaController@obtener');
$router->get('/economia/buscar', 'EconomiaController@buscar');
$router->get('/economia/obtenerProveedores', 'EconomiaController@obtenerProveedores');
$router->get('/economia/obtenerTrabajadores', 'EconomiaController@obtenerTrabajadores');
$router->get('/economia/obtenerVehiculos', 'EconomiaController@obtenerVehiculos');
$router->get('/economia/obtenerParcelas', 'EconomiaController@obtenerParcelas');
$router->get('/economia/obtenerResumen', 'EconomiaController@obtenerResumen');

// Rutas para edición inline de relaciones en el modal de tareas
$router->post('/tareas/agregarTrabajador', 'TareasController@agregarTrabajador');
$router->post('/tareas/quitarTrabajador', 'TareasController@quitarTrabajador');
$router->post('/tareas/asignarCuadrilla', 'TareasController@asignarCuadrilla');
$router->post('/tareas/agregarParcela', 'TareasController@agregarParcela');
$router->post('/tareas/quitarParcela', 'TareasController@quitarParcela');
$router->post('/tareas/cambiarTrabajo', 'TareasController@cambiarTrabajo');
$router->get('/tareas/opcionesModal', 'TareasController@opcionesModal');

// Ruta para el autocompletado de parcelas
$router->get('/parcelas/buscar', 'ParcelasController@buscar');
$router->post('/parcelas/crear', 'ParcelasController@crear');
$router->get('/parcelas/obtener', 'ParcelasController@obtener');
$router->post('/parcelas/actualizar', 'ParcelasController@actualizar');
$router->post('/parcelas/eliminar', 'ParcelasController@eliminar');

// Rutas para trabajadores (CRUD + búsqueda)
$router->get('/trabajadores/buscar', 'TrabajadoresController@buscar');
$router->post('/trabajadores/crear', 'TrabajadoresController@crear');
$router->get('/trabajadores/obtener', 'TrabajadoresController@obtener');
$router->post('/trabajadores/actualizar', 'TrabajadoresController@actualizar');
$router->post('/trabajadores/eliminar', 'TrabajadoresController@eliminar');
$router->post('/trabajadores/subirFoto', 'TrabajadoresController@subirFoto');
$router->get('/trabajadores/cuadrilla', 'TrabajadoresController@obtenerCuadrilla');

// Rutas para datos detallados de trabajadores
$router->get('/datos/trabajadores', 'DatosTrabajadoresController@index');
$router->post('/datos/trabajadores/actualizar', 'DatosTrabajadoresController@actualizar');

// Rutas para datos detallados de parcelas
$router->get('/datos/parcelas', 'DatosParcelasController@index');
$router->get('/datos/parcelas/detalle', 'DatosParcelasController@getParcelaDetalle');
$router->post('/datos/parcelas/eliminar', 'DatosParcelasController@eliminar');
$router->get('/datos/parcelas/buscar', 'DatosParcelasController@buscar');

// Rutas para trabajos (CRUD + búsqueda)
$router->get('/trabajos/buscar', 'TrabajosController@buscar');
$router->post('/trabajos/crear', 'TrabajosController@crear');
$router->get('/trabajos/obtener', 'TrabajosController@obtener');
$router->post('/trabajos/actualizar', 'TrabajosController@actualizar');
$router->post('/trabajos/eliminar', 'TrabajosController@eliminar');

// Configurar página 404
$router->notFound(function () {
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página no encontrada</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
                background-color: #1e1e1e;
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 500px;
                margin: 0 auto;
            }
            h1 { color: #dc3545; }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404 - Página no encontrada</h1>
            <p>La página que buscas no existe.</p>
            <a href="<?php echo APP_BASE_PATH; ?>/" class="btn">Volver al inicio</a>
        </div>
    </body>
    </html>';
});

// Ejecutar el router
$router->run();
