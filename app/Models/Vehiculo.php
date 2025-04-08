<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'vehiculos';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_vehiculo';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'placa',
        'tipo',
        'marca',
        'modelo',
        'año_fabricacion',
        'capacidad_pasajeros',
        'fecha_adquisicion',
        'kilometraje',
        'estado',
        'id_linea'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_adquisicion' => 'date',
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
     * Obtiene la línea asignada al vehículo.
     */
    public function linea()
    {
        return $this->belongsTo(Linea::class, 'id_linea');
    }

    /**
     * Obtiene las asignaciones de este vehículo.
     */
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_vehiculo');
    }

    /**
     * Obtiene los mantenimientos del vehículo.
     */
    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_vehiculo');
    }

    /**
     * Obtiene los registros GPS del vehículo.
     */
    public function registrosGPS()
    {
        return $this->hasMany(RegistroGPS::class, 'id_vehiculo');
    }

    /**
     * Verifica si el vehículo está activo.
     *
     * @return bool
     */
    public function estaActivo()
    {
        return $this->estado === 'Activo';
    }

    /**
     * Verifica si el vehículo está disponible (activo y sin asignaciones hoy).
     *
     * @return bool
     */
    public function estaDisponible()
    {
        return $this->estaActivo() && $this->asignacionesActuales()->isEmpty();
    }

    /**
     * Obtiene las asignaciones actuales del vehículo.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function asignacionesActuales()
    {
        return $this->asignaciones()
            ->where('fecha', date('Y-m-d'))
            ->whereIn('estado', ['Programado', 'En curso'])
            ->get();
    }

    /**
     * Obtiene el último registro GPS del vehículo.
     *
     * @return RegistroGPS|null
     */
    public function ultimaPosicion()
    {
        return $this->registrosGPS()->latest('timestamp')->first();
    }

    /**
     * Actualiza el kilometraje del vehículo.
     *
     * @param int $kilometraje
     * @return $this
     */
    public function actualizarKilometraje($kilometraje)
    {
        if ($kilometraje > $this->kilometraje) {
            $this->kilometraje = $kilometraje;
            $this->save();
        }
        
        return $this;
    }

    /**
     * Actualiza el estado del vehículo.
     *
     * @param string $estado
     * @return $this
     */
    public function actualizarEstado($estado)
    {
        $estadosValidos = ['Activo', 'En mantenimiento', 'En reparación', 'Fuera de servicio', 'Dado de baja'];
        
        if (in_array($estado, $estadosValidos)) {
            $this->estado = $estado;
            $this->save();
        }
        
        return $this;
    }
}