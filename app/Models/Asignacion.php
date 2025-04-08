<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'asignaciones';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_asignacion';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_usuario',
        'id_vehiculo',
        'id_linea',
        'id_turno',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'kilometraje_inicial',
        'kilometraje_final',
        'vueltas_completas',
        'observaciones'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'date',
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
     * Obtiene el usuario (conductor) asignado.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Obtiene el vehículo asignado.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    /**
     * Obtiene la línea asignada.
     */
    public function linea()
    {
        return $this->belongsTo(Linea::class, 'id_linea');
    }

    /**
     * Obtiene el turno asignado.
     */
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turno');
    }

    /**
     * Obtiene los registros GPS asociados a esta asignación.
     */
    public function registrosGPS()
    {
        return $this->hasMany(RegistroGPS::class, 'id_asignacion');
    }

    /**
     * Obtiene los incidentes reportados durante esta asignación.
     */
    public function incidentes()
    {
        return $this->hasMany(Incidente::class, 'id_asignacion');
    }

    /**
     * Obtiene los pasos por estaciones realizados durante esta asignación.
     */
    public function pasosEstacion()
    {
        return $this->hasMany(PasoEstacion::class, 'id_asignacion');
    }

    /**
     * Obtiene los recorridos (vueltas) realizados durante esta asignación.
     */
    public function recorridos()
    {
        return $this->hasMany(Recorrido::class, 'id_asignacion');
    }

    /**
     * Verifica si la asignación está activa (programada o en curso).
     *
     * @return bool
     */
    public function estaActiva()
    {
        return in_array($this->estado, ['Programado', 'En curso']);
    }

    /**
     * Inicia la asignación.
     *
     * @param int $kilometrajeInicial
     * @return $this
     */
    public function iniciar($kilometrajeInicial = null)
    {
        if ($this->estado === 'Programado') {
            $this->estado = 'En curso';
            
            if ($kilometrajeInicial !== null) {
                $this->kilometraje_inicial = $kilometrajeInicial;
            }
            
            $this->save();
        }
        
        return $this;
    }

    /**
     * Finaliza la asignación.
     *
     * @param int $kilometrajeFinal
     * @param int $vueltasCompletas
     * @param string $observaciones
     * @return $this
     */
    public function finalizar($kilometrajeFinal, $vueltasCompletas = null, $observaciones = null)
    {
        if ($this->estado === 'En curso') {
            $this->estado = 'Completado';
            $this->kilometraje_final = $kilometrajeFinal;
            
            if ($vueltasCompletas !== null) {
                $this->vueltas_completas = $vueltasCompletas;
            }
            
            if ($observaciones !== null) {
                $this->observaciones = $observaciones;
            }
            
            $this->save();
            
            // Actualizar kilometraje del vehículo
            if ($this->vehiculo) {
                $this->vehiculo->actualizarKilometraje($kilometrajeFinal);
            }
        }
        
        return $this;
    }

    /**
     * Cancela la asignación.
     *
     * @param string $observaciones
     * @return $this
     */
    public function cancelar($observaciones = null)
    {
        if (in_array($this->estado, ['Programado', 'En curso'])) {
            $this->estado = 'Cancelado';
            
            if ($observaciones !== null) {
                $this->observaciones = $observaciones;
            }
            
            $this->save();
        }
        
        return $this;
    }

    /**
     * Obtiene la última posición del vehículo asociado a esta asignación.
     *
     * @return RegistroGPS|null
     */
    public function ultimaPosicion()
    {
        return $this->registrosGPS()->latest('timestamp')->first();
    }

    /**
     * Calcula el kilometraje recorrido hasta el momento.
     *
     * @return int
     */
    public function kilometrajeRecorrido()
    {
        if ($this->kilometraje_final) {
            return $this->kilometraje_final - $this->kilometraje_inicial;
        }
        
        // Si no hay kilometraje final registrado, usar el último registro GPS si existe
        $ultimaPosicion = $this->ultimaPosicion();
        if ($ultimaPosicion && $ultimaPosicion->kilometro_ruta) {
            // Estimación basada en el kilómetro de ruta y vueltas
            $vueltasEstimadas = $ultimaPosicion->vuelta_actual ?? $this->vueltas_completas ?? 0;
            $longitudRuta = $this->linea ? $this->linea->calcularLongitudTotal() : 0;
            
            return ($vueltasEstimadas * $longitudRuta) + $ultimaPosicion->kilometro_ruta;
        }
        
        return 0;
    }
}