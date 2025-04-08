<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroGPS;
use App\Models\Vehiculo;
use App\Models\Linea;
use App\Models\Estacion;
use App\Models\Asignacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoreoController extends Controller
{
    /**
     * Muestra la vista principal del monitoreo GPS con el mapa.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener las líneas activas para el filtro
        $lineas = Linea::where('estado', 'Activa')->get();
        
        return view('monitoreo.mapa', [
            'lineas' => $lineas
        ]);
    }
    
    /**
     * Obtiene la ubicación en tiempo real de todos los vehículos activos.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehiculosActivos(Request $request)
    {
        // Filtros opcionales
        $lineaId = $request->input('linea_id');
        $vehiculoId = $request->input('vehiculo_id');
        
        // Consulta base para los registros GPS más recientes por vehículo
        $query = DB::table('registros_gps as rg')
            ->select(
                'rg.id_registro', 
                'rg.id_vehiculo', 
                'rg.latitud', 
                'rg.longitud', 
                'rg.velocidad', 
                'rg.direccion', 
                'rg.timestamp', 
                'rg.estado_vehiculo',
                'rg.vuelta_actual',
                'v.placa', 
                'v.tipo', 
                'a.id_asignacion',
                'l.nombre as linea_nombre',
                'l.color as linea_color',
                DB::raw("CONCAT(u.nombre, ' ', u.apellidos) as conductor_nombre"),
                'e.nombre as estacion_cercana',
                'rg.distancia_estacion'
            )
            ->join('vehiculos as v', 'rg.id_vehiculo', '=', 'v.id_vehiculo')
            ->join('asignaciones as a', 'rg.id_asignacion', '=', 'a.id_asignacion')
            ->join('usuarios as u', 'a.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('lineas as l', 'a.id_linea', '=', 'l.id_linea')
            ->leftJoin('estaciones as e', 'rg.id_estacion_cercana', '=', 'e.id_estacion')
            ->whereIn('rg.id_registro', function($subquery) {
                $subquery->select(DB::raw('MAX(id_registro)'))
                    ->from('registros_gps')
                    ->groupBy('id_vehiculo');
            })
            ->where('a.fecha', '=', Carbon::today()->toDateString())
            ->where('a.estado', 'En curso');
        
        // Aplicar filtros si se proporcionan
        if ($lineaId) {
            $query->where('a.id_linea', $lineaId);
        }
        
        if ($vehiculoId) {
            $query->where('v.id_vehiculo', $vehiculoId);
        }
        
        // Obtener los resultados
        $vehiculos = $query->get();
        
        // Preparar la respuesta con datos adicionales
        $response = [];
        
        foreach ($vehiculos as $vehiculo) {
            // Calcular tiempo desde la última actualización
            $lastUpdate = Carbon::parse($vehiculo->timestamp);
            $timeDiff = $lastUpdate->diffForHumans();
            
            // Agregar datos al array de respuesta
            $response[] = [
                'id_vehiculo' => $vehiculo->id_vehiculo,
                'placa' => $vehiculo->placa,
                'tipo' => $vehiculo->tipo,
                'latitud' => (float)$vehiculo->latitud,
                'longitud' => (float)$vehiculo->longitud,
                'velocidad' => (float)$vehiculo->velocidad,
                'direccion' => (int)$vehiculo->direccion,
                'estado' => $vehiculo->estado_vehiculo,
                'conductor' => $vehiculo->conductor_nombre,
                'linea' => $vehiculo->linea_nombre,
                'color' => $vehiculo->linea_color,
                'ultima_actualizacion' => $timeDiff,
                'timestamp' => $vehiculo->timestamp,
                'vuelta_actual' => $vehiculo->vuelta_actual,
                'estacion_cercana' => $vehiculo->estacion_cercana,
                'distancia_estacion' => $vehiculo->distancia_estacion,
                'id_asignacion' => $vehiculo->id_asignacion
            ];
        }
        
        return response()->json([
            'vehiculos' => $response,
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);
    }
    
    /**
     * Obtiene los detalles de un vehículo específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetallesVehiculo($id)
    {
        // Obtener la asignación activa del vehículo
        $asignacion = Asignacion::where('id_vehiculo', $id)
            ->where('fecha', Carbon::today()->toDateString())
            ->where('estado', 'En curso')
            ->with(['usuario', 'vehiculo', 'linea', 'turno'])
            ->first();
            
        if (!$asignacion) {
            return response()->json([
                'error' => 'No se encontró asignación activa para este vehículo'
            ], 404);
        }
        
        // Obtener el último registro GPS
        $ultimoRegistro = RegistroGPS::where('id_vehiculo', $id)
            ->orderBy('timestamp', 'desc')
            ->with('estacionCercana')
            ->first();
            
        if (!$ultimoRegistro) {
            return response()->json([
                'error' => 'No se encontraron registros GPS para este vehículo'
            ], 404);
        }
        
        // Calcular estadísticas
        $inicioTurno = Carbon::parse($asignacion->hora_inicio);
        $ahora = Carbon::now();
        $tiempoTranscurrido = $inicioTurno->diffInMinutes($ahora);
        
        // Calcular kilometraje recorrido
        $kilometraje = $ultimoRegistro->kilometro_ruta ?? 0;
        
        // Obtener historial de pasos por estaciones
        $pasosEstaciones = DB::table('pasos_estacion')
            ->where('id_asignacion', $asignacion->id_asignacion)
            ->join('estaciones', 'pasos_estacion.id_estacion', '=', 'estaciones.id_estacion')
            ->select('pasos_estacion.*', 'estaciones.nombre as nombre_estacion')
            ->orderBy('hora_real', 'desc')
            ->take(5)
            ->get();
        
        // Preparar la respuesta
        $response = [
            'vehiculo' => [
                'id' => $asignacion->vehiculo->id_vehiculo,
                'placa' => $asignacion->vehiculo->placa,
                'tipo' => $asignacion->vehiculo->tipo,
                'marca' => $asignacion->vehiculo->marca,
                'modelo' => $asignacion->vehiculo->modelo
            ],
            'conductor' => [
                'id' => $asignacion->usuario->id_usuario,
                'nombre' => $asignacion->usuario->nombre . ' ' . $asignacion->usuario->apellidos,
                'telefono' => $asignacion->usuario->telefono
            ],
            'asignacion' => [
                'id' => $asignacion->id_asignacion,
                'linea' => $asignacion->linea->nombre,
                'color_linea' => $asignacion->linea->color,
                'turno' => $asignacion->turno->nombre,
                'hora_inicio' => $asignacion->hora_inicio,
                'hora_fin' => $asignacion->hora_fin,
                'tiempo_transcurrido' => $tiempoTranscurrido
            ],
            'ubicacion' => [
                'latitud' => $ultimoRegistro->latitud,
                'longitud' => $ultimoRegistro->longitud,
                'velocidad' => $ultimoRegistro->velocidad,
                'direccion' => $ultimoRegistro->direccion,
                'estado' => $ultimoRegistro->estado_vehiculo,
                'ultima_actualizacion' => $ultimoRegistro->timestamp->diffForHumans(),
                'estacion_cercana' => $ultimoRegistro->estacionCercana ? $ultimoRegistro->estacionCercana->nombre : null,
                'distancia_estacion' => $ultimoRegistro->distancia_estacion,
                'vuelta_actual' => $ultimoRegistro->vuelta_actual,
                'kilometraje' => $kilometraje
            ],
            'historial_estaciones' => $pasosEstaciones
        ];
        
        return response()->json($response);
    }
    
    /**
     * Obtiene la ruta completa de un vehículo para una asignación específica.
     *
     * @param int $vehiculoId
     * @param int $asignacionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRutaVehiculo($vehiculoId, $asignacionId)
    {
        // Verificar que la asignación existe y pertenece al vehículo
        $asignacion = Asignacion::where('id_asignacion', $asignacionId)
            ->where('id_vehiculo', $vehiculoId)
            ->first();
            
        if (!$asignacion) {
            return response()->json([
                'error' => 'No se encontró la asignación especificada para este vehículo'
            ], 404);
        }
        
        // Obtener todos los registros GPS para esta asignación, ordenados por timestamp
        $registros = RegistroGPS::where('id_asignacion', $asignacionId)
            ->where('id_vehiculo', $vehiculoId)
            ->orderBy('timestamp', 'asc')
            ->get(['latitud', 'longitud', 'velocidad', 'timestamp', 'estado_vehiculo']);
            
        if ($registros->isEmpty()) {
            return response()->json([
                'error' => 'No se encontraron registros GPS para esta asignación'
            ], 404);
        }
        
        // Preparar la respuesta
        $puntos = $registros->map(function($registro) {
            return [
                'lat' => (float)$registro->latitud,
                'lng' => (float)$registro->longitud,
                'velocidad' => (float)$registro->velocidad,
                'timestamp' => $registro->timestamp->toDateTimeString(),
                'estado' => $registro->estado_vehiculo
            ];
        });
        
        return response()->json([
            'vehiculo_id' => $vehiculoId,
            'asignacion_id' => $asignacionId,
            'fecha' => $asignacion->fecha,
            'puntos' => $puntos
        ]);
    }
    
    /**
     * Obtiene las estaciones de una línea específica.
     *
     * @param int $lineaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEstacionesLinea($lineaId)
    {
        // Verificar que la línea existe
        $linea = Linea::find($lineaId);
        
        if (!$linea) {
            return response()->json([
                'error' => 'No se encontró la línea especificada'
            ], 404);
        }
        
        // Obtener las estaciones asociadas a esta línea, ordenadas por su posición en la ruta
        $estaciones = DB::table('estaciones_lineas')
            ->where('id_linea', $lineaId)
            ->join('estaciones', 'estaciones_lineas.id_estacion', '=', 'estaciones.id_estacion')
            ->select(
                'estaciones.id_estacion',
                'estaciones.nombre',
                'estaciones.latitud',
                'estaciones.longitud',
                'estaciones.es_terminal',
                'estaciones_lineas.orden',
                'estaciones_lineas.kilometro_ruta',
                'estaciones_lineas.direccion'
            )
            ->orderBy('estaciones_lineas.orden')
            ->get();
            
        if ($estaciones->isEmpty()) {
            return response()->json([
                'error' => 'No se encontraron estaciones para esta línea'
            ], 404);
        }
        
        // Preparar la respuesta
        $estacionesResponse = $estaciones->map(function($estacion) {
            return [
                'id' => $estacion->id_estacion,
                'nombre' => $estacion->nombre,
                'lat' => (float)$estacion->latitud,
                'lng' => (float)$estacion->longitud,
                'es_terminal' => (bool)$estacion->es_terminal,
                'orden' => $estacion->orden,
                'kilometro' => (float)$estacion->kilometro_ruta,
                'direccion' => $estacion->direccion
            ];
        });
        
        return response()->json([
            'linea_id' => $lineaId,
            'nombre' => $linea->nombre,
            'color' => $linea->color,
            'estaciones' => $estacionesResponse
        ]);
    }
    
    /**
     * Simula la generación de nuevos registros GPS para pruebas.
     * Este método sólo debe usarse en desarrollo.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function simularRegistrosGPS()
    {
        // Verificar si estamos en ambiente de desarrollo
        if (app()->environment('production')) {
            return response()->json([
                'error' => 'Esta funcionalidad solo está disponible en ambiente de desarrollo'
            ], 403);
        }
        
        // Obtener asignaciones activas
        $asignaciones = Asignacion::where('estado', 'En curso')
            ->with(['vehiculo', 'linea'])
            ->get();
            
        $registrosCreados = 0;
        
        foreach ($asignaciones as $asignacion) {
            // Obtener el último registro GPS del vehículo si existe
            $ultimoRegistro = RegistroGPS::where('id_vehiculo', $asignacion->id_vehiculo)
                ->orderBy('timestamp', 'desc')
                ->first();
                
            // Si no hay registro previo, crear uno inicial basado en la primera estación de la ruta
            if (!$ultimoRegistro) {
                // Obtener la primera estación de la ruta
                $primeraEstacion = DB::table('estaciones_lineas')
                    ->where('id_linea', $asignacion->id_linea)
                    ->join('estaciones', 'estaciones_lineas.id_estacion', '=', 'estaciones.id_estacion')
                    ->orderBy('estaciones_lineas.orden')
                    ->first();
                    
                if (!$primeraEstacion) {
                    continue; // Saltar si no hay estaciones definidas
                }
                
                // Crear registro inicial
                $nuevoRegistro = RegistroGPS::create([
                    'id_vehiculo' => $asignacion->id_vehiculo,
                    'id_asignacion' => $asignacion->id_asignacion,
                    'latitud' => $primeraEstacion->latitud,
                    'longitud' => $primeraEstacion->longitud,
                    'velocidad' => 0,
                    'direccion' => 0,
                    'timestamp' => Carbon::now(),
                    'id_estacion_cercana' => $primeraEstacion->id_estacion,
                    'distancia_estacion' => 0,
                    'kilometro_ruta' => 0,
                    'direccion_ruta' => 'Norte-Sur',
                    'vuelta_actual' => 1,
                    'estado_vehiculo' => 'En estación'
                ]);
                
                $registrosCreados++;
                continue;
            }
            
            // Simular movimiento desde el último registro
            $latitud = $ultimoRegistro->latitud;
            $longitud = $ultimoRegistro->longitud;
            $velocidad = rand(0, 60); // Velocidad aleatoria entre 0 y 60 km/h
            
            // Simular cambio de posición (desplazamiento pequeño aleatorio)
            $latVariacion = (rand(-10, 10) / 10000); // Variación pequeña en latitud
            $lngVariacion = (rand(-10, 10) / 10000); // Variación pequeña en longitud
            
            $nuevaLatitud = $latitud + $latVariacion;
            $nuevaLongitud = $longitud + $lngVariacion;
            
            // Determinar la estación más cercana
            $estacionCercana = DB::table('estaciones')
                ->select('id_estacion', 'latitud', 'longitud', 
                    DB::raw("6371 * acos(cos(radians($nuevaLatitud)) * cos(radians(latitud)) * cos(radians(longitud) - radians($nuevaLongitud)) + sin(radians($nuevaLatitud)) * sin(radians(latitud))) AS distancia"))
                ->orderBy('distancia')
                ->first();
            
            // Calcular distancia a la estación en metros
            $distanciaEstacion = null;
            $idEstacionCercana = null;
            
            if ($estacionCercana) {
                $distanciaEstacion = $estacionCercana->distancia * 1000; // Convertir km a metros
                $idEstacionCercana = $estacionCercana->id_estacion;
            }
            
            // Determinar estado del vehículo
            $estadoVehiculo = 'En movimiento';
            if ($velocidad < 5) {
                $estadoVehiculo = 'Detenido';
            }
            if ($distanciaEstacion && $distanciaEstacion < 50) {
                $estadoVehiculo = 'En estación';
            }
            
            // Calcular dirección (ángulo)
            $direccion = rand(0, 359); // Dirección aleatoria entre 0 y 359 grados
            
            // Crear nuevo registro
            $nuevoRegistro = RegistroGPS::create([
                'id_vehiculo' => $asignacion->id_vehiculo,
                'id_asignacion' => $asignacion->id_asignacion,
                'latitud' => $nuevaLatitud,
                'longitud' => $nuevaLongitud,
                'velocidad' => $velocidad,
                'direccion' => $direccion,
                'timestamp' => Carbon::now(),
                'id_estacion_cercana' => $idEstacionCercana,
                'distancia_estacion' => $distanciaEstacion,
                'kilometro_ruta' => $ultimoRegistro->kilometro_ruta + (rand(1, 5) / 100), // Incremento pequeño en el kilometraje
                'direccion_ruta' => $ultimoRegistro->direccion_ruta,
                'vuelta_actual' => $ultimoRegistro->vuelta_actual,
                'estado_vehiculo' => $estadoVehiculo
            ]);
            
            $registrosCreados++;
        }
        
        return response()->json([
            'success' => true,
            'mensaje' => "Se crearon $registrosCreados registros GPS simulados",
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);
    }
}