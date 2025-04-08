@extends('layouts.admin')

@section('title', 'Detalles de Asignación')

@section('styles')
<style>
    .badge-linea {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .estado-badge {
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
        left: 50%;
        margin-left: -1px;
    }
    .timeline-container {
        position: relative;
        background-color: inherit;
        width: 50%;
        padding-left: 50px;
        padding-right: 25px;
        padding-bottom: 20px;
    }
    .timeline-container::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        right: -10px;
        background-color: white;
        border: 2px solid #0d6efd;
        top: 15px;
        border-radius: 50%;
        z-index: 1;
    }
    .timeline-left {
        left: 0;
    }
    .timeline-right {
        left: 50%;
    }
    .timeline-right::after {
        left: -10px;
    }
    .timeline-content {
        padding: 15px;
        background-color: white;
        position: relative;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    @media screen and (max-width: 600px) {
        .timeline::after {
            left: 31px;
        }
        .timeline-container {
            width: 100%;
            padding-left: 70px;
            padding-right: 25px;
        }
        .timeline-right {
            left: 0;
        }
        .timeline-container::after {
            left: 21px;
        }
    }
    .map-container {
        height: 400px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detalles de Asignación #{{ $asignacion->id_asignacion }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('asignaciones.index') }}">Asignaciones</a></li>
        <li class="breadcrumb-item active">Detalles de Asignación #{{ $asignacion->id_asignacion }}</li>
    </ol>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Información Principal -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <div>
                        <i class="fas fa-info-circle me-1"></i>
                        Información General
                    </div>
                    <div>
                        <a href="{{ route('asignaciones.edit', $asignacion->id_asignacion) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('asignaciones.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body py-2">
                                    <h5 class="card-title"><i class="fas fa-calendar me-1"></i> Información de Programación</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>ID de Asignación:</strong></span>
                                            <span class="text-muted">{{ $asignacion->id_asignacion }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Fecha:</strong></span>
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($asignacion->fecha)->format('d/m/Y') }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Horario:</strong></span>
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($asignacion->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($asignacion->hora_fin)->format('H:i') }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Turno:</strong></span>
                                            <span class="text-muted">{{ $asignacion->turno->nombre }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Línea:</strong></span>
                                            <span>
                                                <span class="badge badge-linea" style="background-color: {{ $asignacion->linea->color }};">
                                                    {{ $asignacion->linea->nombre }}
                                                </span>
                                            </span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Estado:</strong></span>
                                            <span>
                                                <span class="badge estado-badge 
                                                    {{ $asignacion->estado == 'Programado' ? 'bg-secondary' : 
                                                      ($asignacion->estado == 'En curso' ? 'bg-primary' : 
                                                      ($asignacion->estado == 'Completado' ? 'bg-success' : 'bg-danger')) }}">
                                                    {{ $asignacion->estado }}
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body py-2">
                                    <h5 class="card-title"><i class="fas fa-route me-1"></i> Información de Operación</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Kilometraje Inicial:</strong></span>
                                            <span class="text-muted">{{ number_format($asignacion->kilometraje_inicial, 0, ',', '.') }} km</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Kilometraje Final:</strong></span>
                                            <span class="text-muted">
                                                {{ $asignacion->kilometraje_final ? number_format($asignacion->kilometraje_final, 0, ',', '.') . ' km' : 'No registrado' }}
                                            </span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Distancia Recorrida:</strong></span>
                                            <span class="text-muted">
                                                {{ $asignacion->kilometraje_final ? number_format($asignacion->kilometraje_final - $asignacion->kilometraje_inicial, 0, ',', '.') . ' km' : 'N/A' }}
                                            </span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Vueltas Completadas:</strong></span>
                                            <span class="text-muted">{{ $asignacion->vueltas_completas }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Fecha de Creación:</strong></span>
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($asignacion->fecha_creacion)->format('d/m/Y H:i') }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                            <span><strong>Última Actualización:</strong></span>
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($asignacion->fecha_modificacion)->format('d/m/Y H:i') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white py-2">
                                    <i class="fas fa-user me-1"></i> Información del Conductor
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-3 me-3">
                                            <i class="fas fa-user fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $asignacion->usuario->nombre }} {{ $asignacion->usuario->apellidos }}</h5>
                                            <p class="text-muted mb-0">Conductor ID: {{ $asignacion->usuario->id_usuario }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>DNI:</strong> {{ $asignacion->usuario->dni }}</p>
                                            <p class="mb-1"><strong>Teléfono:</strong> {{ $asignacion->usuario->telefono }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Licencia:</strong> {{ $asignacion->usuario->numero_licencia }}</p>
                                            <p class="mb-1"><strong>Tipo:</strong> {{ $asignacion->usuario->tipo_licencia }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('usuarios.show', $asignacion->usuario->id_usuario) }}" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-eye me-1"></i> Ver Perfil Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-white py-2">
                                    <i class="fas fa-bus me-1"></i> Información del Vehículo
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-3 me-3">
                                            <i class="fas fa-bus fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $asignacion->vehiculo->placa }}</h5>
                                            <p class="text-muted mb-0">{{ $asignacion->vehiculo->tipo }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Marca:</strong> {{ $asignacion->vehiculo->marca }}</p>
                                            <p class="mb-1"><strong>Modelo:</strong> {{ $asignacion->vehiculo->modelo }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Año:</strong> {{ $asignacion->vehiculo->año_fabricacion }}</p>
                                            <p class="mb-1"><strong>Capacidad:</strong> {{ $asignacion->vehiculo->capacidad_pasajeros }} pasajeros</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('vehiculos.show', $asignacion->vehiculo->id_vehiculo) }}" class="btn btn-sm btn-outline-warning mt-2">
                                        <i class="fas fa-eye me-1"></i> Ver Detalles del Vehículo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($asignacion->observaciones)
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2">
                            <i class="fas fa-comment-alt me-1"></i> Observaciones
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $asignacion->observaciones }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3">
                        @if($asignacion->estado == 'Programado')
                            <form action="{{ route('asignaciones.iniciar', $asignacion->id_asignacion) }}" method="POST" class="me-2">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play me-1"></i> Iniciar Recorrido
                                </button>
                            </form>
                        @elseif($asignacion->estado == 'En curso')
                            <form action="{{ route('asignaciones.completar', $asignacion->id_asignacion) }}" method="POST" class="me-2">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Completar Recorrido
                                </button>
                                </form>
                        @endif

                        <div>
                            <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver al Listado
                            </a>
                            @if($asignacion->estado != 'Completado' && $asignacion->estado != 'Cancelado')
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelarModal">
                                    <i class="fas fa-times-circle me-1"></i> Cancelar Asignación
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapa de Recorrido -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-map-marked-alt me-1"></i>
                    Mapa de Recorrido
                </div>
                <div class="card-body">
                    @if(count($registrosGPS) > 0)
                        <div id="map" class="map-container mb-3"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge bg-primary me-2">Inicio</span>
                                <span class="badge bg-success me-2">Fin</span>
                                <span class="badge bg-warning">Paradas</span>
                            </div>
                            <div>
                                <strong>Distancia Total Recorrida:</strong> <span id="distancia-total">Calculando...</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-0">No hay datos de recorrido disponibles para esta asignación.</p>
                            <p class="text-muted">Los datos de GPS se mostrarán una vez que el conductor inicie el recorrido.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cronología de Recorrido -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-history me-1"></i>
                    Cronología de Recorrido
                </div>
                <div class="card-body">
                    @if(count($pasosEstacion) > 0)
                        <div class="timeline">
                            @foreach($pasosEstacion as $index => $paso)
                                <div class="timeline-container {{ $index % 2 == 0 ? 'timeline-left' : 'timeline-right' }}">
                                    <div class="timeline-content">
                                        <h6>{{ $paso->estacion->nombre }}</h6>
                                        <p class="mb-1">
                                            <strong>Hora programada:</strong> 
                                            {{ $paso->hora_programada ? \Carbon\Carbon::parse($paso->hora_programada)->format('H:i') : 'No programada' }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Hora real:</strong> 
                                            {{ $paso->hora_real ? \Carbon\Carbon::parse($paso->hora_real)->format('H:i:s') : 'No registrada' }}
                                        </p>
                                        @if($paso->hora_programada && $paso->hora_real)
                                            @php
                                                $diferencia = \Carbon\Carbon::parse($paso->hora_real)->diffInMinutes(\Carbon\Carbon::parse($paso->hora_programada), false);
                                                $claseDelay = $diferencia > 5 ? 'text-danger' : ($diferencia > 0 ? 'text-warning' : 'text-success');
                                                $textoDelay = $diferencia > 0 ? $diferencia . ' min tarde' : ($diferencia < 0 ? abs($diferencia) . ' min anticipado' : 'A tiempo');
                                            @endphp
                                            <p class="mb-0 {{ $claseDelay }}">
                                                <i class="fas {{ $diferencia > 5 ? 'fa-exclamation-circle' : ($diferencia > 0 ? 'fa-clock' : 'fa-check-circle') }} me-1"></i>
                                                {{ $textoDelay }}
                                            </p>
                                        @endif
                                        <small class="text-muted">Vuelta #{{ $paso->vuelta }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="mb-0">No hay registros de pasos por estaciones disponibles.</p>
                            <p class="text-muted">La cronología se actualizará a medida que el vehículo pase por las estaciones.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Panel de Estado -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    Panel de Estado
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-4 mb-2">
                            @if($asignacion->estado == 'Programado')
                                <i class="fas fa-clock text-secondary"></i>
                            @elseif($asignacion->estado == 'En curso')
                                <i class="fas fa-play-circle text-primary"></i>
                            @elseif($asignacion->estado == 'Completado')
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-danger"></i>
                            @endif
                        </div>
                        <h4>{{ $asignacion->estado }}</h4>
                        @if($asignacion->estado == 'Programado')
                            @php
                                $fechaAsignacion = \Carbon\Carbon::parse($asignacion->fecha.' '.$asignacion->hora_inicio);
                                $ahora = \Carbon\Carbon::now();
                                $diffHoras = $fechaAsignacion->diffInHours($ahora);
                                $diffMinutos = $fechaAsignacion->diffInMinutes($ahora) % 60;
                            @endphp
                            @if($fechaAsignacion->isFuture())
                                <p class="text-muted">Inicia en {{ $diffHoras }}h {{ $diffMinutos }}m</p>
                            @else
                                <p class="text-danger">Retrasado {{ $diffHoras }}h {{ $diffMinutos }}m</p>
                            @endif
                        @elseif($asignacion->estado == 'En curso')
                            @php
                                $inicioAsignacion = \Carbon\Carbon::parse($asignacion->fecha_modificacion);
                                $ahora = \Carbon\Carbon::now();
                                $diffHoras = $inicioAsignacion->diffInHours($ahora);
                                $diffMinutos = $inicioAsignacion->diffInMinutes($ahora) % 60;
                            @endphp
                            <p class="text-muted">En operación desde hace {{ $diffHoras }}h {{ $diffMinutos }}m</p>
                        @elseif($asignacion->estado == 'Completado')
                            @php
                                $fechaInicio = \Carbon\Carbon::parse($asignacion->fecha.' '.$asignacion->hora_inicio);
                                $fechaFin = \Carbon\Carbon::parse($asignacion->fecha_modificacion);
                                $duracionHoras = $fechaInicio->diffInHours($fechaFin);
                                $duracionMinutos = $fechaInicio->diffInMinutes($fechaFin) % 60;
                            @endphp
                            <p class="text-muted">Duración total: {{ $duracionHoras }}h {{ $duracionMinutos }}m</p>
                        @endif
                    </div>

                    @if($recorridos && count($recorridos) > 0)
                        <h6 class="border-bottom pb-2 mb-3">Detalles de Vueltas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Duración</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recorridos as $recorrido)
                                        <tr>
                                            <td>{{ $recorrido->numero_vuelta }}</td>
                                            <td>{{ \Carbon\Carbon::parse($recorrido->hora_inicio)->format('H:i') }}</td>
                                            <td>{{ $recorrido->hora_fin ? \Carbon\Carbon::parse($recorrido->hora_fin)->format('H:i') : '-' }}</td>
                                            <td>
                                                @if($recorrido->hora_fin)
                                                    {{ $recorrido->tiempo_real }} min
                                                @else
                                                    En curso
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Incidentes Reportados -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Incidentes Reportados
                        </div>
                        <a href="{{ route('incidentes.create', ['asignacion_id' => $asignacion->id_asignacion]) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i> Reportar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($incidentes) > 0)
                        <div class="list-group">
                            @foreach($incidentes as $incidente)
                                <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $incidente->tipo_incidente }}</h6>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($incidente->fecha_hora)->format('H:i') }}</small>
                                    </div>
                                    <p class="mb-1 text-truncate">{{ Str::limit($incidente->descripcion, 60) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small>
                                            @if($incidente->estacion)
                                                <i class="fas fa-map-marker-alt me-1"></i> {{ $incidente->estacion->nombre }}
                                            @endif
                                        </small>
                                        <span class="badge 
                                            {{ $incidente->estado == 'Resuelto' ? 'bg-success' : 
                                              ($incidente->estado == 'En atención' ? 'bg-primary' : 
                                              ($incidente->estado == 'Escalado' ? 'bg-danger' : 'bg-secondary')) }}">
                                            {{ $incidente->estado }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">No se han reportado incidentes para esta asignación.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Estadísticas de Operación -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-chart-pie me-1"></i>
                    Estadísticas de Operación
                </div>
                <div class="card-body">
                    <canvas id="puntualidadChart" height="200"></canvas>
                    
                    <div class="row text-center mt-4">
                        <div class="col-6">
                            <h2 class="display-6">{{ $estadisticas['puntualidad'] ?? 'N/A' }}</h2>
                            <p class="text-muted">Puntualidad</p>
                        </div>
                        <div class="col-6">
                            <h2 class="display-6">{{ $estadisticas['pasajeros'] ?? 'N/A' }}</h2>
                            <p class="text-muted">Pasajeros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cancelar asignación -->
<div class="modal fade" id="cancelarModal" tabindex="-1" aria-labelledby="cancelarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelarModalLabel">Cancelar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea cancelar esta asignación?</p>
                <p class="text-danger">Esta acción no se puede deshacer.</p>
                
                <form id="cancelarForm" action="{{ route('asignaciones.cancelar', $asignacion->id_asignacion) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="motivo_cancelacion" class="form-label">Motivo de Cancelación</label>
                        <textarea class="form-control" id="motivo_cancelacion" name="motivo_cancelacion" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('cancelarForm').submit();">
                    <i class="fas fa-times-circle me-1"></i> Cancelar Asignación
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(count($registrosGPS) > 0)
<!-- Leaflet JS para el mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
    // Inicializar mapa
    const map = L.map('map').setView([-12.046374, -77.042793], 13); // Coordenadas de Lima como ejemplo

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Cargar registros GPS
    const registros = @json($registrosGPS);
    const paradas = @json($pasosEstacion);
    
    // Crear puntos del recorrido
    const points = registros.map(reg => [reg.latitud, reg.longitud]);
    
    // Crear línea del recorrido
    const polyline = L.polyline(points, {
        color: '{{ $asignacion->linea->color }}',
        weight: 4,
        opacity: 0.7
    }).addTo(map);
    
    // Ajustar la vista para ver todo el recorrido
    map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
    
    // Agregar marcadores de inicio y fin
    if (points.length > 0) {
        // Marcador de inicio
        L.marker(points[0], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 30px; height: 30px;"><i class="fas fa-play"></i></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(map)
        .bindPopup('<b>Inicio del Recorrido</b><br>' + 
                  'Hora: ' + registros[0].timestamp.substring(11, 16));
        
        // Marcador de fin (último punto)
        L.marker(points[points.length - 1], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 30px; height: 30px;"><i class="fas fa-flag-checkered"></i></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(map)
        .bindPopup('<b>Fin del Recorrido</b><br>' + 
                  'Hora: ' + registros[registros.length - 1].timestamp.substring(11, 16));
    }
    
    // Agregar marcadores de paradas (estaciones)
    paradas.forEach(parada => {
        if (parada.estacion && parada.estacion.latitud && parada.estacion.longitud) {
            L.marker([parada.estacion.latitud, parada.estacion.longitud], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" 
                           style="width: 25px; height: 25px;"><i class="fas fa-stop"></i></div>`,
                    iconSize: [25, 25],
                    iconAnchor: [12, 12]
                })
            }).addTo(map)
            .bindPopup('<b>' + parada.estacion.nombre + '</b><br>' +
                      (parada.hora_real ? 'Hora: ' + parada.hora_real.substring(11, 16) : 'Sin registro de hora'));
        }
    });
    
    // Calcular distancia total recorrida
    let distanciaTotal = 0;
    for (let i = 1; i < points.length; i++) {
        distanciaTotal += map.distance(
            L.latLng(points[i-1][0], points[i-1][1]),
            L.latLng(points[i][0], points[i][1])
        );
    }
    
    // Mostrar distancia en kilómetros
    document.getElementById('distancia-total').textContent = 
        (distanciaTotal / 1000).toFixed(2) + ' km';
</script>
@endif

<!-- Chart.js para gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    // Gráfica de puntualidad (ejemplo con datos ficticios)
    const ctxPuntualidad = document.getElementById('puntualidadChart').getContext('2d');
    const puntualidadData = @json($datos_puntualidad ?? []);
    
    if (puntualidadData.length > 0) {
        const puntualidadChart = new Chart(ctxPuntualidad, {
            type: 'line',
            data: {
                labels: puntualidadData.map(item => item.estacion),
                datasets: [{
                    label: 'Minutos de desvío',
                    data: puntualidadData.map(item => item.desviacion),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Minutos de desvío'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Estación'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Puntualidad por Estación'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                return value > 0 ? 
                                    `${value} min tarde` : 
                                    (value < 0 ? `${Math.abs(value)} min anticipado` : 'A tiempo');
                            }
                        }
                    }
                }
            }
        });
    } else {
        // Mostrar mensaje cuando no hay datos
        const puntualidadChart = new Chart(ctxPuntualidad, {
            type: 'bar',
            data: {
                labels: ['No hay datos disponibles'],
                datasets: [{
                    data: [0],
                    backgroundColor: 'rgba(200, 200, 200, 0.2)',
                    borderColor: 'rgba(200, 200, 200, 1)',
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'No hay datos de puntualidad disponibles'
                    }
                },
                scales: {
                    y: {
                        display: false
                    }
                }
            }
        });
    }
</script>
@endsection    