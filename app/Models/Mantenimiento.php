<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'mantenimientos';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_mantenimiento';

    /**
     * Indicar a Laravel que no use las columnas created_at y updated_at
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'id_vehiculo',
        'tipo_mantenimiento',
        'descripcion',
        'fecha_programada',
        'fecha_realizada',
        'costo',
        'proveedor',
        'resultado',
        'observaciones',
        'fecha_creacion',
        'fecha_modificacion'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada' => 'date',
        'costo' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    /**
     * Configura eventos del modelo para mantener las fechas personalizadas
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->fecha_creacion = now();
            $model->fecha_modificacion = now();
        });

        static::updating(function ($model) {
            $model->fecha_modificacion = now();
        });
    }

    /**
     * Obtiene el vehículo al que pertenece este mantenimiento.
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }
}