@extends('layouts.admin')

@section('title', 'Horarios y Asignaciones')

@section('styles')
<style>
    .schedule-card {
        border-left: 4px solid #007bff;
        transition: all 0.2s;
    }
    
    .schedule-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .schedule-card.active {
        border-left-color: #28a745;
    }
    
    .schedule-card.completed {
        border-left-color: #6c757d;
        opacity: 0.8;
    }
    
    .calendar-day {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 15px;
        font-weight: 600;
        font-size: 1.2rem;
    }
    
    .today {
        background-color: #007bff;
        color: white;
    }
    
    .route-info {
        padding: 20px;
        border-radius: 10px;
        background-color: #f8f9fa;
        margin-bottom: 20px;
    }
    
    .route-station {
        position: relative;
        padding-left: 25px;
        margin-bottom: 15px;
    }
    
    .route-station:before {
        content: "";
        position: absolute;
        left: 8px;
        top: 8px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #007bff;
        z-index: 1;
    }
    
    .route-station:after {
        content: "";
        position: absolute;
        left: 13px;
        top: 20px;
        width: 2px;
        height: calc(100% - 15px);
        background-color: #007bff;
    }
    
    .route-station:last-child:after {
        display: none;
    }
    
    .route-time {
        font-weight: 600;
        color: #007bff;
    }
    
    .station-name {
        font-weight: 600;
    }
    
    .current-location {
        background-color: rgba(40, 167, 69, 0.1);
        border-radius: 5px;
    }
    
    .current-location:before {
        background-color: #28a745;
    }
    
    .completed-station:before {
        background-color: #6c757d;
    }
    
    .completed-station:after {
        background-color: #6c757d;
    }
    
    .status-pill {
        border-radius: 15px;
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .checklist-item {
        padding: 10px 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        background-color: #f8f9fa;
        transition: all 0.2s;
    }
    
    .checklist-item:hover {
        background-color: #e9ecef;
    }
    
    .checklist-item.completed {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .weekly-schedule {
        overflow-x: auto;
    }
    
    .weekly-schedule table {
        min-width: 800px;
    }
    
    .schedule-time {
        font-size: 0.8rem;
        white-space: nowrap;
    }
    
    .vehicle-info-list {
        list-style: none;
        padding: 0;
    }
    
    .vehicle-info-list li {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .vehicle-info-list i {
        margin-right: 10px;
        color: #007bff;
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .calendar-day {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Mis Horarios y Asignaciones</h6>
                </div>
                <div class="card-body">
                    <p>Bienvenido, {{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}. Aquí puedes consultar tus asignaciones y horarios programados.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Asignación Actual -->
    @if($asignacionActual)
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Asignación Actual</h6>
                            <p class="text-sm mb-0">
                                <i class="bi bi-clock text-info"></i>
                                <span class="font-weight-bold ms-1">{{ date('H:i', strtotime($asignacionActual->hora_inicio)) }} - {{ date('H:i', strtotime($asignacionActual->hora_fin)) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 text-end d-flex align-items-center justify-content-end">
                            <span class="status-pill bg-success text-white me-3">
                                <i class="bi bi-check-circle"></i> En curso
                            </span>
                            <a href="{{ route('conductor.detalles_asignacion', $asignacionActual->id_asignacion) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver detalles
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Información del Vehículo</h6>
                                    <ul class="vehicle-info-list">
                                        <li>
                                            <i class="bi bi-truck"></i>
                                            <span><strong>Vehículo:</strong> {{ $asignacionActual->vehiculo->placa }} ({{ $asignacionActual->vehiculo->tipo }})</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-signpost-split"></i>
                                            <span><strong>Línea:</strong> {{ $asignacionActual->linea->nombre }}</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-palette"></i>
                                            <span><strong>Color:</strong> <span class="badge" style="background-color: {{ $asignacionActual->linea->color }}">{{ $asignacionActual->linea->color }}</span></span>
                                        </li>
                                        <li>
                                            <i class="bi bi-speedometer"></i>
                                            <span><strong>Kilometraje inicial:</strong> {{ number_format($asignacionActual->kilometraje_inicial) }} km</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-arrow-repeat"></i>
                                            <span><strong>Vueltas completadas:</strong> {{ $asignacionActual->vueltas_completas }} de {{ $vueltasProgramadas }}</span>
                                        </li>
                                    </ul>
                                    
                                    <div class="mt-4">
                                        <h6 class="mb-3">Lista de Verificación</h6>
                                        <div class="checklist-item {{ $checklist->revision_frenos ? 'completed' : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="check-frenos" {{ $checklist->revision_frenos ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-frenos">
                                                    Revisión de frenos
                                                </label>
                                            </div>
                                        </div>
                                        <div class="checklist-item {{ $checklist->revision_luces ? 'completed' : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="check-luces" {{ $checklist->revision_luces ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-luces">
                                                    Revisión de luces
                                                </label>
                                            </div>
                                        </div>
                                        <div class="checklist-item {{ $checklist->revision_neumaticos ? 'completed' : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="check-neumaticos" {{ $checklist->revision_neumaticos ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-neumaticos">
                                                    Revisión de neumáticos
                                                </label>
                                            </div>
                                        </div>
                                        <div class="checklist-item {{ $checklist->revision_aceite ? 'completed' : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="check-aceite" {{ $checklist->revision_aceite ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-aceite">
                                                    Nivel de aceite
                                                </label>
                                            </div>
                                        </div>
                                        <div class="checklist-item {{ $checklist->limpieza_unidad ? 'completed' : '' }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="check-limpieza" {{ $checklist->limpieza_unidad ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-limpieza">
                                                    Limpieza de la unidad
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="route-info">
                                <h6 class="mb-4">Ruta Actual - {{ $asignacionActual->linea->nombre }}</h6>
                                
                                @foreach($rutaEstaciones as $index => $estacion)
                                <div class="route-station {{ $estacion->id_estacion === $estacionActual->id_estacion ? 'current-location' : ($index < $estacionActualIndex ? 'completed-station' : '') }}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="station-name">{{ $estacion->nombre }}</span>
                                            <p class="text-muted mb-0 small">{{ $estacion->direccion }}</p>
                                        </div>
                                        <div class="text-end">
                                            <span class="route-time">{{ $horarios[$estacion->id_estacion] }}</span>
                                            @if($estacion->id_estacion === $estacionActual->id_estacion)
                                            <p class="text-success mb-0 small">Ubicación actual</p>
                                            @elseif($index < $estacionActualIndex)
                                            <p class="text-muted mb-0 small">Completada</p>
                                            @else
                                            <p class="text-muted mb-0 small">Próximamente</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6>Próxima Estación</h6>
                                            <div class="d-flex align-items-center mt-3">
                                                <div>
                                                    <h5 class="mb-0">{{ $proximaEstacion->nombre }}</h5>
                                                    <p class="text-muted mb-0 small">{{ $proximaEstacion->direccion }}</p>
                                                    <p class="text-primary mt-2 mb-0">
                                                        <i class="bi bi-clock"></i> Llegada estimada: {{ $horarioProximaEstacion }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6>Acciones Rápidas</h6>
                                            <div class="d-grid gap-2 mt-3">
                                                <button class="btn btn-outline-primary">
                                                    <i class="bi bi-geo-alt"></i> Marcar llegada a estación
                                                </button>
                                                <button class="btn btn-outline-warning">
                                                    <i class="bi bi-exclamation-triangle"></i> Reportar incidente
                                                </button>
                                                <button class="btn btn-outline-info">
                                                    <i class="bi bi-headset"></i> Contactar supervisor
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
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Próximas Asignaciones -->
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6">
                            <h6>Próximas Asignaciones</h6>
                        </div>
                        <div class="col-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm active" id="view-week">Esta semana</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="view-month">Este mes</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0" style="max-height: 400px; overflow-y: auto;">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Horario</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Vehículo</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Línea</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proximasAsignaciones as $asignacion)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1 align-items-center">
                                            <div class="calendar-day {{ date('Y-m-d') == date('Y-m-d', strtotime($asignacion->fecha)) ? 'today' : '' }}">
                                                {{ date('d', strtotime($asignacion->fecha)) }}
                                            </div>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm">{{ date('l', strtotime($asignacion->fecha)) }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ date('F', strtotime($asignacion->fecha)) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ date('H:i', strtotime($asignacion->hora_inicio)) }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ date('H:i', strtotime($asignacion->hora_fin)) }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $asignacion->vehiculo->placa }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $asignacion->vehiculo->tipo }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $asignacion->linea->nombre }}</p>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $asignacion->estado }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen Semanal -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Resumen Semanal</h6>
                </div>
                <div class="card-body">
                    <div class="weekly-schedule">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th></th>
                                    @foreach($diasSemana as $dia)
                                    <th class="text-center {{ $dia['esDiaActual'] ? 'table-primary' : '' }}">{{ $dia['nombre'] }}<br>{{ $dia['fecha'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($horasLaborales as $hora)
                                <tr>
                                    <td class="text-center schedule-time">{{ $hora }}</td>
                                    @foreach($diasSemana as $dia)
                                    <td class="
                                        @if(isset($calendarioSemanal[$dia['fecha']][$hora]))
                                            @if($calendarioSemanal[$dia['fecha']][$hora]['estado'] == 'Programado')
                                                table-info
                                            @elseif($calendarioSemanal[$dia['fecha']][$hora]['estado'] == 'En curso')
                                                table-success
                                            @elseif($calendarioSemanal[$dia['fecha']][$hora]['estado'] == 'Completado')
                                                table-secondary
                                            @endif
                                        @endif
                                    ">
                                        @if(isset($calendarioSemanal[$dia['fecha']][$hora]))
                                        <small>{{ $calendarioSemanal[$dia['fecha']][$hora]['linea'] }}</small>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-gradient-primary mb-2">
                                <div class="card-body py-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Horas esta semana</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ $horasSemana }} horas
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                                <i class="bi bi-clock-history text-primary opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-gradient-success mb-2">
                                <div class="card-body py-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-white text-sm mb-0 opacity-7">Km Recorridos</p>
                                                <h5 class="text-white font-weight-bolder mb-0">
                                                    {{ $kilometrosRecorridos }} km
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                                <i class="bi bi-speedometer2 text-success opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Historial de Asignaciones -->
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Historial de Asignaciones</h6>
                            <p class="text-sm mb-0">
                                <i class="bi bi-calendar-check text-info"></i>
                                <span class="font-weight-bold ms-1">Últimas asignaciones completadas</span>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <form class="d-flex">
                                <input class="form-control form-control-sm me-2" type="month" value="{{ date('Y-m') }}" aria-label="Mes">
                                <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Horario</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Vehículo/Línea</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Vueltas</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kilometraje</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Estado</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historialAsignaciones as $asignacion)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1 align-items-center">
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm">{{ date('d/m/Y', strtotime($asignacion->fecha)) }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ date('l', strtotime($asignacion->fecha)) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ date('H:i', strtotime($asignacion->hora_inicio)) }} - {{ date('H:i', strtotime($asignacion->hora_fin)) }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ round((strtotime($asignacion->hora_fin) - strtotime($asignacion->hora_inicio)) / 3600, 1) }} horas</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $asignacion->vehiculo->placa }} ({{ $asignacion->vehiculo->tipo }})</p>
                                        <p class="text-xs text-secondary mb-0">{{ $asignacion->linea->nombre }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $asignacion->vueltas_completas }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ number_format($asignacion->kilometraje_final - $asignacion->kilometraje_inicial) }} km</p>
                                        <p class="text-xs text-secondary mb-0">Inicial: {{ number_format($asignacion->kilometraje_inicial) }} km</p>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-sm bg-gradient-success">{{ $asignacion->estado }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('conductor.detalles_asignacion', $asignacion->id_asignacion) }}" class="btn btn-link text-secondary mb-0">
                                            <i class="bi bi-eye text-xs"></i>
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
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cambiar entre vista de semana y mes
        document.getElementById('view-week').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('view-month').classList.remove('active');
            // Aquí iría la lógica para cambiar la vista
        });
        
        document.getElementById('view-month').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('view-week').classList.remove('active');
            // Aquí iría la lógica para cambiar la vista
        });
        
        // Lógica para los checkboxes de la lista de verificación
        document.querySelectorAll('.checklist-item input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const item = this.closest('.checklist-item');
                if (this.checked) {
                    item.classList.add('completed');
                } else {
                    item.classList.remove('completed');
                }
                
                // Aquí se enviaría una petición AJAX para actualizar el estado en el servidor
                const itemId = this.id;
                const isChecked = this.checked;
                
                // Simulamos un envío de datos
                console.log(`Actualizando ${itemId}: ${isChecked}`);
                
                // En un caso real, usarías algo como:
                /*
                fetch('/api/checklist/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        item: itemId,
                        checked: isChecked
                    })
                })
                .then(response => response.json())
                .then(data => console.log(data))
                .catch(error => console.error('Error:', error));
                */
            });
        });
    });
</script>
@endsection