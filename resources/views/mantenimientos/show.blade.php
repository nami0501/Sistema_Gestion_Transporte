@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Detalle de Mantenimiento</h3>
                <div>
                    <a href="{{ route('mantenimientos.edit', $mantenimiento->id_mantenimiento) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
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

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Información del Mantenimiento</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">Tipo de Mantenimiento:</th>
                                    <td>{{ $mantenimiento->tipo_mantenimiento }}</td>
                                </tr>
                                <tr>
                                    <th>Descripción:</th>
                                    <td>{{ $mantenimiento->descripcion }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha Programada:</th>
                                    <td>{{ date('d/m/Y', strtotime($mantenimiento->fecha_programada)) }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha Realizada:</th>
                                    <td>{{ $mantenimiento->fecha_realizada ? date('d/m/Y', strtotime($mantenimiento->fecha_realizada)) : 'Pendiente' }}</td>
                                </tr>
                                <tr>
                                    <th>Resultado:</th>
                                    <td>
                                        <span class="badge {{ $mantenimiento->resultado == 'Completado' ? 'bg-success' : 
                                            ($mantenimiento->resultado == 'Pendiente' ? 'bg-warning' : 
                                            ($mantenimiento->resultado == 'Cancelado' ? 'bg-danger' : 'bg-secondary')) }}">
                                            {{ $mantenimiento->resultado }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Información Adicional</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">Vehículo:</th>
                                    <td>
                                        <a href="{{ route('vehiculos.show', $mantenimiento->vehiculo->id_vehiculo) }}">
                                            {{ $mantenimiento->vehiculo->placa }} - {{ $mantenimiento->vehiculo->marca }} {{ $mantenimiento->vehiculo->modelo }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Costo:</th>
                                    <td>{{ $mantenimiento->costo ? '$'.number_format($mantenimiento->costo, 2) : 'No especificado' }}</td>
                                </tr>
                                <tr>
                                    <th>Proveedor:</th>
                                    <td>{{ $mantenimiento->proveedor ?? 'No especificado' }}</td>
                                </tr>
                                <tr>
                                    <th>Observaciones:</th>
                                    <td>{{ $mantenimiento->observaciones ?? 'Sin observaciones' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha de Registro:</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($mantenimiento->fecha_creacion)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-4 d-flex justify-content-between">
                <div>
                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Ver Todos
                    </a>
                    @if($mantenimiento->resultado == 'Pendiente')
                        <a href="{{ route('mantenimientos.edit', $mantenimiento->id_mantenimiento) }}" class="btn btn-success ms-2">
                            <i class="fas fa-check"></i> Marcar como Completado
                        </a>
                    @endif
                </div>
                <form action="{{ route('mantenimientos.destroy', $mantenimiento->id_mantenimiento) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro que desea eliminar este registro de mantenimiento? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection