<?php

namespace App\Http\Controllers\Conductor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Asignacion;
use App\Models\Vehiculo;
use App\Models\Estacion;
use App\Models\Linea;
use App\Models\Incidente;
use Carbon\Carbon;

class ConductorController extends Controller
{
    /**
     * Crear una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Conductor|Supervisor|Administrador');
    }

    /**
     * Mostrar horarios y asignaciones del conductor.
     *
     * @return \Illuminate\Http\Response
     */
    public function horarios()
    {
        $usuario = Auth::user();

        // Obtener asignación actual
        $asignacionActual = Asignacion::with(['vehiculo', 'linea', 'turno'])
            ->where('id_usuario', $usuario->id_usuario)
            ->where('fecha', Carbon::today())
            ->whereIn('estado', ['Programado', 'En curso'])
            ->where('hora_inicio', '<=', Carbon::now())
            ->where('hora_fin', '>=', Carbon::now())
            ->orderBy('hora_inicio')
            ->first();

        // Obtener próximas asignaciones
        $proximasAsignaciones = Asignacion::with(['vehiculo', 'linea', 'turno'])
            ->where('id_usuario', $usuario->id_usuario)
            ->where(function ($query) {
                $query->where('fecha', '>', Carbon::today())
                    ->orWhere(function ($q) {
                        $q->where('fecha', Carbon::today())
                            ->where('hora_inicio', '>', Carbon::now());
                    });
            })
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->limit(10)
            ->get();

        // Obtener historial de asignaciones
        $historialAsignaciones = Asignacion::with(['vehiculo', 'linea', 'turno'])
            ->where('id_usuario', $usuario->id_usuario)
            ->where('estado', 'Completado')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->limit(15)
            ->get();

        // Datos de la ruta y estación actual (simulado)
        $rutaEstaciones = [];
        $estacionActual = null;
        $estacionActualIndex = 0;
        $proximaEstacion = null;
        $horarioProximaEstacion = null;
        $horarios = [];
        $vueltasProgramadas = 0;

        if ($asignacionActual) {
            // Obtener estaciones de la ruta
            $rutaEstaciones = Estacion::join('estaciones_lineas', 'estaciones.id_estacion', '=', 'estaciones_lineas.id_estacion')
                ->where('estaciones_lineas.id_linea', $asignacionActual->id_linea)
                ->where('estaciones_lineas.direccion', 'Norte-Sur') // Simulado, normalmente sería dinámico
                ->orderBy('estaciones_lineas.orden')
                ->select('estaciones.*', 'estaciones_lineas.orden')
                ->get();

            // Simular estación actual
            $estacionActualIndex = rand(0, count($rutaEstaciones) - 2);
            $estacionActual = $rutaEstaciones[$estacionActualIndex];
            $proximaEstacion = $rutaEstaciones[$estacionActualIndex + 1];

            // Simular horarios
            $now = Carbon::now();
            $baseHorario = $now->copy()->subMinutes(rand(30, 90));
            
            foreach ($rutaEstaciones as $index => $estacion) {
                $horarioEstacion = $baseHorario->copy()->addMinutes($index * 10);
                $horarios[$estacion->id_estacion] = $horarioEstacion->format('H:i');
            }

            $horarioProximaEstacion = $now->copy()->addMinutes(rand(5, 15))->format('H:i');
            $vueltasProgramadas = rand(4, 8);
        }

        // Lista de verificación (simulada)
        $checklist = (object)[
            'revision_frenos' => true,
            'revision_luces' => true,
            'revision_neumaticos' => true,
            'revision_aceite' => false,
            'limpieza_unidad' => false,
        ];

        // Datos para la vista semanal
        $diasSemana = [];
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $diasSemana[] = [
                'nombre' => $day->locale('es')->dayName,
                'fecha' => $day->format('d/m'),
                'esDiaActual' => $day->isSameDay($today),
            ];
        }

        $horasLaborales = [];
        for ($hour = 5; $hour <= 23; $hour++) {
            $horasLaborales[] = sprintf("%02d:00", $hour);
        }

        // Calendario semanal (simulado)
        $calendarioSemanal = [];
        
        // Estadísticas semanales (simuladas)
        $horasSemana = rand(30, 45);
        $kilometrosRecorridos = rand(800, 1200);

