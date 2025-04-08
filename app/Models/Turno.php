<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'turnos';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_turno';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'hora_inicio',
        'hora_fin',
        'descripcion'
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
     * Obtiene las asignaciones relacionadas con este turno.
     */
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_turno');
    }

    /**
     * Verifica si el turno está activo en este momento.
     *
     * @return bool
     */
    public function estaActivo()
    {
        $ahora = now()->format('H:i:s');
        return $ahora >= $this->hora_inicio->format('H:i:s') && $ahora <= $this->hora_fin->format('H:i:s');
    }

    /**
     * Calcula la duración del turno en horas.
     *
     * @return float
     */
    public function getDuracionAttribute()
    {
        return $this->hora_inicio->diffInMinutes($this->hora_fin) / 60;
    }

    /**
     * Verifica si el turno es nocturno.
     *
     * @return bool
     */
    public function esNocturno()
    {
        $inicio = (int) $this->hora_inicio->format('H');
        $fin = (int) $this->hora_fin->format('H');
        
        // Si la hora de inicio es mayor que la hora fin, significa que el turno cruza la medianoche
        if ($inicio > $fin) {
            return true;
        }
        
        // O si ambas horas están en la noche (21:00 - 06:00)
        return ($inicio >= 21 || $inicio < 6) && ($fin >= 21 || $fin < 6);
    }

    /**
     * Obtiene las asignaciones del día de hoy para este turno.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function asignacionesHoy()
    {
        return $this->asignaciones()
            ->where('fecha', date('Y-m-d'))
            ->get();
    }
}