<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;
use App\Models\Linea;
use App\Models\Estacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    /**
     * Muestra el listado de horarios.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener parámetros de filtrado
        $lineaId = $request->input('linea_id');
        $estacionId = $request->input('estacion_id');
        $diaSemana = $request->input('dia_semana');
        $tipoHora = $request->input('tipo_hora');
        $tipoServicio = $request->input('tipo_servicio');
        
        // Consulta base
        $query = Horario::with(['linea', 'estacion'])
            ->orderBy('dia_semana')
            ->orderBy('hora');
        
        // Aplicar filtros si se proporcionan
        if ($lineaId) {
            $query->where('id_linea', $lineaId);
        }
        
        if ($estacionId) {
            $query->where('id_estacion', $estacionId);
        }
        
        if ($diaSemana) {
            $query->where('dia_semana', $diaSemana);
        }
        
        if ($tipoHora) {
            $query->where('tipo_hora', $tipoHora);
        }
        
        if ($tipoServicio) {
            $query->where('tipo_servicio', $tipoServicio);
        }
        
        // Obtener resultados paginados
        $horarios = $query->paginate(20);
        
        // Obtener líneas y estaciones para los filtros
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $estaciones = Estacion::where('estado', 'Activa')->orderBy('nombre')->get();
        
        return view('horarios.index', compact('horarios', 'lineas', 'estaciones', 'lineaId', 'estacionId', 'diaSemana', 'tipoHora', 'tipoServicio'));
    }
    
    /**
     * Muestra la vista para crear un horario individual.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $estaciones = Estacion::where('estado', 'Activa')->orderBy('nombre')->get();
        
        return view('horarios.create', compact('lineas', 'estaciones'));
    }
    
    /**
     * Almacena un nuevo horario individual en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'id_linea' => 'required|exists:lineas,id_linea',
            'id_estacion' => 'required|exists:estaciones,id_estacion',
            'dia_semana' => 'required|integer|min:1|max:7',
            'hora' => 'required|date_format:H:i',
            'tipo_hora' => ['required', Rule::in(['Llegada', 'Salida'])],
            'tipo_servicio' => ['required', Rule::in(['Regular', 'Expreso', 'Económico', 'Especial'])],
            'es_feriado' => 'boolean',
            'observaciones' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('horarios.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Crear horario
        $horario = new Horario();
        $horario->id_linea = $request->input('id_linea');
        $horario->id_estacion = $request->input('id_estacion');
        $horario->dia_semana = $request->input('dia_semana');
        $horario->hora = $request->input('hora');
        $horario->tipo_hora = $request->input('tipo_hora');
        $horario->tipo_servicio = $request->input('tipo_servicio');
        $horario->es_feriado = $request->has('es_feriado');
        $horario->observaciones = $request->input('observaciones');
        $horario->save();
        
        return redirect()
            ->route('horarios.index')
            ->with('success', 'Horario creado correctamente.');
    }
    
    /**
     * Muestra la vista para editar un horario.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $horario = Horario::findOrFail($id);
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $estaciones = Estacion::where('estado', 'Activa')->orderBy('nombre')->get();
        
        return view('horarios.edit', compact('horario', 'lineas', 'estaciones'));
    }
    
    /**
     * Actualiza un horario específico en la base de datos.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'id_linea' => 'required|exists:lineas,id_linea',
            'id_estacion' => 'required|exists:estaciones,id_estacion',
            'dia_semana' => 'required|integer|min:1|max:7',
            'hora' => 'required|date_format:H:i',
            'tipo_hora' => ['required', Rule::in(['Llegada', 'Salida'])],
            'tipo_servicio' => ['required', Rule::in(['Regular', 'Expreso', 'Económico', 'Especial'])],
            'es_feriado' => 'boolean',
            'observaciones' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('horarios.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Actualizar horario
        $horario = Horario::findOrFail($id);
        $horario->id_linea = $request->input('id_linea');
        $horario->id_estacion = $request->input('id_estacion');
        $horario->dia_semana = $request->input('dia_semana');
        $horario->hora = $request->input('hora');
        $horario->tipo_hora = $request->input('tipo_hora');
        $horario->tipo_servicio = $request->input('tipo_servicio');
        $horario->es_feriado = $request->has('es_feriado');
        $horario->observaciones = $request->input('observaciones');
        $horario->save();
        
        return redirect()
            ->route('horarios.index')
            ->with('success', 'Horario actualizado correctamente.');
    }
    
    /**
     * Elimina un horario específico.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $horario = Horario::findOrFail($id);
        $horario->delete();
        
        return redirect()
            ->route('horarios.index')
            ->with('success', 'Horario eliminado correctamente.');
    }
    
    /**
     * Muestra la vista para la programación masiva de horarios.
     *
     * @return \Illuminate\View\View
     */
    public function programacion()
    {
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        
        // Obtener las estaciones relacionadas con la primera línea, o una lista vacía si no hay líneas
        $estaciones = [];
        if ($lineas->isNotEmpty()) {
            $primeraLinea = $lineas->first();
            $estaciones = DB::table('estaciones_lineas')
                ->join('estaciones', 'estaciones_lineas.id_estacion', '=', 'estaciones.id_estacion')
                ->where('estaciones_lineas.id_linea', $primeraLinea->id_linea)
                ->where('estaciones.estado', 'Activa')
                ->orderBy('estaciones_lineas.orden')
                ->select('estaciones.*')
                ->get();
        }
        
        return view('horarios.programacion', compact('lineas', 'estaciones'));
    }
    
    /**
     * Obtiene las estaciones para una línea específica (para AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEstacionesPorLinea(Request $request)
    {
        $lineaId = $request->input('linea_id');
        
        if (!$lineaId) {
            return response()->json(['estaciones' => []]);
        }
        
        $estaciones = DB::table('estaciones_lineas')
            ->join('estaciones', 'estaciones_lineas.id_estacion', '=', 'estaciones.id_estacion')
            ->where('estaciones_lineas.id_linea', $lineaId)
            ->where('estaciones.estado', 'Activa')
            ->orderBy('estaciones_lineas.orden')
            ->select('estaciones.*', 'estaciones_lineas.orden', 'estaciones_lineas.direccion')
            ->get();
            
        return response()->json(['estaciones' => $estaciones]);
    }
    
    /**
     * Procesa la generación masiva de horarios.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generarHorarios(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'id_linea' => 'required|exists:lineas,id_linea',
            'id_estacion' => 'required|exists:estaciones,id_estacion',
            'dias' => 'required|array',
            'dias.*' => 'integer|min:1|max:7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'frecuencia' => 'required|integer|min:1|max:120',
            'tipo_hora' => ['required', Rule::in(['Llegada', 'Salida'])],
            'tipo_servicio' => ['required', Rule::in(['Regular', 'Expreso', 'Económico', 'Especial'])],
            'es_feriado' => 'boolean',
            'observaciones' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('horarios.programacion')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Obtener datos de entrada
        $lineaId = $request->input('id_linea');
        $estacionId = $request->input('id_estacion');
        $dias = $request->input('dias');
        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');
        $frecuencia = $request->input('frecuencia');
        $tipoHora = $request->input('tipo_hora');
        $tipoServicio = $request->input('tipo_servicio');
        $esFeriado = $request->has('es_feriado');
        $observaciones = $request->input('observaciones');
        
        // Contador de horarios creados
        $horariosCreados = 0;
        
        // Generar horarios para cada día seleccionado
        foreach ($dias as $dia) {
            // Eliminar horarios existentes si se seleccionó la opción de reemplazar
            if ($request->has('reemplazar_existentes')) {
                Horario::where('id_linea', $lineaId)
                    ->where('id_estacion', $estacionId)
                    ->where('dia_semana', $dia)
                    ->where('tipo_hora', $tipoHora)
                    ->where('es_feriado', $esFeriado)
                    ->delete();
            }
            
            // Generar horarios
            $horaActual = Carbon::createFromFormat('H:i', $horaInicio);
            $horaFinal = Carbon::createFromFormat('H:i', $horaFin);
            
            while ($horaActual <= $horaFinal) {
                $horario = new Horario();
                $horario->id_linea = $lineaId;
                $horario->id_estacion = $estacionId;
                $horario->dia_semana = $dia;
                $horario->hora = $horaActual->format('H:i:s');
                $horario->tipo_hora = $tipoHora;
                $horario->es_feriado = $esFeriado;
                $horario->tipo_servicio = $tipoServicio;
                $horario->observaciones = $observaciones;
                $horario->save();
                
                $horariosCreados++;
                $horaActual->addMinutes($frecuencia);
            }
        }
        
        return redirect()
            ->route('horarios.index', ['linea_id' => $lineaId, 'estacion_id' => $estacionId])
            ->with('success', "Se generaron $horariosCreados horarios correctamente.");
    }
    
    /**
     * Obtiene los horarios formateados para un día y línea específicos (para AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHorariosPorLineaDia(Request $request)
    {
        $lineaId = $request->input('linea_id');
        $dia = $request->input('dia') ?: Carbon::now()->dayOfWeek;
        $esFeriado = $request->input('es_feriado') === 'true';
        
        if (!$lineaId) {
            return response()->json(['horarios' => []]);
        }
        
        // Obtener estaciones de la línea
        $estaciones = DB::table('estaciones_lineas')
            ->join('estaciones', 'estaciones_lineas.id_estacion', '=', 'estaciones.id_estacion')
            ->where('estaciones_lineas.id_linea', $lineaId)
            ->orderBy('estaciones_lineas.orden')
            ->select('estaciones.id_estacion', 'estaciones.nombre')
            ->get();
            
        $estacionesIds = $estaciones->pluck('id_estacion')->toArray();
        
        // Obtener horarios de la línea para el día seleccionado
        $horarios = Horario::with('estacion')
            ->where('id_linea', $lineaId)
            ->where('dia_semana', $dia)
            ->where('es_feriado', $esFeriado)
            ->whereIn('id_estacion', $estacionesIds)
            ->orderBy('hora')
            ->orderBy('id_estacion')
            ->get();
            
        // Organizar horarios por tipo (llegada/salida) y estación
        $horariosPorEstacion = [];
        $horasList = [];
        
        foreach ($estaciones as $estacion) {
            $horariosPorEstacion[$estacion->id_estacion] = [
                'nombre' => $estacion->nombre,
                'llegadas' => [],
                'salidas' => []
            ];
        }
        
        foreach ($horarios as $horario) {
            $hora = $horario->hora->format('H:i');
            
            if (!in_array($hora, $horasList)) {
                $horasList[] = $hora;
            }
            
            if ($horario->tipo_hora === 'Llegada') {
                $horariosPorEstacion[$horario->id_estacion]['llegadas'][] = [
                    'id' => $horario->id_horario,
                    'hora' => $hora,
                    'tipo_servicio' => $horario->tipo_servicio
                ];
            } else {
                $horariosPorEstacion[$horario->id_estacion]['salidas'][] = [
                    'id' => $horario->id_horario,
                    'hora' => $hora,
                    'tipo_servicio' => $horario->tipo_servicio
                ];
            }
        }
        
        // Ordenar horas
        sort($horasList);
        
        return response()->json([
            'estaciones' => $estaciones,
            'horarios_por_estacion' => $horariosPorEstacion,
            'horas_list' => $horasList
        ]);
    }
    
    /**
     * Obtiene la información comparativa entre horarios programados y reales.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComparativaHorarios(Request $request)
    {
        $lineaId = $request->input('linea_id');
        $fecha = $request->input('fecha') ?: Carbon::now()->format('Y-m-d');
        
        if (!$lineaId) {
            return response()->json(['datos' => []]);
        }
        
        // Obtener el día de la semana para la fecha seleccionada
        $diaSemana = Carbon::parse($fecha)->dayOfWeek;
        $diaSemana = $diaSemana === 0 ? 7 : $diaSemana; // Ajustar domingo de 0 a 7
        
        // Verificar si es feriado (aquí deberías tener una lógica para detectar feriados)
        $esFeriado = false; // Por defecto no es feriado
        
        // Obtener estaciones de la línea
        $estaciones = DB::table('estaciones_lineas')
            ->join('estaciones', 'estaciones_lineas.id_estacion', '=', 'estaciones.id_estacion')
            ->where('estaciones_lineas.id_linea', $lineaId)
            ->orderBy('estaciones_lineas.orden')
            ->select('estaciones.id_estacion', 'estaciones.nombre')
            ->get();
            
        $estacionesIds = $estaciones->pluck('id_estacion')->toArray();
        
        // Obtener horarios programados
        $horariosProgramados = Horario::where('id_linea', $lineaId)
            ->where('dia_semana', $diaSemana)
            ->where('es_feriado', $esFeriado)
            ->whereIn('id_estacion', $estacionesIds)
            ->orderBy('hora')
            ->get();
            
        // Obtener pasos reales de los vehículos por estaciones en la fecha seleccionada
        $pasosReales = DB::table('pasos_estacion')
            ->join('asignaciones', 'pasos_estacion.id_asignacion', '=', 'asignaciones.id_asignacion')
            ->where('pasos_estacion.id_linea', $lineaId)
            ->whereIn('pasos_estacion.id_estacion', $estacionesIds)
            ->whereDate('pasos_estacion.hora_real', $fecha)
            ->select('pasos_estacion.*')
            ->orderBy('pasos_estacion.hora_real')
            ->get();
            
        // Organizar datos para la comparativa
        $comparativa = [];
        
        foreach ($estaciones as $estacion) {
            $horariosPorEstacion = $horariosProgramados->where('id_estacion', $estacion->id_estacion);
            $pasosPorEstacion = $pasosReales->where('id_estacion', $estacion->id_estacion);
            
            $comparativaEstacion = [
                'id_estacion' => $estacion->id_estacion,
                'nombre' => $estacion->nombre,
                'horarios' => []
            ];
            
            // Procesar horarios programados
            foreach ($horariosPorEstacion as $horario) {
                $programado = $horario->hora->format('H:i');
                $horaProgramada = Carbon::parse($fecha . ' ' . $programado);
                
                // Buscar el paso real más cercano a este horario (dentro de +- 30 minutos)
                $pasoMasCercano = null;
                $diferenciaMinima = 30; // 30 minutos como máximo
                
                foreach ($pasosPorEstacion as $paso) {
                    $horaReal = Carbon::parse($paso->hora_real);
                    $diferenciaMinutos = abs($horaProgramada->diffInMinutes($horaReal));
                    
                    if ($diferenciaMinutos < $diferenciaMinima) {
                        $diferenciaMinima = $diferenciaMinutos;
                        $pasoMasCercano = $paso;
                    }
                }
                
                // Calcular diferencia y estado de puntualidad
                $diferencia = null;
                $puntualidad = null;
                $horaReal = null;
                
                if ($pasoMasCercano) {
                    $horaReal = Carbon::parse($pasoMasCercano->hora_real)->format('H:i');
                    $diferenciaMinutos = Carbon::parse($pasoMasCercano->hora_real)->diffInMinutes($horaProgramada, false);
                    $diferencia = $diferenciaMinutos;
                    
                    if ($diferenciaMinutos < -5) {
                        $puntualidad = 'Adelantado';
                    } elseif ($diferenciaMinutos > 5) {
                        $puntualidad = 'Retrasado';
                    } else {
                        $puntualidad = 'A tiempo';
                    }
                }
                
                $comparativaEstacion['horarios'][] = [
                    'tipo' => $horario->tipo_hora,
                    'programado' => $programado,
                    'real' => $horaReal,
                    'diferencia' => $diferencia,
                    'puntualidad' => $puntualidad,
                    'servicio' => $horario->tipo_servicio
                ];
            }
            
            $comparativa[] = $comparativaEstacion;
        }
        
        return response()->json(['datos' => $comparativa]);
    }
}