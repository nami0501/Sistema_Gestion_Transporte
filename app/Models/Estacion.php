<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'estaciones';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_estacion';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'direccion',
        'latitud',
        'longitud',
        'capacidad_maxima',
        'es_terminal',
        'estado'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'es_terminal' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    /**
     * Los atributos para las fechas de creación y actualización.
     *
     * @var array
     */
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    /**
     * Obtiene las líneas que pasan por esta estación.
     */
    public function lineas()
    {
        return $this->belongsToMany(Linea::class, 'estaciones_lineas', 'id_estacion', 'id_linea')
                    ->withPivot('orden', 'tiempo_estimado_siguiente', 'distancia_siguiente', 'kilometro_ruta', 'direccion');
    }

    /**
     * Obtiene los horarios programados para esta estación.
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_estacion');
    }

    /**
     * Obtiene los incidentes reportados en esta estación.
     */
    public function incidentes()
    {
        return $this->hasMany(Incidente::class, 'id_estacion');
    }

    /**
     * Obtiene las recargas realizadas en esta estación.
     */
    public function recargas()
    {
        return $this->hasMany(Recarga::class, 'id_estacion');
    }

    /**
     * Obtiene las transacciones de entrada en esta estación.
     */
    public function transaccionesEntrada()
    {
        return $this->hasMany(Transaccion::class, 'estacion_entrada');
    }

    /**
     * Obtiene las transacciones de salida en esta estación.
     */
    public function transaccionesSalida()
    {
        return $this->hasMany(Transaccion::class, 'estacion_salida');
    }

    /**
     * Obtiene los registros de paso por esta estación.
     */
    public function pasosEstacion()
    {
        return $this->hasMany(PasoEstacion::class, 'id_estacion');
    }

    /**
     * Obtiene las estadísticas de pasajeros para esta estación.
     */
    public function estadisticasPasajeros()
    {
        return $this->hasMany(EstadisticaPasajero::class, 'id_estacion');
    }

    /**
     * Verifica si la estación está activa.
     *
     * @return bool
     */
    public function estaActiva()
    {
        return $this->estado === 'Activa';
    }

    /**
     * Calcula la distancia a otra estación en kilómetros.
     *
     * @param Estacion $estacion
     * @return float
     */
    public function distanciaA(Estacion $estacion)
    {
        // Fórmula de Haversine para calcular la distancia entre dos puntos geográficos
        $earthRadius = 6371; // Radio de la Tierra en kilómetros
        
        $latFrom = deg2rad($this->latitud);
        $lonFrom = deg2rad($this->longitud);
        $latTo = deg2rad($estacion->latitud);
        $lonTo = deg2rad($estacion->longitud);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    /**
     * Obtiene las estaciones cercanas en un radio determinado.
     *
     * @param float $radioKm Radio en kilómetros
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function estacionesCercanas($radioKm = 1)
    {
        return Estacion::where('id_estacion', '!=', $this->id_estacion)
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud)))) AS distancia',
                [$this->latitud, $this->longitud, $this->latitud]
            )
            ->having('distancia', '<', $radioKm)
            ->orderBy('distancia')
            ->get();
    }
}