<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\Incidente;
use App\Models\Mantenimiento;
use App\Models\Estacion;
use App\Models\Linea;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Crear una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Administrador');
    }

    /**
     * Mostrar el panel de control del administrador.
     *
     * @return \Illuminate\Http\Response
     */
    public function panelControl()
    {
        // Obtener estadísticas para el dashboard
        $vehiculosActivos = Vehiculo::where('estado', 'Activo')->count();
        $totalVehiculos = Vehiculo::count();
        
        $totalUsuarios = Usuario::count();
        $usuariosUltimaSemana = Usuario::where('fecha_creacion', '>=', Carbon::now()->subDays(7))->count();
        
        $incidentesPendientes = Incidente::whereIn('estado', ['Reportado', 'En atención'])->count();
        $incidentesCriticos = Incidente::where('impacto', 'Crítico')->whereIn('estado', ['Reportado', 'En atención'])->count();
        
        $mantenimientosProgramados = Mantenimiento::where('fecha_programada', '>=', Carbon::today())
            ->where('fecha_programada', '<=', Carbon::today()->addDays(7))
            ->where('resultado', 'Pendiente')
            ->count();
        
        // Obtener próximas asignaciones
        $proximasAsignaciones = DB::table('v_proximas_asignaciones')
            ->limit(10)
            ->get();
        
        // Obtener incidentes recientes
        $incidentesRecientes = Incidente::with('estacion')
            ->where('fecha_hora', '>=', Carbon::now()->subHours(24))
            ->orderBy('fecha_hora', 'desc')
            ->limit(10)
            ->get();
        
        // Obtener estado de la flota
        $estadoFlota = DB::table('v_estado_flota')->first();
        
        // Estadísticas de pasajeros por día (último 7 días)
        $pasajerosPorDia = DB::table('estadisticas_pasajeros')
            ->select(DB::raw('fecha, SUM(cantidad_entradas) as total'))
            ->where('fecha', '>=', Carbon::now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        return view('admin.panel_control', compact(
            'vehiculosActivos',
            'totalVehiculos',
            'totalUsuarios',
            'usuariosUltimaSemana',
            'incidentesPendientes',
            'incidentesCriticos',
            'mantenimientosProgramados',
            'proximasAsignaciones',
            'incidentesRecientes',
            'estadoFlota',
            'pasajerosPorDia'
        ));
    }

    /**
     * Mostrar la lista de usuarios.
     *
     * @return \Illuminate\Http\Response
     */
    public function usuarios()
    {
        $usuarios = Usuario::with('rol')->paginate(10);
        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Mostrar la lista de vehículos.
     *
     * @return \Illuminate\Http\Response
     */
    public function vehiculos()
    {
        $vehiculos = Vehiculo::with('linea')->paginate(10);
        return view('admin.vehiculos.index', compact('vehiculos'));
    }

    /**
     * Mostrar la lista de estaciones.
     *
     * @return \Illuminate\Http\Response
     */
    public function estaciones()
    {
        $estaciones = Estacion::paginate(10);
        return view('admin.estaciones.index', compact('estaciones'));
    }

    /**
     * Mostrar la lista de líneas.
     *
     * @return \Illuminate\Http\Response
     */
    public function lineas()
    {
        $lineas = Linea::paginate(10);
        return view('admin.lineas.index', compact('lineas'));
    }

    /**
     * Mostrar la lista de incidentes.
     *
     * @return \Illuminate\Http\Response
     */
    public function incidentes()
    {
        $incidentes = Incidente::with(['asignacion', 'estacion'])->orderBy('fecha_hora', 'desc')->paginate(10);
        return view('admin.incidentes.index', compact('incidentes'));
    }

    /**
     * Mostrar la lista de mantenimientos.
     *
     * @return \Illuminate\Http\Response
     */
    public function mantenimientos()
    {
        $mantenimientos = Mantenimiento::with('vehiculo')->orderBy('fecha_programada')->paginate(10);
        return view('admin.mantenimientos.index', compact('mantenimientos'));
    }
}