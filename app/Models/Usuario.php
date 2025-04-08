<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre_usuario',
        'contrasena',
        'dni',
        'nombre',
        'apellidos',
        'fecha_nacimiento',
        'direccion',
        'telefono',
        'email',
        'id_rol',
        'es_conductor',
        'numero_licencia',
        'tipo_licencia',
        'fecha_ingreso',
        'estado',
    ];

    /**
     * Los atributos que deben estar ocultos para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'ultimo_acceso' => 'datetime',
        'es_conductor' => 'boolean',
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
     * Obtiene el nombre de la columna de contraseña.
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    /**
     * Obtiene el rol del usuario.
     */
    // In App\Models\User.php
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
    /**
     * Obtiene las asignaciones de este usuario.
     */
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_usuario');
    }

    /**
     * Obtiene los logs de sistema de este usuario.
     */
    public function logs()
    {
        return $this->hasMany(LogSistema::class, 'id_usuario');
    }

    /**
     * Verifica si el usuario es administrador.
     *
     * @return bool
     */
    public function esAdministrador()
    {
        return $this->rol && $this->rol->nombre === 'Administrador';
    }

    /**
     * Verifica si el usuario es supervisor.
     *
     * @return bool
     */
    public function esSupervisor()
    {
        return $this->rol && $this->rol->nombre === 'Supervisor';
    }

    /**
     * Verifica si el usuario es operador.
     *
     * @return bool
     */
    public function esOperador()
    {
        return $this->rol && $this->rol->nombre === 'Operador';
    }

    /**
     * Verifica si el usuario es conductor.
     *
     * @return bool
     */
    public function esConductor()
    {
        return $this->rol && $this->rol->nombre === 'Conductor';
    }

    /**
     * Verifica si el usuario es de consulta.
     *
     * @return bool
     */
    public function esConsulta()
    {
        return $this->rol && $this->rol->nombre === 'Consulta';
    }

    /**
     * Obtiene el nombre completo del usuario.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellidos}";
    }

    /**
     * Verifica si el usuario está activo.
     *
     * @return bool
     */
    public function estaActivo()
    {
        return $this->estado === 'Activo';
    }

    /**
     * Verifica si el usuario tiene un permiso específico.
     *
     * @param string $permiso
     * @return bool
     */
    public function tienePermiso($permiso)
    {
        return $this->rol && in_array($permiso, $this->rol->permisos);
    }

    /**
     * Obtiene las asignaciones actuales del usuario.
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
     * Verifica si el usuario está disponible.
     *
     * @return bool
     */
    public function estaDisponible()
    {
        return $this->estaActivo() && $this->asignacionesActuales()->isEmpty();
    }
}