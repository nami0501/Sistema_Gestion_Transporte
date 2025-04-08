@extends('layouts.admin')

@section('title', 'Detalles de Usuario')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Detalles del Usuario</h6>
                    <div>
                        <a href="{{ route('usuarios.edit', $usuario->id_usuario) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0">Información Personal</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Nombre Completo</h6>
                                        <p class="text-lg font-weight-bold mb-0">{{ $usuario->nombre }} {{ $usuario->apellidos }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">DNI</h6>
                                        <p class="mb-0">{{ $usuario->dni }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Fecha de Nacimiento</h6>
                                        <p class="mb-0">{{ $usuario->fecha_nacimiento->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Dirección</h6>
                                        <p class="mb-0">{{ $usuario->direccion }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Teléfono</h6>
                                        <p class="mb-0">{{ $usuario->telefono }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0">Información de Cuenta</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Nombre de Usuario</h6>
                                        <p class="mb-0">{{ $usuario->nombre_usuario }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Email</h6>
                                        <p class="mb-0">{{ $usuario->email }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Rol</h6>
                                        <p class="mb-0">
                                            @if($usuario->rol)
                                                <span class="badge bg-primary">{{ $usuario->rol->nombre }}</span>
                                            @else
                                                <span class="badge bg-secondary">Sin rol</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Estado</h6>
                                        <p class="mb-0">
                                            @if($usuario->estado == 'Activo')
                                                <span class="badge bg-success">{{ $usuario->estado }}</span>
                                            @elseif($usuario->estado == 'Bloqueado')
                                                <span class="badge bg-danger">{{ $usuario->estado }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $usuario->estado }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Fecha de Ingreso</h6>
                                        <p class="mb-0">{{ $usuario->fecha_ingreso->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="text-uppercase text-muted mb-1">Último Acceso</h6>
                                        <p class="mb-0">
                                            @if($usuario->ultimo_acceso)
                                                {{ $usuario->ultimo_acceso->format('d/m/Y H:i:s') }}
                                            @else
                                                <span class="text-muted">Nunca</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($usuario->es_conductor)
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0">Información de Conductor</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-muted mb-1">Número de Licencia</h6>
                                                <p class="mb-0">{{ $usuario->numero_licencia ?: 'No disponible' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-muted mb-1">Tipo de Licencia</h6>
                                                <p class="mb-0">{{ $usuario->tipo_licencia ?: 'No disponible' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0">Otra Información</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-muted mb-1">Creado</h6>
                                                <p class="mb-0">{{ $usuario->fecha_creacion->format('d/m/Y H:i:s') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-muted mb-1">Última Modificación</h6>
                                                <p class="mb-0">{{ $usuario->fecha_modificacion->format('d/m/Y H:i:s') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection