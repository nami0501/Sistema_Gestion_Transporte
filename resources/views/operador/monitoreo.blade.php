@extends('layouts.admin')

@section('title', 'Sistema de Monitoreo')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
<style>
    #map-container {
        height: calc(100vh - 250px);
        min-height: 500px;
        width: 100%;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .vehicle-info-panel {
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .status-on-time {
        background-color: #28a745;
    }
    
    .status-delayed {
        background-color: #ffc107;
    }
    
    .status-alert {
        background-color: #dc3545;
    }
    
    .status-offline {
        background-color: #6c757d;
    }
    
    .status-card {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .status-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    .status-card.active {
        border-left: 3px solid #007bff;
    }
    
    .controls-panel {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .line-filter {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 250px;
    }
    
    .incident-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        background: #dc3545;
        border-radius: 50%;
        color: white;
        font-weight: bold;
    }

    .vehicle-details {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin-top: 15px;
    }

    .vehicle-details h6 {
        margin-bottom: 15px;
        font-weight: 600;
    }

    .data-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .data-label {
        font-weight: 500;
        color: #6c757d;
    }
    
    .data-value {
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }

    .station-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        background: #3498db;
        border: 2px solid white;
        border-radius: 50%;
    }

    .terminal-marker {
        width: 24px;
        height: 24px;
        background: #9b59b6;
    }

    .route-line {
        stroke-width: 5;
        opacity: 0.7;
    }

    .time-indicator {
        width: 100px;
        text-align: center;
        font-weight: 600;
        padding: 5px;
        border-radius: 15px;
        font-size: 0.8rem;
    }

    .time-on-schedule {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }

    .time-slight-delay {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .time-delayed {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Sistema de Monitoreo en Tiempo Real</h6>
                            <p class="text-sm">
                                <i class="bi bi-clock text-info"></i>
                                <span>Última actualización: <span id="last-update">{{ date('H:i:s') }}</span></span>
                                <button id="refresh-button" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                                </button>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="view-all">
                                    <i class="bi bi-grid"></i> Todo
                                </button>
                                <button type="button" class="btn btn-outline-warning" id="view-delayed">
                                    <i class="bi bi-exclamation-triangle"></i> Retrasados
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="view-incidents">
                                    <i class="bi bi-exclamation-circle"></i> Incidentes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-9 position-relative">
                            <div id="map-container"></div>
                            
                            <!-- Controles del mapa -->
                            <div class="controls-panel">
                                <div class="btn-group-vertical">
                                    <button class="btn btn-sm btn-light" id="zoom-in">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light" id="zoom-out">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light" id="center-map">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Filtro de líneas -->
                            <div class="line-filter">
                                <div class="form-group">
                                    <label for="line-select" class="form-label text-xs mb-1">Filtrar por línea:</label>
                                    <select class="form-select form-select-sm" id="line-select">
                                        <option value="all">Todas las líneas</option>
                                        @foreach($lineas as $linea)
                                            <option value="{{ $linea->id_linea }}">{{ $linea->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 border-start">
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Vehículos en servicio</h6>
                                    <span class="badge bg-primary">{{ count($vehiculos) }}</span>
                                </div>
                                
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="vehicle-search" placeholder="Buscar por placa o conductor">
                                </div>
                                
                                <div class="vehicle-info-panel">
                                    @foreach($vehiculos as $vehiculo)
                                    <div class="status-card card mb-2" data-vehicle-id="{{ $vehiculo->id_vehiculo }}" data-line-id="{{ $vehiculo->id_linea }}">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="status-indicator status-{{ $vehiculo->estado_operacion }}"></span>
                                                    <span class="fw-bold">{{ $vehiculo->placa }}</span>
                                                </div>
                                                <div class="time-indicator time-{{ $vehiculo->estado_tiempo }}">
                                                    {{ $vehiculo->diferencia_tiempo > 0 ? '+' : '' }}{{ $vehiculo->diferencia_tiempo }} min
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <div class="text-xs text-muted">
                                                    <i class="bi bi-signpost-split"></i> {{ $vehiculo->linea }}
                                                </div>
                                                <div class="text-xs">
                                                    <i class="bi bi-speedometer2"></i> {{ $vehiculo->velocidad }} km/h
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Detalles del vehículo seleccionado -->
                                <div id="vehicle-details" class="vehicle-details d-none">
                                    <h6>Detalles del Vehículo</h6>
                                    <div class="data-row">
                                        <span class="data-label">Placa:</span>
                                        <span class="data-value" id="detail-placa">ABC123</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Conductor:</span>
                                        <span class="data-value" id="detail-conductor">Juan Pérez</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Línea:</span>
                                        <span class="data-value" id="detail-linea">Línea 1</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Estado:</span>
                                        <span class="data-value" id="detail-estado">En ruta</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Velocidad:</span>
                                        <span class="data-value" id="detail-velocidad">45 km/h</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Última estación:</span>
                                        <span class="data-value" id="detail-estacion">Terminal Central</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Retraso:</span>
                                        <span class="data-value" id="detail-retraso">+2 minutos</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Vuelta:</span>
                                        <span class="data-value" id="detail-vuelta">2 de 5</span>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm" id="btn-contact">
                                            <i class="bi bi-telephone-fill"></i> Contactar
                                        </button>
                                        <button class="btn btn-warning btn-sm" id="btn-report">
                                            <i class="bi bi-exclamation-triangle"></i> Reportar
                                        </button>
                                        <button class="btn btn-info btn-sm" id="btn-history">
                                            <i class="bi bi-clock-history"></i> Historial
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Incidentes Activos -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Incidentes Activos</h6>
                            <p class="text-sm mb-0">
                                <i class="bi bi-exclamation-triangle text-warning" aria-hidden="true"></i>
                                <span class="font-weight-bold ms-1">{{ count($incidentesActivos) }} incidentes requieren atención</span>
                            </p>
                        </div>
                        <div class="col-lg-6 col-5 text-end">
                            <a href="{{ route('incidentes.create') }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-plus-lg"></i> Nuevo
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ubicación</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Hora</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Impacto</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incidentesActivos as $incidente)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $incidente->tipo_incidente }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $incidente->estacion ? $incidente->estacion->nombre : 'En ruta' }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ date('H:i', strtotime($incidente->fecha_hora)) }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ date('d/m/Y', strtotime($incidente->fecha_hora)) }}</p>
                                    </td>
                                    <td class="align-middle text-sm">
                                        <span class="badge badge-sm bg-gradient-{{ $incidente->impacto == 'Bajo' ? 'info' : ($incidente->impacto == 'Medio' ? 'warning' : 'danger') }}">{{ $incidente->impacto }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="btn btn-link text-info text-gradient p-2 m-0">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('incidentes.edit', $incidente->id_incidente) }}" class="btn btn-link text-warning text-gradient p-2 m-0">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Operación -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Estadísticas de Operación</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-gradient-primary mb-0">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Puntualidad</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ $puntualidad }}%
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="bi bi-clock text-primary opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-gradient-success mb-0">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Ocupación</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ $ocupacionPromedio }}%
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="bi bi-people text-success opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-gradient-warning mb-0">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Vehículos en Servicio</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ $vehiculosEnServicio }}/{{ $totalVehiculos }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="bi bi-bus-front text-warning opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-gradient-info mb-0">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Pasajeros Hoy</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ number_format($pasajerosHoy) }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="bi bi-person-check text-info opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chart mt-3">
                        <canvas id="activity-chart" class="chart-canvas" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para contactar conductor -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Contactar a <span id="contact-name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Métodos de contacto:</label>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary">
                            <i class="bi bi-telephone-fill"></i> Llamar al radio: <span id="contact-radio"></span>
                        </button>
                        <button type="button" class="btn btn-outline-success">
                            <i class="bi bi-chat-left-text-fill"></i> Enviar mensaje
                        </button>
                        <button type="button" class="btn btn-outline-danger">
                            <i class="bi bi-broadcast"></i> Alerta de emergencia
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="message-text" class="form-label">Mensaje:</label>
                    <textarea class="form-control" id="message-text" rows="3" placeholder="Escriba su mensaje aquí..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para reportar incidente -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Reportar Incidente - Vehículo <span id="report-vehicle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="incident-type" class="form-label">Tipo de Incidente:</label>
                        <select class="form-select" id="incident-type" required>
                            <option value="">Seleccione...</option>
                            <option value="Accidente">Accidente</option>
                            <option value="Avería">Avería</option>
                            <option value="Retraso">Retraso</option>
                            <option value="Seguridad">Problema de Seguridad</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="incident-impact" class="form-label">Impacto:</label>
                        <select class="form-select" id="incident-impact" required>
                            <option value="">Seleccione...</option>
                            <option value="Bajo">Bajo</option>
                            <option value="Medio">Medio</option>
                            <option value="Alto">Alto</option>
                            <option value="Crítico">Crítico</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="incident-description" class="form-label">Descripción:</label>
                        <textarea class="form-control" id="incident-description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="incident-action" class="form-label">Acción Recomendada:</label>
                        <textarea class="form-control" id="incident-action" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning">Reportar Incidente</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el mapa
        const map = L.map('map-container').setView([-12.046374, -77.042793], 13); // Coordenadas de Lima, Perú (ajustar según tu ubicación)

        // Añadir capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Controles del mapa
        document.getElementById('zoom-in').addEventListener('click', function() {
            map.zoomIn();
        });
        
        document.getElementById('zoom-out').addEventListener('click', function() {
            map.zoomOut();
        });
        
        document.getElementById('center-map').addEventListener('click', function() {
            map.setView([-12.046374, -77.042793], 13);
        });

        // Datos de ejemplo de vehículos
        const vehiculos = {!! json_encode($vehiculos) !!};
        const estaciones = {!! json_encode($estaciones) !!};
        const lineas = {!! json_encode($lineasRutas) !!};
        const incidentes = {!! json_encode($incidentesActivos) !!};

        // Iconos personalizados
        const vehicleIcon = L.divIcon({
            html: '<i class="bi bi-bus-front" style="font-size: 20px; color: #28a745;"></i>',
            className: 'vehicle-marker',
            iconSize: [20, 20]
        });

        const vehicleDelayedIcon = L.divIcon({
            html: '<i class="bi bi-bus-front" style="font-size: 20px; color: #ffc107;"></i>',
            className: 'vehicle-marker',
            iconSize: [20, 20]
        });

        const vehicleAlertIcon = L.divIcon({
            html: '<i class="bi bi-bus-front" style="font-size: 20px; color: #dc3545;"></i>',
            className: 'vehicle-marker',
            iconSize: [20, 20]
        });

        // Marcadores de vehículos
        const vehicleMarkers = {};
        
        vehiculos.forEach(vehiculo => {
            let icon;
            
            if (vehiculo.estado_operacion === 'on-time') {
                icon = vehicleIcon;
            } else if (vehiculo.estado_operacion === 'delayed') {
                icon = vehicleDelayedIcon;
            } else {
                icon = vehicleAlertIcon;
            }
            
            const marker = L.marker([vehiculo.latitud, vehiculo.longitud], {
                icon: icon
            }).addTo(map);
            
            marker.bindPopup(`
                <b>${vehiculo.placa}</b><br>
                Línea: ${vehiculo.linea}<br>
                Conductor: ${vehiculo.conductor}<br>
                Velocidad: ${vehiculo.velocidad} km/h<br>
                Estado: ${vehiculo.estado_tiempo === 'on-schedule' ? 'En hora' : 
                         vehiculo.estado_tiempo === 'slight-delay' ? 'Leve retraso' : 'Retrasado'}<br>
                Retraso: ${vehiculo.diferencia_tiempo > 0 ? '+' : ''}${vehiculo.diferencia_tiempo} min
            `);
            
            // Guardar referencia al marcador
            vehicleMarkers[vehiculo.id_vehiculo] = marker;
            
            marker.on('click', function() {
                selectVehicle(vehiculo.id_vehiculo);
            });
        });
        
        // Estaciones
        estaciones.forEach(estacion => {
            const stationClass = estacion.es_terminal ? 'station-marker terminal-marker' : 'station-marker';
            const stationIcon = L.divIcon({
                className: stationClass,
                iconSize: [estacion.es_terminal ? 24 : 20, estacion.es_terminal ? 24 : 20]
            });
            
            const marker = L.marker([estacion.latitud, estacion.longitud], {
                icon: stationIcon
            }).addTo(map);
            
            marker.bindPopup(`
                <b>${estacion.nombre}</b><br>
                ${estacion.es_terminal ? 'Terminal' : 'Estación'}<br>
                ${estacion.direccion}
            `);
        });
        
        // Líneas de rutas
        lineas.forEach(linea => {
            const coordenadas = linea.coordenadas.map(punto => [punto.latitud, punto.longitud]);
            
            const ruta = L.polyline(coordenadas, {
                color: linea.color,
                className: 'route-line',
                weight: 5,
                opacity: 0.7,
                lineJoin: 'round'
            }).addTo(map);
            
            ruta.bindPopup(`<b>${linea.nombre}</b>`);
        });
        
        // Incidentes
        incidentes.forEach(incidente => {
            if (incidente.latitud && incidente.longitud) {
                const incidentIcon = L.divIcon({
                    className: 'incident-marker',
                    html: '<i class="bi bi-exclamation-triangle-fill"></i>',
                    iconSize: [30, 30]
                });
                
                const marker = L.marker([incidente.latitud, incidente.longitud], {
                    icon: incidentIcon
                }).addTo(map);
                
                marker.bindPopup(`
                    <b>${incidente.tipo_incidente}</b><br>
                    Impacto: <span class="text-${incidente.impacto === 'Bajo' ? 'info' : 
                               (incidente.impacto === 'Medio' ? 'warning' : 'danger')}">${incidente.impacto}</span><br>
                    Hora: ${new Date(incidente.fecha_hora).toLocaleTimeString()}<br>
                    ${incidente.descripcion}
                `);
            }
        });
        
        // Seleccionar vehículo
        function selectVehicle(vehiculoId) {
            // Quitar selección anterior
            document.querySelectorAll('.status-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Marcar vehículo seleccionado
            const selectedCard = document.querySelector(`.status-card[data-vehicle-id="${vehiculoId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('active');
                selectedCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            
            // Centrar mapa en vehículo
            const marker = vehicleMarkers[vehiculoId];
            if (marker) {
                map.setView(marker.getLatLng(), 15);
                marker.openPopup();
            }
            
            // Mostrar detalles
            const vehiculo = vehiculos.find(v => v.id_vehiculo === vehiculoId);
            if (vehiculo) {
                document.getElementById('detail-placa').textContent = vehiculo.placa;
                document.getElementById('detail-conductor').textContent = vehiculo.conductor;
                document.getElementById('detail-linea').textContent = vehiculo.linea;
                document.getElementById('detail-estado').textContent = vehiculo.estado_operacion === 'on-time' ? 'En hora' : 
                                                                     vehiculo.estado_operacion === 'delayed' ? 'Retrasado' : 'Alerta';
                document.getElementById('detail-velocidad').textContent = `${vehiculo.velocidad} km/h`;
                document.getElementById('detail-estacion').textContent = vehiculo.ultima_estacion || 'N/A';
                document.getElementById('detail-retraso').textContent = `${vehiculo.diferencia_tiempo > 0 ? '+' : ''}${vehiculo.diferencia_tiempo} minutos`;
                document.getElementById('detail-vuelta').textContent = `${vehiculo.vuelta_actual} de ${vehiculo.vueltas_programadas}`;
                
                document.getElementById('vehicle-details').classList.remove('d-none');
                
                // Configurar botones
                document.getElementById('btn-contact').onclick = function() {
                    document.getElementById('contact-name').textContent = vehiculo.conductor;
                    document.getElementById('contact-radio').textContent = vehiculo.radio || 'No disponible';
                    const contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
                    contactModal.show();
                };
                
                document.getElementById('btn-report').onclick = function() {
                    document.getElementById('report-vehicle').textContent = vehiculo.placa;
                    const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
                    reportModal.show();
                };
                
                document.getElementById('btn-history').onclick = function() {
                    window.location.href = `/monitoreo/vehiculo/${vehiculoId}/historial`;
                };
            }
        }
        
        // Click en tarjetas de vehículos
        document.querySelectorAll('.status-card').forEach(card => {
            card.addEventListener('click', function() {
                const vehiculoId = parseInt(this.dataset.vehicleId);
                selectVehicle(vehiculoId);
            });
        });
        
        // Filtro de líneas
        document.getElementById('line-select').addEventListener('change', function() {
            const lineaId = this.value;
            
            document.querySelectorAll('.status-card').forEach(card => {
                if (lineaId === 'all' || card.dataset.lineId === lineaId) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Buscar vehículo
        document.getElementById('vehicle-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            document.querySelectorAll('.status-card').forEach(card => {
                const vehiculo = vehiculos.find(v => v.id_vehiculo === parseInt(card.dataset.vehicleId));
                if (vehiculo) {
                    if (vehiculo.placa.toLowerCase().includes(searchTerm) || 
                        vehiculo.conductor.toLowerCase().includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
        
        // Botones de filtro
        document.getElementById('view-all').addEventListener('click', function() {
            document.querySelectorAll('.status-card').forEach(card => {
                card.style.display = 'block';
            });
            
            document.querySelectorAll('#view-all, #view-delayed, #view-incidents').forEach(btn => {
                btn.classList.remove('active');
            });
            
            this.classList.add('active');
        });
        
        document.getElementById('view-delayed').addEventListener('click', function() {
            document.querySelectorAll('.status-card').forEach(card => {
                const vehiculo = vehiculos.find(v => v.id_vehiculo === parseInt(card.dataset.vehicleId));
                if (vehiculo && (vehiculo.estado_tiempo === 'slight-delay' || vehiculo.estado_tiempo === 'delayed')) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.querySelectorAll('#view-all, #view-delayed, #view-incidents').forEach(btn => {
                btn.classList.remove('active');
            });
            
            this.classList.add('active');
        });
        
        document.getElementById('view-incidents').addEventListener('click', function() {
            document.querySelectorAll('.status-card').forEach(card => {
                const vehiculo = vehiculos.find(v => v.id_vehiculo === parseInt(card.dataset.vehicleId));
                if (vehiculo && vehiculo.tiene_incidente) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.querySelectorAll('#view-all, #view-delayed, #view-incidents').forEach(btn => {
                btn.classList.remove('active');
            });
            
            this.classList.add('active');
        });
        
        // Actualizar hora
        document.getElementById('refresh-button').addEventListener('click', function() {
            const now = new Date();
            document.getElementById('last-update').textContent = now.toLocaleTimeString();
            // Aquí se podría hacer una llamada AJAX para actualizar los datos
        });

        // Gráfico de actividad
        const activityChart = new Chart(
            document.getElementById('activity-chart'),
            {
                type: 'line',
                data: {
                    labels: {!! json_encode($actividadHoras) !!},
                    datasets: [
                        {
                            label: 'Pasajeros',
                            data: {!! json_encode($actividadPasajeros) !!},
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Vehículos Activos',
                            data: {!! json_encode($actividadVehiculos) !!},
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.0)',
                            tension: 0.4,
                            borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Actividad por Hora (Hoy)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );
    });
</script>
@endsection