<?php

namespace App\Http\Controllers;

use App\Models\Linea;
use App\Models\Estacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LineaController extends Controller
{
    /**
     * Muestra una lista de todas las líneas.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener filtros de la petición
        $estado = $request->input('estado');

        // Consulta base para líneas
        $query = Linea::withCount(['vehiculos', 'estaciones']);

        // Aplicar filtros si existen
        if ($estado) {
            $query->where('estado', $estado);
        }

        // Obtener las líneas paginadas
        $lineas = $query->orderBy('nombre')->paginate(10);

        // Obtener estadísticas
        $estadisticas = [
            'total' => Linea::count(),
            'activas' => Linea::where('estado', 'Activa')->count(),
            'suspendidas' => Linea::where('estado', 'Suspendida')->count(),
            'en_mantenimiento' => Linea::where('estado', 'En mantenimiento')->count()
        ];

        // Obtener estados para el filtro
        $estados = ['Activa', 'Suspendida', 'En mantenimiento'];

        return view('lineas.index', compact('lineas', 'estadisticas', 'estados', 'estado'));
    }

    /**
     * Muestra el formulario para crear una nueva línea.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('lineas.create');
    }

    /**
     * Almacena una nueva línea en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:lineas',
            'color' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'frecuencia_min' => 'required|integer|min:1|max:120',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|in:Activa,Suspendida,En mantenimiento'
        ]);

        if ($validator->fails()) {
            return redirect()->route('lineas.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Crear la nueva línea
        $linea = Linea::create($request->all());

        return redirect()->route('lineas.index')
            ->with('success', 'Línea creada exitosamente');
    }

    /**
     * Muestra la información detallada de una línea específica.
     *
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\View\View
     */
    public function show(Linea $linea)
    {
        // Cargar relaciones
        $linea->load(['estaciones' => function($query) {
            $query->orderBy('pivot_orden');
        }]);

        // Obtener estaciones por dirección si es necesario
        $estacionesNorteSur = $linea->estacionesPorDireccion('Norte-Sur');
        $estacionesSurNorte = $linea->estacionesPorDireccion('Sur-Norte');
        $estacionesEsteOeste = $linea->estacionesPorDireccion('Este-Oeste');
        $estacionesOesteEste = $linea->estacionesPorDireccion('Oeste-Este');

        // Obtener vehículos asignados a esta línea
        $vehiculos = $linea->vehiculos()->get();

        // Calcular estadísticas
        $longitudTotal = $linea->calcularLongitudTotal();
        $tiempoEstimado = $linea->calcularTiempoEstimado();

        return view('lineas.show', compact(
            'linea', 
            'estacionesNorteSur', 
            'estacionesSurNorte', 
            'estacionesEsteOeste', 
            'estacionesOesteEste', 
            'vehiculos', 
            'longitudTotal', 
            'tiempoEstimado'
        ));
    }

    /**
     * Muestra el formulario para editar una línea específica.
     *
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\View\View
     */
    public function edit(Linea $linea)
    {
        $estados = ['Activa', 'Suspendida', 'En mantenimiento'];
        
        return view('lineas.edit', compact('linea', 'estados'));
    }

    /**
     * Actualiza una línea específica en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Linea $linea)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:lineas,nombre,' . $linea->id_linea . ',id_linea',
            'color' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'frecuencia_min' => 'required|integer|min:1|max:120',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|in:Activa,Suspendida,En mantenimiento'
        ]);

        if ($validator->fails()) {
            return redirect()->route('lineas.edit', $linea->id_linea)
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar la línea
        $linea->update($request->all());

        return redirect()->route('lineas.show', $linea->id_linea)
            ->with('success', 'Línea actualizada exitosamente');
    }

    /**
     * Elimina una línea específica de la base de datos.
     *
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Linea $linea)
    {
        // Verificar si la línea tiene vehículos asignados
        if ($linea->vehiculos()->count() > 0) {
            return redirect()->route('lineas.show', $linea->id_linea)
                ->with('error', 'No se puede eliminar la línea porque tiene vehículos asociados');
        }

        // Verificar si la línea tiene asignaciones
        if ($linea->asignaciones()->count() > 0) {
            return redirect()->route('lineas.show', $linea->id_linea)
                ->with('error', 'No se puede eliminar la línea porque tiene asignaciones asociadas');
        }

        // Eliminar primero las relaciones en la tabla pivot
        $linea->estaciones()->detach();
        
        // Eliminar la línea
        $linea->delete();

        return redirect()->route('lineas.index')
            ->with('success', 'Línea eliminada exitosamente');
    }

    /**
     * Muestra el formulario para gestionar las estaciones de una línea.
     *
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\View\View
     */
    public function estaciones(Linea $linea)
    {
        // Cargar las estaciones actuales de la línea
        $linea->load(['estaciones' => function($query) {
            $query->orderBy('pivot_orden');
        }]);

        // Obtener todas las estaciones disponibles
        $estacionesDisponibles = Estacion::where('estado', 'Activa')
            ->whereNotIn('id_estacion', $linea->estaciones->pluck('id_estacion'))
            ->get();

        // Estaciones por dirección
        $estacionesNorteSur = $linea->estacionesPorDireccion('Norte-Sur');
        $estacionesSurNorte = $linea->estacionesPorDireccion('Sur-Norte');
        $estacionesEsteOeste = $linea->estacionesPorDireccion('Este-Oeste');
        $estacionesOesteEste = $linea->estacionesPorDireccion('Oeste-Este');

        // Direcciones disponibles
        $direcciones = ['Norte-Sur', 'Sur-Norte', 'Este-Oeste', 'Oeste-Este'];

        return view('lineas.estaciones', compact(
            'linea', 
            'estacionesDisponibles', 
            'estacionesNorteSur', 
            'estacionesSurNorte', 
            'estacionesEsteOeste', 
            'estacionesOesteEste', 
            'direcciones'
        ));
    }

    /**
     * Asocia una estación a una línea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function agregarEstacion(Request $request, Linea $linea)
    {
        // Validar datos
        $validator = Validator::make($request->all(), [
            'id_estacion' => 'required|exists:estaciones,id_estacion',
            'orden' => 'required|integer|min:1',
            'tiempo_estimado_siguiente' => 'required|integer|min:1',
            'distancia_siguiente' => 'required|numeric|min:0.1',
            'kilometro_ruta' => 'required|numeric|min:0',
            'direccion' => 'required|string|in:Norte-Sur,Sur-Norte,Este-Oeste,Oeste-Este'
        ]);

        if ($validator->fails()) {
            return redirect()->route('lineas.estaciones', $linea->id_linea)
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar si ya existe la relación
        $existente = DB::table('estaciones_lineas')
            ->where('id_linea', $linea->id_linea)
            ->where('id_estacion', $request->id_estacion)
            ->where('direccion', $request->direccion)
            ->first();

        if ($existente) {
            return redirect()->route('lineas.estaciones', $linea->id_linea)
                ->with('error', 'La estación ya está asociada a esta línea en esa dirección');
        }

        // Reordenar las estaciones si es necesario
        if ($request->has('orden')) {
            DB::table('estaciones_lineas')
                ->where('id_linea', $linea->id_linea)
                ->where('direccion', $request->direccion)
                ->where('orden', '>=', $request->orden)
                ->increment('orden');
        }

        // Asociar la estación
        $linea->estaciones()->attach($request->id_estacion, [
            'orden' => $request->orden,
            'tiempo_estimado_siguiente' => $request->tiempo_estimado_siguiente,
            'distancia_siguiente' => $request->distancia_siguiente,
            'kilometro_ruta' => $request->kilometro_ruta,
            'direccion' => $request->direccion
        ]);

        return redirect()->route('lineas.estaciones', $linea->id_linea)
            ->with('success', 'Estación agregada exitosamente');
    }

    /**
     * Desasocia una estación de una línea.
     *
     * @param  \App\Models\Linea  $linea
     * @param  int  $id_estacion
     * @param  string  $direccion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function eliminarEstacion(Linea $linea, $id_estacion, $direccion)
    {
        // Obtener el orden de la estación que se va a eliminar
        $estacionAEliminar = DB::table('estaciones_lineas')
            ->where('id_linea', $linea->id_linea)
            ->where('id_estacion', $id_estacion)
            ->where('direccion', $direccion)
            ->first();

        if (!$estacionAEliminar) {
            return redirect()->route('lineas.estaciones', $linea->id_linea)
                ->with('error', 'La estación no está asociada a esta línea');
        }

        // Eliminar la asociación
        $linea->estaciones()->newPivotStatement()
            ->where('id_linea', $linea->id_linea)
            ->where('id_estacion', $id_estacion)
            ->where('direccion', $direccion)
            ->delete();

        // Reordenar las estaciones restantes
        DB::table('estaciones_lineas')
            ->where('id_linea', $linea->id_linea)
            ->where('direccion', $direccion)
            ->where('orden', '>', $estacionAEliminar->orden)
            ->decrement('orden');

        return redirect()->route('lineas.estaciones', $linea->id_linea)
            ->with('success', 'Estación eliminada exitosamente');
    }

    /**
     * Actualiza el orden de las estaciones de una línea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\Http\JsonResponse
     */
    public function reordenarEstaciones(Request $request, Linea $linea)
    {
        $validator = Validator::make($request->all(), [
            'estaciones' => 'required|array',
            'estaciones.*.id_estacion' => 'required|exists:estaciones,id_estacion',
            'estaciones.*.orden' => 'required|integer|min:1',
            'direccion' => 'required|string|in:Norte-Sur,Sur-Norte,Este-Oeste,Oeste-Este'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $direccion = $request->direccion;

        // Actualizar el orden de cada estación
        foreach ($request->estaciones as $estacion) {
            DB::table('estaciones_lineas')
                ->where('id_linea', $linea->id_linea)
                ->where('id_estacion', $estacion['id_estacion'])
                ->where('direccion', $direccion)
                ->update(['orden' => $estacion['orden']]);
        }

        return response()->json(['success' => true, 'message' => 'Estaciones reordenadas correctamente']);
    }

    /**
     * Cambia el estado de una línea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\Http\JsonResponse
     */
    public function cambiarEstado(Request $request, Linea $linea)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:Activa,Suspendida,En mantenimiento',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $estadoAnterior = $linea->estado;
        $linea->estado = $request->estado;
        $linea->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'estado' => $linea->estado,
            'estadoAnterior' => $estadoAnterior
        ]);
    }

    /**
     * Genera horarios para una línea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Linea  $linea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generarHorarios(Request $request, Linea $linea)
    {
        $validator = Validator::make($request->all(), [
            'dias' => 'required|array',
            'dias.*' => 'integer|min:1|max:7',
            'tipo_servicio' => 'required|string|in:Regular,Expreso,Económico,Especial',
            'es_feriado' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('lineas.show', $linea->id_linea)
                ->withErrors($validator)
                ->withInput();
        }

        // Obtener las estaciones ordenadas por dirección
        $estacionesNorteSur = $linea->estacionesPorDireccion('Norte-Sur');
        $estacionesSurNorte = $linea->estacionesPorDireccion('Sur-Norte');
        
        $esFeriado = $request->has('es_feriado') ? true : false;
        $tipoServicio = $request->tipo_servicio;
        $horaInicio = $linea->hora_inicio->format('H:i:s');
        $horaFin = $linea->hora_fin->format('H:i:s');
        $frecuenciaMin = $linea->frecuencia_min;
        
        $contadorHorarios = 0;
        
        // Para cada día seleccionado
        foreach ($request->dias as $dia) {
            // Para cada dirección (si tiene estaciones en esa dirección)
            if ($estacionesNorteSur->count() > 0) {
                // Para cada estación en dirección Norte-Sur
                foreach ($estacionesNorteSur as $index => $estacion) {
                    // Calcular el tiempo de desfase para esta estación (minutos después de la hora de inicio)
                    $desfaseMinutos = 0;
                    for ($i = 0; $i < $index; $i++) {
                        // Suma los tiempos estimados hasta la estación actual
                        $desfaseMinutos += $estacionesNorteSur[$i]->pivot->tiempo_estimado_siguiente;
                    }
                    
                    // Calcular la hora de inicio para esta estación
                    $horaInicioEstacion = date('H:i:s', strtotime($horaInicio . " + $desfaseMinutos minutes"));
                    
                    // Calcular la hora fin para esta estación (ajustada por el desfase)
                    $horaFinEstacion = date('H:i:s', strtotime($horaFin . " + $desfaseMinutos minutes"));
                    
                    // Generar horarios para esta estación
                    $horarios = \App\Models\Horario::generarHorarios(
                        $linea->id_linea,
                        $estacion->id_estacion,
                        $dia,
                        $horaInicioEstacion,
                        $horaFinEstacion,
                        $frecuenciaMin,
                        $index == 0 ? 'Salida' : 'Llegada',
                        $tipoServicio,
                        $esFeriado
                    );
                    
                    $contadorHorarios += count($horarios);
                }
            }
            
            if ($estacionesSurNorte->count() > 0) {
                // Repetir el proceso para dirección Sur-Norte
                foreach ($estacionesSurNorte as $index => $estacion) {
                    $desfaseMinutos = 0;
                    for ($i = 0; $i < $index; $i++) {
                        $desfaseMinutos += $estacionesSurNorte[$i]->pivot->tiempo_estimado_siguiente;
                    }
                    
                    $horaInicioEstacion = date('H:i:s', strtotime($horaInicio . " + $desfaseMinutos minutes"));
                    $horaFinEstacion = date('H:i:s', strtotime($horaFin . " + $desfaseMinutos minutes"));
                    
                    $horarios = \App\Models\Horario::generarHorarios(
                        $linea->id_linea,
                        $estacion->id_estacion,
                        $dia,
                        $horaInicioEstacion,
                        $horaFinEstacion,
                        $frecuenciaMin,
                        $index == 0 ? 'Salida' : 'Llegada',
                        $tipoServicio,
                        $esFeriado
                    );
                    
                    $contadorHorarios += count($horarios);
                }
            }
        }
        
        return redirect()->route('lineas.show', $linea->id_linea)
            ->with('success', "Se generaron $contadorHorarios horarios correctamente");
    }
}