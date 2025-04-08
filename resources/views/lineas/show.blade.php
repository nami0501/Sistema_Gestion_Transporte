@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Detalles de la Línea: {{ $linea->nombre }}</h3>
                <div>
                    <a href="{{ route('lineas.edit', $linea->id_linea) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('lineas.index') }}" class="btn btn-secondary">
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
                                    <th style="width: 30%">Nombre:</th>
                                    <td>{{ $linea->nombre }}</td>
                                </tr>
                                <tr>
                                    <th>Color:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="width: 20px; height: 20px; background-color: {{ $linea->color }}; border: 1px solid #ccc; margin-right: 10px;"></div>
                                            {{ $linea->color }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Descripción:</th>
                                    <td>{{ $linea->descripcion ?? 'No hay descripción disponible' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Horarios y Estado</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Horario:</th>
                                    <td>{{ date('H:i', strtotime($linea->hora_inicio)) }} - {{ date('H:i', strtotime($linea->hora_fin)) }}</td>
                                </tr>
                                <tr>
                                    <th>Frecuencia:</th>
                                    <td>{{ $linea->frecuencia_min }} minutos</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge {{ $linea->estado == 'Activa' ? 'bg-success' : 
                                            ($linea->estado == 'En mantenimiento' ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $linea->estado }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha de Creación:</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($linea->fecha_creacion)) }}</td>
                                </tr>
                                <tr>
                                    <th>Última Actualización:</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($linea->fecha_modificacion)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapa de la Ruta -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Mapa de la Ruta</h5>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 400px; width: 100%;"></div>
                </div>
            </div>

            <!-- Estaciones de la Línea -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Estaciones de la Línea</h5>
                        <a href="{{ route('lineas.edit', $linea->id_linea) }}#estaciones" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Gestionar Estaciones
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($estaciones) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Estación</th>
                                        <th>Dirección</th>
                                        <th>Kilómetro</th>
                                        <th>Tiempo al Siguiente</th>
                                        <th>Distancia al Siguiente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($estaciones as $estacion)
                                        <tr>
                                            <td>{{ $estacion->orden }}</td>
                                            <td>{{ $estacion->nombre }}</td>
                                            <td>{{ $estacion->direccion }}</td>
                                            <td>{{ $estacion->kilometro_ruta }} km</td>
                                            <td>{{ $estacion->tiempo_estimado_siguiente ?? '-' }} min</td>
                                            <td>{{ $estacion->distancia_siguiente ?? '-' }} km</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay estaciones asociadas a esta línea.</p>
                    @endif
                </div>
            </div>

            <!-- Vehículos Asignados -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Vehículos Asignados</h5>
                </div>
                <div class="card-body">
                    @if(count($vehiculos) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Tipo</th>
                                        <th>Marca/Modelo</th>
                                        <th>Capacidad</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehiculos as $vehiculo)
                                        <tr>
                                            <td>{{ $vehiculo->placa }}</td>
                                            <td>{{ $vehiculo->tipo }}</td>
                                            <td>{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</td>
                                            <td>{{ $vehiculo->capacidad_pasajeros }} pasajeros</td>
                                            <td>
                                                <span class="badge {{ $vehiculo->estado == 'Activo' ? 'bg-success' : 
                                                    ($vehiculo->estado == 'En mantenimiento' || $vehiculo->estado == 'En reparación' ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ $vehiculo->estado }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('vehiculos.show', $vehiculo->id_vehiculo) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay vehículos asignados a esta línea.</p>
                    @endif
                </div>
            </div>

            <!-- Asignaciones Actuales -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Asignaciones Actuales</h5>
                        <a href="{{ route('asignaciones.create', ['linea_id' => $linea->id_linea]) }}" class="btn btn-sm btn-primary">
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
                                        <th>Vehículo</th>
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
                                            <td>{{ $asignacion->vehiculo->placa }} ({{ $asignacion->vehiculo->tipo }})</td>
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
                        <p class="text-muted">No hay asignaciones activas para esta línea.</p>
                    @endif
                </div>
            </div>

            <!-- Programación de Horarios -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Programación de Horarios</h5>
                        <a href="{{ route('horarios.create', ['linea_id' => $linea->id_linea]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Programar Horarios
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Selector de día de la semana -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <select class="form-select" id="selectorDia">
                                <option value="1">Lunes</option>
                                <option value="2">Martes</option>
                                <option value="3">Miércoles</option>
                                <option value="4">Jueves</option>
                                <option value="5">Viernes</option>
                                <option value="6">Sábado</option>
                                <option value="7">Domingo</option>
                                <option value="0">Feriados</option>
                            </select>
                        </div>
                    </div>

                    <div id="tablaHorarios">
                        <!-- La tabla de horarios se llenará con JavaScript -->
                        <p class="text-center">Seleccione un día para ver los horarios programados.</p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Operación -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Estadísticas de Operación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Puntualidad (últimos 7 días)</h6>
                            <canvas id="chartPuntualidad" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h6>Pasajeros por Día (últimos 7 días)</h6>
                            <canvas id="chartPasajeros" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incidentes Recientes -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Incidentes Recientes</h5>
                </div>
                <div class="card-body">
                    @if(count($incidentes) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Estación</th>
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
                                            <td>{{ $incidente->estacion->nombre ?? 'N/A' }}</td>
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
                        <p class="text-muted">No se han reportado incidentes para esta línea.</p>
                    @endif
                </div>
            </div>

            <!-- Botón Eliminar -->
            <div class="mt-4 text-end">
                <form action="{{ route('lineas.destroy', $linea->id_linea) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro que desea eliminar esta línea? Esta acción eliminará también todas las estaciones y asignaciones relacionadas.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Línea
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<!-- Leaflet JS para el mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<!-- Chart.js para las gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<script>
    // Inicializar mapa
    const map = L.map('map').setView([-12.046374, -77.042793], 13); // Coordenadas de Lima como ejemplo

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Cargar estaciones en el mapa
    const estaciones = @json($estaciones);
    const estacionesMarkers = [];
    
    if (estaciones && estaciones.length > 0) {
        estaciones.forEach(estacion => {
            if (estacion.latitud && estacion.longitud) {
                const marker = L.marker([estacion.latitud, estacion.longitud])
                    .addTo(map)
                    .bindPopup(`<b>${estacion.nombre}</b><br>Orden: ${estacion.orden}`);
                estacionesMarkers.push(marker);
            }
        });
        
        // Crear línea que conecta las estaciones
        const points = estaciones
            .filter(e => e.latitud && e.longitud)
            .sort((a, b) => a.orden - b.orden)
            .map(e => [e.latitud, e.longitud]);
            
        if (points.length > 1) {
            const polyline = L.polyline(points, {
                color: '{{ $linea->color }}',
                weight: 5,
                opacity: 0.7
            }).addTo(map);
            
            // Ajustar la vista para ver todas las estaciones
            map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        } else if (points.length === 1) {
            map.setView(points[0], 13);
        }
    }

    // Cargar horarios al cambiar el día seleccionado
    document.getElementById('selectorDia').addEventListener('change', function() {
        const diaSemana = this.value;
        fetch(`/api/lineas/{{ $linea->id_linea }}/horarios/${diaSemana}`)
            .then(response => response.json())
            .then(data => {
                renderizarTablaHorarios(data);
            })
            .catch(error => {
                console.error('Error al cargar horarios:', error);
                document.getElementById('tablaHorarios').innerHTML = '<p class="text-danger">Error al cargar los horarios.</p>';
            });
    });

    function renderizarTablaHorarios(horarios) {
        const container = document.getElementById('tablaHorarios');
        
        if (!horarios || horarios.length === 0) {
            container.innerHTML = '<p class="text-muted">No hay horarios programados para el día seleccionado.</p>';
            return;
        }
        
        // Agrupar por estaciones
        const estacionesHorarios = {};
        horarios.forEach(h => {
            if (!estacionesHorarios[h.id_estacion]) {
                estacionesHorarios[h.id_estacion] = {
                    nombre: h.nombre_estacion,
                    horarios: []
                };
            }
            estacionesHorarios[h.id_estacion].horarios.push({
                hora: h.hora,
                tipo: h.tipo_hora
            });
        });
        
        // Crear tabla HTML
        let html = `
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Estación</th>
                            <th>Horarios</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        Object.values(estacionesHorarios).forEach(estacion => {
            html += `
                <tr>
                    <td>${estacion.nombre}</td>
                    <td>
                        <div class="d-flex flex-wrap">
            `;
            
            estacion.horarios.sort((a, b) => a.hora.localeCompare(b.hora)).forEach(h => {
                const tipoBadge = h.tipo === 'Llegada' ? 'bg-info' : 'bg-success';
                html += `<span class="badge ${tipoBadge} me-1 mb-1">${h.hora.substring(0, 5)} (${h.tipo})</span>`;
            });
            
            html += `
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
    }

    // Gráficas de estadísticas
    // Puntualidad
    const ctxPuntualidad = document.getElementById('chartPuntualidad').getContext('2d');
    
    // Datos de ejemplo - En producción estos vendrían de una API
    const puntualidadChart = new Chart(ctxPuntualidad, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Puntualidad (%)',
                data: [92, 88, 90, 85, 95, 97, 93],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false,
                    min: 80,
                    max: 100
                }
            }
        }
    });

    // Pasajeros
    const ctxPasajeros = document.getElementById('chartPasajeros').getContext('2d');
    
    // Datos de ejemplo - En producción estos vendrían de una API
    const pasajerosChart = new Chart(ctxPasajeros, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Pasajeros',
                data: [1250, 1340, 1320, 1380, 1450, 980, 850],
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Cargar horarios del día actual por defecto
    document.getElementById('selectorDia').value = new Date().getDay() || 7; // Si es 0 (domingo), poner 7
    document.getElementById('selectorDia').dispatchEvent(new Event('change'));
</script>
@endsection
@endsection