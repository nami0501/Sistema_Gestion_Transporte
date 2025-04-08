<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'horarios';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_horario';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_linea',
        'id_estacion',
        'dia_semana',
        'hora',
        'tipo_hora',
        'es_feriado',
        'tipo_servicio',
        'observaciones'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'hora' => 'datetime',
        'es_feriado' => 'boolean',
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
     * Obtiene la línea asociada a este horario.
     */
    public function linea()
    {
        return $this->belongsTo(Linea::class, 'id_linea');
    }

    /**
     * Obtiene la estación asociada a este horario.
     */
    public function estacion()
    {
        return $this->belongsTo(Estacion::class, 'id_estacion');
    }

    /**
     * Verifica si el horario es para un día específico.
     *
     * @param int $dia 1: Lunes a 7: Domingo
     * @return bool
     */
    public function esDia($dia)
    {
        return $this->dia_semana === $dia;
    }

    /**
     * Verifica si el horario es para hoy.
     *
     * @return bool
     */
    public function esHoy()
    {
        $diaActual = date('N'); // 1 (para Lunes) hasta 7 (para Domingo)
        return $this->dia_semana === $diaActual;
    }

    /**
     * Obtiene la hora formateada.
     *
     * @return string
     */
    public function getHoraFormateadaAttribute()
    {
        return $this->hora->format('H:i');
    }

    /**
     * Verifica si es horario de llegada.
     *
     * @return bool
     */
    public function esLlegada()
    {
        return $this->tipo_hora === 'Llegada';
    }

    /**
     * Verifica si es horario de salida.
     *
     * @return bool
     */
    public function esSalida()
    {
        return $this->tipo_hora === 'Salida';
    }

    /**
     * Verifica si el horario ya pasó.
     *
     * @return bool
     */
    public function yaPaso()
    {
        if (!$this->esHoy()) {
            return false;
        }
        
        $ahora = now();
        $horaHorario = $this->hora->setDate(
            $ahora->year,
            $ahora->month,
            $ahora->day
        );
        
        return $ahora > $horaHorario;
    }

    /**
     * Obtiene el nombre del día de la semana.
     *
     * @return string
     */
    public function getNombreDiaAttribute()
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        
        return $dias[$this->dia_semana] ?? 'Desconocido';
    }

    /**
     * Guarda los horarios para una línea y estación en un rango de tiempo con una frecuencia determinada.
     *
     * @param int $idLinea
     * @param int $idEstacion
     * @param int $diaSemana
     * @param string $horaInicio
     * @param string $horaFin
     * @param int $frecuenciaMinutos
     * @param string $tipoHora
     * @param string $tipoServicio
     * @param bool $esFeriado
     * @return array
     */
    public static function generarHorarios($idLinea, $idEstacion, $diaSemana, $horaInicio, $horaFin, $frecuenciaMinutos, $tipoHora, $tipoServicio, $esFeriado = false)
    {
        $horariosCreados = [];
        
        $horaActual = strtotime($horaInicio);
        $horaFinal = strtotime($horaFin);
        
        while ($horaActual <= $horaFinal) {
            $horario = new self();
            $horario->id_linea = $idLinea;
            $horario->id_estacion = $idEstacion;
            $horario->dia_semana = $diaSemana;
            $horario->hora = date('H:i:s', $horaActual);
            $horario->tipo_hora = $tipoHora;
            $horario->es_feriado = $esFeriado;
            $horario->tipo_servicio = $tipoServicio;
            $horario->save();
            
            $horariosCreados[] = $horario;
            
            $horaActual = strtotime("+{$frecuenciaMinutos} minutes", $horaActual);
        }
        
        return $horariosCreados;
    }
}