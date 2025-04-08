@extends('layouts.admin')

@section('title', 'Centro de Información')

@section('styles')
<style>
    .info-card {
        transition: all 0.3s;
        border-radius: 10px;
        overflow: hidden;
        height: 100%;
    }
    
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .info-card .card-img-top {
        height: 160px;
        object-fit: cover;
    }
    
    .info-header {
        position: relative;
        background-size: cover;
        background-position: center;
        height: 200px;
        display: flex;
        align-items: flex-end;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .info-header-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.7));
        border-radius: 10px;
    }
    
    .info-header-content {
        position: relative;
        z-index: 1;
        color: white;
    }
    
    .line-card {
        border-left: 5px solid #007bff;
        transition: all 0.3s;
    }
    
    .line-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .station-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .station-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f1f1f1;
        transition: all 0.2s;
    }
    
    .station-item:hover {
        background-color: #f8f9fa;
    }
    
    .station-item:last-child {
        border-bottom: none;
    }
    
    .tariff-table th, .tariff-table td {
        text-align: center;
    }
    
    .news-item {
        padding: 15px;
        border-bottom: 1px solid #f1f1f1;
        transition: all 0.2s;
    }
    
    .news-item:hover {
        background-color: #f8f9fa;
    }
    
    .news-item:last-child {
        border-bottom: none;
    }
    
    .news-date {
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    .news-title {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box .form-control {
        padding-left: 40px;
        border-radius: 20px;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 10px;
        color: #6c757d;
    }
    
    .nav-pills .nav-link.active {
        background-color: #007bff;
    }
    
    .route-map-container {
        height: 400px;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .schedule-time {
        font-weight: 600;
        color: #007bff;
    }
    
    .terminal-station {
        font-weight: 700;
    }
    
    .service-icon {
        width: 50px;
        height: 50px;
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.5rem;
        margin-right: 15px;
    }
    
    .faq-item {
        border-bottom: 1px solid #f1f1f1;
        padding: 15px 0;
    }
    
    .faq-item:last-child {
        border-bottom: none;
    }
    
    .faq-question {
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .faq-answer {
        padding-top: 10px;
        display: none;
    }
    
    .faq-item.active .faq-answer {
        display: block;
    }
    
    .passenger-chart {
        height: 250px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header Banner -->
    <div class="info-header" style="background-image: url('{{ asset('images/transport-banner.jpg') }}')">
        <div class="info-header-overlay"></div>
        <div class="info-header-content">
            <h2>Centro de Información</h2>
            <p class="mb-0">Toda la información sobre nuestro sistema de transporte</p>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="quick-search" placeholder="Buscar líneas, estaciones, horarios...">
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="bi bi-signpost-split"></i> Líneas
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="bi bi-geo-alt"></i> Estaciones
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="bi bi-clock"></i> Horarios
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="bi bi-cash-coin"></i> Tarifas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informative Sections -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card info-card">
                <img src="{{ asset('images/lines.jpg') }}" class="card-img-top" alt="Líneas">
                <div class="card-body">
                    <h5 class="card-title">Líneas y Rutas</h5>
                    <p class="card-text">Explora nuestras líneas de transporte y sus rutas.</p>
                    <a href="#lines-section" class="btn btn-sm btn-primary">Ver más</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card info-card">
                <img src="{{ asset('images/stations.jpg') }}" class="card-img-top" alt="Estaciones">
                <div class="card-body">
                    <h5 class="card-title">Estaciones</h5>
                    <p class="card-text">Información sobre nuestras estaciones y terminales.</p>
                    <a href="#stations-section" class="btn btn-sm btn-primary">Ver más</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card info-card">
                <img src="{{ asset('images/schedules.jpg') }}" class="card-img-top" alt="Horarios">
                <div class="card-body">
                    <h5 class="card-title">Horarios</h5>
                    <p class="card-text">Consulta los horarios de todas nuestras líneas.</p>
                    <a href="#schedules-section" class="btn btn-sm btn-primary">Ver más</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card info-card">
                <img src="{{ asset('images/tariffs.jpg') }}" class="card-img-top" alt="Tarifas">
                <div class="card-body">
                    <h5 class="card-title">Tarifas</h5>
                    <p class="card-text">Conoce nuestras tarifas y opciones de pago.</p>
                    <a href="#tariffs-section" class="btn btn-sm btn-primary">Ver más</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Líneas y Rutas Section -->
    <div class="row mb-4" id="lines-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Líneas y Rutas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                @foreach($lineas as $index => $linea)
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="v-pills-{{ $linea->id_linea }}-tab" data-bs-toggle="pill" data-bs-target="#v-pills-{{ $linea->id_linea }}" type="button" role="tab" aria-controls="v-pills-{{ $linea->id_linea }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    <div class="d-flex align-items-center">
                                        <span class="badge me-2" style="background-color: {{ $linea->color }};">&nbsp;</span>
                                        <span>{{ $linea->nombre }}</span>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="tab-content" id="v-pills-tabContent">
                                @foreach($lineas as $index => $linea)
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="v-pills-{{ $linea->id_linea }}" role="tabpanel" aria-labelledby="v-pills-{{ $linea->id_linea }}-tab">
                                    <h4>{{ $linea->nombre }}</h4>
                                    <p>{{ $linea->descripcion }}</p>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <p><strong>Horario de operación:</strong><br> {{ date('H:i', strtotime($linea->hora_inicio)) }} - {{ date('H:i', strtotime($linea->hora_fin)) }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Frecuencia:</strong><br> Cada {{ $linea->frecuencia_min }} minutos</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Mapa de la ruta -->
                                    <div class="route-map-container mb-3" id="route-map-{{ $linea->id_linea }}"></div>
                                    
                                    <!-- Estaciones de la línea -->
                                    <h5 class="mb-3">Estaciones</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Orden</th>
                                                    <th>Estación</th>
                                                    <th>Tiempo a siguiente</th>
                                                    <th>Distancia</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lineasEstaciones[$linea->id_linea] as $estacion)
                                                <tr>
                                                    <td>{{ $estacion->orden }}</td>
                                                    <td class="{{ $estacion->es_terminal ? 'terminal-station' : '' }}">{{ $estacion->nombre }}</td>
                                                    <td>{{ $estacion->tiempo_estimado_siguiente ? $estacion->tiempo_estimado_siguiente . ' min' : '-' }}</td>
                                                    <td>{{ $estacion->distancia_siguiente ? $estacion->distancia_siguiente . ' km' : '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estaciones Section -->
    <div class="row mb-4" id="stations-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Estaciones</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="station-search" placeholder="Buscar estación...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="line-filter">
                                <option value="">Todas las líneas</option>
                                @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}">{{ $linea->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="station-type-filter">
                                <option value="">Todos los tipos</option>
                                <option value="1">Terminales</option>
                                <option value="0">Estaciones regulares</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="station-list">
                                @foreach($estaciones as $estacion)
                                <div class="station-item" data-station-id="{{ $estacion->id_estacion }}" data-es-terminal="{{ $estacion->es_terminal ? '1' : '0' }}" data-lines="{{ implode(',', $estacionLineas[$estacion->id_estacion]) }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $estacion->nombre }} {{ $estacion->es_terminal ? '(Terminal)' : '' }}</h6>
                                            <p class="text-muted mb-1 small">{{ $estacion->direccion }}</p>
                                            <div>
                                                @foreach($estacionLineas[$estacion->id_estacion] as $lineaId)
                                                <span class="badge bg-primary me-1">{{ $lineasNombres[$lineaId] }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary view-station-btn" data-station-id="{{ $estacion->id_estacion }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="station-map" style="height: 300px; border-radius: 10px;"></div>
                            
                            <div id="station-details" class="mt-3 d-none">
                                <h5 id="detail-station-name"></h5>
                                <p id="detail-station-address" class="text-muted"></p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <p><strong>Capacidad:</strong> <span id="detail-station-capacity"></span> personas</p>
                                    </div>
                                    <div class="col-6">
                                        <p><strong>Estado:</strong> <span id="detail-station-status"></span></p>
                                    </div>
                                </div>
                                
                                <h6>Líneas que pasan por esta estación:</h6>
                                <div id="detail-station-lines" class="mb-3"></div>
                                
                                <h6>Próximas llegadas:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm" id="detail-station-arrivals">
                                        <thead>
                                            <tr>
                                                <th>Línea</th>
                                                <th>Llegada</th>
                                                <th>Destino</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Filled by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Horarios Section -->
    <div class="row mb-4" id="schedules-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Horarios</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="schedule-line" class="form-label">Línea:</label>
                            <select class="form-select" id="schedule-line">
                                @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}">{{ $linea->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="schedule-direction" class="form-label">Dirección:</label>
                            <select class="form-select" id="schedule-direction">
                                <option value="Norte-Sur">Norte - Sur</option>
                                <option value="Sur-Norte">Sur - Norte</option>
                                <option value="Este-Oeste">Este - Oeste</option>
                                <option value="Oeste-Este">Oeste - Este</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="schedule-day" class="form-label">Día:</label>
                            <select class="form-select" id="schedule-day">
                                <option value="1">Lunes</option>
                                <option value="2">Martes</option>
                                <option value="3">Miércoles</option>
                                <option value="4">Jueves</option>
                                <option value="5">Viernes</option>
                                <option value="6">Sábado</option>
                                <option value="7">Domingo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="schedule-table">
                            <thead>
                                <tr>
                                    <th>Estación</th>
                                    <th colspan="6">Hora de salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ejemplo de contenido, esto sería dinámico según la selección -->
                                @foreach($horarioEjemplo as $estacion)
                                <tr>
                                    <td class="{{ $estacion['es_terminal'] ? 'terminal-station' : '' }}">{{ $estacion['nombre'] }}</td>
                                    @foreach($estacion['horarios'] as $horario)
                                    <td class="schedule-time">{{ $horario }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle-fill"></i>
                        Los tiempos son aproximados y pueden variar según las condiciones del tráfico.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarifas Section -->
    <div class="row mb-4" id="tariffs-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Tarifas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Precios por tipo de usuario</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered tariff-table">
                                    <thead>
                                        <tr>
                                            <th>Tipo de Usuario</th>
                                            <th>Tarifa</th>
                                            <th>Descuento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tarifas as $tarifa)
                                        <tr>
                                            <td>{{ $tarifa->tipo }}</td>
                                            <td class="fw-bold">S/. {{ number_format($tarifa->monto, 2) }}</td>
                                            <td>{{ $tarifa->descuento }}%</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Medios de pago</h6>
                            <div class="d-flex align-items-center mb-4">
                                <div class="service-icon">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Tarjeta de Transporte</h6>
                                    <p class="mb-0 text-muted">Recargable en cualquier estación o punto autorizado.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-4">
                                <div class="service-icon">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Efectivo</h6>
                                    <p class="mb-0 text-muted">Disponible solo en terminales principales.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="service-icon">
                                    <i class="bi bi-phone"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Aplicación Móvil</h6>
                                    <p class="mb-0 text-muted">Paga desde tu smartphone con nuestra app oficial.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="row">
        <!-- Noticias -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Noticias y Avisos</h5>
                </div>
                <div class="card-body p-0">
                    @foreach($noticias as $noticia)
                    <div class="news-item">
                        <div class="news-date">{{ date('d/m/Y', strtotime($noticia->fecha)) }}</div>
                        <div class="news-title">{{ $noticia->titulo }}</div>
                        <p class="mb-0">{{ Str::limit($noticia->contenido, 80) }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('noticias.index') }}" class="btn btn-sm btn-outline-primary">Ver todas las noticias</a>
                </div>
            </div>
        </div>
        
        <!-- Preguntas Frecuentes -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Preguntas Frecuentes</h5>
                </div>
                <div class="card-body">
                    <div class="faq-item active">
                        <div class="faq-question">
                            <span>¿Cómo puedo obtener una tarjeta de transporte?</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Puedes obtener tu tarjeta en cualquier terminal principal o punto autorizado presentando tu documento de identidad y pagando la cuota de emisión de S/. 5.00.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>¿Cuáles son los horarios de atención?</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Nuestro sistema opera de lunes a domingo desde las 5:00 am hasta las 11:00 pm. Los horarios pueden variar según la línea y estación.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>¿Qué debo hacer si perdí mi tarjeta?</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Debes acudir a cualquier centro de atención al cliente con tu documento de identidad para reportar la pérdida y solicitar una nueva tarjeta. Se aplicará un cargo por reposición.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>¿Cómo puedo recargar mi tarjeta?</span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Puedes recargar tu tarjeta en todas las estaciones, terminales, puntos autorizados y a través de nuestra aplicación móvil.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Estadísticas del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="passenger-chart" id="passengers-chart"></div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="mb-0">{{ number_format($totalEstaciones) }}</h3>
                            <p class="text-muted">Estaciones</p>
                        </div>
                        <div class="col-6">
                            <h3 class="mb-0">{{ number_format($totalVehiculos) }}</h3>
                            <p class="text-muted">Vehículos</p>
                        </div>
                        <div class="col-6">
                            <h3 class="mb-0">{{ number_format($totalKilometros) }}</h3>
                            <p class="text-muted">Km de ruta</p>
                        </div>
                        <div class="col-6">
                            <h3 class="mb-0">{{ number_format($pasajerosDiarios) }}</h3>
                            <p class="text-muted">Pasajeros diarios</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar mapas de líneas
        @foreach($lineas as $linea)
        const routeMap{{ $linea->id_linea }} = L.map('route-map-{{ $linea->id_linea }}').setView([-12.046374, -77.042793], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(routeMap{{ $linea->id_linea }});
        
        // Dibujar la ruta
        const rutaCoords{{ $linea->id_linea }} = {!! json_encode($lineasRutas[$linea->id_linea] ?? []) !!};
        
        if (rutaCoords{{ $linea->id_linea }}.length > 0) {
            const polyline = L.polyline(rutaCoords{{ $linea->id_linea }}.map(point => [point.latitud, point.longitud]), {
                color: '{{ $linea->color }}',
                weight: 5,
                opacity: 0.7
            }).addTo(routeMap{{ $linea->id_linea }});
            
            // Ajustar vista al recorrido
            routeMap{{ $linea->id_linea }}.fitBounds(polyline.getBounds());
        }
        
        // Añadir marcadores de estaciones
        @foreach($lineasEstaciones[$linea->id_linea] as $estacion)
        L.marker([{{ $estacion->latitud }}, {{ $estacion->longitud }}], {
            icon: L.divIcon({
                className: '{{ $estacion->es_terminal ? "terminal-marker" : "station-marker" }}',
                iconSize: [{{ $estacion->es_terminal ? 24 : 20 }}, {{ $estacion->es_terminal ? 24 : 20 }}]
            })
        }).addTo(routeMap{{ $linea->id_linea }})
        .bindPopup('<b>{{ $estacion->nombre }}</b><br>{{ $estacion->es_terminal ? "Terminal" : "Estación" }}');
        @endforeach
        @endforeach
        
        // Inicializar mapa de estaciones
        const stationMap = L.map('station-map').setView([-12.046374, -77.042793], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(stationMap);
        
        // Añadir todas las estaciones al mapa
        const stationMarkers = {};
        
        @foreach($estaciones as $estacion)
        stationMarkers[{{ $estacion->id_estacion }}] = L.marker([{{ $estacion->latitud }}, {{ $estacion->longitud }}], {
            icon: L.divIcon({
                className: '{{ $estacion->es_terminal ? "terminal-marker" : "station-marker" }}',
                iconSize: [{{ $estacion->es_terminal ? 24 : 20 }}, {{ $estacion->es_terminal ? 24 : 20 }}]
            })
        }).addTo(stationMap)
        .bindPopup('<b>{{ $estacion->nombre }}</b><br>{{ $estacion->es_terminal ? "Terminal" : "Estación" }}');
        @endforeach
        
        // Filtro de estaciones
        function filterStations() {
            const searchTerm = document.getElementById('station-search').value.toLowerCase();
            const lineFilter = document.getElementById('line-filter').value;
            const typeFilter = document.getElementById('station-type-filter').value;
            
            document.querySelectorAll('.station-item').forEach(item => {
                const stationName = item.querySelector('h6').textContent.toLowerCase();
                const stationLines = item.dataset.lines.split(',');
                const isTerminal = item.dataset.esTerminal;
                
                const matchesSearch = searchTerm === '' || stationName.includes(searchTerm);
                const matchesLine = lineFilter === '' || stationLines.includes(lineFilter);
                const matchesType = typeFilter === '' || isTerminal === typeFilter;
                
                if (matchesSearch && matchesLine && matchesType) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        document.getElementById('station-search').addEventListener('input', filterStations);
        document.getElementById('line-filter').addEventListener('change', filterStations);
        document.getElementById('station-type-filter').addEventListener('change', filterStations);
        
        // Ver detalles de estación
        document.querySelectorAll('.view-station-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const stationId = this.dataset.stationId;
                const station = {!! json_encode($estacionesData) !!}[stationId];
                
                // Mostrar detalles
                document.getElementById('detail-station-name').textContent = station.nombre;
                document.getElementById('detail-station-address').textContent = station.direccion;
                document.getElementById('detail-station-capacity').textContent = station.capacidad_maxima;
                document.getElementById('detail-station-status').textContent = station.estado;
                
                // Mostrar líneas
                const linesContainer = document.getElementById('detail-station-lines');
                linesContainer.innerHTML = '';
                
                station.lineas.forEach(linea => {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary me-1';
                    badge.textContent = linea.nombre;
                    badge.style.backgroundColor = linea.color;
                    linesContainer.appendChild(badge);
                });
                
                // Llenar tabla de llegadas
                const arrivalsTable = document.getElementById('detail-station-arrivals').querySelector('tbody');
                arrivalsTable.innerHTML = '';
                
                station.proximasLlegadas.forEach(llegada => {
                    const row = document.createElement('tr');
                    
                    const lineCell = document.createElement('td');
                    const lineBadge = document.createElement('span');
                    lineBadge.className = 'badge me-1';
                    lineBadge.style.backgroundColor = llegada.color;
                    lineBadge.textContent = llegada.linea;
                    lineCell.appendChild(lineBadge);
                    
                    const timeCell = document.createElement('td');
                    timeCell.className = 'schedule-time';
                    timeCell.textContent = llegada.hora;
                    
                    const destinationCell = document.createElement('td');
                    destinationCell.textContent = llegada.destino;
                    
                    row.appendChild(lineCell);
                    row.appendChild(timeCell);
                    row.appendChild(destinationCell);
                    
                    arrivalsTable.appendChild(row);
                });
                
                // Mostrar el panel de detalles
                document.getElementById('station-details').classList.remove('d-none');
                
                // Centrar mapa en la estación
                stationMap.setView([station.latitud, station.longitud], 15);
                stationMarkers[stationId].openPopup();
            });
        });
        
        // Cambio de horarios
        document.getElementById('schedule-line').addEventListener('change', updateSchedule);
        document.getElementById('schedule-direction').addEventListener('change', updateSchedule);
        document.getElementById('schedule-day').addEventListener('change', updateSchedule);
        
        function updateSchedule() {
            const lineId = document.getElementById('schedule-line').value;
            const direction = document.getElementById('schedule-direction').value;
            const day = document.getElementById('schedule-day').value;
            
            // Aquí se haría una petición AJAX para obtener los horarios
            console.log(`Obteniendo horarios para línea ${lineId}, dirección ${direction}, día ${day}`);
            
            // En un caso real, usarías algo como:
            /*
            fetch(`/api/schedules?line=${lineId}&direction=${direction}&day=${day}`)
                .then(response => response.json())
                .then(data => {
                    // Actualizar la tabla con los datos recibidos
                    const table = document.getElementById('schedule-table').querySelector('tbody');
                    table.innerHTML = '';
                    
                    data.forEach(row => {
                        const tr = document.createElement('tr');
                        
                        const stationCell = document.createElement('td');
                        stationCell.textContent = row.station;
                        if (row.is_terminal) {
                            stationCell.className = 'terminal-station';
                        }
                        tr.appendChild(stationCell);
                        
                        row.times.forEach(time => {
                            const timeCell = document.createElement('td');
                            timeCell.className = 'schedule-time';
                            timeCell.textContent = time;
                            tr.appendChild(timeCell);
                        });
                        
                        table.appendChild(tr);
                    });
                })
                .catch(error => console.error('Error:', error));
            */
        }
        
        // Gráfico de pasajeros
        const ctx = document.getElementById('passengers-chart').getContext('2d');
        const passengersChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($pasajerosDias) !!},
                datasets: [{
                    label: 'Pasajeros por día',
                    data: {!! json_encode($pasajerosCantidad) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) {
                                    return value / 1000 + 'k';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
        
        // FAQ accordions
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const item = this.closest('.faq-item');
                const wasActive = item.classList.contains('active');
                
                // Cerrar todos los items
                document.querySelectorAll('.faq-item').forEach(faq => {
                    faq.classList.remove('active');
                });
                
                // Si no estaba abierto, abrirlo
                if (!wasActive) {
                    item.classList.add('active');
                }
            });
        });
        
        // Smooth scroll para navegación interna
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>
@endsection