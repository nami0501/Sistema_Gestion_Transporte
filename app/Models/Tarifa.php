<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'tarifas';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_tarifa';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tipo',
        'monto',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
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
     * Obtiene las transacciones asociadas a esta tarifa.
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'id_tarifa');
    }

    /**
     * Verifica si la tarifa está activa.
     *
     * @return bool
     */
    public function estaActiva()
    {
        return $this->estado === 'Activa';
    }

    /**
     * Verifica si la tarifa está vigente (dentro del rango de fechas).
     *
     * @return bool
     */
    public function estaVigente()
    {
        $ahora = now()->toDateString();
        
        if ($this->fecha_fin) {
            return $this->estaActiva() && $this->fecha_inicio <= $ahora && $this->fecha_fin >= $ahora;
        }
        
        return $this->estaActiva() && $this->fecha_inicio <= $ahora;
    }

    /**
     * Obtiene todas las tarifas activas y vigentes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function tarifasVigentes()
    {
        $ahora = now()->toDateString();
        
        return self::where('estado', 'Activa')
            ->where('fecha_inicio', '<=', $ahora)
            ->where(function ($query) use ($ahora) {
                $query->where('fecha_fin', '>=', $ahora)
                    ->orWhereNull('fecha_fin');
            })
            ->get();
    }
}