@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Panel de Supervisión</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Panel de Supervisión</li>
    </ol>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Panel de Supervisión de Operaciones</h5>
                </div>
                <div class="card-body">
                    <p>Bienvenido, {{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}. Como Supervisor, tienes acceso a la información operativa del sistema de transporte.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Estadísticas de Operación -->
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Vehículos en Servicio</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $vehiculosEnServicio }}</div>
                            <div class="text-sm mb-0">
                                <span class="text-success font-weight-bold">+{{ $porcentajeOperacion }}%</span>
                                de la flota
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Conductores Activos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $conductoresActivos }}</div>
                            <div class="text-sm mb-0">
                                <span class="text-success font-weight-bold">{{ $conductoresEnRuta }}</span>
                                en ruta ahora
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Incidentes Activos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $incidentesActivos }}</div>
                            <div class="text-sm mb-0">
                                <span class="text-danger font-weight-bold">{{ $incidentesCriticos }}</span>
                                de alta prioridad
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Puntualidad</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $puntualidad }}%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                            style="width: {{ $puntualidad }}%" aria-valuenow="{{ $puntualidad }}" aria-valuemin="0" 
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm mb-0">
                                <span class="text-{{ $puntualidad > 85 ? 'success' : 'warning' }} font-weight-bold">{{ $tendenciaPuntualidad }}</span>
                                vs ayer
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mapa de Operaciones -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Mapa de Operaciones en Tiempo Real</h6>
                    <div class="dropdown no-arrow">
                        <a href="{{ route('operador.monitoreo') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-eye fa-sm text-white-50"></i> Vista completa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-2 text-sm">
                        <i class="fas fa-map-marker-alt text-success mr-1"></i>
                        Actualizado hace {{ $tiempoActualizacion }} minutos
                    </p>
                    <div id="operation-map" style="height: 400px;"></div>
                </div>
            </div>
        </div>

        <!-- Estado de Líneas -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado de Líneas</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Línea</th>
                                    <th>Vehículos</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadoLineas as $linea)
                                <tr>
                                    <td>{{ $linea->nombre }}</td>
                                    <td>{{ $linea->vehiculos_activos }} / {{ $linea->total_vehiculos }}</td>
                                    <td>
                                        <span class="badge {{ $linea->estado == 'Activa' ? 'bg-success' : ($linea->estado == 'Suspendida' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ $linea->estado }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Asignaciones del día -->
        <div class="col-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Asignaciones del Día</h6>
                    <div class="dropdown no-arrow">
                        <a href="{{ route('asignaciones.index') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-list fa-sm text-white-50"></i> Ver todas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-2 text-sm">
                        <i class="fas fa-calendar text-info mr-1"></i>
                        {{ date('d/m/Y') }}
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Conductor</th>
                                    <th>Vehículo</th>
                                    <th>Línea</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asignacionesHoy as $asignacion)
                                <tr>
                                    <td>
                                        {{ $asignacion->conductor }}
                                        <small class="d-block text-muted">{{ $asignacion->dni }}</small>
                                    </td>
                                    <td>
                                        {{ $asignacion->placa }}
                                        <small class="d-block text-muted">{{ $asignacion->tipo_vehiculo }}</small>
                                    </td>
                                    <td>{{ $asignacion->linea }}</td>
                                    <td>{{ date('H:i', strtotime($asignacion->hora_inicio)) }} - {{ date('H:i', strtotime($asignacion->hora_fin)) }}</td>
                                    <td>
                                        <span class="badge {{ $asignacion->estado == 'Programado' ? 'bg-info' : 
                                            ($asignacion->estado == 'En curso' ? 'bg-success' : 
                                            ($asignacion->estado == 'Completado' ? 'bg-secondary' : 'bg-danger')) }}">
                                            {{ $asignacion->estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('asignaciones.show', $asignacion->id_asignacion) }}" class="btn btn-info btn-sm" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('asignaciones.edit', $asignacion->id_asignacion) }}" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
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
    </div>

    <div class="row">
        <!-- Acciones Rápidas -->
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="{{ route('asignaciones.create') }}" class="btn btn-primary btn-block py-3">
                                <i class="fas fa-plus-circle mr-1"></i> Nueva Asignación
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="{{ route('incidentes.create') }}" class="btn btn-warning btn-block py-3">
                                <i class="fas fa-exclamation-circle mr-1"></i> Reportar Incidente
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="{{ route('comunicaciones.index') }}" class="btn btn-success btn-block py-3">
                                <i class="fas fa-bullhorn mr-1"></i> Comunicados
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el mapa
        const map = L.map('operation-map').setView([-12.046374, -77.042793], 13); // Coordenadas de Lima, Perú (ajustar según tu ubicación)

        // Añadir capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Datos de ejemplo de vehículos (estos datos deberían venir de tu backend)
        const vehiculos = {!! json_encode($vehiculosEnRuta) !!};

        // Iconos personalizados para diferentes estados
        const normalIcon = L.divIcon({
            html: '<div style="background-color: #28a745; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white;"></div>',
            className: 'marker-icon',
            iconSize: [18, 18]
        });

        const delayedIcon = L.divIcon({
            html: '<div style="background-color: #ffc107; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white;"></div>',
            className: 'marker-icon',
            iconSize: [18, 18]
        });

        const alertIcon = L.divIcon({
            html: '<div style="background-color: #dc3545; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white;"></div>',
            className: 'marker-icon',
            iconSize: [18, 18]
        });

        // Añadir marcadores para cada vehículo
        vehiculos.forEach(vehicle => {
            const icon = vehicle.estado === 'normal' ? normalIcon : 
                         (vehicle.estado === 'retrasado' ? delayedIcon : alertIcon);
            
            // Crear marcador
            const marker = L.marker([vehicle.latitud, vehicle.longitud], {
                icon: icon
            }).addTo(map);
            
            // Añadir popup con información
            marker.bindPopup(`
                <b>${vehicle.placa}</b><br>
                Línea: ${vehicle.linea}<br>
                Conductor: ${vehicle.conductor}<br>
                Velocidad: ${vehicle.velocidad} km/h<br>
                Última actualización: ${vehicle.ultima_actualizacion}
            `);
        });
    });
</script>
@endsection