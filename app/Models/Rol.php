<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_rol';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'permisos'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'permisos' => 'array',
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
     * Obtiene los usuarios con este rol.
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }

    /**
     * Verifica si el rol tiene un permiso específico.
     *
     * @param string $permiso
     * @return bool
     */
    public function tienePermiso($permiso)
    {
        return in_array($permiso, $this->permisos);
    }

    /**
     * Agrega un permiso al rol.
     *
     * @param string $permiso
     * @return $this
     */
    public function agregarPermiso($permiso)
    {
        if (!$this->tienePermiso($permiso)) {
            $permisos = $this->permisos ?? [];
            $permisos[] = $permiso;
            $this->permisos = $permisos;
        }
        
        return $this;
    }

    /**
     * Elimina un permiso del rol.
     *
     * @param string $permiso
     * @return $this
     */
    public function eliminarPermiso($permiso)
    {
        if ($this->tienePermiso($permiso)) {
            $this->permisos = array_diff($this->permisos, [$permiso]);
        }
        
        return $this;
    }
}