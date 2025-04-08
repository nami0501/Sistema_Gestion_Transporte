<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\Linea;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AsignacionController extends Controller
{
    /**
     * Muestra una lista de todas las asignaciones.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener filtros de la petición
        $fecha = $request->input('fecha', date('Y-m-d'));
        $estado = $request->input('estado');
        $id_linea = $request->input('id_linea');
        $id_turno = $request->input('id_turno');

        // Consulta base
        $query = Asignacion::with(['usuario', 'vehiculo', 'linea', 'turno']);

        // Aplicar filtros
        $query->where('fecha', $fecha);

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($id_linea) {
            $query->where('id_linea', $id_linea);
        }

        if ($id_turno) {
            $query->where('id_turno', $id_turno);
        }

        // Obtener las asignaciones paginadas
        $asignaciones = $query->orderBy('hora_inicio')->paginate(15);

        // Obtener datos para los filtros
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $turnos = Turno::orderBy('hora_inicio')->get();
        $estados = ['Programado', 'En curso', 'Completado', 'Cancelado'];

        // Estadísticas de la fecha seleccionada
        $estadisticas = [
            'total' => Asignacion::where('fecha', $fecha)->count(),
            'programado' => Asignacion::where('fecha', $fecha)->where('estado', 'Programado')->count(),
            'en_curso' => Asignacion::where('fecha', $fecha)->where('estado', 'En curso')->count(),
            'completado' => Asignacion::where('fecha', $fecha)->where('estado', 'Completado')->count(),
            'cancelado' => Asignacion::where('fecha', $fecha)->where('estado', 'Cancelado')->count()
        ];

        return view('asignaciones.index', compact(
            'asignaciones', 
            'fecha', 
            'estado', 
            'id_linea', 
            'id_turno', 
            'lineas', 
            'turnos', 
            'estados', 
            'estadisticas'
        ));
    }

    /**
     * Muestra el formulario para crear una nueva asignación.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Obtener datos para el formulario
        $fechaHoy = date('Y-m-d');
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $turnos = Turno::orderBy('hora_inicio')->get();
        
        // Inicializar carrito de asignación si no existe
        if (!Session::has('carrito_asignacion')) {
            Session::put('carrito_asignacion', [
                'fecha' => $fechaHoy,
                'id_turno' => null,
                'id_linea' => null,
                'asignaciones' => []
            ]);
        }
        
        $carrito = Session::get('carrito_asignacion');

        return view('asignaciones.create', compact('lineas', 'turnos', 'fechaHoy', 'carrito'));
    }

    /**
     * Obtiene conductores disponibles para asignación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConductoresDisponibles(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        $id_turno = $request->input('id_turno');
        
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'id_turno' => 'required|exists:turnos,id_turno',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Obtener el turno para conocer hora_inicio y hora_fin
        $turno = Turno::find($id_turno);
        
        if (!$turno) {
            return response()->json(['success' => false, 'message' => 'Turno no encontrado'], 404);
        }
        
        // Obtener conductores activos que no estén asignados en este horario
        $conductores = Usuario::where('es_conductor', true)
            ->where('estado', 'Activo')
            ->whereNotIn('id_usuario', function($query) use ($fecha, $turno) {
                $query->select('id_usuario')
                    ->from('asignaciones')
                    ->where('fecha', $fecha)
                    ->where(function($q) use ($turno) {
                        // Condición para verificar si hay superposición de horarios
                        $q->where(function($inner) use ($turno) {
                            $inner->where('hora_inicio', '<=', $turno->hora_inicio)
                                  ->where('hora_fin', '>=', $turno->hora_inicio);
                        })
                        ->orWhere(function($inner) use ($turno) {
                            $inner->where('hora_inicio', '<=', $turno->hora_fin)
                                  ->where('hora_fin', '>=', $turno->hora_fin);
                        })
                        ->orWhere(function($inner) use ($turno) {
                            $inner->where('hora_inicio', '>=', $turno->hora_inicio)
                                  ->where('hora_fin', '<=', $turno->hora_fin);
                        });
                    })
                    ->whereIn('estado', ['Programado', 'En curso']);
            })
            ->select('id_usuario', 'nombre', 'apellidos', 'dni', 'numero_licencia', 'tipo_licencia')
            ->orderBy('apellidos')
            ->get();
        
        // Agregar nombre completo para mostrar en el frontend
        $conductores->each(function($conductor) {
            $conductor->nombre_completo = $conductor->nombre . ' ' . $conductor->apellidos;
        });
        
        return response()->json(['success' => true, 'data' => $conductores]);
    }

    /**
     * Obtiene vehículos disponibles para asignación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehiculosDisponibles(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        $id_turno = $request->input('id_turno');
        $id_linea = $request->input('id_linea');
        
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'id_turno' => 'required|exists:turnos,id_turno',
            'id_linea' => 'required|exists:lineas,id_linea',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Obtener el turno para conocer hora_inicio y hora_fin
        $turno = Turno::find($id_turno);
        
        if (!$turno) {
            return response()->json(['success' => false, 'message' => 'Turno no encontrado'], 404);
        }
        
        // Obtener vehículos activos que no estén asignados en este horario y que pertenezcan a la línea seleccionada o no tengan línea
        $vehiculos = Vehiculo::where('estado', 'Activo')
            ->where(function($query) use ($id_linea) {
                $query->where('id_linea', $id_linea)
                      ->orWhereNull('id_linea');
            })
            ->whereNotIn('id_vehiculo', function($query) use ($fecha, $turno) {
                $query->select('id_vehiculo')
                    ->from('asignaciones')
                    ->where('fecha', $fecha)
                    ->where(function($q) use ($turno) {
                        // Condición para verificar si hay superposición de horarios
                        $q->where(function($inner) use ($turno) {
                            $inner->where('hora_inicio', '<=', $turno->hora_inicio)
                                  ->where('hora_fin', '>=', $turno->hora_inicio);
                        })
                        ->orWhere(function($inner) use ($turno) {
                            $inner->where('hora_inicio', '<=', $turno->hora_fin)
                                  ->where('hora_fin', '>=', $turno->hora_fin);
                        })
                        ->orWhere(function($inner) use ($turno) {
                            $inner->where('hora_inicio', '>=', $turno->hora_inicio)
                                  ->where('hora_fin', '<=', $turno->hora_fin);
                        });
                    })
                    ->whereIn('estado', ['Programado', 'En curso']);
            })
            ->select('id_vehiculo', 'placa', 'tipo', 'marca', 'modelo', 'kilometraje')
            ->orderBy('placa')
            ->get();
        
        return response()->json(['success' => true, 'data' => $vehiculos]);
    }

    /**
     * Guarda la configuración del carrito de asignación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configurarCarrito(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'id_turno' => 'required|exists:turnos,id_turno',
            'id_linea' => 'required|exists:lineas,id_linea',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Obtener datos
        $fecha = $request->input('fecha');
        $id_turno = $request->input('id_turno');
        $id_linea = $request->input('id_linea');
        
        // Obtener información de turno y línea
        $turno = Turno::find($id_turno);
        $linea = Linea::find($id_linea);
        
        if (!$turno || !$linea) {
            return response()->json(['success' => false, 'message' => 'Datos no encontrados'], 404);
        }
        
        // Actualizar el carrito en la sesión
        $carrito = Session::get('carrito_asignacion', [
            'asignaciones' => []
        ]);
        
        $carrito['fecha'] = $fecha;
        $carrito['id_turno'] = $id_turno;
        $carrito['id_linea'] = $id_linea;
        $carrito['turno'] = [
            'nombre' => $turno->nombre,
            'hora_inicio' => $turno->hora_inicio->format('H:i'),
            'hora_fin' => $turno->hora_fin->format('H:i')
        ];
        $carrito['linea'] = [
            'nombre' => $linea->nombre,
            'color' => $linea->color
        ];
        
        // Limpiar asignaciones previas si cambió la configuración
        if ($request->has('limpiar_asignaciones') && $request->input('limpiar_asignaciones')) {
            $carrito['asignaciones'] = [];
        }
        
        Session::put('carrito_asignacion', $carrito);
        
        return response()->json([
            'success' => true, 
            'message' => 'Configuración actualizada', 
            'data' => $carrito
        ]);
    }

    /**
     * Agrega una asignación al carrito.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function agregarAlCarrito(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_vehiculo' => 'required|exists:vehiculos,id_vehiculo',
            'kilometraje_inicial' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Verificar que el carrito está configurado
        $carrito = Session::get('carrito_asignacion');
        
        if (!$carrito || !isset($carrito['id_turno']) || !isset($carrito['id_linea'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Debe configurar primero el turno y la línea'
            ], 422);
        }
        
        // Obtener datos de usuario y vehículo
        $usuario = Usuario::find($request->id_usuario);
        $vehiculo = Vehiculo::find($request->id_vehiculo);
        
        if (!$usuario || !$vehiculo) {
            return response()->json(['success' => false, 'message' => 'Datos no encontrados'], 404);
        }
        
        // Verificar que el usuario sea conductor
        if (!$usuario->es_conductor) {
            return response()->json([
                'success' => false, 
                'message' => 'El usuario seleccionado no es conductor'
            ], 422);
        }
        
        // Verificar que el vehículo esté activo
        if ($vehiculo->estado !== 'Activo') {
            return response()->json([
                'success' => false, 
                'message' => 'El vehículo seleccionado no está activo'
            ], 422);
        }
        
        // Verificar que el kilometraje sea válido
        if ($request->kilometraje_inicial < $vehiculo->kilometraje) {
            return response()->json([
                'success' => false, 
                'message' => 'El kilometraje inicial debe ser mayor o igual al kilometraje actual del vehículo (' . $vehiculo->kilometraje . ')'
            ], 422);
        }
        
        // Generar un ID temporal único para esta asignación en el carrito
        $temp_id = uniqid();
        
        // Agregar al carrito
        $asignacion = [
            'temp_id' => $temp_id,
            'id_usuario' => $usuario->id_usuario,
            'id_vehiculo' => $vehiculo->id_vehiculo,
            'kilometraje_inicial' => $request->kilometraje_inicial,
            'usuario' => [
                'nombre' => $usuario->nombre,
                'apellidos' => $usuario->apellidos,
                'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellidos,
                'dni' => $usuario->dni,
                'numero_licencia' => $usuario->numero_licencia
            ],
            'vehiculo' => [
                'placa' => $vehiculo->placa,
                'tipo' => $vehiculo->tipo,
                'marca' => $vehiculo->marca,
                'modelo' => $vehiculo->modelo
            ]
        ];
        
        $carrito['asignaciones'][$temp_id] = $asignacion;
        Session::put('carrito_asignacion', $carrito);
        
        return response()->json([
            'success' => true, 
            'message' => 'Asignación agregada al carrito', 
            'data' => [
                'asignacion' => $asignacion,
                'carrito' => $carrito
            ]
        ]);
    }

    /**
     * Elimina una asignación del carrito.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quitarDelCarrito(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'temp_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Obtener el carrito
        $carrito = Session::get('carrito_asignacion');
        
        if (!$carrito || !isset($carrito['asignaciones']) || !isset($carrito['asignaciones'][$request->temp_id])) {
            return response()->json([
                'success' => false, 
                'message' => 'Asignación no encontrada en el carrito'
            ], 404);
        }
        
        // Eliminar la asignación del carrito
        unset($carrito['asignaciones'][$request->temp_id]);
        Session::put('carrito_asignacion', $carrito);
        
        return response()->json([
            'success' => true, 
            'message' => 'Asignación eliminada del carrito', 
            'data' => $carrito
        ]);
    }

    /**
     * Procesa y guarda todas las asignaciones del carrito.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function procesarCarrito(Request $request)
    {
        // Obtener el carrito
        $carrito = Session::get('carrito_asignacion');
        
        if (!$carrito || !isset($carrito['asignaciones']) || count($carrito['asignaciones']) == 0) {
            return redirect()->route('asignaciones.create')
                ->with('error', 'No hay asignaciones en el carrito para procesar');
        }
        
        // Obtener datos comunes
        $fecha = $carrito['fecha'];
        $id_turno = $carrito['id_turno'];
        $id_linea = $carrito['id_linea'];
        
        // Obtener el turno
        $turno = Turno::find($id_turno);
        
        if (!$turno) {
            return redirect()->route('asignaciones.create')
                ->with('error', 'El turno seleccionado no es válido');
        }
        
        $hora_inicio = $turno->hora_inicio->format('H:i:s');
        $hora_fin = $turno->hora_fin->format('H:i:s');
        
        // Procesar cada asignación
        $asignacionesCreadas = 0;
        $errores = [];
        
        foreach ($carrito['asignaciones'] as $tempId => $asignacionData) {
            try {
                // Crear la asignación
                $asignacion = new Asignacion([
                    'id_usuario' => $asignacionData['id_usuario'],
                    'id_vehiculo' => $asignacionData['id_vehiculo'],
                    'id_linea' => $id_linea,
                    'id_turno' => $id_turno,
                    'fecha' => $fecha,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin' => $hora_fin,
                    'estado' => 'Programado',
                    'kilometraje_inicial' => $asignacionData['kilometraje_inicial'],
                    'observaciones' => $request->input('observaciones')
                ]);
                
                $asignacion->save();
                $asignacionesCreadas++;
            } catch (\Exception $e) {
                $errores[] = "Error al crear asignación para el conductor {$asignacionData['usuario']['nombre_completo']} y vehículo {$asignacionData['vehiculo']['placa']}: " . $e->getMessage();
            }
        }
        
        // Limpiar el carrito después de procesar
        Session::forget('carrito_asignacion');
        
        if ($asignacionesCreadas > 0) {
            $mensaje = "Se crearon $asignacionesCreadas asignaciones correctamente";
            
            if (count($errores) > 0) {
                $mensaje .= ", pero hubo " . count($errores) . " errores";
            }
            
            return redirect()->route('asignaciones.index', ['fecha' => $fecha])
                ->with('success', $mensaje)
                ->with('errores', $errores);
        } else {
            return redirect()->route('asignaciones.create')
                ->with('error', 'No se pudo crear ninguna asignación')
                ->with('errores', $errores);
        }
    }

    /**
     * Muestra información detallada de una asignación.
     *
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\View\View
     */
    public function show(Asignacion $asignacion)
    {
        // Cargar relaciones
        $asignacion->load(['usuario', 'vehiculo', 'linea', 'turno', 'incidentes']);
        
        // Obtener la última posición GPS si existe
        $ultimaPosicion = $asignacion->ultimaPosicion();
        
        // Obtener los recorridos (vueltas) asociados a esta asignación
        $recorridos = $asignacion->recorridos()->orderBy('numero_vuelta')->get();
        
        // Calcular kilometraje recorrido
        $kilometrajeRecorrido = $asignacion->kilometrajeRecorrido();
        
        return view('asignaciones.show', compact(
            'asignacion', 
            'ultimaPosicion', 
            'recorridos', 
            'kilometrajeRecorrido'
        ));
    }

    /**
     * Muestra el formulario para editar una asignación.
     *
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\View\View
     */
    public function edit(Asignacion $asignacion)
    {
        // Si la asignación ya está completada o cancelada, no permitir edición
        if (in_array($asignacion->estado, ['Completado', 'Cancelado'])) {
            return redirect()->route('asignaciones.show', $asignacion->id_asignacion)
                ->with('error', 'No se puede editar una asignación que ya está completada o cancelada');
        }
        
        // Cargar relaciones
        $asignacion->load(['usuario', 'vehiculo', 'linea', 'turno']);
        
        // Obtener datos para los select
        $lineas = Linea::where('estado', 'Activa')->orderBy('nombre')->get();
        $turnos = Turno::orderBy('hora_inicio')->get();
        
        // No permitir editar el conductor ni el vehículo si la asignación está en curso
        $permitirEditarConductor = $asignacion->estado !== 'En curso';
        $permitirEditarVehiculo = $asignacion->estado !== 'En curso';
        
        return view('asignaciones.edit', compact(
            'asignacion', 
            'lineas', 
            'turnos', 
            'permitirEditarConductor', 
            'permitirEditarVehiculo'
        ));
    }

    /**
     * Actualiza una asignación existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\Http\RedirectResponse
     */
    
     public function update(Request $request, Asignacion $asignacion)
    {
        // Si la asignación ya está completada o cancelada, no permitir actualización
        if (in_array($asignacion->estado, ['Completado', 'Cancelado'])) {
            return redirect()->route('asignaciones.show', $asignacion->id_asignacion)
                ->with('error', 'No se puede actualizar una asignación que ya está completada o cancelada');
        }
        
        // Validaciones según el estado de la asignación
        $rules = [
            'fecha' => 'required|date',
            'id_linea' => 'required|exists:lineas,id_linea',
            'id_turno' => 'required|exists:turnos,id_turno',
            'observaciones' => 'nullable|string'
        ];
        
        // Si la asignación no está en curso, permitir cambiar conductor y vehículo
        if ($asignacion->estado !== 'En curso') {
            $rules['id_usuario'] = 'required|exists:usuarios,id_usuario';
            $rules['id_vehiculo'] = 'required|exists:vehiculos,id_vehiculo';
            $rules['kilometraje_inicial'] = 'required|integer|min:0';
        }
        
        // Si la asignación está en curso, permitir actualizar kilometraje final
        if ($asignacion->estado === 'En curso') {
            $rules['kilometraje_final'] = 'nullable|integer|min:' . $asignacion->kilometraje_inicial;
            $rules['vueltas_completas'] = 'nullable|integer|min:0';
        }
        
        // Validar los datos
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->route('asignaciones.edit', $asignacion->id_asignacion)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Obtener el turno para actualizar horas
        $turno = Turno::find($request->id_turno);
        
        if (!$turno) {
            return redirect()->route('asignaciones.edit', $asignacion->id_asignacion)
                ->with('error', 'El turno seleccionado no es válido')
                ->withInput();
        }
        
        // Actualizar campos básicos
        $asignacion->fecha = $request->fecha;
        $asignacion->id_linea = $request->id_linea;
        $asignacion->id_turno = $request->id_turno;
        $asignacion->hora_inicio = $turno->hora_inicio;
        $asignacion->hora_fin = $turno->hora_fin;
        $asignacion->observaciones = $request->observaciones;
        
        // Actualizar conductor y vehículo si se permite
        if ($asignacion->estado !== 'En curso') {
            $asignacion->id_usuario = $request->id_usuario;
            $asignacion->id_vehiculo = $request->id_vehiculo;
            $asignacion->kilometraje_inicial = $request->kilometraje_inicial;
        }
        
        // Actualizar kilometraje final y vueltas si está en curso
        if ($asignacion->estado === 'En curso' && $request->has('kilometraje_final')) {
            $asignacion->kilometraje_final = $request->kilometraje_final;
            
            if ($request->has('vueltas_completas')) {
                $asignacion->vueltas_completas = $request->vueltas_completas;
            }
        }
        
        $asignacion->save();
        
        return redirect()->route('asignaciones.show', $asignacion->id_asignacion)
            ->with('success', 'Asignación actualizada correctamente');
    }

    /**
     * Elimina una asignación existente.
     *
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Asignacion $asignacion)
    {
        // Solo permitir eliminar asignaciones que estén en estado "Programado"
        if ($asignacion->estado !== 'Programado') {
            return redirect()->route('asignaciones.show', $asignacion->id_asignacion)
                ->with('error', 'Solo se pueden eliminar asignaciones que estén en estado "Programado"');
        }
        
        // Guardar la fecha para redireccionar después
        $fecha = $asignacion->fecha;
        
        // Eliminar la asignación
        $asignacion->delete();
        
        return redirect()->route('asignaciones.index', ['fecha' => $fecha])
            ->with('success', 'Asignación eliminada correctamente');
    }

    /**
     * Inicia una asignación (cambia el estado a "En curso").
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\Http\JsonResponse
     */
    public function iniciar(Request $request, Asignacion $asignacion)
    {
        // Verificar que la asignación esté en estado "Programado"
        if ($asignacion->estado !== 'Programado') {
            return response()->json([
                'success' => false, 
                'message' => 'Solo se pueden iniciar asignaciones que estén en estado "Programado"'
            ], 422);
        }
        
        // Validar kilometraje inicial si se proporciona
        if ($request->has('kilometraje_inicial')) {
            $validator = Validator::make($request->all(), [
                'kilometraje_inicial' => 'required|integer|min:' . $asignacion->vehiculo->kilometraje,
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            
            $kilometrajeInicial = $request->kilometraje_inicial;
        } else {
            $kilometrajeInicial = $asignacion->kilometraje_inicial;
        }
        
        // Iniciar la asignación
        $asignacion->iniciar($kilometrajeInicial);
        
        return response()->json([
            'success' => true, 
            'message' => 'Asignación iniciada correctamente', 
            'data' => ['asignacion' => $asignacion]
        ]);
    }

    /**
     * Finaliza una asignación (cambia el estado a "Completado").
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalizar(Request $request, Asignacion $asignacion)
    {
        // Verificar que la asignación esté en estado "En curso"
        if ($asignacion->estado !== 'En curso') {
            return response()->json([
                'success' => false, 
                'message' => 'Solo se pueden finalizar asignaciones que estén en estado "En curso"'
            ], 422);
        }
        
        // Validar datos
        $validator = Validator::make($request->all(), [
            'kilometraje_final' => 'required|integer|min:' . $asignacion->kilometraje_inicial,
            'vueltas_completas' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Finalizar la asignación
        $asignacion->finalizar(
            $request->kilometraje_final,
            $request->input('vueltas_completas', $asignacion->vueltas_completas),
            $request->input('observaciones')
        );
        
        return response()->json([
            'success' => true, 
            'message' => 'Asignación finalizada correctamente', 
            'data' => ['asignacion' => $asignacion]
        ]);
    }

    /**
     * Cancela una asignación (cambia el estado a "Cancelado").
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar(Request $request, Asignacion $asignacion)
    {
        // Verificar que la asignación no esté ya completada o cancelada
        if (in_array($asignacion->estado, ['Completado', 'Cancelado'])) {
            return response()->json([
                'success' => false, 
                'message' => 'No se puede cancelar una asignación que ya está completada o cancelada'
            ], 422);
        }
        
        // Validar motivo
        $validator = Validator::make($request->all(), [
            'observaciones' => 'required|string|min:5',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Cancelar la asignación
        $asignacion->cancelar($request->observaciones);
        
        return response()->json([
            'success' => true, 
            'message' => 'Asignación cancelada correctamente', 
            'data' => ['asignacion' => $asignacion]
        ]);
    }

    /**
     * Muestra las asignaciones para el dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAsignacionesDashboard(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        
        // Obtener asignaciones para la fecha seleccionada con sus relaciones
        $asignaciones = Asignacion::with(['usuario', 'vehiculo', 'linea', 'turno'])
            ->where('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->get();
        
        return response()->json([
            'success' => true, 
            'data' => [
                'asignaciones' => $asignaciones,
                'estadisticas' => [
                    'total' => $asignaciones->count(),
                    'programado' => $asignaciones->where('estado', 'Programado')->count(),
                    'en_curso' => $asignaciones->where('estado', 'En curso')->count(),
                    'completado' => $asignaciones->where('estado', 'Completado')->count(),
                    'cancelado' => $asignaciones->where('estado', 'Cancelado')->count(),
                ]
            ]
        ]);
    }

    /**
     * Crea un nuevo recorrido para una asignación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asignacion  $asignacion
     * @return \Illuminate\Http\JsonResponse
     */
    public function iniciarRecorrido(Request $request, Asignacion $asignacion)
    {
        // Verificar que la asignación esté en estado "En curso"
        if ($asignacion->estado !== 'En curso') {
            return response()->json([
                'success' => false, 
                'message' => 'Solo se pueden iniciar recorridos para asignaciones en estado "En curso"'
            ], 422);
        }
        
        // Validar datos
        $validator = Validator::make($request->all(), [
            'estacion_inicio' => 'required|exists:estaciones,id_estacion',
            'direccion_inicial' => 'required|string|in:Norte-Sur,Sur-Norte,Este-Oeste,Oeste-Este',
            'tiempo_estimado' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Verificar si hay un recorrido en curso para esta asignación
        $recorridoEnCurso = $asignacion->recorridos()->where('estado', 'En curso')->first();
        
        if ($recorridoEnCurso) {
            return response()->json([
                'success' => false, 
                'message' => 'Ya existe un recorrido en curso para esta asignación'
            ], 422);
        }
        
        // Obtener el número de la próxima vuelta
        $numeroVuelta = $asignacion->recorridos()->max('numero_vuelta') + 1;
        
        // Crear el nuevo recorrido
        $recorrido = new \App\Models\Recorrido([
            'id_asignacion' => $asignacion->id_asignacion,
            'id_vehiculo' => $asignacion->id_vehiculo,
            'id_linea' => $asignacion->id_linea,
            'numero_vuelta' => $numeroVuelta,
            'direccion_inicial' => $request->direccion_inicial,
            'hora_inicio' => now(),
            'estacion_inicio' => $request->estacion_inicio,
            'tiempo_estimado' => $request->tiempo_estimado,
            'estado' => 'En curso'
        ]);
        
        $recorrido->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Recorrido iniciado correctamente',
            'data' => ['recorrido' => $recorrido]
        ]);
    }

    /**
     * Finaliza un recorrido de una asignación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recorrido  $recorrido
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalizarRecorrido(Request $request, \App\Models\Recorrido $recorrido)
    {
        // Verificar que el recorrido esté en estado "En curso"
        if ($recorrido->estado !== 'En curso') {
            return response()->json([
                'success' => false, 
                'message' => 'Solo se pueden finalizar recorridos que estén en estado "En curso"'
            ], 422);
        }
        
        // Validar datos
        $validator = Validator::make($request->all(), [
            'estacion_fin' => 'required|exists:estaciones,id_estacion',
            'kilometraje_vuelta' => 'required|numeric|min:0.1',
            'observaciones' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        // Finalizar el recorrido
        $recorrido->finalizar(
            now(),
            $request->estacion_fin,
            $request->kilometraje_vuelta,
            $request->input('observaciones')
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Recorrido finalizado correctamente',
            'data' => ['recorrido' => $recorrido]
        ]);
    }
}