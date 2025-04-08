@extends('layouts.admin')

@section('title', 'Gestión de Vehículos')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Vehículos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Vehículos</li>
    </ol>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total de Vehículos</div>
                            <div class="h3 mb-0">{{ $estadisticas['total'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-bus"></i>
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
                            <div class="small">Vehículos Activos</div>
                            <div class="h3 mb-0">{{ $estadisticas['activos'] }}</div>
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
                            <div class="small">En Mantenimiento</div>
                            <div class="h3 mb-0">{{ $estadisticas['mantenimiento'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Fuera de Servicio</div>
                            <div class="h3 mb-0">{{ $estadisticas['fuera_servicio'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-bus me-1"></i>
                Listado de Vehículos
            </div>
            <a href="{{ route('vehiculos.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nuevo Vehículo
            </a>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="{{ route('vehiculos.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $estadoItem)
                                <option value="{{ $estadoItem }}" {{ $estado == $estadoItem ? 'selected' : '' }}>{{ $estadoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-select">
                                <option value="">Todos los tipos</option>
                                @foreach($tipos as $tipoItem)
                                <option value="{{ $tipoItem }}" {{ $tipo == $tipoItem ? 'selected' : '' }}>{{ $tipoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="id_linea" class="form-label">Línea</label>
                            <select name="id_linea" id="id_linea" class="form-select">
                                <option value="">Todas las líneas</option>
                                @foreach($lineas as $lineaItem)
                                <option value="{{ $lineaItem->id_linea }}" {{ $id_linea == $lineaItem->id_linea ? 'selected' : '' }}>{{ $lineaItem->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de vehículos -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Placa</th>
                            <th>Tipo</th>
                            <th>Marca / Modelo</th>
                            <th>Capacidad</th>
                            <th>Línea</th>
                            <th>Kilometraje</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehiculos as $vehiculo)
                        <tr>
                            <td>{{ $vehiculo->placa }}</td>
                            <td>{{ $vehiculo->tipo }}</td>
                            <td>{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->año_fabricacion }})</td>
                            <td>{{ $vehiculo->capacidad_pasajeros }} pasajeros</td>
                            <td>
                                @if($vehiculo->linea)
                                <span class="badge rounded-pill" style="background-color: {{ $vehiculo->linea->color }}">
                                    {{ $vehiculo->linea->nombre }}
                                </span>
                                @else
                                <span class="badge bg-secondary">No asignado</span>
                                @endif
                            </td>
                            <td>{{ number_format($vehiculo->kilometraje, 0, ',', '.') }} km</td>
                            <td>
                                @php
                                $badgeClass = 'bg-secondary';
                                switch($vehiculo->estado) {
                                    case 'Activo': $badgeClass = 'bg-success'; break;
                                    case 'En mantenimiento': $badgeClass = 'bg-warning text-dark'; break;
                                    case 'En reparación': $badgeClass = 'bg-info text-dark'; break;
                                    case 'Fuera de servicio': $badgeClass = 'bg-danger'; break;
                                    case 'Dado de baja': $badgeClass = 'bg-dark'; break;
                                }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $vehiculo->estado }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('vehiculos.show', $vehiculo->id_vehiculo) }}" class="btn btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('vehiculos.edit', $vehiculo->id_vehiculo) }}" class="btn btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal" 
                                        data-id="{{ $vehiculo->id_vehiculo }}"
                                        data-placa="{{ $vehiculo->placa }}"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay vehículos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-3">
                {{ $vehiculos->appends(request()->query())->links() }}
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
                ¿Estás seguro de eliminar el vehículo con placa <span id="deleteVehiculoPlaca" class="fw-bold"></span>?
                <p class="text-danger mt-2"><small>Esta acción no se puede deshacer.</small></p>
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
                const placa = button.getAttribute('data-placa');
                
                document.getElementById('deleteVehiculoPlaca').textContent = placa;
                document.getElementById('deleteForm').action = `/vehiculos/${id}`;
            });
        }
    });
</script>
@endsection