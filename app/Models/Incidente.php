<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidente extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'incidentes';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_incidente';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_asignacion',
        'id_estacion',
        'tipo_incidente',
        'descripcion',
        'fecha_hora',
        'estado',
        'impacto',
        'retraso_estimado',
        'resolucion',
        'fecha_resolucion'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_resolucion' => 'datetime',
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
     * Obtiene la asignación asociada al incidente.
     */
    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class, 'id_asignacion');
    }

    /**
     * Obtiene la estación asociada al incidente.
     */
    public function estacion()
    {
        return $this->belongsTo(Estacion::class, 'id_estacion');
    }

    /**
     * Obtiene el conductor asociado al incidente a través de la asignación.
     */
    public function conductor()
    {
        return $this->asignacion ? $this->asignacion->usuario : null;
    }

    /**
     * Obtiene el vehículo asociado al incidente a través de la asignación.
     */
    public function vehiculo()
    {
        return $this->asignacion ? $this->asignacion->vehiculo : null;
    }

    /**
     * Verifica si el incidente está resuelto.
     *
     * @return bool
     */
    public function estaResuelto()
    {
        return $this->estado === 'Resuelto';
    }

    /**
     * Verifica si el incidente es crítico.
     *
     * @return bool
     */
    public function esCritico()
    {
        return $this->impacto === 'Crítico';
    }

    /**
     * Actualiza el estado del incidente.
     *
     * @param string $estado
     * @param string|null $resolucion
     * @return $this
     */
    public function actualizarEstado($estado, $resolucion = null)
    {
        $estadosValidos = ['Reportado', 'En atención', 'Resuelto', 'Escalado'];
        
        if (in_array($estado, $estadosValidos)) {
            $this->estado = $estado;
            
            if ($estado === 'Resuelto' && $resolucion) {
                $this->resolucion = $resolucion;
                $this->fecha_resolucion = now();
            }
            
            $this->save();
        }
        
        return $this;
    }

    /**
     * Calcula el tiempo de resolución en minutos.
     *
     * @return int|null
     */
    public function tiempoResolucion()
    {
        if ($this->fecha_resolucion) {
            return $this->fecha_hora->diffInMinutes($this->fecha_resolucion);
        }
        
        return null;
    }

    /**
     * Escala el incidente.
     *
     * @param string $motivo
     * @return $this
     */
    public function escalar($motivo)
    {
        $this->estado = 'Escalado';
        $this->resolucion = $motivo;
        $this->save();
        
        return $this;
    }

    /**
     * Obtiene una descripción corta del incidente.
     *
     * @return string
     */
    public function getDescripcionCortaAttribute()
    {
        if (strlen($this->descripcion) <= 50) {
            return $this->descripcion;
        }
        
        return substr($this->descripcion, 0, 50) . '...';
    }
}