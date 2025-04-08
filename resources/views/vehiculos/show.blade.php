@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Detalles del Vehículo: {{ $vehiculo->placa }}</h3>
                <div>
                    <a href="{{ route('vehiculos.edit', $vehiculo->id_vehiculo) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
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
                            <h5 class="mb-0">Información General</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Placa:</th>
                                    <td>{{ $vehiculo->placa }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo:</th>
                                    <td>{{ $vehiculo->tipo }}</td>
                                </tr>
                                <tr>
                                    <th>Marca:</th>
                                    <td>{{ $vehiculo->marca }}</td>
                                </tr>
                                <tr>
                                    <th>Modelo:</th>
                                    <td>{{ $vehiculo->modelo }}</td>
                                </tr>
                                <tr>
                                    <th>Año de Fabricación:</th>
                                    <td>{{ $vehiculo->año_fabricacion }}</td>
                                </tr>
                                <tr>
                                    <th>Capacidad:</th>
                                    <td>{{ $vehiculo->capacidad_pasajeros }} pasajeros</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Estado y Operación</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Estado:</th>
                                    <td>
                                        <span class="badge {{ $vehiculo->estado == 'Activo' ? 'bg-success' : 
                                            ($vehiculo->estado == 'En mantenimiento' || $vehiculo->estado == 'En reparación' ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $vehiculo->estado }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Línea Asignada:</th>
                                    <td>{{ $vehiculo->linea ? $vehiculo->linea->nombre : 'Sin asignación' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha de Adquisición:</th>
                                    <td>{{ date('d/m/Y', strtotime($vehiculo->fecha_adquisicion)) }}</td>
                                </tr>
                                <tr>
                                    <th>Kilometraje:</th>
                                    <td>{{ number_format($vehiculo->kilometraje, 0, '.', ',') }} km</td>
                                </tr>
                                <tr>
                                    <th>Fecha de Creación:</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($vehiculo->fecha_creacion)) }}</td>
                                </tr>
                                <tr>
                                    <th>Última Actualización:</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($vehiculo->fecha_modificacion)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asignaciones Recientes -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Asignaciones Recientes</h5>
                        <a href="{{ route('asignaciones.create', ['vehiculo_id' => $vehiculo->id_vehiculo]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nueva Asignación
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($asignaciones) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Línea</th>
                                        <th>Conductor</th>
                                        <th>Turno</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asignaciones as $asignacion)
                                        <tr>
                                            <td>{{ date('d/m/Y', strtotime($asignacion->fecha)) }}</td>
                                            <td>{{ $asignacion->linea->nombre }}</td>
                                            <td>{{ $asignacion->usuario->nombre }} {{ $asignacion->usuario->apellidos }}</td>
                                            <td>{{ date('H:i', strtotime($asignacion->hora_inicio)) }} - {{ date('H:i', strtotime($asignacion->hora_fin)) }}</td>
                                            <td>
                                                <span class="badge {{ $asignacion->estado == 'Completado' ? 'bg-success' : 
                                                    ($asignacion->estado == 'En curso' ? 'bg-primary' : 
                                                    ($asignacion->estado == 'Cancelado' ? 'bg-danger' : 'bg-secondary')) }}">
                                                    {{ $asignacion->estado }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('asignaciones.show', $asignacion->id_asignacion) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay asignaciones recientes para este vehículo.</p>
                    @endif
                </div>
            </div>

            <!-- Mantenimientos Recientes -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Mantenimientos Recientes</h5>
                        <a href="{{ route('mantenimientos.create', ['vehiculo_id' => $vehiculo->id_vehiculo]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Mantenimiento
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($mantenimientos) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Fecha Programada</th>
                                        <th>Fecha Realizada</th>
                                        <th>Resultado</th>
                                        <th>Proveedor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mantenimientos as $mantenimiento)
                                        <tr>
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
                                            <td>{{ $mantenimiento->proveedor ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('mantenimientos.show', $mantenimiento->id_mantenimiento) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay registros de mantenimiento para este vehículo.</p>
                    @endif
                </div>
            </div>

            <!-- Incidentes Reportados -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Incidentes Reportados</h5>
                </div>
                <div class="card-body">
                    @if(count($incidentes) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Impacto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incidentes as $incidente)
                                        <tr>
                                            <td>{{ date('d/m/Y H:i', strtotime($incidente->fecha_hora)) }}</td>
                                            <td>{{ $incidente->tipo_incidente }}</td>
                                            <td>
                                                <span class="badge {{ $incidente->impacto == 'Bajo' ? 'bg-success' : 
                                                    ($incidente->impacto == 'Medio' ? 'bg-warning' : 
                                                    ($incidente->impacto == 'Alto' ? 'bg-danger' : 'bg-dark')) }}">
                                                    {{ $incidente->impacto }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $incidente->estado == 'Resuelto' ? 'bg-success' : 
                                                    ($incidente->estado == 'En atención' ? 'bg-primary' : 
                                                    ($incidente->estado == 'Escalado' ? 'bg-danger' : 'bg-secondary')) }}">
                                                    {{ $incidente->estado }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No se han reportado incidentes para este vehículo.</p>
                    @endif
                </div>
            </div>

            <!-- Botón Eliminar -->
            <div class="mt-4 text-end">
                <form action="{{ route('vehiculos.destroy', $vehiculo->id_vehiculo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro que desea eliminar este vehículo? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Vehículo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection