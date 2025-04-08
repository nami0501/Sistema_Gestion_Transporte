<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehiculo;
use App\Models\Incidente;
use App\Models\Linea;
use App\Models\Estacion;
use Carbon\Carbon;

class OperadorController extends Controller
{
    /**
     * Crear una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Operador|Supervisor|Administrador');
    }

    /**
     * Mostrar el sistema de monitoreo.
     *
     * @return \Illuminate\Http\Response
     */
    public function monitoreo()
    {
        // Obtener vehículos en servicio
        $vehiculos = DB::table('v_ubicacion_tiempo_real')
            ->select(
                'id_vehiculo',
                'placa',
                'tipo',
                'linea',
                'conductor',
                'latitud',
                'longitud',
                'velocidad',
                'timestamp',
                'estacion_cercana',
                'distancia_estacion',
                'kilometro_ruta',
                'direccion_ruta',
                'vuelta_actual',
                'vueltas_completas',
                'estado_vehiculo',
                DB::raw('CASE 
                    WHEN diferencia_tiempo < 3 THEN "on-schedule" 
                    WHEN diferencia_tiempo < 8 THEN "slight-delay" 
                    ELSE "delayed" END as estado_tiempo'),
                DB::raw('CASE 
                    WHEN estado_vehiculo = "En movimiento" THEN "on-time" 
                    WHEN estado_vehiculo = "Detenido" THEN "delayed" 
                    ELSE "alert" END as estado_operacion'),
                DB::raw('TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP, proxima_llegada) as diferencia_tiempo'),
                DB::raw('EXISTS(SELECT 1 FROM incidentes i WHERE i.id_asignacion = v_ubicacion_tiempo_real.id_asignacion AND i.estado IN ("Reportado", "En atención")) as tiene_incidente')
            )
            ->where('estado_vehiculo', '<>', 'Fuera de servicio')
            ->get();

        // Obtener líneas
        $lineas = Linea::where('estado', 'Activa')->get();

        // Obtener estaciones
        $estaciones = Estacion::where('estado', 'Activa')->get();

        // Obtener incidentes activos
        $incidentesActivos = Incidente::with('estacion')
            ->whereIn('estado', ['Reportado', 'En atención'])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        // Obtener estadísticas
        $vehiculosEnServicio = $vehiculos->count();
        $totalVehiculos = Vehiculo::where('estado', 'Activo')->count();
        $puntualidad = $vehiculos->where('estado_tiempo', 'on-schedule')->count() / max(1, $vehiculosEnServicio) * 100;
        $ocupacionPromedio = 75; // Simulado, en un sistema real se obtendría de sensores o estimaciones

        // Simulación de datos para el gráfico de actividad
        $pasajerosHoy = DB::table('estadisticas_pasajeros')
            ->where('fecha', Carbon::today())
            ->sum('cantidad_entradas');

        $actividadHoras = [];
        $actividadPasajeros = [];
        $actividadVehiculos = [];

        for ($i = 0; $i < 24; $i++) {
            $hora = sprintf("%02d:00", $i);
            $actividadHoras[] = $hora;

            // Simular una curva típica de pasajeros
            $peakMorning = 7;
            $peakEvening = 18;
            $basePassengers = 500;
            $peakPassengers = 5000;

            $peakFactor = min(
                exp(-0.5 * pow(($i - $peakMorning) / 2, 2)),
                exp(-0.5 * pow(($i - $peakEvening) / 2, 2))
            );
            $pasajeros = $basePassengers + $peakFactor * $peakPassengers;
            $actividadPasajeros[] = round($pasajeros);

            // Vehículos activos correlacionados con pasajeros
            $vehiculosActivos = round($totalVehiculos * (0.3 + 0.7 * $peakFactor));
            $actividadVehiculos[] = $vehiculosActivos;
        }

        // Rutas de líneas para el mapa (simuladas)
        $lineasRutas = [];
        foreach ($lineas as $linea) {
            $lineasRutas[$linea->id_linea] = [];
        }

        return view('operador.monitoreo', compact(
            'vehiculos',
            'lineas',
            'estaciones',
            'incidentesActivos',
            'vehiculosEnServicio',
            'totalVehiculos',
            'puntualidad',
            'ocupacionPromedio',
            'pasajerosHoy',
            'actividadHoras',
            'actividadPasajeros',
            'actividadVehiculos',
            'lineasRutas'
        ));
    }

    /**
     * Mostrar detalles de un vehículo.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detallesVehiculo($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $asignacionActual = DB::table('asignaciones')
            ->join('usuarios', 'asignaciones.id_usuario', '=', 'usuarios.id_usuario')
            ->join('lineas', 'asignaciones.id_linea', '=', 'lineas.id_linea')
            ->select(
                'asignaciones.*',
                DB::raw('CONCAT(usuarios.nombre, " ", usuarios.apellidos) as conductor'),
                'usuarios.telefono',
                'lineas.nombre as linea',
                'lineas.color'
            )
            ->where('asignaciones.id_vehiculo', $id)
            ->where('asignaciones.fecha', Carbon::today())
            ->whereIn('asignaciones.estado', ['Programado', 'En curso'])
            ->first();

        // Historial de ubicaciones (simulado)
        $historialUbicaciones = [];
        $base_lat = -12.046374;
        $base_lng = -77.042793;

        for ($i = 0; $i < 20; $i++) {
            $time = Carbon::now()->subMinutes($i * 15);
            $historialUbicaciones[] = [
                'latitud' => $base_lat + sin($i * 0.1) * 0.01,
                'longitud' => $base_lng + cos($i * 0.1) * 0.01,
                'velocidad' => 20 + rand(0, 30),
                'timestamp' => $time->format('Y-m-d H:i:s'),
                'estado' => ['En movimiento', 'Detenido', 'En estación'][rand(0, 2)]
            ];
        }

        // Incidentes del vehículo
        $incidentes = Incidente::whereHas('asignacion', function ($query) use ($id) {
            $query->where('id_vehiculo', $id);
        })
        ->orderBy('fecha_hora', 'desc')
        ->limit(5)
        ->get();

        return view('operador.vehiculos.show', compact(
            'vehiculo',
            'asignacionActual',
            'historialUbicaciones',
            'incidentes'
        ));
    }

    /**
     * Mostrar gestión de incidentes.
     *
     * @return \Illuminate\Http\Response
     */
    public function incidentes()
    {
        $incidentes = Incidente::with(['estacion', 'asignacion'])
            ->orderBy('fecha_hora', 'desc')
            ->paginate(15);

        return view('operador.incidentes.index', compact('incidentes'));
    }

    /**
     * Mostrar formulario para reportar incidente.
     *
     * @return \Illuminate\Http\Response
     */
    public function crearIncidente()
    {
        $vehiculos = Vehiculo::where('estado', 'Activo')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('asignaciones')
                    ->whereColumn('asignaciones.id_vehiculo', 'vehiculos.id_vehiculo')
                    ->where('asignaciones.fecha', Carbon::today())
                    ->whereIn('asignaciones.estado', ['Programado', 'En curso']);
            })
            ->orderBy('placa')
            ->get();

        $estaciones = Estacion::where('estado', 'Activa')
            ->orderBy('nombre')
            ->get();

        return view('operador.incidentes.create', compact('vehiculos', 'estaciones'));
    }

    /**
     * Guardar nuevo incidente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guardarIncidente(Request $request)
    {
        $request->validate([
            'tipo_incidente' => 'required|string',
            'descripcion' => 'required|string',
            'impacto' => 'required|string',
            'id_estacion' => 'nullable|exists:estaciones,id_estacion',
            'id_asignacion' => 'nullable|exists:asignaciones,id_asignacion',
        ]);

        $incidente = new Incidente();
        $incidente->tipo_incidente = $request->tipo_incidente;
        $incidente->descripcion = $request->descripcion;
        $incidente->fecha_hora = Carbon::now();
        $incidente->estado = 'Reportado';
        $incidente->impacto = $request->impacto;
        $incidente->id_estacion = $request->id_estacion;
        $incidente->id_asignacion = $request->id_asignacion;
        $incidente->retraso_estimado = $request->retraso_estimado;
        $incidente->save();

        return redirect()->route('operador.incidentes')
            ->with('success', 'Incidente reportado correctamente.');
    }
}