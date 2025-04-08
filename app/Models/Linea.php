<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Linea extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'lineas';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_linea';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'color',
        'hora_inicio',
        'hora_fin',
        'frecuencia_min',
        'descripcion',
        'estado'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
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
     * Obtiene las estaciones que pertenecen a esta línea.
     */
    public function estaciones()
    {
        return $this->belongsToMany(Estacion::class, 'estaciones_lineas', 'id_linea', 'id_estacion')
                    ->withPivot('orden', 'tiempo_estimado_siguiente', 'distancia_siguiente', 'kilometro_ruta', 'direccion')
                    ->orderBy('pivot_orden');
    }

    /**
     * Obtiene los vehículos asignados a esta línea.
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_linea');
    }

    /**
     * Obtiene las asignaciones de esta línea.
     */
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_linea');
    }

    /**
     * Obtiene los horarios programados para esta línea.
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_linea');
    }

    /**
     * Obtiene los recorridos de esta línea.
     */
    public function recorridos()
    {
        return $this->hasMany(Recorrido::class, 'id_linea');
    }

    /**
     * Verifica si la línea está activa.
     *
     * @return bool
     */
    public function estaActiva()
    {
        return $this->estado === 'Activa';
    }

    /**
     * Obtiene las transacciones relacionadas con esta línea.
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'id_linea');
    }

    /**
     * Obtiene las estaciones de esta línea por dirección.
     *
     * @param string $direccion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function estacionesPorDireccion($direccion)
    {
        return $this->estaciones()
                    ->wherePivot('direccion', $direccion)
                    ->orderBy('pivot_orden')
                    ->get();
    }

    /**
     * Calcula la longitud total de la línea en kilómetros.
     *
     * @return float
     */
    public function calcularLongitudTotal()
    {
        $estacionFinal = $this->estaciones()
                            ->orderBy('pivot_kilometro_ruta', 'desc')
                            ->first();
        
        return $estacionFinal ? $estacionFinal->pivot->kilometro_ruta : 0;
    }

    /**
     * Calcula el tiempo estimado de recorrido completo en minutos.
     *
     * @return int
     */
    public function calcularTiempoEstimado()
    {
        return $this->estaciones()
                    ->sum('pivot_tiempo_estimado_siguiente');
    }

    /**
     * Obtiene las estadísticas de pasajeros para esta línea.
     */
    public function estadisticasPasajeros()
    {
        return $this->hasMany(EstadisticaPasajero::class, 'id_linea');
    }
}