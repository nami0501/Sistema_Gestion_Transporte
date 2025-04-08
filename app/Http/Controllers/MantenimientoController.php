<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MantenimientoController extends Controller
{
    /**
     * Muestra una lista de los mantenimientos.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener los filtros de la petición
        $vehiculo_id = $request->input('vehiculo_id');
        $tipo = $request->input('tipo');
        $resultado = $request->input('resultado');

        // Consulta base para mantenimientos
        $query = Mantenimiento::with('vehiculo');

        // Aplicar filtros si existen
        if ($vehiculo_id) {
            $query->where('id_vehiculo', $vehiculo_id);
        }

        if ($tipo) {
            $query->where('tipo_mantenimiento', $tipo);
        }

        if ($resultado) {
            $query->where('resultado', $resultado);
        }

        // Obtener los mantenimientos paginados
        $mantenimientos = $query->orderBy('fecha_programada', 'desc')->paginate(10);

        // Obtener vehículos para el filtro
        $vehiculos = Vehiculo::orderBy('placa')->get();

        // Obtener los tipos de mantenimiento para el filtro
        $tipos = ['Preventivo', 'Correctivo', 'Revisión técnica'];

        // Obtener los resultados para el filtro
        $resultados = ['Pendiente', 'Completado', 'Reprogramado', 'Cancelado'];

        return view('mantenimientos.index', compact('mantenimientos', 'vehiculos', 'tipos', 'resultados', 'vehiculo_id', 'tipo', 'resultado'));
    }

    /**
     * Muestra el formulario para crear un nuevo mantenimiento.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Si se proporciona un ID de vehículo, obtenerlo para prellenar
        $vehiculo_id = $request->input('vehiculo_id');
        $vehiculo = null;
        
        if ($vehiculo_id) {
            $vehiculo = Vehiculo::find($vehiculo_id);
        }
        
        // Obtener todos los vehículos para el selector
        $vehiculos = Vehiculo::orderBy('placa')->get();
        
        // Tipos de mantenimiento disponibles
        $tipos = ['Preventivo', 'Correctivo', 'Revisión técnica'];
        
        // Resultados posibles
        $resultados = ['Pendiente', 'Completado', 'Reprogramado', 'Cancelado'];
        
        return view('mantenimientos.create', compact('vehiculos', 'vehiculo', 'tipos', 'resultados'));
    }

    /**
     * Almacena un nuevo mantenimiento en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'id_vehiculo' => 'required|exists:vehiculos,id_vehiculo',
            'tipo_mantenimiento' => 'required|string|in:Preventivo,Correctivo,Revisión técnica',
            'descripcion' => 'required|string',
            'fecha_programada' => 'required|date',
            'fecha_realizada' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'proveedor' => 'nullable|string|max:100',
            'resultado' => 'required|string|in:Pendiente,Completado,Reprogramado,Cancelado',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->route('mantenimientos.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Crear el nuevo mantenimiento
        $mantenimiento = Mantenimiento::create($request->all());

        // Si el mantenimiento es completado, actualizar el estado del vehículo si es necesario
        if ($request->input('resultado') == 'Completado' && $request->input('fecha_realizada')) {
            $vehiculo = Vehiculo::find($request->input('id_vehiculo'));
            if ($vehiculo && $vehiculo->estado == 'En mantenimiento') {
                $vehiculo->estado = 'Activo';
                $vehiculo->save();
            }
        }

        return redirect()->route('mantenimientos.show', $mantenimiento->id_mantenimiento)
            ->with('success', 'Mantenimiento registrado exitosamente');
    }

    /**
     * Muestra la información detallada de un mantenimiento específico.
     *
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\View\View
     */
    public function show(Mantenimiento $mantenimiento)
    {
        // Cargar el vehículo relacionado
        $mantenimiento->load('vehiculo');
        
        return view('mantenimientos.show', compact('mantenimiento'));
    }

    /**
     * Muestra el formulario para editar un mantenimiento específico.
     *
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\View\View
     */
    public function edit(Mantenimiento $mantenimiento)
    {
        // Obtener todos los vehículos para el selector
        $vehiculos = Vehiculo::orderBy('placa')->get();
        
        // Tipos de mantenimiento disponibles
        $tipos = ['Preventivo', 'Correctivo', 'Revisión técnica'];
        
        // Resultados posibles
        $resultados = ['Pendiente', 'Completado', 'Reprogramado', 'Cancelado'];
        
        return view('mantenimientos.edit', compact('mantenimiento', 'vehiculos', 'tipos', 'resultados'));
    }

    /**
     * Actualiza un mantenimiento específico en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'id_vehiculo' => 'required|exists:vehiculos,id_vehiculo',
            'tipo_mantenimiento' => 'required|string|in:Preventivo,Correctivo,Revisión técnica',
            'descripcion' => 'required|string',
            'fecha_programada' => 'required|date',
            'fecha_realizada' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'proveedor' => 'nullable|string|max:100',
            'resultado' => 'required|string|in:Pendiente,Completado,Reprogramado,Cancelado',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->route('mantenimientos.edit', $mantenimiento->id_mantenimiento)
                ->withErrors($validator)
                ->withInput();
        }

        // Estado anterior del mantenimiento
        $resultadoAnterior = $mantenimiento->resultado;
        
        // Actualizar el mantenimiento
        $mantenimiento->update($request->all());

        // Si el mantenimiento cambia a completado, actualizar el estado del vehículo si es necesario
        if ($resultadoAnterior != 'Completado' && $request->input('resultado') == 'Completado') {
            $vehiculo = Vehiculo::find($mantenimiento->id_vehiculo);
            if ($vehiculo && $vehiculo->estado == 'En mantenimiento') {
                $vehiculo->estado = 'Activo';
                $vehiculo->save();
            }
        }

        return redirect()->route('mantenimientos.show', $mantenimiento->id_mantenimiento)
            ->with('success', 'Mantenimiento actualizado exitosamente');
    }

    /**
     * Elimina un mantenimiento específico de la base de datos.
     *
     * @param  \App\Models\Mantenimiento  $mantenimiento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Mantenimiento $mantenimiento)
    {
        // Guardar el ID del vehículo antes de eliminar
        $vehiculoId = $mantenimiento->id_vehiculo;
        
        $mantenimiento->delete();

        // Redirigir a la lista de mantenimientos del vehículo
        return redirect()->route('mantenimientos.index', ['vehiculo_id' => $vehiculoId])
            ->with('success', 'Mantenimiento eliminado exitosamente');
    }
}