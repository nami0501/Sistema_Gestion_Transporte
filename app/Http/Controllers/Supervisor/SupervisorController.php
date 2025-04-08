<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehiculo;
use App\Models\Incidente;
use App\Models\Linea;
use App\Models\Usuario;
use App\Models\Asignacion;
use App\Models\Estacion;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    /**
     * Crear una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Mostrar el panel de supervisión.
     *
     * @return \Illuminate\Http\Response
     */
    public function supervision()
    {
        $vehiculosEnServicio = Vehiculo::where('estado', 'Activo')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('asignaciones')
                ->whereColumn('asignaciones.id_vehiculo', 'vehiculos.id_vehiculo')
                ->where('asignaciones.fecha', Carbon::today())
                ->whereIn('asignaciones.estado', ['Programado', 'En curso']);
        })
        ->count();

        $totalVehiculos = Vehiculo::where('estado', 'Activo')->count();
        $porcentajeOperacion = $totalVehiculos > 0 ? round(($vehiculosEnServicio / $totalVehiculos) * 100, 1) : 0;
        
        $conductoresActivos = Usuario::where('es_conductor', true)
            ->where('estado', 'Activo')
            ->count();
            
        $conductoresEnRuta = Asignacion::where('fecha', Carbon::today())
            ->where('estado', 'En curso')
            ->count();
            
        $incidentesActivos = Incidente::whereIn('estado', ['Reportado', 'En atención'])
            ->count();
            
        $incidentesCriticos = Incidente::where('impacto', 'Crítico')
            ->whereIn('estado', ['Reportado', 'En atención'])
            ->count();
            
        $puntualidad = DB::table('v_estadisticas_vueltas')
            ->avg('desviacion_promedio');
            
        $puntualidad = 100 - (abs($puntualidad) / 10); // Convertir desviación a porcentaje de puntualidad
        $puntualidad = max(0, min(100, $puntualidad)); // Asegurar que esté entre 0-100
        
        // Calcular tendencia de puntualidad (comparando con el día anterior)
        $puntualidadAnterior = DB::table('v_estadisticas_vueltas')
            ->avg('desviacion_promedio');
            
        $puntualidadAnterior = 100 - (abs($puntualidadAnterior) / 10);
        $puntualidadAnterior = max(0, min(100, $puntualidadAnterior));
        
        $tendenciaPuntualidad = $puntualidad - $puntualidadAnterior;
        
        // Tiempo desde la última actualización
        $tiempoActualizacion = rand(1, 15); // Simulado, en un entorno real sería desde la última actualización real
        
        // Estado de las líneas
        $estadoLineas = Linea::select(
            'lineas.id_linea',
            'lineas.nombre',
            'lineas.estado',
            DB::raw('COUNT(DISTINCT vehiculos.id_vehiculo) as total_vehiculos'),
            DB::raw("SUM(CASE WHEN vehiculos.estado = 'Activo' THEN 1 ELSE 0 END) as vehiculos_activos")
        )
        ->leftJoin('vehiculos', 'lineas.id_linea', '=', 'vehiculos.id_linea')
        ->groupBy('lineas.id_linea', 'lineas.nombre', 'lineas.estado')
        ->get();
            
        // Asignaciones de hoy
        // Asignaciones de hoy
        $asignacionesHoy = DB::table('asignaciones')
        ->join('usuarios', 'asignaciones.id_usuario', '=', 'usuarios.id_usuario')
        ->join('vehiculos', 'asignaciones.id_vehiculo', '=', 'vehiculos.id_vehiculo')
        ->join('lineas', 'asignaciones.id_linea', '=', 'lineas.id_linea')
        ->select(
            'asignaciones.id_asignacion',
            'asignaciones.fecha',
            'asignaciones.hora_inicio',
            'asignaciones.hora_fin',
            'asignaciones.estado',
            DB::raw("usuarios.nombre || ' ' || usuarios.apellidos as conductor"), // Cambio aquí
            'usuarios.dni',
            'vehiculos.placa',
            'vehiculos.tipo as tipo_vehiculo',
            'lineas.nombre as linea'
        )
        ->where('asignaciones.fecha', Carbon::today())
        ->orderBy('asignaciones.hora_inicio')
        ->get();
            
        // Datos para el mapa (simulados, en un entorno real vendrían de la tabla registros_gps)
        $vehiculosEnRuta = [];
        for ($i = 0; $i < 15; $i++) {
            $vehiculosEnRuta[] = [
                'id_vehiculo' => $i + 1,
                'placa' => 'V' . sprintf("%03d", $i + 1),
                'latitud' => -12.046374 + (rand(-100, 100) / 1000),
                'longitud' => -77.042793 + (rand(-100, 100) / 1000),
                'velocidad' => rand(15, 60),
                'linea' => 'Línea ' . rand(1, 5),
                'conductor' => 'Conductor ' . ($i + 1),
                'estado' => ['normal', 'retrasado', 'incidente'][rand(0, 2)],
                'ultima_actualizacion' => Carbon::now()->subMinutes(rand(1, 30))->format('H:i')
            ];
        }
        
        return view('supervisor.supervision', compact(
            'vehiculosEnServicio',
            'totalVehiculos',
            'porcentajeOperacion',
            'conductoresActivos',
            'conductoresEnRuta',
            'incidentesActivos',
            'incidentesCriticos',
            'puntualidad',
            'tendenciaPuntualidad',
            'tiempoActualizacion',
            'estadoLineas',
            'asignacionesHoy',
            'vehiculosEnRuta'
        ));
    }

    /**
     * Mostrar monitoreo en vivo.
     */
    public function monitoreo()
    {
        // Redirigir a la vista de monitoreo del operador
        return redirect()->route('operador.monitoreo');
    }

    /**
     * Mostrar reportes de operación.
     */
    public function reporteDiario()
    {
        $fecha = request('fecha', Carbon::today()->format('Y-m-d'));
        
        // Obtener estadísticas del día
        $estadisticas = [
            'pasajeros_total' => DB::table('estadisticas_pasajeros')
                ->where('fecha', $fecha)
                ->sum('cantidad_entradas'),
                
            'vueltas_completadas' => DB::table('recorridos')
                ->where('estado', 'Completado')
                ->whereDate('hora_inicio', $fecha)
                ->count(),
                
            'incidentes' => Incidente::whereDate('fecha_hora', $fecha)->count(),
            
            'puntualidad' => DB::table('recorridos')
                ->where('estado', 'Completado')
                ->whereDate('hora_inicio', $fecha)
                ->avg('diferencia_tiempo'),
                
            'vehiculos_activos' => Asignacion::where('fecha', $fecha)->count(),
        ];
        
        // Pasajeros por hora
        $pasajerosPorHora = DB::table('estadisticas_pasajeros')
            ->select('hora', DB::raw('SUM(cantidad_entradas) as total'))
            ->where('fecha', $fecha)
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();
            
        // Pasajeros por línea
        $pasajerosPorLinea = DB::table('estadisticas_pasajeros')
            ->join('lineas', 'estadisticas_pasajeros.id_linea', '=', 'lineas.id_linea')
            ->select('lineas.nombre', DB::raw('SUM(cantidad_entradas) as total'))
            ->where('fecha', $fecha)
            ->groupBy('lineas.id_linea', 'lineas.nombre')
            ->orderBy('total', 'desc')
            ->get();
            
        // Incidentes del día
        $incidentes = Incidente::with(['estacion', 'asignacion'])
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora', 'desc')
            ->get();
            
        return view('supervisor.reportes.diario', compact(
            'fecha',
            'estadisticas',
            'pasajerosPorHora',
            'pasajerosPorLinea',
            'incidentes'
        ));
    }

    /**
     * Mostrar listado de asignaciones.
     */
    public function asignaciones()
    {
        $fecha = request('fecha', Carbon::today()->format('Y-m-d'));
        
        $asignaciones = Asignacion::with(['usuario', 'vehiculo', 'linea', 'turno'])
            ->where('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->paginate(15);
            
        return view('supervisor.asignaciones.index', compact('fecha', 'asignaciones'));
    }

    /**
     * Crear nueva asignación.
     */
    public function crearAsignacion()
    {
        $conductores = Usuario::where('es_conductor', true)
            ->where('estado', 'Activo')
            ->orderBy('apellidos')
            ->get();
            
        $vehiculos = Vehiculo::where('estado', 'Activo')
            ->orderBy('placa')
            ->get();
            
        $lineas = Linea::where('estado', 'Activa')
            ->orderBy('nombre')
            ->get();
            
        $turnos = DB::table('turnos')->orderBy('hora_inicio')->get();
        
        return view('supervisor.asignaciones.create', compact(
            'conductores',
            'vehiculos',
            'lineas',
            'turnos'
        ));
    }

    /**
     * Mostrar gestión de incidentes.
     */
    public function incidentes()
    {
        $incidentes = Incidente::with(['estacion', 'asignacion'])
            ->orderBy('fecha_hora', 'desc')
            ->paginate(15);
            
        return view('supervisor.incidentes.index', compact('incidentes'));
    }

}