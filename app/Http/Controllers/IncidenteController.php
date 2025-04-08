<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\Asignacion;
use App\Models\Estacion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class IncidenteController extends Controller
{
    /**
     * Muestra una lista de todos los incidentes.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener filtros de la petición
        $fecha = $request->input('fecha');
        $tipo = $request->input('tipo_incidente');
        $estado = $request->input('estado');
        $impacto = $request->input('impacto');
        
        // Rango de fechas por defecto (último mes)
        $fechaInicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-30 days')));
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));

        // Consulta base
        $query = Incidente::with(['asignacion.usuario', 'asignacion.vehiculo', 'asignacion.linea', 'estacion']);

        // Aplicar filtros
        if ($fecha) {
            $query->whereDate('fecha_hora', $fecha);
        } else {
            $query->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        }

        if ($tipo) {
            $query->where('tipo_incidente', $tipo);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($impacto) {
            $query->where('impacto', $impacto);
        }

        // Ordenar y paginar
        $incidentes = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        // Obtener datos para los filtros
        $tipos = ['Accidente', 'Avería', 'Retraso', 'Seguridad', 'Otro'];
        $estados = ['Reportado', 'En atención', 'Resuelto', 'Escalado'];
        $impactos = ['Bajo', 'Medio', 'Alto', 'Crítico'];

        // Estadísticas
        $estadisticas = [
            'total' => Incidente::whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])->count(),
            'reportados' => Incidente::where('estado', 'Reportado')->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])->count(),
            'en_atencion' => Incidente::where('estado', 'En atención')->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])->count(),
            'resueltos' => Incidente::where('estado', 'Resuelto')->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])->count(),
            'escalados' => Incidente::where('estado', 'Escalado')->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])->count(),
            'criticos' => Incidente::where('impacto', 'Crítico')->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])->count(),
        ];

        return view('incidentes.index', compact(
            'incidentes', 
            'tipos', 
            'estados', 
            'impactos', 
            'fechaInicio', 
            'fechaFin', 
            'fecha', 
            'tipo', 
            'estado', 
            'impacto',
            'estadisticas'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo incidente.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Verifica si viene de una asignación específica
        $id_asignacion = $request->input('id_asignacion');
        $asignacion = null;
        
        if ($id_asignacion) {
            $asignacion = Asignacion::with(['usuario', 'vehiculo', 'linea'])->find($id_asignacion);
        }
        
        // Datos para el formulario
        $tipos = ['Accidente', 'Avería', 'Retraso', 'Seguridad', 'Otro'];
        $impactos = ['Bajo', 'Medio', 'Alto', 'Crítico'];
        $asignaciones = [];
        
        // Si no hay asignación específica, cargar las asignaciones activas
        if (!$asignacion) {
            $asignaciones = Asignacion::with(['usuario', 'vehiculo', 'linea'])
                ->where('fecha', date('Y-m-d'))
                ->whereIn('estado', ['Programado', 'En curso'])
                ->get();
        }
        
        // Cargar estaciones
        $estaciones = Estacion::where('estado', 'Activa')
            ->orderBy('nombre')
            ->get();
        
        return view('incidentes.create', compact('asignacion', 'asignaciones', 'estaciones', 'tipos', 'impactos'));
    }

    /**
     * Almacena un nuevo incidente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'id_asignacion' => 'nullable|exists:asignaciones,id_asignacion',
            'id_estacion' => 'nullable|exists:estaciones,id_estacion',
            'tipo_incidente' => 'required|string|in:Accidente,Avería,Retraso,Seguridad,Otro',
            'descripcion' => 'required|string|min:10',
            'fecha_hora' => 'required|date_format:Y-m-d\TH:i',
            'impacto' => 'required|string|in:Bajo,Medio,Alto,Crítico',
            'retraso_estimado' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('incidentes.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Crear el nuevo incidente
        $incidente = new Incidente([
            'id_asignacion' => $request->id_asignacion,
            'id_estacion' => $request->id_estacion,
            'tipo_incidente' => $request->tipo_incidente,
            'descripcion' => $request->descripcion,
            'fecha_hora' => $request->fecha_hora,
            'estado' => 'Reportado',
            'impacto' => $request->impacto,
            'retraso_estimado' => $request->retraso_estimado,
        ]);

        $incidente->save();

        // Si es un incidente crítico, notificar a los supervisores (implementar según necesidad)
        if ($incidente->impacto === 'Crítico') {
            // Implementar notificación
        }

        return redirect()->route('incidentes.show', $incidente->id_incidente)
            ->with('success', 'Incidente reportado exitosamente');
    }

    /**
     * Muestra la información detallada de un incidente específico.
     *
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\View\View
     */
    public function show(Incidente $incidente)
    {
        // Cargar relaciones
        $incidente->load(['asignacion.usuario', 'asignacion.vehiculo', 'asignacion.linea', 'estacion']);
        
        // Verificar si el incidente ya ha sido resuelto
        $resuelto = $incidente->estaResuelto();
        
        // Calcular tiempo transcurrido desde el reporte
        $tiempoTranscurrido = $incidente->fecha_hora->diffForHumans();
        
        // Calcular tiempo de resolución si ya se resolvió
        $tiempoResolucion = $incidente->tiempoResolucion();
        
        // Obtener incidentes relacionados (por ejemplo, de la misma asignación)
        $incidentesRelacionados = collect(); // Colección vacía por defecto
        if ($incidente->id_asignacion) {
            $incidentesRelacionados = Incidente::where('id_asignacion', $incidente->id_asignacion)
                ->where('id_incidente', '!=', $incidente->id_incidente)
                ->orderBy('fecha_hora', 'desc')
                ->limit(5)
                ->get();
        }
        
        // Actividades del incidente (si tienes esta funcionalidad)
        $actividadesIncidente = collect(); // O tu lógica para obtener actividades
        
        return view('incidentes.show', compact(
            'incidente', 
            'resuelto', 
            'tiempoTranscurrido', 
            'tiempoResolucion',
            'incidentesRelacionados',
            'actividadesIncidente'
        ));
    }

    /**
     * Muestra el formulario para editar un incidente existente.
     *
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\View\View
     */
    public function edit(Incidente $incidente)
    {
        // Cargar relaciones
        $incidente->load(['asignacion.usuario', 'asignacion.vehiculo', 'asignacion.linea', 'estacion']);
        
        // Datos para el formulario
        $tipos = ['Accidente', 'Avería', 'Retraso', 'Seguridad', 'Otro'];
        $impactos = ['Bajo', 'Medio', 'Alto', 'Crítico'];
        $estados = ['Reportado', 'En atención', 'Resuelto', 'Escalado'];
        
        // Cargar asignaciones de la fecha del incidente
        $asignaciones = Asignacion::with(['usuario', 'vehiculo', 'linea'])
            ->where('fecha', $incidente->fecha_hora->format('Y-m-d'))
            ->get();
        
        // Cargar estaciones
        $estaciones = Estacion::where('estado', 'Activa')
            ->orderBy('nombre')
            ->get();
        
        return view('incidentes.edit', compact(
            'incidente', 
            'asignaciones', 
            'estaciones', 
            'tipos', 
            'impactos', 
            'estados'
        ));
    }

    /**
     * Actualiza un incidente existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Incidente $incidente)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'id_asignacion' => 'nullable|exists:asignaciones,id_asignacion',
            'id_estacion' => 'nullable|exists:estaciones,id_estacion',
            'tipo_incidente' => 'required|string|in:Accidente,Avería,Retraso,Seguridad,Otro',
            'descripcion' => 'required|string|min:10',
            'fecha_hora' => 'required|date_format:Y-m-d\TH:i',
            'estado' => 'required|string|in:Reportado,En atención,Resuelto,Escalado',
            'impacto' => 'required|string|in:Bajo,Medio,Alto,Crítico',
            'retraso_estimado' => 'nullable|integer|min:0',
            'resolucion' => 'nullable|string|min:10',
            'fecha_resolucion' => 'nullable|date_format:Y-m-d H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->route('incidentes.edit', $incidente->id_incidente)
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar el incidente
        $incidente->id_asignacion = $request->id_asignacion;
        $incidente->id_estacion = $request->id_estacion;
        $incidente->tipo_incidente = $request->tipo_incidente;
        $incidente->descripcion = $request->descripcion;
        $incidente->fecha_hora = $request->fecha_hora;
        $incidente->impacto = $request->impacto;
        $incidente->retraso_estimado = $request->retraso_estimado;
        
        // Actualizar el estado y la resolución si es necesario
        $estadoAnterior = $incidente->estado;
        $incidente->estado = $request->estado;
        
        if ($request->estado === 'Resuelto') {
            $incidente->resolucion = $request->resolucion;
            $incidente->fecha_resolucion = $request->fecha_resolucion ?? now();
        } elseif ($request->estado === 'Escalado') {
            $incidente->resolucion = $request->resolucion;
        }
        
        $incidente->save();
        
        // Si el estado cambió a "Resuelto", notificar si es necesario
        if ($estadoAnterior !== 'Resuelto' && $incidente->estado === 'Resuelto') {
            // Implementar notificación si es necesario
        }

        return redirect()->route('incidentes.show', $incidente->id_incidente)
            ->with('success', 'Incidente actualizado exitosamente');
    }

    /**
     * Elimina un incidente de la base de datos.
     *
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Incidente $incidente)
    {
        // Solo permitir eliminar incidentes en estado "Reportado"
        if ($incidente->estado !== 'Reportado') {
            return redirect()->route('incidentes.show', $incidente->id_incidente)
                ->with('error', 'Solo se pueden eliminar incidentes en estado "Reportado"');
        }
        
        $incidente->delete();

        return redirect()->route('incidentes.index')
            ->with('success', 'Incidente eliminado exitosamente');
    }

    /**
     * Actualiza el estado de un incidente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\Http\JsonResponse
     */
    public function actualizarEstado(Request $request, Incidente $incidente)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:Reportado,En atención,Resuelto,Escalado',
            'resolucion' => 'nullable|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Guardar el estado anterior
        $estadoAnterior = $incidente->estado;

        // Actualizar el estado
        $incidente->actualizarEstado($request->estado, $request->resolucion);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'data' => [
                'incidente' => $incidente,
                'estadoAnterior' => $estadoAnterior
            ]
        ]);
    }

    /**
     * Obtiene incidentes para el dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIncidentesDashboard(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));

        // Obtener los incidentes activos (no resueltos) para la fecha seleccionada
        $incidentes = Incidente::with(['asignacion.usuario', 'asignacion.vehiculo', 'asignacion.linea', 'estacion'])
            ->whereDate('fecha_hora', $fecha)
            ->whereNotIn('estado', ['Resuelto'])
            ->orderBy('impacto', 'desc')
            ->orderBy('fecha_hora', 'desc')
            ->get();

        // Estadísticas del día
        $estadisticas = [
            'total' => Incidente::whereDate('fecha_hora', $fecha)->count(),
            'activos' => $incidentes->count(),
            'reportados' => $incidentes->where('estado', 'Reportado')->count(),
            'en_atencion' => $incidentes->where('estado', 'En atención')->count(),
            'escalados' => $incidentes->where('estado', 'Escalado')->count(),
            'resueltos' => Incidente::whereDate('fecha_hora', $fecha)->where('estado', 'Resuelto')->count(),
            'criticos' => $incidentes->where('impacto', 'Crítico')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'incidentes' => $incidentes,
                'estadisticas' => $estadisticas
            ]
        ]);
    }

    /**
     * Muestra los incidentes para un conductor.
     *
     * @return \Illuminate\View\View
     */
    public function misConductor()
    {
        // Obtener el usuario actual
        $usuario = Auth::user();

        // Verificar que sea conductor
        if (!$usuario->esConductor()) {
            return redirect()->route('dashboard')
                ->with('error', 'Esta página es solo para conductores');
        }

        // Obtener las asignaciones del conductor
        $asignaciones = $usuario->asignaciones()
            ->where('fecha', '>=', date('Y-m-d', strtotime('-30 days')))
            ->orderBy('fecha', 'desc')
            ->pluck('id_asignacion');

        // Obtener los incidentes relacionados con esas asignaciones
        $incidentes = Incidente::with(['asignacion.linea', 'estacion'])
            ->whereIn('id_asignacion', $asignaciones)
            ->orderBy('fecha_hora', 'desc')
            ->paginate(15);

        return view('incidentes.mis_conductor', compact('incidentes'));
    }

    /**
     * Muestra el formulario para que un conductor reporte un incidente.
     *
     * @return \Illuminate\View\View
     */
    public function reportarConductor()
    {
        // Obtener el usuario actual
        $usuario = Auth::user();

        // Verificar que sea conductor
        if (!$usuario->esConductor()) {
            return redirect()->route('dashboard')
                ->with('error', 'Esta página es solo para conductores');
        }

        // Obtener la asignación actual del conductor
        $asignacion = $usuario->asignacionesActuales()->first();

        if (!$asignacion) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes una asignación activa para reportar incidentes');
        }

        // Cargar datos relacionados
        $asignacion->load(['linea', 'vehiculo']);

        // Datos para el formulario
        $tipos = ['Accidente', 'Avería', 'Retraso', 'Seguridad', 'Otro'];
        $impactos = ['Bajo', 'Medio', 'Alto', 'Crítico'];

        // Obtener estaciones de la línea asignada
        $estaciones = $asignacion->linea
            ? $asignacion->linea->estaciones()->orderBy('pivot_orden')->get()
            : collect();

        return view('incidentes.reportar_conductor', compact('asignacion', 'estaciones', 'tipos', 'impactos'));
    }

    /**
     * Almacena un incidente reportado por un conductor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeReporteConductor(Request $request)
    {
        // Obtener el usuario actual
        $usuario = Auth::user();

        // Verificar que sea conductor
        if (!$usuario->esConductor()) {
            return redirect()->route('dashboard')
                ->with('error', 'Esta acción es solo para conductores');
        }

        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'id_asignacion' => 'required|exists:asignaciones,id_asignacion',
            'id_estacion' => 'nullable|exists:estaciones,id_estacion',
            'tipo_incidente' => 'required|string|in:Accidente,Avería,Retraso,Seguridad,Otro',
            'descripcion' => 'required|string|min:10',
            'impacto' => 'required|string|in:Bajo,Medio,Alto,Crítico',
            'retraso_estimado' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('incidentes.reportar_conductor')
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar que la asignación pertenezca al conductor
        $asignacion = Asignacion::find($request->id_asignacion);
        if (!$asignacion || $asignacion->id_usuario !== $usuario->id_usuario) {
            return redirect()->route('dashboard')
                ->with('error', 'La asignación seleccionada no es válida');
        }

        // Crear el nuevo incidente
        $incidente = new Incidente([
            'id_asignacion' => $request->id_asignacion,
            'id_estacion' => $request->id_estacion,
            'tipo_incidente' => $request->tipo_incidente,
            'descripcion' => $request->descripcion,
            'fecha_hora' => now(),
            'estado' => 'Reportado',
            'impacto' => $request->impacto,
            'retraso_estimado' => $request->retraso_estimado,
        ]);

        $incidente->save();

        // Si es un incidente crítico, notificar a los supervisores (implementar según necesidad)
        if ($incidente->impacto === 'Crítico') {
            // Implementar notificación
        }

        return redirect()->route('incidentes.mis_conductor')
            ->with('success', 'Incidente reportado exitosamente');
    }

    /**
     * Marca un incidente como "En atención".
     *
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atender(Incidente $incidente)
    {
        // Verificar que el incidente esté en estado "Reportado"
        if ($incidente->estado !== 'Reportado') {
            return redirect()->route('incidentes.show', $incidente->id_incidente)
                ->with('error', 'Solo se pueden atender incidentes en estado "Reportado"');
        }
        
        // Actualizar el estado del incidente
        $incidente->estado = 'En atención';
        $incidente->save();
        
        // Opcional: Registrar la actividad si tienes una tabla para esto
        // $this->registrarActividad($incidente, 'Incidente marcado como "En atención"');
        
        return redirect()->route('incidentes.show', $incidente->id_incidente)
            ->with('success', 'Incidente marcado como "En atención" exitosamente');
    }

    /**
     * Marca un incidente como "Resuelto".
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolver(Request $request, Incidente $incidente)
    {
        // Validar los datos
        $request->validate([
            'resolucion' => 'required|string|min:10'
        ]);

        // Verificar que el incidente no esté ya resuelto
        if ($incidente->estado === 'Resuelto') {
            return redirect()->route('incidentes.show', $incidente->id_incidente)
                ->with('error', 'Este incidente ya ha sido resuelto');
        }
        
        // Actualizar el incidente
        $incidente->estado = 'Resuelto';
        $incidente->resolucion = $request->resolucion;
        $incidente->fecha_resolucion = now();
        $incidente->save();
        
        // Opcional: Registrar la actividad
        // $this->registrarActividad($incidente, 'Incidente resuelto');
        
        return redirect()->route('incidentes.show', $incidente->id_incidente)
            ->with('success', 'Incidente resuelto exitosamente');
    }

    /**
     * Escala un incidente a un nivel superior.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Incidente  $incidente
     * @return \Illuminate\Http\RedirectResponse
     */
    public function escalar(Request $request, Incidente $incidente)
    {
        // Validar los datos - solo validamos el motivo de escalación, no el departamento
        $request->validate([
            'motivo_escalacion' => 'required|string|min:10',
        ]);

        // Verificar que el incidente no esté ya escalado o resuelto
        if ($incidente->estado === 'Escalado' || $incidente->estado === 'Resuelto') {
            return redirect()->route('incidentes.show', $incidente->id_incidente)
                ->with('error', 'No se puede escalar este incidente en su estado actual');
        }
        
        // Actualizar el incidente
        $incidente->estado = 'Escalado';
        $incidente->resolucion = $request->motivo_escalacion; // Usar el campo resolucion para el motivo de escalación
        
        // Guardamos la fecha de escalación en el campo fecha_resolucion
        $incidente->fecha_resolucion = now();
        
        $incidente->save();
        
        return redirect()->route('incidentes.show', $incidente->id_incidente)
            ->with('success', 'Incidente escalado exitosamente');
    }

    /**
     * Registra una actividad relacionada con el incidente.
     *
     * @param  \App\Models\Incidente  $incidente
     * @param  string  $accion
     * @param  string|null  $detalles
     * @return void
     */
    private function registrarActividad(Incidente $incidente, $accion, $detalles = null)
    {
        // Asumiendo que tienes un modelo ActividadIncidente
        \App\Models\ActividadIncidente::create([
            'id_incidente' => $incidente->id_incidente,
            'id_usuario' => auth()->id(),
            'accion' => $accion,
            'detalles' => $detalles,
            'fecha_hora' => now(),
        ]);
    }
} 