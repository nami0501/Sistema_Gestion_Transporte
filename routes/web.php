<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Supervisor\SupervisorController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\Conductor\ConductorController;
use App\Http\Controllers\Consulta\ConsultaController;
use App\Http\Controllers\AsignacionController;
use App\Http\Middleware\VerifyUserRole;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\IncidenteController;
use App\Http\Controllers\EstacionLineaController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\HorarioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirigir la ruta principal a la página de login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación - usando tu AuthController actual
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // Ruta principal (dashboard)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rutas de perfil (accesibles para todos los usuarios autenticados)
    Route::get('/perfil', [DashboardController::class, 'perfil'])->name('perfil');
    Route::get('/configuracion', [DashboardController::class, 'configuracion'])->name('configuracion');
    
    // Rutas para Administradores
    Route::middleware('userrole:Administrador')->prefix('admin')->group(function () {
        Route::get('/panel', [AdminController::class, 'panelControl'])->name('admin.panel');
        
        // Gestión de usuarios
        Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios.index');
        Route::get('/usuarios/create', [AdminController::class, 'crearUsuario'])->name('usuarios.create');
        Route::post('/usuarios', [AdminController::class, 'guardarUsuario'])->name('usuarios.store');
        Route::get('/usuarios/{id}', [AdminController::class, 'verUsuario'])->name('usuarios.show');
        Route::get('/usuarios/{id}/edit', [AdminController::class, 'editarUsuario'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.destroy');
        
        // Gestión de roles
        Route::get('/roles', [AdminController::class, 'roles'])->name('roles.index');
        Route::get('/roles/create', [AdminController::class, 'crearRol'])->name('roles.create');
        Route::post('/roles', [AdminController::class, 'guardarRol'])->name('roles.store');
        Route::get('/roles/{id}/edit', [AdminController::class, 'editarRol'])->name('roles.edit');
        Route::put('/roles/{id}', [AdminController::class, 'actualizarRol'])->name('roles.update');
        Route::delete('/roles/{id}', [AdminController::class, 'eliminarRol'])->name('roles.destroy');
        
        // Gestión de vehículos
        Route::get('/vehiculos', [AdminController::class, 'vehiculos'])->name('vehiculos.index');
        Route::get('/vehiculos/create', [AdminController::class, 'crearVehiculo'])->name('vehiculos.create');
        Route::post('/vehiculos', [AdminController::class, 'guardarVehiculo'])->name('vehiculos.store');
        Route::get('/vehiculos/{id}', [AdminController::class, 'verVehiculo'])->name('vehiculos.show');
        Route::get('/vehiculos/{id}/edit', [AdminController::class, 'editarVehiculo'])->name('vehiculos.edit');
        Route::put('/vehiculos/{id}', [AdminController::class, 'actualizarVehiculo'])->name('vehiculos.update');
        Route::delete('/vehiculos/{id}', [AdminController::class, 'eliminarVehiculo'])->name('vehiculos.destroy');
        
        // Gestión de líneas
        Route::get('/lineas', [AdminController::class, 'lineas'])->name('lineas.index');
        Route::get('/lineas/create', [AdminController::class, 'crearLinea'])->name('lineas.create');
        Route::post('/lineas', [AdminController::class, 'guardarLinea'])->name('lineas.store');
        Route::get('/lineas/{id}', [AdminController::class, 'verLinea'])->name('lineas.show');
        // Ruta para ver las estaciones de una línea
        Route::get('/lineas/{linea}/estaciones', [LineaController::class, 'estaciones'])->name('lineas.estaciones');
        Route::get('/lineas/{id}/edit', [AdminController::class, 'editarLinea'])->name('lineas.edit');
        Route::put('/lineas/{id}', [AdminController::class, 'actualizarLinea'])->name('lineas.update');
        Route::delete('/lineas/{id}', [AdminController::class, 'eliminarLinea'])->name('lineas.destroy');
        
        
        // Gestión de estaciones
        Route::get('/estaciones', [AdminController::class, 'estaciones'])->name('estaciones.index');
        Route::get('/estaciones/create', [AdminController::class, 'crearEstacion'])->name('estaciones.create');
        Route::post('/estaciones', [AdminController::class, 'guardarEstacion'])->name('estaciones.store');
        Route::get('/estaciones/{id}', [AdminController::class, 'verEstacion'])->name('estaciones.show');
        Route::get('/estaciones/{id}/edit', [AdminController::class, 'editarEstacion'])->name('estaciones.edit');
        Route::put('/estaciones/{id}', [AdminController::class, 'actualizarEstacion'])->name('estaciones.update');
        Route::delete('/estaciones/{id}', [AdminController::class, 'eliminarEstacion'])->name('estaciones.destroy');
        
        // Gestión de incidentes
        Route::get('/incidentes', [AdminController::class, 'incidentes'])->name('incidentes.index');
        Route::get('/incidentes/{id}', [AdminController::class, 'verIncidente'])->name('incidentes.show');
        Route::get('/incidentes/{id}/edit', [AdminController::class, 'editarIncidente'])->name('incidentes.edit');
        Route::put('/incidentes/{id}', [AdminController::class, 'actualizarIncidente'])->name('incidentes.update');
        
    });
    
    // Rutas para Supervisores
    Route::prefix('supervisor')
        ->middleware(CheckRole::class.':Supervisor,Administrador')
        ->group(function () {
            Route::get('/supervision', [SupervisorController::class, 'supervision'])->name('supervisor.supervision');
            
            // Monitoreo
            Route::get('/operador/monitoreo', [App\Http\Controllers\Operador\OperadorController::class, 'monitoreo']) ->name('operador.monitoreo');

            // Asignaciones
            Route::get('/asignaciones', [SupervisorController::class, 'asignaciones'])->name('asignaciones.index');
            Route::get('/asignaciones/create', [SupervisorController::class, 'crearAsignacion'])->name('asignaciones.create');
            Route::post('/asignaciones', [SupervisorController::class, 'guardarAsignacion'])->name('asignaciones.store');
            Route::get('/asignaciones/{id}', [SupervisorController::class, 'verAsignacion'])->name('asignaciones.show');
            Route::get('/asignaciones/{id}/edit', [SupervisorController::class, 'editarAsignacion'])->name('asignaciones.edit');
            Route::put('/asignaciones/{id}', [SupervisorController::class, 'actualizarAsignacion'])->name('asignaciones.update');
            
            // Incidentes
            Route::get('/incidentes', [SupervisorController::class, 'incidentes'])->name('supervisor.incidentes');
            Route::get('/incidentes/create', [SupervisorController::class, 'crearIncidente'])->name('incidentes.create');
            Route::post('/incidentes', [SupervisorController::class, 'guardarIncidente'])->name('incidentes.store');
            
            // Reportes
            Route::get('/reportes/diario', [SupervisorController::class, 'reporteDiario'])->name('reportes.diario');
            
            // Comunicados
            Route::get('/comunicaciones', [SupervisorController::class, 'comunicaciones'])->name('comunicaciones.index');
            Route::get('/comunicaciones/create', [SupervisorController::class, 'crearComunicacion'])->name('comunicaciones.create');
            Route::post('/comunicaciones', [SupervisorController::class, 'guardarComunicacion'])->name('comunicaciones.store');
    });
    
    // Rutas para Operadores
    Route::prefix('operador')
    ->middleware([
        'auth', // Primero autenticación
        \App\Http\Middleware\CheckRole::class.':Operador' // Luego verificación de rol con ruta completa
    ])
    ->group(function () {
        // Monitoreo
        Route::get('/monitoreo', [App\Http\Controllers\OperadorController::class, 'monitoreo'])->name('operador.monitoreo');
        
        // Incidentes
        Route::get('/incidentes', [App\Http\Controllers\OperadorController::class, 'incidentes'])->name('operador.incidentes');
        Route::get('/incidentes/create', [App\Http\Controllers\OperadorController::class, 'crearIncidente'])->name('operador.incidentes.create');
        Route::post('/incidentes', [App\Http\Controllers\OperadorController::class, 'guardarIncidente'])->name('operador.incidentes.store');
        
        // Vehículos
        Route::get('/vehiculos/{id}', [App\Http\Controllers\OperadorController::class, 'detallesVehiculo'])->name('operador.vehiculos.show');
    });
    
    // Rutas para Conductores
    Route::middleware('verify.role:Conductor,Supervisor,Administrador')->prefix('conductor')->group(function () {
        Route::get('/horarios', [ConductorController::class, 'horarios'])->name('conductor.horarios');
        Route::get('/asignaciones/{id}', [ConductorController::class, 'detallesAsignacion'])->name('conductor.detalles_asignacion');
        Route::post('/asignaciones/{id}/iniciar', [ConductorController::class, 'iniciarAsignacion'])->name('conductor.iniciar_asignacion');
        Route::post('/asignaciones/{id}/finalizar', [ConductorController::class, 'finalizarAsignacion'])->name('conductor.finalizar_asignacion');
        Route::post('/incidentes', [ConductorController::class, 'reportarIncidente'])->name('conductor.reportar_incidente');
        Route::post('/estaciones/registrar-paso', [ConductorController::class, 'registrarPasoEstacion'])->name('conductor.registrar_paso');
    });
    
    // Rutas para Consulta (accesibles para todos los usuarios autenticados)
    Route::prefix('consulta')->group(function () {
        Route::get('/informacion', [ConsultaController::class, 'informacion'])->name('consulta.informacion');
        Route::get('/lineas', [ConsultaController::class, 'lineas'])->name('consulta.lineas');
        Route::get('/estaciones', [ConsultaController::class, 'estaciones'])->name('consulta.estaciones');
        Route::get('/horarios', [ConsultaController::class, 'horarios'])->name('consulta.horarios');
        Route::get('/tarifas', [ConsultaController::class, 'tarifas'])->name('consulta.tarifas');
        Route::get('/faq', [ConsultaController::class, 'faq'])->name('consulta.faq');
    });

    // Rutas para el monitoreo GPS
    Route::prefix('monitoreo')->middleware(['auth'])->group(function () {
        // Vista principal del mapa
        Route::get('/mapa', [MonitoreoController::class, 'index'])->name('monitoreo.mapa');
        
        // API para obtener datos en tiempo real
        Route::prefix('api')->group(function () {
            // Obtener vehículos activos
            Route::get('/vehiculos-activos', [MonitoreoController::class, 'getVehiculosActivos'])->name('api.vehiculos.activos');
            
            // Obtener detalles de un vehículo
            Route::get('/vehiculos/{id}/detalles', [MonitoreoController::class, 'getDetallesVehiculo'])->name('api.vehiculos.detalles');
            
            // Obtener ruta de un vehículo
            Route::get('/vehiculos/{vehiculoId}/asignaciones/{asignacionId}/ruta', [MonitoreoController::class, 'getRutaVehiculo'])->name('api.vehiculos.ruta');
            
            // Obtener estaciones de una línea
            Route::get('/lineas/{lineaId}/estaciones', [MonitoreoController::class, 'getEstacionesLinea'])->name('api.lineas.estaciones');
            
            // Simulador para testing (solo en entorno de desarrollo)
            Route::get('/simular-gps', [MonitoreoController::class, 'simularRegistrosGPS'])->name('api.simular.gps');
        });
    });

    // Rutas para la gestión de horarios
    Route::prefix('horarios')->middleware(['auth'])->group(function () {
        // CRUD básico
        Route::get('/', [HorarioController::class, 'index'])->name('horarios.index');
        Route::get('/create', [HorarioController::class, 'create'])->name('horarios.create');
        Route::post('/', [HorarioController::class, 'store'])->name('horarios.store');
        Route::get('/{id}/edit', [HorarioController::class, 'edit'])->name('horarios.edit');
        Route::put('/{id}', [HorarioController::class, 'update'])->name('horarios.update');
        Route::delete('/{id}', [HorarioController::class, 'destroy'])->name('horarios.destroy');
        
        // Programación masiva
        Route::get('/programacion', [HorarioController::class, 'programacion'])->name('horarios.programacion');
        Route::post('/generar', [HorarioController::class, 'generarHorarios'])->name('horarios.generar');
    });

    // Rutas API para horarios (para AJAX)
    Route::prefix('api/horarios')->middleware(['auth'])->group(function () {
        Route::get('/estaciones-por-linea', [HorarioController::class, 'getEstacionesPorLinea']);
        Route::get('/horarios-por-linea-dia', [HorarioController::class, 'getHorariosPorLineaDia']);
        Route::get('/comparativa-horarios', [HorarioController::class, 'getComparativaHorarios']);
    });

    // Rutas API para la funcionalidad tipo carrito (asignaciones)
    Route::prefix('api')->middleware(['auth'])->group(function () {
        Route::post('/asignaciones/procesar-carrito', [AsignacionController::class, 'procesarCarrito'])
            ->name('asignaciones.procesar-carrito');

        // Ruta para configurar el carrito
        Route::post('/asignaciones/configurar-carrito', [AsignacionController::class, 'configurarCarrito'])
            ->name('asignaciones.configurar-carrito');

        // Ruta para obtener conductores disponibles
        Route::get('/conductores-disponibles', [AsignacionController::class, 'getConductoresDisponibles'])
            ->name('api.conductores.disponibles');

        // Ruta para obtener vehículos disponibles
        Route::get('/vehiculos-disponibles', [AsignacionController::class, 'getVehiculosDisponibles'])
            ->name('api.vehiculos.disponibles');

        // Ruta para agregar al carrito
        Route::post('/asignaciones/agregar-al-carrito', [AsignacionController::class, 'agregarAlCarrito'])
            ->name('api.asignaciones.agregar-al-carrito');

        // Ruta para quitar del carrito
        Route::post('/asignaciones/quitar-del-carrito', [AsignacionController::class, 'quitarDelCarrito'])
            ->name('api.asignaciones.quitar-del-carrito');
    });

    // Rutas para Vehículos (disponibles para todos los roles que necesiten acceso)
    Route::resource('vehiculos', VehiculoController::class);

    // Rutas para Líneas
    Route::resource('lineas', LineaController::class);

    // Rutas para Asignaciones
    Route::resource('asignaciones', AsignacionController::class);
    Route::post('/asignaciones/procesar-carrito', [AsignacionController::class, 'procesarCarrito'])->name('asignaciones.procesar-carrito');
    Route::post('/asignaciones/configurar-carrito', [AsignacionController::class, 'configurarCarrito'])->name('asignaciones.configurar-carrito');
    Route::post('/api/asignaciones/quitar-del-carrito', [AsignacionController::class, 'quitarDelCarrito'])->name('api.asignaciones.quitar-del-carrito');
    Route::post('/asignaciones/{id}/iniciar', [AsignacionController::class, 'iniciar'])->name('asignaciones.iniciar');
    Route::post('/asignaciones/{id}/completar', [AsignacionController::class, 'completar'])->name('asignaciones.completar');
    Route::post('/asignaciones/{id}/cancelar', [AsignacionController::class, 'cancelar'])->name('asignaciones.cancelar');

    // Rutas para Incidentes
    Route::resource('incidentes', IncidenteController::class);
    Route::post('/incidentes/{incidente}/resolver', [IncidenteController::class, 'resolver'])->name('incidentes.resolver');
    Route::post('/incidentes/{incidente}/atender', [IncidenteController::class, 'atender'])->name('incidentes.atender');
    Route::post('/incidentes/{incidente}/escalar', [IncidenteController::class, 'escalar'])->name('incidentes.escalar');

    // Rutas para la relación entre estaciones y líneas
    Route::resource('estaciones-lineas', EstacionLineaController::class)->except(['index', 'show']);

    // Rutas para Mantenimientos
    Route::resource('mantenimientos', MantenimientoController::class);

});