<?php

/**
 * Definición de rutas de la aplicación.
 *
 * Este archivo recibe la instancia del Router y registra todas las rutas.
 * Se carga desde index.php después del bootstrap.
 */

// General
$router->get('/', 'HomeController@index');
$router->get('/sobre-nosotros', 'HomeController@sobreNosotros');
$router->get('/enlaces', 'EnlacesController@index');

// PWA: manifest y service worker (servidos por el router para evitar bloqueo .htaccess)
$router->get('/manifest.json', 'PwaController@manifest');
$router->get('/sw.js', 'PwaController@sw');

// Contacto (pagina independiente)
$router->get('/contacto', 'HomeController@contacto');
$router->post('/contacto/enviar', 'ContactoController@enviar');

// Autenticacion (pagina independiente de login)
$router->get('/login', 'HomeController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Paginas legales
$router->get('/privacidad', 'HomeController@privacidad');
$router->get('/aviso-legal', 'HomeController@avisoLegal');
$router->get('/cookies', 'HomeController@cookies');

// Perfil
$router->get('/perfil', 'PerfilController@index');
$router->post('/perfil/actualizarNombre', 'PerfilController@actualizarNombre');
$router->post('/perfil/cambiarPassword', 'PerfilController@cambiarPassword');

// Gestión de usuarios vinculados (empresa crea usuarios para sus trabajadores/propietarios)
$router->get('/perfil/usuarios', 'PerfilController@usuariosVinculados');
$router->post('/perfil/usuarios/crear', 'PerfilController@crearUsuarioVinculado');
$router->post('/perfil/usuarios/cambiarPassword', 'PerfilController@cambiarPasswordUsuario');
$router->post('/perfil/usuarios/eliminar', 'PerfilController@eliminarUsuarioVinculado');

// Notificaciones / Recordatorios
$router->get('/notificaciones/pendientes', 'NotificacionesController@pendientes');
$router->post('/notificaciones/crear', 'NotificacionesController@crear');
$router->post('/notificaciones/leido', 'NotificacionesController@leido');
$router->post('/notificaciones/eliminar', 'NotificacionesController@eliminar');
$router->get('/notificaciones/config', 'NotificacionesController@config');
$router->post('/notificaciones/toggleConfig', 'NotificacionesController@toggleConfig');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');
$router->get('/datos', 'DashboardController@datos');

// Búsqueda avanzada
$router->get('/busqueda', 'BusquedaController@index');
$router->get('/busqueda/buscar', 'BusquedaController@buscar');

// Reportes
$router->get('/reportes', 'ReportesController@index');
$router->get('/reportes/personal', 'ReportesController@personal');
$router->get('/reportes/parcelas', 'ReportesController@parcelas');
$router->get('/reportes/trabajos', 'ReportesController@trabajos');
$router->get('/reportes/economia', 'ReportesController@economia');
$router->get('/reportes/recursos', 'ReportesController@recursos');
$router->get('/reportes/proveedores', 'ReportesController@proveedores');

// Tareas
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
$router->get('/tareas/opcionesModal', 'TareasController@opcionesModal');
$router->get('/tareas/pendientes', 'TareasController@pendientes');
$router->get('/tareas/obtenerPendientes', 'TareasController@obtenerPendientes');
$router->post('/tareas/crearPendiente', 'TareasController@crearPendiente');
$router->post('/tareas/fechar', 'TareasController@fechar');
$router->post('/tareas/desfechar', 'TareasController@desfechar');
$router->post('/tareas/agregarTrabajador', 'TareasController@agregarTrabajador');
$router->post('/tareas/quitarTrabajador', 'TareasController@quitarTrabajador');
$router->post('/tareas/asignarCuadrilla', 'TareasController@asignarCuadrilla');
$router->post('/tareas/agregarParcela', 'TareasController@agregarParcela');
$router->post('/tareas/quitarParcela', 'TareasController@quitarParcela');
$router->post('/tareas/cambiarTrabajo', 'TareasController@cambiarTrabajo');
$router->post('/tareas/guardarPrecioFijo', 'TareasController@guardarPrecioFijo');
$router->post('/tareas/duplicar', 'TareasController@duplicar');

// Trabajadores
$router->get('/datos/trabajadores', 'TrabajadoresController@index');
$router->get('/trabajadores/buscar', 'TrabajadoresController@buscar');
$router->post('/trabajadores/crear', 'TrabajadoresController@crear');
$router->get('/trabajadores/obtener', 'TrabajadoresController@obtener');
$router->post('/trabajadores/actualizar', 'TrabajadoresController@actualizar');
$router->post('/trabajadores/eliminar', 'TrabajadoresController@eliminar');
$router->post('/trabajadores/reactivar', 'TrabajadoresController@reactivar');
$router->post('/trabajadores/subirFoto', 'TrabajadoresController@subirFoto');
$router->get('/trabajadores/cuadrilla', 'TrabajadoresController@obtenerCuadrilla');
$router->get('/trabajadores/{id}', 'TrabajadoresController@detalle');
$router->post('/trabajadores/subirDocumento', 'TrabajadoresController@subirDocumento');

// Trabajos
$router->get('/datos/trabajos', 'TrabajosController@index');
$router->get('/trabajos/buscar', 'TrabajosController@buscar');
$router->post('/trabajos/crear', 'TrabajosController@crear');
$router->get('/trabajos/obtener', 'TrabajosController@obtener');
$router->post('/trabajos/actualizar', 'TrabajosController@actualizar');
$router->post('/trabajos/eliminar', 'TrabajosController@eliminar');
$router->post('/trabajos/subirDocumento', 'TrabajosController@subirDocumento');
$router->post('/trabajos/eliminarDocumento', 'TrabajosController@eliminarDocumento');

// Parcelas
$router->get('/datos/parcelas', 'ParcelasController@index');
$router->get('/parcelas/buscar', 'ParcelasController@buscar');
$router->get('/parcelas/propietarios', 'ParcelasController@obtenerListaPropietarios');
$router->post('/parcelas/crear', 'ParcelasController@crear');
$router->get('/parcelas/obtener', 'ParcelasController@obtener');
$router->post('/parcelas/actualizar', 'ParcelasController@actualizar');
$router->post('/parcelas/eliminar', 'ParcelasController@eliminar');
$router->get('/parcelas/{id}', 'ParcelasController@detalle');
$router->post('/parcelas/subirDocumento', 'ParcelasController@subirDocumento');
$router->post('/parcelas/eliminarDocumento', 'ParcelasController@eliminarDocumento');
$router->post('/parcelas/subirImagen', 'ParcelasController@subirImagen');
$router->post('/parcelas/eliminarImagen', 'ParcelasController@eliminarImagen');

// Propietarios
$router->get('/propietarios/buscar', 'PropietariosController@buscar');
$router->get('/datos/propietarios', 'PropietariosController@index');
$router->get('/propietarios/{id}', 'PropietariosController@detalle');
$router->post('/propietarios/crear', 'PropietariosController@crear');
$router->get('/propietarios/obtener', 'PropietariosController@obtener');
$router->post('/propietarios/actualizar', 'PropietariosController@actualizar');
$router->post('/propietarios/eliminar', 'PropietariosController@eliminar');
$router->post('/propietarios/subirImagenDni', 'PropietariosController@subirImagenDni');

// Vehículos
$router->get('/datos/vehiculos', 'VehiculosController@index');
$router->get('/vehiculos/{id}', 'VehiculosController@detalle');
$router->post('/vehiculos/actualizar', 'VehiculosController@actualizar');
$router->post('/vehiculos/crear', 'VehiculosController@crear');
$router->post('/vehiculos/eliminar', 'VehiculosController@eliminar');
$router->post('/vehiculos/subirDocumento', 'VehiculosController@subirDocumento');

// Herramientas
$router->get('/datos/herramientas', 'HerramientasController@index');
$router->post('/herramientas/subirInstrucciones', 'HerramientasController@subirInstrucciones');

// Proveedores
$router->get('/datos/proveedores', 'ProveedoresController@index');
$router->get('/proveedores/obtener', 'ProveedoresController@obtener');
$router->post('/proveedores/crear', 'ProveedoresController@crear');
$router->post('/proveedores/actualizar', 'ProveedoresController@actualizar');
$router->post('/proveedores/eliminar', 'ProveedoresController@eliminar');

// Riego
$router->get('/riego', 'RiegoController@index');
$router->get('/datos/riego', 'RiegoController@index');
$router->post('/riego/crear', 'RiegoController@crear');
$router->get('/riego/obtener', 'RiegoController@obtener');
$router->post('/riego/actualizar', 'RiegoController@actualizar');
$router->post('/riego/eliminar', 'RiegoController@eliminar');
$router->post('/riego/iniciarTemporada', 'RiegoController@iniciarTemporada');
$router->post('/riego/terminarTemporada', 'RiegoController@terminarTemporada');

// Economía
$router->get('/economia', 'EconomiaController@index');
$router->get('/economia/gastos', 'EconomiaController@gastos');
$router->get('/economia/ingresos', 'EconomiaController@ingresos');
$router->get('/economia/deudas', 'EconomiaController@deudas_trabajadores');
$router->post('/economia/crear', 'EconomiaController@crear');
$router->post('/economia/editar', 'EconomiaController@editar');
$router->post('/economia/eliminar', 'EconomiaController@eliminar');
$router->get('/economia/obtener', 'EconomiaController@obtener');
$router->post('/economia/cerrarMes', 'EconomiaController@cerrar_mes');
$router->post('/economia/registrarPago', 'EconomiaController@registrar_pago');

// Campaña
$router->get('/campana', 'CampanaController@index');
$router->get('/campana/{id}', 'CampanaController@detalle');
$router->post('/campana/crear', 'CampanaController@crear');
$router->post('/campana/actualizar', 'CampanaController@actualizar');
$router->post('/campana/eliminar', 'CampanaController@eliminar');
$router->post('/campana/crearRegistro', 'CampanaController@crearRegistro');
$router->post('/campana/actualizarRegistro', 'CampanaController@actualizarRegistro');
$router->post('/campana/eliminarRegistro', 'CampanaController@eliminarRegistro');
$router->post('/campana/cerrar', 'CampanaController@cerrar');

// Fitosanitarios
$router->get('/datos/fitosanitarios', 'FitosanitariosController@inventario');
$router->post('/fitosanitarios/crearInventario', 'FitosanitariosController@crearInventario');
$router->post('/fitosanitarios/actualizarInventario', 'FitosanitariosController@actualizarInventario');
$router->post('/fitosanitarios/eliminarInventario', 'FitosanitariosController@eliminarInventario');
$router->post('/fitosanitarios/crearAplicacion', 'FitosanitariosController@crearAplicacion');
$router->post('/fitosanitarios/eliminarAplicacion', 'FitosanitariosController@eliminarAplicacion');

// Admin
$router->get('/admin/usuarios', 'AdminController@usuarios');
$router->post('/admin/crearUsuario', 'AdminController@crearUsuario');
$router->post('/admin/actualizarUsuario', 'AdminController@actualizarUsuario');
$router->post('/admin/eliminarUsuario', 'AdminController@eliminarUsuario');

// Vistas de rol
$router->get('/propietario', 'PropietarioDashboardController@index');
$router->get('/propietario/parcela/{id}', 'PropietarioDashboardController@parcelaDetalle');
$router->get('/trabajador', 'TrabajadorDashboardController@index');

// Página 404
$router->notFound(function () {
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página no encontrada</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #1e1e1e; }
            .container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
            h1 { color: #dc3545; }
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404 - Página no encontrada</h1>
            <p>La página que buscas no existe.</p>
            <a href="' . APP_BASE_PATH . '/" class="btn">Volver al inicio</a>
        </div>
    </body>
    </html>';
});
