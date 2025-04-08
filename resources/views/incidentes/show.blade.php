@extends('layouts.admin')

@section('title', 'Detalles de Incidente')

@section('styles')
<style>
    .badge-linea {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .map-container {
        height: 300px;
        width: 100%;
    }
    .estado-badge {
        font-size: 0.9rem;
        padding: 0.35em 0.65em;
    }
    .impacto-badge {
        font-size: 0.9rem;
        padding: 0.35em 0.65em;
    }
    .timeline {
        position: relative;
        margin: 0 auto;
    }
    .timeline::after {
        content: '';
        position: absolute;
        width: 2px;
        background-color: #e9ecef;
        top: 0;
        bottom: 0;
        left: 25px;
        margin-left: -1px;
    }
    .timeline-container {
        position: relative;
        background-color: inherit;
        padding-left: 50px;
        padding-bottom: 20px;
    }
    .timeline-container::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        left: 15px;
        background-color: white;
        border: 2px solid #0d6efd;
        top: 15px;
        border-radius: 50%;
        z-index: 1;
    }
    .timeline-content {
        padding: 15px;
        background-color: white;
        position: relative;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Incidente #{{ $incidente->id_incidente }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidentes.index') }}">Incidentes</a></li>
        <li class="breadcrumb-item active">Incidente #{{ $incidente->id_incidente }}</li>
    </ol>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <!-- Detalles del Incidente -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
                    <div>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Detalles del Incidente
                    </div>
                    <div>
                        <a href="{{ route('incidentes.edit', $incidente->id_incidente) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('incidentes.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th style="width: 40%">ID:</th>
                                    <td>{{ $incidente->id_incidente }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo:</th>
                                    <td>{{ $incidente->tipo_incidente }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha y Hora:</th>
                                    <td>{{ \Carbon\Carbon::parse($incidente->fecha_hora)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Impacto:</th>
                                    <td>
                                        <span class="badge impacto-badge 
                                            {{ $incidente->impacto == 'Bajo' ? 'bg-success' : 
                                              ($incidente->impacto == 'Medio' ? 'bg-warning' : 
                                              ($incidente->impacto == 'Alto' ? 'bg-danger' : 'bg-dark')) }}">
                                            {{ $incidente->impacto }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Retraso Estimado:</th>
                                    <td>{{ $incidente->retraso_estimado ? $incidente->retraso_estimado . ' minutos' : 'No especificado' }}</td>
                                </tr>
                                <tr>
                                    <th>Reportado por:</th>
                                    <td>{{ $incidente->usuario_creacion ? $incidente->usuario_creacion->nombre . ' ' . $incidente->usuario_creacion->apellidos : 'Sistema' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th style="width: 40%">Estado:</th>
                                    <td>
                                        <span class="badge estado-badge 
                                            {{ $incidente->estado == 'Resuelto' ? 'bg-success' : 
                                              ($incidente->estado == 'En atención' ? 'bg-primary' : 
                                              ($incidente->estado == 'Escalado' ? 'bg-danger' : 'bg-secondary')) }}">
                                            {{ $incidente->estado }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha de Creación:</th>
                                    <td>{{ \Carbon\Carbon::parse($incidente->fecha_creacion)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha de Resolución:</th>
                                    <td>{{ $incidente->fecha_resolucion ? \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d/m/Y H:i') : 'Pendiente' }}</td>
                                </tr>
                                <tr>
                                    <th>Tiempo Transcurrido:</th>
                                    <td>
                                        @if($incidente->estado == 'Resuelto' && $incidente->fecha_resolucion)
                                            {{ \Carbon\Carbon::parse($incidente->fecha_hora)->diffForHumans(\Carbon\Carbon::parse($incidente->fecha_resolucion), true) }}
                                        @else
                                            {{ \Carbon\Carbon::parse($incidente->fecha_hora)->diffForHumans(null, true) . ' (En curso)' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estación:</th>
                                    <td>{{ $incidente->estacion ? $incidente->estacion->nombre : 'No especificada' }}</td>
                                </tr>
                                <tr>
                                    <th>Asignación:</th>
                                    <td>
                                        @if($incidente->asignacion)
                                            <a href="{{ route('asignaciones.show', $incidente->asignacion->id_asignacion) }}">
                                                #{{ $incidente->asignacion->id_asignacion }} - {{ $incidente->asignacion->linea->nombre }}
                                            </a>
                                        @else
                                            No asociado
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Descripción</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $incidente->descripcion }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($incidente->estado == 'Resuelto' && $incidente->resolucion)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Resolución</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $incidente->resolucion }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($incidente->evidencia)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Evidencia</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="{{ asset('storage/evidencias/' . $incidente->evidencia) }}" class="img-fluid" style="max-height: 400px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ubicación -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    Ubicación del Incidente
                </div>
                <div class="card-body">
                    @if($incidente->latitud && $incidente->longitud)
                        <div id="map" class="map-container mb-3"></div>
                        <div class="small text-muted">
                            <strong>Coordenadas:</strong> {{ $incidente->latitud }}, {{ $incidente->longitud }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-0">No hay información de ubicación disponible.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <!-- Panel de Estado -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-tasks me-1"></i>
                    Gestión del Incidente
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center mb-4">
                        <div class="mb-2">
                            @if($incidente->estado == 'Reportado')
                                <i class="fas fa-exclamation-circle fa-4x text-secondary"></i>
                            @elseif($incidente->estado == 'En atención')
                                <i class="fas fa-tools fa-4x text-primary"></i>
                            @elseif($incidente->estado == 'Resuelto')
                                <i class="fas fa-check-circle fa-4x text-success"></i>
                            @elseif($incidente->estado == 'Escalado')
                                <i class="fas fa-arrow-circle-up fa-4x text-danger"></i>
                            @endif
                        </div>
                        <h5>{{ $incidente->estado }}</h5>
                        @if($incidente->estado == 'Resuelto')
                            <p class="text-muted">Resuelto hace {{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->diffForHumans() }}</p>
                        @else
                            <p class="text-muted">Reportado hace {{ \Carbon\Carbon::parse($incidente->fecha_creacion)->diffForHumans() }}</p>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        @if($incidente->estado != 'Resuelto')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolverModal">
                                <i class="fas fa-check-circle me-1"></i> Marcar como Resuelto
                            </button>
                        @endif
                        
                        @if($incidente->estado == 'Reportado')
                            <form action="{{ route('incidentes.atender', $incidente->id_incidente) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-tools me-1"></i> Atender Incidente
                                </button>
                            </form>
                        @endif
                        
                        @if($incidente->estado != 'Escalado' && $incidente->estado != 'Resuelto')
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#escalarModal">
                                <i class="fas fa-arrow-circle-up me-1"></i> Escalar Incidente
                            </button>
                        @endif
                        
                        <a href="{{ route('incidentes.edit', $incidente->id_incidente) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Editar Incidente
                        </a>
                    </div>

                    <hr>

                    @if($incidente->asignacion)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Detalles de la Asignación</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Conductor:</strong> {{ $incidente->asignacion->usuario->nombre }} {{ $incidente->asignacion->usuario->apellidos }}</p>
                                <p class="mb-1"><strong>Vehículo:</strong> {{ $incidente->asignacion->vehiculo->placa }} ({{ $incidente->asignacion->vehiculo->tipo }})</p>
                                <p class="mb-1">
                                    <strong>Línea:</strong> 
                                    <span class="badge badge-linea" style="background-color: {{ $incidente->asignacion->linea->color }};">
                                        {{ $incidente->asignacion->linea->nombre }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Incidentes relacionados -->
                    @if(count($incidentesRelacionados) > 0)
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Incidentes Relacionados</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @foreach($incidentesRelacionados as $incRel)
                                        <a href="{{ route('incidentes.show', $incRel->id_incidente) }}" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $incRel->tipo_incidente }}</h6>
                                                <small>
                                                    <span class="badge {{ $incRel->estado == 'Resuelto' ? 'bg-success' : 
                                                        ($incRel->estado == 'En atención' ? 'bg-primary' : 
                                                        ($incRel->estado == 'Escalado' ? 'bg-danger' : 'bg-secondary')) }}">
                                                        {{ $incRel->estado }}
                                                    </span>
                                                </small>
                                            </div>
                                            <p class="mb-1 small text-truncate">{{ Str::limit($incRel->descripcion, 60) }}</p>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($incRel->fecha_hora)->format('d/m/Y H:i') }}
                                            </small>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Actividad -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-history me-1"></i>
                    Historial de Actividad
                </div>
                <div class="card-body p-3">
                    <div class="timeline">
                        <div class="timeline-container">
                            <div class="timeline-content">
                                <h6>Incidente Reportado</h6>
                                <p class="mb-0 small">{{ \Carbon\Carbon::parse($incidente->fecha_creacion)->format('d/m/Y H:i') }}</p>
                                <small class="text-muted">Por {{ $incidente->usuario_creacion ? $incidente->usuario_creacion->nombre . ' ' . $incidente->usuario_creacion->apellidos : 'Sistema' }}</small>
                            </div>
                        </div>

                        @foreach($actividadesIncidente as $actividad)
                            <div class="timeline-container">
                                <div class="timeline-content">
                                    <h6>{{ $actividad->accion }}</h6>
                                    <p class="mb-0 small">{{ \Carbon\Carbon::parse($actividad->fecha_hora)->format('d/m/Y H:i') }}</p>
                                    @if($actividad->detalles)
                                        <p class="mb-0 small">{{ $actividad->detalles }}</p>
                                    @endif
                                    <small class="text-muted">Por {{ $actividad->usuario ? $actividad->usuario->nombre . ' ' . $actividad->usuario->apellidos : 'Sistema' }}</small>
                                </div>
                            </div>
                        @endforeach

                        @if($incidente->estado == 'Resuelto')
                            <div class="timeline-container">
                                <div class="timeline-content">
                                    <h6>Incidente Resuelto</h6>
                                    <p class="mb-0 small">{{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d/m/Y H:i') }}</p>
                                    <small class="text-muted">Duración: {{ \Carbon\Carbon::parse($incidente->fecha_hora)->diffForHumans(\Carbon\Carbon::parse($incidente->fecha_resolucion), true) }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para resolver incidente -->
<div class="modal fade" id="resolverModal" tabindex="-1" aria-labelledby="resolverModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="resolverModalLabel">Resolver Incidente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('incidentes.resolver', $incidente->id_incidente) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resolucion" class="form-label">Resolución *</label>
                        <textarea class="form-control" id="resolucion" name="resolucion" rows="4" required></textarea>
                        <div class="form-text">Describa cómo se resolvió el incidente y las acciones tomadas.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Marcar como Resuelto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para escalar incidente -->
<div class="modal fade" id="escalarModal" tabindex="-1" aria-labelledby="escalarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="escalarModalLabel">Escalar Incidente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('incidentes.escalar', $incidente->id_incidente) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="motivo_escalacion" class="form-label">Motivo de Escalación *</label>
                        <textarea class="form-control" id="motivo_escalacion" name="motivo_escalacion" rows="4" required></textarea>
                        <div class="form-text">Explique por qué es necesario escalar este incidente.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_departamento" class="form-label">Escalar a Departamento</label>
                        <select class="form-select" id="id_departamento" name="id_departamento">
                            <option value="">Seleccione un departamento</option>
                            <option value="1">Mantenimiento</option>
                            <option value="2">Seguridad</option>
                            <option value="3">Operaciones</option>
                            <option value="4">Dirección General</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-arrow-circle-up me-1"></i> Escalar Incidente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($incidente->latitud && $incidente->longitud)
<!-- Leaflet JS para el mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
    // Inicializar mapa
    const map = L.map('map').setView([{{ $incidente->latitud }}, {{ $incidente->longitud }}], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Agregar marcador en la ubicación del incidente
    const marker = L.marker([{{ $incidente->latitud }}, {{ $incidente->longitud }}]).addTo(map)
        .bindPopup('<b>{{ $incidente->tipo_incidente }}</b><br>{{ \Carbon\Carbon::parse($incidente->fecha_hora)->format('d/m/Y H:i') }}')
        .openPopup();

    // Si hay estación asociada, mostrarla también
    @if($incidente->estacion && $incidente->estacion->latitud && $incidente->estacion->longitud)
        const estacionMarker = L.marker([{{ $incidente->estacion->latitud }}, {{ $incidente->estacion->longitud }}], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                      style="width: 25px; height: 25px;"><i class="fas fa-bus"></i></div>`,
                iconSize: [25, 25],
                iconAnchor: [12, 12]
            })
        }).addTo(map)
        .bindPopup('<b>Estación:</b> {{ $incidente->estacion->nombre }}');
    @endif
</script>
@endif
@endsection