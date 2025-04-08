@extends('layouts.admin')

@section('title', 'Nueva Asignación')

@section('styles')
<style>
    .conductor-card, .vehiculo-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .conductor-card:hover, .vehiculo-card:hover {
        border-color: #6c757d;
        transform: translateY(-3px);
    }
    .conductor-card.selected, .vehiculo-card.selected {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }
    .carrito-item {
        transition: all 0.3s ease;
    }
    .carrito-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    #configuracion-carrito {
        transition: all 0.5s ease;
    }
    .badge-linea {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-ready {
        background-color: #28a745;
    }
    .status-pending {
        background-color: #ffc107;
    }
    .status-error {
        background-color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Nueva Asignación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('asignaciones.index') }}">Asignaciones</a></li>
        <li class="breadcrumb-item active">Nueva Asignación</li>
    </ol>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        @if(session('errores'))
            <ul class="mt-2 mb-0">
                @foreach(session('errores') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-calendar-plus me-1"></i>
            Sistema de Asignaciones (Tipo Carrito)
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Panel de configuración del carrito -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-cog me-1"></i>
                            Configuración
                        </div>
                        <div class="card-body">
                            <form id="configForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $carrito['fecha'] ?? $fechaHoy }}" min="{{ $fechaHoy }}">
                                </div>
                                <div class="mb-3">
                                    <label for="id_turno" class="form-label">Turno</label>
                                    <select class="form-select" id="id_turno" name="id_turno">
                                        <option value="">Seleccione un turno</option>
                                        @foreach($turnos as $turno)
                                        <option value="{{ $turno->id_turno }}" {{ isset($carrito['id_turno']) && $carrito['id_turno'] == $turno->id_turno ? 'selected' : '' }}>
                                            {{ $turno->nombre }} ({{ $turno->hora_inicio->format('H:i') }} - {{ $turno->hora_fin->format('H:i') }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="id_linea" class="form-label">Línea</label>
                                    <select class="form-select" id="id_linea" name="id_linea">
                                        <option value="">Seleccione una línea</option>
                                        @foreach($lineas as $linea)
                                        <option value="{{ $linea->id_linea }}" {{ isset($carrito['id_linea']) && $carrito['id_linea'] == $linea->id_linea ? 'selected' : '' }}>
                                            {{ $linea->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <button type="button" id="btnConfigurar" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar Configuración
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Resumen del Carrito -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Carrito de Asignaciones
                        </div>
                        <div class="card-body p-0">
                            <div id="configuracion-carrito" class="p-3 border-bottom bg-light">
                                <div id="config-empty" class="{{ isset($carrito['id_linea']) ? 'd-none' : '' }}">
                                    <p class="text-center text-muted mb-0">Configure primero el turno y la línea</p>
                                </div>
                                <div id="config-details" class="d-flex justify-content-between {{ isset($carrito['id_linea']) ? '' : 'd-none' }}">
                                    <div>
                                        <p class="mb-0"><strong>Fecha:</strong> <span id="config-fecha">{{ isset($carrito['fecha']) ? \Carbon\Carbon::parse($carrito['fecha'])->format('d/m/Y') : '' }}</span></p>
                                        <p class="mb-0"><strong>Turno:</strong> <span id="config-turno">{{ $carrito['turno']['nombre'] ?? '' }}</span></p>
                                        <p class="mb-0"><strong>Horario:</strong> <span id="config-horario">{{ isset($carrito['turno']) ? $carrito['turno']['hora_inicio'] . ' - ' . $carrito['turno']['hora_fin'] : '' }}</span></p>
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-0"><strong>Línea:</strong></p>
                                        @if(isset($carrito['linea']))
                                        <span class="badge rounded-pill" id="config-linea" style="background-color: {{ $carrito['linea']['color'] }};">
                                            {{ $carrito['linea']['nombre'] }}
                                        </span>
                                        @else
                                        <span class="badge rounded-pill bg-secondary" id="config-linea">No seleccionada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="carrito-container">
                                <div id="carrito-empty" class="{{ isset($carrito['asignaciones']) && count($carrito['asignaciones']) > 0 ? 'd-none' : '' }}">
                                    <p class="text-center text-muted p-3 mb-0">No hay asignaciones en el carrito</p>
                                </div>
                                <div id="carrito-items">
                                    @if(isset($carrito['asignaciones']) && count($carrito['asignaciones']) > 0)
                                        @foreach($carrito['asignaciones'] as $tempId => $asignacion)
                                        <div class="carrito-item p-3 border-bottom" data-id="{{ $tempId }}">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-user me-1"></i> {{ $asignacion['usuario']['nombre_completo'] }}
                                                    </h6>
                                                    <small class="text-muted d-block">DNI: {{ $asignacion['usuario']['dni'] }}</small>
                                                    <small class="text-muted d-block">Licencia: {{ $asignacion['usuario']['numero_licencia'] }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-bus me-1"></i> {{ $asignacion['vehiculo']['placa'] }}
                                                    </h6>
                                                    <small class="text-muted d-block">{{ $asignacion['vehiculo']['tipo'] }}</small>
                                                    <small class="text-muted d-block">{{ $asignacion['vehiculo']['marca'] }} {{ $asignacion['vehiculo']['modelo'] }}</small>
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-danger btn-quitar-carrito" data-id="{{ $tempId }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mt-1">
                                                <small class="text-muted">Kilometraje inicial: {{ number_format($asignacion['kilometraje_inicial'], 0, ',', '.') }} km</small>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="p-3">
                                <form id="procesarForm" action="{{ route('asignaciones.procesar-carrito') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="observaciones" class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" id="btnProcesar" class="btn btn-success {{ isset($carrito['asignaciones']) && count($carrito['asignaciones']) > 0 ? '' : 'disabled' }}">
                                            <i class="fas fa-check-circle me-1"></i> Procesar Asignaciones
                                        </button>
                                        <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times-circle me-1"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de selección -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                            <div>
                                <i class="fas fa-user me-1"></i>
                                Conductores Disponibles
                            </div>
                            <div>
                                <div id="conductor-status" class="text-white">
                                    <div class="status-indicator status-pending"></div>
                                    <span id="conductor-status-text">No configurado</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="conductores-container">
                                <div id="conductores-loading" class="text-center p-4 d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Cargando conductores disponibles...</p>
                                </div>
                                <div id="conductores-empty" class="text-center p-4">
                                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                    <p class="mb-0">Configure el turno y la línea para ver los conductores disponibles</p>
                                </div>
                                <div id="conductores-error" class="text-center p-4 d-none">
                                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                    <p class="mb-0">Error al cargar conductores disponibles</p>
                                    <p class="text-muted mt-2 mb-0" id="conductores-error-message"></p>
                                </div>
                                <div id="conductores-list" class="row g-3 d-none">
                                    <!-- Los conductores se cargarán aquí mediante JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-warning text-white">
                            <div>
                                <i class="fas fa-bus me-1"></i>
                                Vehículos Disponibles
                            </div>
                            <div>
                                <div id="vehiculo-status" class="text-white">
                                    <div class="status-indicator status-pending"></div>
                                    <span id="vehiculo-status-text">No configurado</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="vehiculos-container">
                                <div id="vehiculos-loading" class="text-center p-4 d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Cargando vehículos disponibles...</p>
                                </div>
                                <div id="vehiculos-empty" class="text-center p-4">
                                    <i class="fas fa-bus fa-3x text-muted mb-3"></i>
                                    <p class="mb-0">Configure el turno y la línea para ver los vehículos disponibles</p>
                                </div>
                                <div id="vehiculos-error" class="text-center p-4 d-none">
                                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                    <p class="mb-0">Error al cargar vehículos disponibles</p>
                                    <p class="text-muted mt-2 mb-0" id="vehiculos-error-message"></p>
                                </div>
                                <div id="vehiculos-list" class="row g-3 d-none">
                                    <!-- Los vehículos se cargarán aquí mediante JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar al carrito -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="agregarModalLabel">Agregar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-2">
                            <h6 class="mb-2"><i class="fas fa-user me-1"></i> Conductor</h6>
                            <div id="modal-conductor"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-2">
                            <h6 class="mb-2"><i class="fas fa-bus me-1"></i> Vehículo</h6>
                            <div id="modal-vehiculo"></div>
                        </div>
                    </div>
                </div>
                <form id="agregarForm">
                    <div class="mb-3">
                        <label for="kilometraje_inicial" class="form-label">Kilometraje Inicial</label>
                        <input type="number" class="form-control" id="kilometraje_inicial" name="kilometraje_inicial" min="0" required>
                        <div id="km-help" class="form-text">Kilometraje actual del vehículo: <span id="km-actual">0</span> km</div>
                    </div>
                    <div class="alert alert-danger d-none" id="agregar-error"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAgregarCarrito">Agregar al Carrito</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Scripts específicos para esta vista -->
<script src="{{ asset('js/asignaciones.js') }}"></script>
@endsection