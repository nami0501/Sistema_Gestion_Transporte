<!-- resources/views/admin/usuarios/index.blade.php -->
@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Lista de Usuarios</h6>
                    <a href="{{ route('usuarios.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Usuario
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-3">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        @if($usuarios->count() > 0)
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Usuario</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->id_usuario }}</td>
                                    <td>{{ $usuario->nombre_usuario }}</td>
                                    <td>{{ $usuario->nombre_completo }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>
                                        @if($usuario->rol)
                                            <span class="badge bg-primary">{{ $usuario->rol->nombre }}</span>
                                        @else
                                            <span class="badge bg-secondary">Sin rol</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($usuario->estado == 'Activo')
                                            <span class="badge bg-success">{{ $usuario->estado }}</span>
                                        @elseif($usuario->estado == 'Bloqueado')
                                            <span class="badge bg-danger">{{ $usuario->estado }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ $usuario->estado }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('usuarios.show', $usuario->id_usuario) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('usuarios.edit', $usuario->id_usuario) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $usuario->id_usuario }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            <nav>
                                <ul class="pagination">
                                    <li class="page-item {{ $usuarios->currentPage() == 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $usuarios->previousPageUrl() }}" tabindex="-1">Anterior</a>
                                    </li>
                                    
                                    @for ($i = 1; $i <= $usuarios->lastPage(); $i++)
                                        <li class="page-item {{ $usuarios->currentPage() == $i ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $usuarios->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor
                                    
                                    <li class="page-item {{ $usuarios->currentPage() == $usuarios->lastPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $usuarios->nextPageUrl() }}">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <p class="text-muted mb-0">No hay usuarios registrados</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($usuarios as $usuario)
<!-- Modal de eliminación para cada usuario -->
<div class="modal fade" id="deleteModal{{ $usuario->id_usuario }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro que deseas eliminar al usuario <strong>{{ $usuario->nombre_completo }}</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('usuarios.destroy', $usuario->id_usuario) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection