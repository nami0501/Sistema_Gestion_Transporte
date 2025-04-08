@extends('layouts.admin')

@section('title', 'Gestión de Líneas')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Líneas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Líneas</li>
    </ol>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total de Líneas</div>
                            <div class="h3 mb-0">{{ $estadisticas['total'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-route"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Líneas Activas</div>
                            <div class="h3 mb-0">{{ $estadisticas['activas'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Líneas Suspendidas</div>
                            <div class="h3 mb-0">{{ $estadisticas['suspendidas'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">En Mantenimiento</div>
                            <div class="h3 mb-0">{{ $estadisticas['en_mantenimiento'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-route me-1"></i>
                Listado de Líneas
            </div>
            <a href="{{ route('lineas.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva Línea
            </a>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="{{ route('lineas.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $estadoItem)
                                <option value="{{ $estadoItem }}" {{ $estado == $estadoItem ? 'selected' : '' }}>{{ $estadoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('lineas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de líneas -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px">Color</th>
                            <th>Nombre</th>
                            <th>Horario</th>
                            <th>Frecuencia</th>
                            <th>Estaciones</th>
                            <th>Vehículos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lineas as $linea)
                        <tr>
                            <td>
                                <div class="color-box" style="background-color: {{ $linea->color }}; width: 30px; height: 30px; border-radius: 4px;"></div>
                            </td>
                            <td>{{ $linea->nombre }}</td>
                            <td>{{ $linea->hora_inicio->format('H:i') }} - {{ $linea->hora_fin->format('H:i') }}</td>
                            <td>{{ $linea->frecuencia_min }} min</td>
                            <td>
                                <span class="badge bg-primary">{{ $linea->estaciones_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $linea->vehiculos_count }}</span>
                            </td>
                            <td>
                                @php
                                $badgeClass = 'bg-secondary';
                                switch($linea->estado) {
                                    case 'Activa': $badgeClass = 'bg-success'; break;
                                    case 'Suspendida': $badgeClass = 'bg-warning text-dark'; break;
                                    case 'En mantenimiento': $badgeClass = 'bg-info text-dark'; break;
                                }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $linea->estado }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('lineas.show', $linea->id_linea) }}" class="btn btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('lineas.edit', $linea->id_linea) }}" class="btn btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('lineas.estaciones', $linea->id_linea) }}" class="btn btn-warning" title="Gestionar Estaciones">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal" 
                                        data-id="{{ $linea->id_linea }}"
                                        data-nombre="{{ $linea->nombre }}"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay líneas registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Paginación personalizada -->
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination">
                        <li class="page-item {{ $lineas->currentPage() == 1 ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $lineas->previousPageUrl() }}" tabindex="-1">Anterior</a>
                        </li>
                        
                        @for ($i = 1; $i <= $lineas->lastPage(); $i++)
                            <li class="page-item {{ $lineas->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $lineas->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        
                        <li class="page-item {{ $lineas->currentPage() == $lineas->lastPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $lineas->nextPageUrl() }}">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de eliminar la línea <span id="deleteLineaNombre" class="fw-bold"></span>?
                <p class="text-danger mt-2"><small>Esta acción no se puede deshacer.</small></p>
                <p><small>Nota: Solo se pueden eliminar líneas que no tengan vehículos o asignaciones asociadas.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar el modal de eliminación
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                
                document.getElementById('deleteLineaNombre').textContent = nombre;
                document.getElementById('deleteForm').action = `/lineas/${id}`;
            });
        }
    });
</script>
@endsection