        return view('conductor.horarios', compact(
            'asignacionActual',
            'proximasAsignaciones',
            'historialAsignaciones',
            'rutaEstaciones',
            'estacionActual',
            'estacionActualIndex',
            'proximaEstacion',
            'horarioProximaEstacion',
            'horarios',
            'vueltasProgramadas',
            'checklist',
            'diasSemana',
            'horasLaborales',
            'calendarioSemanal',
            'horasSemana',
            'kilometrosRecorridos'
        ));
    }

    /**
     * Mostrar detalles de una asignación específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detallesAsignacion($id)
    {
        $asignacion = Asignacion::with(['vehiculo', 'linea', 'turno', 'usuario'])
            ->findOrFail($id);

        // Verificar que sea una asignación del conductor actual o un admin/supervisor
        if (Auth::user()->id_usuario != $asignacion->id_usuario && 
            !Auth::user()->tieneRol(['Administrador', 'Supervisor'])) {
            return redirect()->route('conductor.horarios')
                ->with('error', 'No tienes acceso a esa asignación.');
        }

        // Obtener estaciones de la ruta
        $estaciones = Estacion::join('estaciones_lineas', 'estaciones.id_estacion', '=', 'estaciones_lineas.id_estacion')
            ->where('estaciones_lineas.id_linea', $asignacion->id_linea)
            ->orderBy('estaciones_lineas.orden')
            ->select('estaciones.*', 'estaciones_lineas.orden')
            ->get();

        // Obtener recorridos de la asignación
        $recorridos = DB::table('recorridos')
            ->where('id_asignacion', $asignacion->id_asignacion)
            ->orderBy('hora_inicio')
            ->get();

        // Obtener pasos por estación
        $pasosEstacion = DB::table('pasos_estacion')
            ->join('estaciones', 'pasos_estacion.id_estacion', '=', 'estaciones.id_estacion')
            ->where('pasos_estacion.id_asignacion', $asignacion->id_asignacion)
            ->orderBy('pasos_estacion.hora_real')
            ->select('pasos_estacion.*', 'estaciones.nombre as estacion_nombre')
            ->get();

        // Obtener incidentes
        $incidentes = Incidente::with('estacion')
            ->where('id_asignacion', $asignacion->id_asignacion)
            ->orderBy('fecha_hora')
            ->get();

        return view('conductor.asignaciones.show', compact(
            'asignacion',
            'estaciones',
            'recorridos',
            'pasosEstacion',
            'incidentes'
        ));
    }

    /**
     * Marcar el inicio de una asignación.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function iniciarAsignacion($id)
    {
        $asignacion = Asignacion::where('id_usuario', Auth::user()->id_usuario)
            ->findOrFail($id);

        if ($asignacion->estado != 'Programado') {
            return redirect()->route('conductor.horarios')
                ->with('error', 'Esta asignación no puede ser iniciada.');
        }

        $asignacion->estado = 'En curso';
        $asignacion->save();

        return redirect()->route('conductor.horarios')
            ->with('success', 'Asignación iniciada correctamente.');
    }

    /**
     * Marcar la finalización de una asignación.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function finalizarAsignacion(Request $request, $id)
    {
        $request->validate([
            'kilometraje_final' => 'required|numeric',
            'vueltas_completas' => 'required|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $asignacion = Asignacion::where('id_usuario', Auth::user()->id_usuario)
            ->findOrFail($id);

        if ($asignacion->estado != 'En curso') {
            return redirect()->route('conductor.horarios')
                ->with('error', 'Esta asignación no puede ser finalizada.');
        }

        $asignacion->estado = 'Completado';
        $asignacion->kilometraje_final = $request->kilometraje_final;
        $asignacion->vueltas_completas = $request->vueltas_completas;
        $asignacion->observaciones = $request->observaciones;
        $asignacion->save();

        // Actualizar kilometraje del vehículo
        $vehiculo = Vehiculo::find($asignacion->id_vehiculo);
        $vehiculo->kilometraje = $request->kilometraje_final;
        $vehiculo->save();

        return redirect()->route('conductor.horarios')
            ->with('success', 'Asignación finalizada correctamente.');
    }

    /**
     * Reportar incidente durante una asignación.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reportarIncidente(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|exists:asignaciones,id_asignacion',
            'tipo_incidente' => 'required|string',
            'descripcion' => 'required|string',
            'id_estacion' => 'nullable|exists:estaciones,id_estacion',
        ]);

        $asignacion = Asignacion::where('id_usuario', Auth::user()->id_usuario)
            ->findOrFail($request->id_asignacion);

        if ($asignacion->estado != 'En curso') {
            return redirect()->route('conductor.horarios')
                ->with('error', 'No puedes reportar incidentes para esta asignación.');
        }

        $incidente = new Incidente();
        $incidente->id_asignacion = $request->id_asignacion;
        $incidente->id_estacion = $request->id_estacion;
        $incidente->tipo_incidente = $request->tipo_incidente;
        $incidente->descripcion = $request->descripcion;
        $incidente->fecha_hora = Carbon::now();
        $incidente->estado = 'Reportado';
        $incidente->impacto = $request->impacto ?? 'Medio';
        $incidente->save();

        return redirect()->route('conductor.detalles_asignacion', $request->id_asignacion)
            ->with('success', 'Incidente reportado correctamente.');
    }

    /**
     * Registrar paso por estación.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registrarPasoEstacion(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|exists:asignaciones,id_asignacion',
            'id_estacion' => 'required|exists:estaciones,id_estacion',
        ]);

        $asignacion = Asignacion::where('id_usuario', Auth::user()->id_usuario)
            ->findOrFail($request->id_asignacion);

        if ($asignacion->estado != 'En curso') {
            return redirect()->route('conductor.horarios')
                ->with('error', 'No puedes registrar pasos para esta asignación.');
        }

        DB::table('pasos_estacion')->insert([
            'id_asignacion' => $request->id_asignacion,
            'id_vehiculo' => $asignacion->id_vehiculo,
            'id_estacion' => $request->id_estacion,
            'id_linea' => $asignacion->id_linea,
            'hora_real' => Carbon::now(),
            'direccion_ruta' => $request->direccion_ruta ?? 'Norte-Sur',
            'tiempo_parada' => $request->tiempo_parada ?? 60,
            'vuelta' => $request->vuelta ?? 1,
            'estado' => 'Completado',
            'fecha_creacion' => Carbon::now()
        ]);

        return redirect()->route('conductor.detalles_asignacion', $request->id_asignacion)
            ->with('success', 'Paso por estación registrado correctamente.');
    }
}