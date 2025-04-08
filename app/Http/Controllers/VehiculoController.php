<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Linea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehiculoController extends Controller
{
    /**
     * Muestra una lista de todos los vehículos.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener los filtros de la petición
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');
        $id_linea = $request->input('id_linea');

        // Consulta base para vehículos
        $query = Vehiculo::with('linea');

        // Aplicar filtros si existen
        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($id_linea) {
            $query->where('id_linea', $id_linea);
        }

        // Obtener los vehículos paginados
        $vehiculos = $query->orderBy('placa')->paginate(10);

        // Obtener líneas para el filtro
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();

        // Obtener estadísticas
        $estadisticas = [
            'total' => Vehiculo::count(),
            'activos' => Vehiculo::where('estado', 'Activo')->count(),
            'mantenimiento' => Vehiculo::where('estado', 'En mantenimiento')->count(),
            'reparacion' => Vehiculo::where('estado', 'En reparación')->count(),
            'fuera_servicio' => Vehiculo::where('estado', 'Fuera de servicio')->count(),
            'baja' => Vehiculo::where('estado', 'Dado de baja')->count()
        ];

        // Obtener los tipos de vehículos para el filtro
        $tipos = Vehiculo::select('tipo')->distinct()->orderBy('tipo')->pluck('tipo');

        // Obtener los estados para el filtro
        $estados = ['Activo', 'En mantenimiento', 'En reparación', 'Fuera de servicio', 'Dado de baja'];

        return view('vehiculos.index', compact('vehiculos', 'lineas', 'estadisticas', 'tipos', 'estados', 'estado', 'tipo', 'id_linea'));
    }

    /**
     * Muestra el formulario para crear un nuevo vehículo.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $tipos = ['Bus', 'Articulado', 'Biarticulado', 'Tren', 'Vagón', 'Minibus'];
        
        return view('vehiculos.create', compact('lineas', 'tipos'));
    }

    /**
     * Almacena un nuevo vehículo en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'placa' => 'required|string|max:20|unique:vehiculos',
            'tipo' => 'required|string|max:50',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'año_fabricacion' => 'required|integer|min:1900|max:'.date('Y'),
            'capacidad_pasajeros' => 'required|integer|min:1',
            'fecha_adquisicion' => 'required|date',
            'kilometraje' => 'required|integer|min:0',
            'estado' => 'required|string|max:20',
            'id_linea' => 'nullable|exists:lineas,id_linea'
        ]);

        if ($validator->fails()) {
            return redirect()->route('vehiculos.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Crear el nuevo vehículo
        $vehiculo = Vehiculo::create($request->all());

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo creado exitosamente');
    }

    /**
     * Muestra la información detallada de un vehículo específico.
     *
     * @param  \App\Models\Vehiculo  $vehiculo
     * @return \Illuminate\View\View
     */
    public function show(Vehiculo $vehiculo)
    {
        // Cargar relaciones
        $vehiculo->load(['linea', 'asignaciones' => function($query) {
            $query->orderBy('fecha', 'desc')->limit(10);
        }]);

        // Obtener la última posición GPS si existe
        $ultimaPosicion = $vehiculo->ultimaPosicion();
        
        // Obtener las asignaciones recientes
        $asignaciones = $vehiculo->asignaciones;
        
        // Obtener los mantenimientos recientes (ajusta esto según tu modelo)
        $mantenimientos = $vehiculo->mantenimientos()->orderBy('fecha_programada', 'desc')->limit(10)->get();
        
        // Obtener los incidentes recientes (ajusta esto según tu modelo)
        $incidentes = $vehiculo->incidentes()->orderBy('fecha_hora', 'desc')->limit(10)->get();

        return view('vehiculos.show', compact('vehiculo', 'ultimaPosicion', 'asignaciones', 'mantenimientos', 'incidentes'));
    }

    /**
     * Muestra el formulario para editar un vehículo específico.
     *
     * @param  \App\Models\Vehiculo  $vehiculo
     * @return \Illuminate\View\View
     */
    public function edit(Vehiculo $vehiculo)
    {
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $tipos = ['Bus', 'Articulado', 'Biarticulado', 'Tren', 'Vagón', 'Minibus'];
        $estados = ['Activo', 'En mantenimiento', 'En reparación', 'Fuera de servicio', 'Dado de baja'];
        
        return view('vehiculos.edit', compact('vehiculo', 'lineas', 'tipos', 'estados'));
    }

    /**
     * Actualiza un vehículo específico en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehiculo  $vehiculo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'placa' => 'required|string|max:20|unique:vehiculos,placa,' . $vehiculo->id_vehiculo . ',id_vehiculo',
            'tipo' => 'required|string|max:50',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'año_fabricacion' => 'required|integer|min:1900|max:'.date('Y'),
            'capacidad_pasajeros' => 'required|integer|min:1',
            'fecha_adquisicion' => 'required|date',
            'kilometraje' => 'required|integer|min:0',
            'estado' => 'required|string|max:20',
            'id_linea' => 'nullable|exists:lineas,id_linea'
        ]);

        if ($validator->fails()) {
            return redirect()->route('vehiculos.edit', $vehiculo->id_vehiculo)
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar el vehículo
        $vehiculo->update($request->all());

        return redirect()->route('vehiculos.show', $vehiculo->id_vehiculo)
            ->with('success', 'Vehículo actualizado exitosamente');
    }

    /**
     * Elimina un vehículo específico de la base de datos.
     *
     * @param  \App\Models\Vehiculo  $vehiculo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Vehiculo $vehiculo)
    {
        // Verificar si el vehículo tiene asignaciones
        if ($vehiculo->asignaciones()->count() > 0) {
            return redirect()->route('vehiculos.show', $vehiculo->id_vehiculo)
                ->with('error', 'No se puede eliminar el vehículo porque tiene asignaciones asociadas');
        }

        $vehiculo->delete();

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado exitosamente');
    }

    /**
     * Cambia el estado de un vehículo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehiculo  $vehiculo
     * @return \Illuminate\Http\JsonResponse
     */
    public function cambiarEstado(Request $request, Vehiculo $vehiculo)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:Activo,En mantenimiento,En reparación,Fuera de servicio,Dado de baja',
            'motivo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $estadoAnterior = $vehiculo->estado;
        $vehiculo->actualizarEstado($request->input('estado'));

        // Registrar el cambio de estado (log)
        // Implementar según necesidad

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'estado' => $vehiculo->estado,
            'estadoAnterior' => $estadoAnterior
        ]);
    }

    /**
     * Obtiene la lista de vehículos disponibles para asignación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDisponibles(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        $id_linea = $request->input('id_linea');
        
        // Obtener vehículos activos que no estén asignados en la fecha seleccionada
        $query = Vehiculo::where('estado', 'Activo')
            ->whereNotIn('id_vehiculo', function($query) use ($fecha) {
                $query->select('id_vehiculo')
                    ->from('asignaciones')
                    ->where('fecha', $fecha)
                    ->whereIn('estado', ['Programado', 'En curso']);
            });
            
        // Filtrar por línea si se especifica
        if ($id_linea) {
            $query->where(function($q) use ($id_linea) {
                $q->where('id_linea', $id_linea)
                  ->orWhereNull('id_linea');
            });
        }
        
        $vehiculos = $query->orderBy('placa')->get();
        
        return response()->json($vehiculos);
    }

    /**
     * Actualiza el kilometraje de un vehículo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehiculo  $vehiculo
     * @return \Illuminate\Http\JsonResponse
     */
    public function actualizarKilometraje(Request $request, Vehiculo $vehiculo)
    {
        $validator = Validator::make($request->all(), [
            'kilometraje' => 'required|integer|min:' . $vehiculo->kilometraje,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $kilometrajeAnterior = $vehiculo->kilometraje;
        $vehiculo->actualizarKilometraje($request->input('kilometraje'));

        return response()->json([
            'success' => true,
            'message' => 'Kilometraje actualizado correctamente',
            'kilometraje' => $vehiculo->kilometraje,
            'kilometrajeAnterior' => $kilometrajeAnterior
        ]);
    }
}