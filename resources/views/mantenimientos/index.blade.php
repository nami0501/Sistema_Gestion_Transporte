@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Mantenimientos</h3>
                <div>
                    <a href="{{ route('mantenimientos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Mantenimiento
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('mantenimientos.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="vehiculo_id" class="form-label">Vehículo</label>
                            <select class="form-select" id="vehiculo_id" name="vehiculo_id">
                                <option value="">Todos</option>
                                @foreach($vehiculos as $v)
                                    <option value="{{ $v->id_vehiculo }}" {{ request('vehiculo_id') == $v->id_vehiculo ? 'selected' : '' }}>
                                        {{ $v->placa }} - {{ $v->marca }} {{ $v->modelo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Todos</option>
                                @foreach($tipos as $t)
                                    <option value="{{ $t }}" {{ request('tipo') == $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="resultado" class="form-label">Resultado</label>
                            <select class="form-select" id="resultado" name="resultado">
                                <option value="">Todos</option>
                                @foreach($resultados as $r)
                                    <option value="{{ $r }}" {{ request('resultado') == $r ? 'selected' : '' }}>
                                        {{ $r }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-broom"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de mantenimientos -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Tipo</th>
                            <th>Fecha Programada</th>
                            <th>Fecha Realizada</th>
                            <th>Resultado</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($mantenimientos) > 0)
                            @foreach($mantenimientos as $mantenimiento)
                                <tr>
                                    <td>
                                        <a href="{{ route('vehiculos.show', $mantenimiento->vehiculo->id_vehiculo) }}">
                                            {{ $mantenimiento->vehiculo->placa }}
                                        </a>
                                    </td>
                                    <td>{{ $mantenimiento->tipo_mantenimiento }}</td>
                                    <td>{{ date('d/m/Y', strtotime($mantenimiento->fecha_programada)) }}</td>
                                    <td>{{ $mantenimiento->fecha_realizada ? date('d/m/Y', strtotime($mantenimiento->fecha_realizada)) : 'Pendiente' }}</td>
                                    <td>
                                        <span class="badge {{ $mantenimiento->resultado == 'Completado' ? 'bg-success' : 
                                            ($mantenimiento->resultado == 'Pendiente' ? 'bg-warning' : 
                                            ($mantenimiento->resultado == 'Cancelado' ? 'bg-danger' : 'bg-secondary')) }}">
                                            {{ $mantenimiento->resultado }}
                                        </span>
                                    </td>
                                    <td>{{ $mantenimiento->costo ? '$'.number_format($mantenimiento->costo, 2) : '-' }}</td>
                                    <td>
                                        <a href="{{ route('mantenimientos.show', $mantenimiento->id_mantenimiento) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('mantenimientos.edit', $mantenimiento->id_mantenimiento) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('mantenimientos.destroy', $mantenimiento->id_mantenimiento) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">No se encontraron registros de mantenimiento.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="mt-4">
                {{ $mantenimientos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection