<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroGPS extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'registros_gps';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_registro';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_vehiculo',
        'id_asignacion',
        'latitud',
        'longitud',
        'velocidad',
        'direccion',
        'timestamp',
        'id_estacion_cercana',
        'distancia_estacion',
        'kilometro_ruta',
        'direccion_ruta',
        'vuelta_actual',
        'estado_vehiculo'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'velocidad' => 'decimal:2',
        'distancia_estacion' => 'decimal:2',
        'kilometro_ruta' => 'decimal:2',
        'timestamp' => 'datetime',
        'fecha_creacion' => 'datetime'
    ];

    /**
     * Los atributos para las fechas de creación y actualización.
     *
     * @var array
     */
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null; // No se usa updated_at en este modelo

    /**
     * Obtiene el vehículo asociado a este registro.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    /**
     * Obtiene la asignación asociada a este registro.
     */
    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class, 'id_asignacion');
    }

    /**
     * Obtiene la estación cercana asociada a este registro.
     */
    public function estacionCercana()
    {
        return $this->belongsTo(Estacion::class, 'id_estacion_cercana');
    }

    /**
     * Verifica si el vehículo está en movimiento.
     *
     * @return bool
     */
    public function estaEnMovimiento()
    {
        return $this->estado_vehiculo === 'En movimiento';
    }

    /**
     * Verifica si el vehículo está en una estación.
     *
     * @return bool
     */
    public function estaEnEstacion()
    {
        return $this->estado_vehiculo === 'En estación';
    }

    /**
     * Verifica si el vehículo está fuera de ruta.
     *
     * @return bool
     */
    public function estaFueraDeRuta()
    {
        return $this->estado_vehiculo === 'Fuera de ruta';
    }

    /**
     * Calcula el tiempo transcurrido desde este registro.
     *
     * @return \DateInterval
     */
    public function tiempoTranscurrido()
    {
        return $this->timestamp->diff(now());
    }

    /**
     * Calcula la distancia a otro punto GPS en metros.
     *
     * @param float $latitud
     * @param float $longitud
     * @return float
     */
    public function distanciaA($latitud, $longitud)
    {
        // Fórmula de Haversine para calcular la distancia entre dos puntos geográficos
        $earthRadius = 6371000; // Radio de la Tierra en metros
        
        $latFrom = deg2rad($this->latitud);
        $lonFrom = deg2rad($this->longitud);
        $latTo = deg2rad($latitud);
        $lonTo = deg2rad($longitud);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    /**
     * Calcula el tiempo estimado de llegada a una estación en minutos.
     *
     * @param Estacion $estacion
     * @return int|null
     */
    public function tiempoEstimadoAEstacion(Estacion $estacion)
    {
        if ($this->velocidad <= 0) {
            return null;
        }
        
        $distancia = $this->distanciaA($estacion->latitud, $estacion->longitud);
        
        // Convertir distancia (metros) a tiempo (minutos) usando la velocidad actual (km/h)
        return round(($distancia / 1000) / ($this->velocidad / 60));
    }
}