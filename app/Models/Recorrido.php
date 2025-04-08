<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recorrido extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'recorridos';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_recorrido';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_asignacion',
        'id_vehiculo',
        'id_linea',
        'numero_vuelta',
        'direccion_inicial',
        'hora_inicio',
        'hora_fin',
        'kilometraje_vuelta',
        'estacion_inicio',
        'estacion_fin',
        'tiempo_estimado',
        'tiempo_real',
        'diferencia_tiempo',
        'estado',
        'observaciones'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'kilometraje_vuelta' => 'decimal:2',
        'fecha_creacion' => 'datetime',
    ];

    /**
     * Los atributos para las fechas de creación y actualización.
     *
     * @var array
     */
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null; // No se usa updated_at en este modelo

    /**
     * Obtiene la asignación asociada a este recorrido.
     */
    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class, 'id_asignacion');
    }

    /**
     * Obtiene el vehículo asociado a este recorrido.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    /**
     * Obtiene la línea asociada a este recorrido.
     */
    public function linea()
    {
        return $this->belongsTo(Linea::class, 'id_linea');
    }

    /**
     * Obtiene la estación de inicio del recorrido.
     */
    public function estacionInicio()
    {
        return $this->belongsTo(Estacion::class, 'estacion_inicio');
    }

    /**
     * Obtiene la estación final del recorrido.
     */
    public function estacionFin()
    {
        return $this->belongsTo(Estacion::class, 'estacion_fin');
    }

    /**
     * Verifica si el recorrido está en curso.
     *
     * @return bool
     */
    public function estaEnCurso()
    {
        return $this->estado === 'En curso';
    }

    /**
     * Verifica si el recorrido está completado.
     *
     * @return bool
     */
    public function estaCompletado()
    {
        return $this->estado === 'Completado';
    }

    /**
     * Verifica si el recorrido fue interrumpido.
     *
     * @return bool
     */
    public function fueInterrumpido()
    {
        return $this->estado === 'Interrumpido';
    }

    /**
     * Finaliza el recorrido.
     *
     * @param \DateTime|string $horaFin
     * @param int $estacionFin
     * @param float $kilometrajeVuelta
     * @param string|null $observaciones
     * @return $this
     */
    public function finalizar($horaFin, $estacionFin, $kilometrajeVuelta, $observaciones = null)
    {
        if ($this->estaEnCurso()) {
            $this->estado = 'Completado';
            $this->hora_fin = $horaFin;
            $this->estacion_fin = $estacionFin;
            $this->kilometraje_vuelta = $kilometrajeVuelta;
            
            if ($observaciones) {
                $this->observaciones = $observaciones;
            }
            
            // Calcular tiempo real y diferencia
            $horaInicio = $this->hora_inicio;
            if ($horaInicio) {
                $tiempoReal = $horaInicio->diffInMinutes($this->hora_fin);
                $this->tiempo_real = $tiempoReal;
                $this->diferencia_tiempo = $this->tiempo_estimado ? ($tiempoReal - $this->tiempo_estimado) : null;
            }
            
            $this->save();
            
            // Actualizar la asignación para incrementar vueltas_completas
            if ($this->asignacion) {
                $this->asignacion->increment('vueltas_completas');
            }
        }
        
        return $this;
    }

    /**
     * Interrumpe el recorrido.
     *
     * @param string $motivo
     * @return $this
     */
    public function interrumpir($motivo)
    {
        if ($this->estaEnCurso()) {
            $this->estado = 'Interrumpido';
            $this->hora_fin = now();
            $this->observaciones = $motivo;
            $this->save();
        }
        
        return $this;
    }

    /**
     * Calcula la velocidad promedio del recorrido en km/h.
     *
     * @return float|null
     */
    public function velocidadPromedio()
    {
        if ($this->kilometraje_vuelta && $this->tiempo_real && $this->tiempo_real > 0) {
            // Convertir tiempo_real (minutos) a horas
            $horasRecorrido = $this->tiempo_real / 60;
            
            // Calcular velocidad promedio (km/h)
            return round($this->kilometraje_vuelta / $horasRecorrido, 2);
        }
        
        return null;
    }

    /**
     * Verifica si el recorrido está atrasado.
     *
     * @return bool|null
     */
    public function estaAtrasado()
    {
        if ($this->diferencia_tiempo !== null) {
            return $this->diferencia_tiempo > 0;
        }
        
        return null;
    }
}