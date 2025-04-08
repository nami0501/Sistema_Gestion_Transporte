<?php

namespace App\Http\Controllers\Consulta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Estacion;
use App\Models\Linea;
use App\Models\Tarifa;
use Carbon\Carbon;

class ConsultaController extends Controller
{
    /**
     * Crear una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // Todos los usuarios pueden acceder a la sección de información
    }

    /**
     * Mostrar el centro de información.
     *
     * @return \Illuminate\Http\Response
     */
    public function informacion()
    {
        // Obtener líneas
        $lineas = Linea::where('estado', 'Activa')->get();
        
        // Obtener estaciones
        $estaciones = Estacion::where('estado', 'Activa')->get();
        
        // Mapear las líneas por estación
        $estacionLineas = [];
        $lineasNombres = [];
        
        foreach ($lineas as $linea) {
            $lineasNombres[$linea->id_linea] = $linea->nombre;
        }
        
        // Obtener la relación entre estaciones y líneas
        $estacionesLineasData = DB::table('estaciones_lineas')
            ->select('id_estacion', 'id_linea')
            ->get();
            
        foreach ($estacionesLineasData as $rel) {
            if (!isset($estacionLineas[$rel->id_estacion])) {
                $estacionLineas[$rel->id_estacion] = [];
            }
            $estacionLineas[$rel->id_estacion][] = $rel->id_linea;
        }
        
        // Asegurar que todas las estaciones tengan una entrada, aunque sea vacía
        foreach ($estaciones as $estacion) {
            if (!isset($estacionLineas[$estacion->id_estacion])) {
                $estacionLineas[$estacion->id_estacion] = [];
            }
        }
        
        // Obtener la relación detallada de líneas y estaciones
        $lineasEstaciones = [];
        foreach ($lineas as $linea) {
            $lineasEstaciones[$linea->id_linea] = DB::table('estaciones')
                ->join('estaciones_lineas', 'estaciones.id_estacion', '=', 'estaciones_lineas.id_estacion')
                ->where('estaciones_lineas.id_linea', $linea->id_linea)
                ->orderBy('estaciones_lineas.orden')
                ->select(
                    'estaciones.*',
                    'estaciones_lineas.orden',
                    'estaciones_lineas.tiempo_estimado_siguiente',
                    'estaciones_lineas.distancia_siguiente'
                )
                ->get();
        }
        
        // Obtener tarifas
        $tarifas = Tarifa::where('estado', 'Activa')->get();
        
        // Simulación de rutas de líneas para los mapas
        $lineasRutas = [];
        foreach ($lineas as $linea) {
            $lineasRutas[$linea->id_linea] = [];
        }
        
        // Datos de ejemplo para horarios (simulados)
        $horarioEjemplo = [
            [
                'nombre' => 'Terminal Norte',
                'es_terminal' => true,
                'horarios' => ['05:00', '05:30', '06:00', '06:30', '07:00', '07:30']
            ],
            [
                'nombre' => 'Estación Central',
                'es_terminal' => false,
                'horarios' => ['05:15', '05:45', '06:15', '06:45', '07:15', '07:45']
            ],
            [
                'nombre' => 'Parque Industrial',
                'es_terminal' => false,
                'horarios' => ['05:30', '06:00', '06:30', '07:00', '07:30', '08:00']
            ],
            [
                'nombre' => 'Terminal Sur',
                'es_terminal' => true,
                'horarios' => ['05:45', '06:15', '06:45', '07:15', '07:45', '08:15']
            ]
        ];
        
        // Simulación de datos detallados de estaciones
        $estacionesData = [];
        foreach ($estaciones as $estacion) {
            $lineasEstacion = [];
            foreach ($estacionLineas[$estacion->id_estacion] as $lineaId) {
                $linea = $lineas->firstWhere('id_linea', $lineaId);
                if ($linea) {
                    $lineasEstacion[] = [
                        'id_linea' => $linea->id_linea,
                        'nombre' => $linea->nombre,
                        'color' => $linea->color
                    ];
                }
            }
            
            // Simular próximas llegadas
            $proximasLlegadas = [];
            for ($i = 0; $i < rand(3, 6); $i++) {
                $lineaRandom = $lineas->random();
                $horaLlegada = Carbon::now()->addMinutes(rand(1, 30))->format('H:i');
                $proximasLlegadas[] = [
                    'linea' => $lineaRandom->nombre,
                    'color' => $lineaRandom->color,
                    'hora' => $horaLlegada,
                    'destino' => 'Terminal ' . ['Norte', 'Sur', 'Este', 'Oeste'][rand(0, 3)]
                ];
            }
            
            $estacionesData[$estacion->id_estacion] = [
                'id_estacion' => $estacion->id_estacion,
                'nombre' => $estacion->nombre,
                'direccion' => $estacion->direccion,
                'latitud' => $estacion->latitud,
                'longitud' => $estacion->longitud,
                'capacidad_maxima' => $estacion->capacidad_maxima,
                'es_terminal' => $estacion->es_terminal,
                'estado' => $estacion->estado,
                'lineas' => $lineasEstacion,
                'proximasLlegadas' => $proximasLlegadas
            ];
        }
        
        // Noticias y avisos (simulados)
        $noticias = [];
        $titulosNoticias = [
            'Cambios temporales en la ruta de la Línea 2',
            'Mantenimiento programado en Terminal Norte',
            'Nuevos horarios para días festivos',
            'Ampliación del servicio en horario nocturno',
            'Promoción especial para tarjetas de transporte'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $noticias[] = (object) [
                'id' => $i + 1,
                'titulo' => $titulosNoticias[$i],
                'contenido' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam euismod, nisl eget aliquam ultricies, nunc nisl aliquet nunc, quis aliquam nisl nunc eu nisl.',
                'fecha' => Carbon::now()->subDays(rand(0, 14))
            ];
        }
        
        // Estadísticas del sistema (simuladas)
        $totalEstaciones = $estaciones->count();
        $totalVehiculos = DB::table('vehiculos')->count();
        $totalKilometros = 250; // Simulado
        $pasajerosDiarios = 150000; // Simulado
        
        // Datos para el gráfico de pasajeros (simulados)
        $pasajerosDias = [];
        $pasajerosCantidad = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::now()->subDays($i);
            $pasajerosDias[] = $dia->format('d/m');
            $pasajerosCantidad[] = rand(120000, 180000);
        }
        
        return view('consulta.informacion', compact(
            'lineas',
            'estaciones',
            'estacionLineas',
            'lineasNombres',
            'lineasEstaciones',
            'tarifas',
            'horarioEjemplo',
            'estacionesData',
            'noticias',
            'totalEstaciones',
            'totalVehiculos',
            'totalKilometros',
            'pasajerosDiarios',
            'pasajerosDias',
            'pasajerosCantidad',
            'lineasRutas'
        ));
    }

    /**
     * Mostrar información de líneas.
     *
     * @return \Illuminate\Http\Response
     */
    public function lineas()
    {
        $lineas = Linea::where('estado', 'Activa')->get();
        
        foreach ($lineas as $linea) {
            $linea->estaciones = DB::table('estaciones')
                ->join('estaciones_lineas', 'estaciones.id_estacion', '=', 'estaciones_lineas.id_estacion')
                ->where('estaciones_lineas.id_linea', $linea->id_linea)
                ->orderBy('estaciones_lineas.orden')
                ->select('estaciones.*', 'estaciones_lineas.orden')
                ->get();
        }
        
        return view('consulta.lineas', compact('lineas'));
    }

    /**
     * Mostrar información de estaciones.
     *
     * @return \Illuminate\Http\Response
     */
    public function estaciones()
    {
        $estaciones = Estacion::where('estado', 'Activa')->get();
        
        foreach ($estaciones as $estacion) {
            $estacion->lineas = DB::table('lineas')
                ->join('estaciones_lineas', 'lineas.id_linea', '=', 'estaciones_lineas.id_linea')
                ->where('estaciones_lineas.id_estacion', $estacion->id_estacion)
                ->where('lineas.estado', 'Activa')
                ->select('lineas.*')
                ->get();
        }
        
        return view('consulta.estaciones', compact('estaciones'));
    }

    /**
     * Mostrar información de horarios.
     *
     * @return \Illuminate\Http\Response
     */
    public function horarios(Request $request)
    {
        $lineas = Linea::where('estado', 'Activa')->get();
        
        $lineaSeleccionada = $request->input('linea', $lineas->first()->id_linea ?? null);
        $direccion = $request->input('direccion', 'Norte-Sur');
        $diaSemana = $request->input('dia', Carbon::now()->dayOfWeekIso);
        
        $horarios = [];
        
        if ($lineaSeleccionada) {
            $estaciones = DB::table('estaciones')
                ->join('estaciones_lineas', 'estaciones.id_estacion', '=', 'estaciones_lineas.id_estacion')
                ->where('estaciones_lineas.id_linea', $lineaSeleccionada)
                ->where('estaciones_lineas.direccion', $direccion)
                ->orderBy('estaciones_lineas.orden')
                ->select('estaciones.*', 'estaciones_lineas.orden')
                ->get();
                
            // Obtener horarios reales de la base de datos
            $horariosDB = DB::table('horarios')
                ->where('id_linea', $lineaSeleccionada)
                ->where('dia_semana', $diaSemana)
                ->where('tipo_hora', 'Salida')
                ->where('es_feriado', false)
                ->where('tipo_servicio', 'Regular')
                ->orderBy('hora')
                ->get();
                
            // Organizar horarios por estación
            foreach ($estaciones as $estacion) {
                $horariosEstacion = $horariosDB->where('id_estacion', $estacion->id_estacion);
                $horarios[$estacion->id_estacion] = [
                    'estacion' => $estacion,
                    'horas' => $horariosEstacion->pluck('hora')->map(function($hora) {
                        return Carbon::parse($hora)->format('H:i');
                    })
                ];
            }
        }
        
        return view('consulta.horarios', compact('lineas', 'lineaSeleccionada', 'direccion', 'diaSemana', 'horarios'));
    }

    /**
     * Mostrar información de tarifas.
     *
     * @return \Illuminate\Http\Response
     */
    public function tarifas()
    {
        $tarifas = Tarifa::where('estado', 'Activa')->get();
        
        return view('consulta.tarifas', compact('tarifas'));
    }

    /**
     * Mostrar preguntas frecuentes.
     *
     * @return \Illuminate\Http\Response
     */
    public function faq()
    {
        $faqs = [
            [
                'pregunta' => '¿Cómo puedo obtener una tarjeta de transporte?',
                'respuesta' => 'Puedes obtener tu tarjeta en cualquier terminal principal o punto autorizado presentando tu documento de identidad y pagando la cuota de emisión de S/. 5.00.'
            ],
            [
                'pregunta' => '¿Cuáles son los horarios de atención?',
                'respuesta' => 'Nuestro sistema opera de lunes a domingo desde las 5:00 am hasta las 11:00 pm. Los horarios pueden variar según la línea y estación.'
            ],
            [
                'pregunta' => '¿Qué debo hacer si perdí mi tarjeta?',
                'respuesta' => 'Debes acudir a cualquier centro de atención al cliente con tu documento de identidad para reportar la pérdida y solicitar una nueva tarjeta. Se aplicará un cargo por reposición.'
            ],
            [
                'pregunta' => '¿Cómo puedo recargar mi tarjeta?',
                'respuesta' => 'Puedes recargar tu tarjeta en todas las estaciones, terminales, puntos autorizados y a través de nuestra aplicación móvil.'
            ],
            [
                'pregunta' => '¿Qué hago si el sistema no reconoce mi tarjeta?',
                'respuesta' => 'Si tu tarjeta no es reconocida, acude al personal de la estación para verificar el estado de la misma. Es posible que necesite ser reemplazada si está dañada.'
            ],
            [
                'pregunta' => '¿Existen descuentos para estudiantes y adultos mayores?',
                'respuesta' => 'Sí, ofrecemos tarifas reducidas para estudiantes debidamente acreditados, adultos mayores de 65 años y personas con discapacidad.'
            ]
        ];
        
        return view('consulta.faq', compact('faqs'));
    }
}