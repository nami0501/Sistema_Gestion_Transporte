@extends('layouts.app')

@section('title', 'Monitoreo GPS en Tiempo Real')

@section('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        #mapa-container {
            height: calc(100vh - 200px);
            min-height: 500px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-monitoreo {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .vehiculo-item {
            border-left: 4px solid #ccc;
            margin-bottom: 8px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .vehiculo-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        
        .vehiculo-item.seleccionado {
            background-color: #e9ecef;
            border-left-color: #007bff;
            font-weight: bold;
        }
        
        .indicador-estado {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .estado-en-movimiento {
            background-color: #28a745; /* Verde */
        }
        
        .estado-detenido {
            background-color: #ffc107; /* Amarillo */
        }
        
        .estado-en-estacion {
            background-color: #17a2b8; /* Cyan */
        }
        
        .estado-fuera-de-ruta {
            background-color: #dc3545; /* Rojo */
        }
        
        .detalles-vehiculo {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        
        .detalles-vehiculo h5 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .info-panel {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 300px;
        }
        
        .control-panel {
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">
        <i class="bi bi-map"></i> Monitoreo GPS en Tiempo Real
        <span id="ultima-actualizacion" class="fs-6 ms-2 text-muted"></span>
    </h2>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card control-panel">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro-linea">Filtrar por Línea:</label>
                                <select id="filtro-linea" class="form-select">
                                    <option value="">Todas las Líneas</option>
                                    @foreach($lineas as $linea)
                                        <option value="{{ $linea->id_linea }}">{{ $linea->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro-estado">Filtrar por Estado:</label>
                                <select id="filtro-estado" class="form-select">
                                    <option value="">Todos los Estados</option>
                                    <option value="En movimiento">En Movimiento</option>
                                    <option value="Detenido">Detenidos</option>
                                    <option value="En estación">En Estación</option>
                                    <option value="Fuera de ruta">Fuera de Ruta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="buscar-vehiculo">Buscar Vehículo:</label>
                                <input type="text" id="buscar-vehiculo" class="form-control" placeholder="Ingrese placa o ID">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch me-3">
                                <input class="form-check-input" type="checkbox" id="mostrar-estaciones" checked>
                                <label class="form-check-label" for="mostrar-estaciones">Mostrar Estaciones</label>
                            </div>
                            <button id="btn-refrescar" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise"></i> Refrescar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-9">
            <!-- Mapa principal -->
            <div id="mapa-container"></div>
        </div>
        <div class="col-md-3">
            <div class="sidebar-monitoreo">
                <!-- Contador de vehículos activos -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Vehículos Activos</h5>
                        <div class="row text-center">
                            <div class="col">
                                <h2 id="contador-vehiculos">0</h2>
                                <p>Total</p>
                            </div>
                            <div class="col">
                                <h2 id="contador-en-movimiento">0</h2>
                                <p>En Movimiento</p>
                            </div>
                            <div class="col">
                                <h2 id="contador-detenidos">0</h2>
                                <p>Detenidos</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de vehículos activos -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-list-ul"></i> Vehículos Monitoreados
                    </div>
                    <div class="card-body p-0">
                        <ul id="lista-vehiculos" class="list-group list-group-flush">
                            <li class="list-group-item text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando vehículos...</p>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Detalles del vehículo seleccionado -->
                <div id="detalles-vehiculo" class="card d-none">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle"></i> Detalles del Vehículo
                    </div>
                    <div class="card-body" id="contenido-detalles">
                        <!-- El contenido se cargará dinámicamente -->
                    </div>
                    <div class="card-footer">
                        <button id="btn-ver-ruta" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-geo-alt"></i> Ver Ruta Completa
                        </button>
                        <button id="btn-centrar-vehiculo" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-bullseye"></i> Centrar en Mapa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar la ruta completa -->
<div class="modal fade" id="modal-ruta" tabindex="-1" aria-labelledby="modal-ruta-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-ruta-label">Ruta del Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="mapa-ruta" style="height: 500px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Nuestro JavaScript para el mapa -->
    <script src="{{ asset('js/mapa.js') }}"></script>
    
    <script>
        // Inicializar intervalos y referencias globales
        let mapaMonitoreo;
        let intervaloActualizacion;
        let vehiculoSeleccionadoId = null;
        let marcadoresVehiculos = {};
        let marcadoresEstaciones = {};
        let capasEstaciones = {};
        let datosVehiculos = {};
        let filtroLineaActual = '';
        let filtroEstadoActual = '';
        let busquedaVehiculoActual = '';
        
        // Constantes para iconos de marcadores
        const ICON_SIZE = [32, 32];
        const ICON_ANCHOR = [16, 16];
        
        // Cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el mapa principal
            inicializarMapa();
            
            // Cargar vehículos la primera vez
            cargarVehiculosActivos();
            
            // Configurar actualización automática cada 30 segundos
            intervaloActualizacion = setInterval(cargarVehiculosActivos, 30000);
            
            // Event listeners para controles
            document.getElementById('filtro-linea').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-estado').addEventListener('change', aplicarFiltros);
            document.getElementById('buscar-vehiculo').addEventListener('input', aplicarFiltros);
            document.getElementById('mostrar-estaciones').addEventListener('change', toggleEstaciones);
            document.getElementById('btn-refrescar').addEventListener('click', cargarVehiculosActivos);
            document.getElementById('btn-ver-ruta').addEventListener('click', mostrarRutaCompleta);
            document.getElementById('btn-centrar-vehiculo').addEventListener('click', centrarEnVehiculoSeleccionado);
        });
        
        /**
         * Inicializa el mapa Leaflet
         */
        function inicializarMapa() {
            // Coordenadas iniciales (centrado en una ubicación predeterminada)
            const latInicial = -12.046374; // Coordenadas para Lima, Perú
            const lngInicial = -77.042793;
            
            // Crear el mapa
            mapaMonitoreo = L.map('mapa-container').setView([latInicial, lngInicial], 13);
            
            // Añadir capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mapaMonitoreo);
            
            // Añadir escala
            L.control.scale({imperial: false}).addTo(mapaMonitoreo);
        }
        
        /**
         * Carga los vehículos activos desde la API
         */
        function cargarVehiculosActivos() {
            // Construir URL con filtros
            let url = '/api/vehiculos-activos';
            let params = [];
            
            if (filtroLineaActual) {
                params.push(`linea_id=${filtroLineaActual}`);
            }
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            // Mostrar indicador de carga
            document.getElementById('lista-vehiculos').innerHTML = `
                <li class="list-group-item text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando vehículos...</p>
                </li>
            `;
            
            // Realizar la petición AJAX
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar vehículos activos');
                    }
                    return response.json();
                })
                .then(data => {
                    // Guardar datos de vehículos
                    datosVehiculos = {};
                    data.vehiculos.forEach(vehiculo => {
                        datosVehiculos[vehiculo.id_vehiculo] = vehiculo;
                    });
                    
                    // Actualizar marcadores en el mapa
                    actualizarMarcadoresVehiculos(data.vehiculos);
                    
                    // Actualizar lista de vehículos
                    actualizarListaVehiculos(data.vehiculos);
                    
                    // Actualizar contadores
                    actualizarContadores(data.vehiculos);
                    
                    // Actualizar timestamp de última actualización
                    document.getElementById('ultima-actualizacion').textContent = 
                        `Última actualización: ${new Date(data.timestamp).toLocaleTimeString()}`;
                    
                    // Si hay un vehículo seleccionado, actualizar sus detalles
                    if (vehiculoSeleccionadoId && datosVehiculos[vehiculoSeleccionadoId]) {
                        cargarDetallesVehiculo(vehiculoSeleccionadoId);
                    }
                    
                    // Cargar estaciones si hay un filtro de línea activo
                    if (filtroLineaActual && document.getElementById('mostrar-estaciones').checked) {
                        cargarEstacionesLinea(filtroLineaActual);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('lista-vehiculos').innerHTML = `
                        <li class="list-group-item text-center text-danger py-5">
                            <i class="bi bi-exclamation-triangle"></i>
                            Error al cargar vehículos: ${error.message}
                        </li>
                    `;
                });
        }
        
        /**
         * Actualiza los marcadores de vehículos en el mapa
         */
        function actualizarMarcadoresVehiculos(vehiculos) {
            // Crear set de IDs actuales para detectar vehículos que ya no están activos
            const idsActuales = new Set(vehiculos.map(v => v.id_vehiculo));
            
            // Eliminar marcadores de vehículos que ya no están activos
            for (const id in marcadoresVehiculos) {
                if (!idsActuales.has(parseInt(id))) {
                    mapaMonitoreo.removeLayer(marcadoresVehiculos[id]);
                    delete marcadoresVehiculos[id];
                }
            }
            
            // Actualizar o crear marcadores para vehículos activos
            vehiculos.forEach(vehiculo => {
                // Aplicar filtro de estado si está activo
                if (filtroEstadoActual && vehiculo.estado !== filtroEstadoActual) {
                    // Si el vehículo tiene un marcador pero no cumple con el filtro, eliminarlo
                    if (marcadoresVehiculos[vehiculo.id_vehiculo]) {
                        mapaMonitoreo.removeLayer(marcadoresVehiculos[vehiculo.id_vehiculo]);
                        delete marcadoresVehiculos[vehiculo.id_vehiculo];
                    }
                    return;
                }
                
                // Aplicar filtro de búsqueda
                if (busquedaVehiculoActual) {
                    const terminoBusqueda = busquedaVehiculoActual.toLowerCase();
                    const placa = vehiculo.placa.toLowerCase();
                    const idVehiculo = vehiculo.id_vehiculo.toString();
                    
                    if (!placa.includes(terminoBusqueda) && !idVehiculo.includes(terminoBusqueda)) {
                        // Si el vehículo tiene un marcador pero no cumple con la búsqueda, eliminarlo
                        if (marcadoresVehiculos[vehiculo.id_vehiculo]) {
                            mapaMonitoreo.removeLayer(marcadoresVehiculos[vehiculo.id_vehiculo]);
                            delete marcadoresVehiculos[vehiculo.id_vehiculo];
                        }
                        return;
                    }
                }
                
                // Determinar color e icono según estado y tipo de vehículo
                let iconoUrl = '';
                let colorLinea = vehiculo.color || '#3388ff';
                
                switch (vehiculo.tipo) {
                    case 'Bus':
                        iconoUrl = '/img/iconos/bus.png';
                        break;
                    case 'Articulado':
                        iconoUrl = '/img/iconos/bus-articulado.png';
                        break;
                    case 'Minibus':
                        iconoUrl = '/img/iconos/minibus.png';
                        break;
                    default:
                        iconoUrl = '/img/iconos/vehiculo.png';
                }
                
                // Crear o actualizar marcador
                if (marcadoresVehiculos[vehiculo.id_vehiculo]) {
                    // Actualizar posición del marcador existente
                    marcadoresVehiculos[vehiculo.id_vehiculo].setLatLng([vehiculo.latitud, vehiculo.longitud]);
                    
                    // Actualizar popup
                    const popupContent = crearContenidoPopup(vehiculo);
                    marcadoresVehiculos[vehiculo.id_vehiculo].getPopup().setContent(popupContent);
                } else {
                    // Crear nuevo icono personalizado
                    const icono = L.icon({
                        iconUrl: iconoUrl,
                        iconSize: ICON_SIZE,
                        iconAnchor: ICON_ANCHOR,
                        popupAnchor: [0, -ICON_ANCHOR[1]],
                        className: `vehiculo-icono estado-${vehiculo.estado.toLowerCase().replace(' ', '-')}`
                    });
                    
                    // Crear popup con información del vehículo
                    const popupContent = crearContenidoPopup(vehiculo);
                    
                    // Crear nuevo marcador
                    const marcador = L.marker([vehiculo.latitud, vehiculo.longitud], {
                        icon: icono,
                        rotationAngle: vehiculo.direccion || 0,
                        title: `${vehiculo.placa} - ${vehiculo.tipo}`
                    }).addTo(mapaMonitoreo);
                    
                    // Añadir popup
                    marcador.bindPopup(popupContent);
                    
                    // Añadir evento de clic
                    marcador.on('click', function() {
                        seleccionarVehiculo(vehiculo.id_vehiculo);
                    });
                    
                    // Guardar referencia
                    marcadoresVehiculos[vehiculo.id_vehiculo] = marcador;
                }
            });
        }
        
        /**
         * Crea el contenido HTML para el popup de un vehículo
         */
        function crearContenidoPopup(vehiculo) {
            let estadoClase = '';
            switch (vehiculo.estado) {
                case 'En movimiento': estadoClase = 'success'; break;
                case 'Detenido': estadoClase = 'warning'; break;
                case 'En estación': estadoClase = 'info'; break;
                case 'Fuera de ruta': estadoClase = 'danger'; break;
                default: estadoClase = 'secondary';
            }
            
            return `
                <div class="popup-vehiculo">
                    <h6><strong>${vehiculo.placa}</strong> - ${vehiculo.tipo}</h6>
                    <p class="mb-1"><strong>Línea:</strong> ${vehiculo.linea || 'No asignada'}</p>
                    <p class="mb-1"><strong>Conductor:</strong> ${vehiculo.conductor || 'No asignado'}</p>
                    <p class="mb-1"><strong>Velocidad:</strong> ${vehiculo.velocidad} km/h</p>
                    <p class="mb-2">
                        <strong>Estado:</strong> 
                        <span class="badge bg-${estadoClase}">${vehiculo.estado}</span>
                    </p>
                    <p class="mb-0 text-muted small">
                        Última actualización: ${vehiculo.ultima_actualizacion}
                    </p>
                    <div class="mt-2">
                        <button onclick="seleccionarVehiculo(${vehiculo.id_vehiculo})" class="btn btn-sm btn-primary w-100">
                            Ver Detalles
                        </button>
                    </div>
                </div>
            `;
        }
        
        /**
         * Actualiza la lista de vehículos en el sidebar
         */
        function actualizarListaVehiculos(vehiculos) {
            const listaVehiculos = document.getElementById('lista-vehiculos');
            
            // Si no hay vehículos para mostrar
            if (vehiculos.length === 0) {
                listaVehiculos.innerHTML = `
                    <li class="list-group-item text-center py-4">
                        <i class="bi bi-info-circle"></i> No hay vehículos activos para mostrar
                    </li>
                `;
                return;
            }
            
            // Filtrar vehículos según criterios actuales
            let vehiculosFiltrados = vehiculos;
            
            if (filtroEstadoActual) {
                vehiculosFiltrados = vehiculosFiltrados.filter(v => v.estado === filtroEstadoActual);
            }
            
            if (busquedaVehiculoActual) {
                const terminoBusqueda = busquedaVehiculoActual.toLowerCase();
                vehiculosFiltrados = vehiculosFiltrados.filter(v => 
                    v.placa.toLowerCase().includes(terminoBusqueda) || 
                    v.id_vehiculo.toString().includes(terminoBusqueda)
                );
            }
            
            // Si después de filtrar no hay vehículos
            if (vehiculosFiltrados.length === 0) {
                listaVehiculos.innerHTML = `
                    <li class="list-group-item text-center py-4">
                        <i class="bi bi-filter"></i> No hay vehículos que coincidan con los filtros
                    </li>
                `;
                return;
            }
            
            // Ordenar por placa
            vehiculosFiltrados.sort((a, b) => a.placa.localeCompare(b.placa));
            
            // Generar HTML para cada vehículo
            let html = '';
            
            vehiculosFiltrados.forEach(vehiculo => {
                let estadoClass = '';
                switch (vehiculo.estado) {
                    case 'En movimiento': estadoClass = 'estado-en-movimiento'; break;
                    case 'Detenido': estadoClass = 'estado-detenido'; break;
                    case 'En estación': estadoClass = 'estado-en-estacion'; break;
                    case 'Fuera de ruta': estadoClass = 'estado-fuera-de-ruta'; break;
                }
                
                // Clase para vehículo seleccionado
                const seleccionadoClass = vehiculo.id_vehiculo === vehiculoSeleccionadoId ? 'seleccionado' : '';
                
                html += `
                    <li class="list-group-item vehiculo-item ${seleccionadoClass}" 
                        onclick="seleccionarVehiculo(${vehiculo.id_vehiculo})" 
                        data-id="${vehiculo.id_vehiculo}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="indicador-estado ${estadoClass}"></span>
                                <strong>${vehiculo.placa}</strong> - ${vehiculo.tipo}
                            </div>
                            <span class="badge bg-light text-dark">${vehiculo.velocidad} km/h</span>
                        </div>
                        <div class="mt-1 small text-muted">
                            <div>${vehiculo.linea || 'Sin línea asignada'}</div>
                            <div>${vehiculo.conductor || 'Sin conductor'}</div>
                        </div>
                    </li>
                `;
            });
            
            listaVehiculos.innerHTML = html;
        }
        
        /**
         * Actualiza los contadores de vehículos
         */
        function actualizarContadores(vehiculos) {
            // Contador total
            document.getElementById('contador-vehiculos').textContent = vehiculos.length;
            
            // Contadores por estado
            const enMovimiento = vehiculos.filter(v => v.estado === 'En movimiento').length;
            const detenidos = vehiculos.filter(v => v.estado === 'Detenido' || v.estado === 'En estación').length;
            
            document.getElementById('contador-en-movimiento').textContent = enMovimiento;
            document.getElementById('contador-detenidos').textContent = detenidos;
        }
        
        /**
         * Selecciona un vehículo y muestra sus detalles
         */
        window.seleccionarVehiculo = function(vehiculoId) {
            // Actualizar vehículo seleccionado
            vehiculoSeleccionadoId = vehiculoId;
            
            // Actualizar clase seleccionado en la lista
            const items = document.querySelectorAll('.vehiculo-item');
            items.forEach(item => {
                if (parseInt(item.dataset.id) === vehiculoId) {
                    item.classList.add('seleccionado');
                } else {
                    item.classList.remove('seleccionado');
                }
            });
            
            // Cargar detalles del vehículo
            cargarDetallesVehiculo(vehiculoId);
            
            // Si hay un marcador para este vehículo, centrarlo en el mapa
            if (marcadoresVehiculos[vehiculoId]) {
                mapaMonitoreo.setView(marcadoresVehiculos[vehiculoId].getLatLng(), 15);
                marcadoresVehiculos[vehiculoId].openPopup();
            }
        };
        
        /**
         * Carga los detalles de un vehículo desde la API
         */
        function cargarDetallesVehiculo(vehiculoId) {
            const detallesVehiculo = document.getElementById('detalles-vehiculo');
            const contenidoDetalles = document.getElementById('contenido-detalles');
            
            // Mostrar el panel de detalles con indicador de carga
            detallesVehiculo.classList.remove('d-none');
            contenidoDetalles.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>
            `;
            
            // Realizar la petición AJAX
            fetch(`/api/vehiculos/${vehiculoId}/detalles`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar detalles del vehículo');
                    }
                    return response.json();
                })
                .then(data => {
                    // Actualizar contenido del panel de detalles
                    contenidoDetalles.innerHTML = crearContenidoDetalles(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    contenidoDetalles.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error al cargar detalles: ${error.message}
                        </div>
                    `;
                });
        }
        
        /**
         * Crea el contenido HTML para el panel de detalles
         */
        function crearContenidoDetalles(data) {
            let estadoClase = '';
            switch (data.ubicacion.estado) {
                case 'En movimiento': estadoClase = 'success'; break;
                case 'Detenido': estadoClase = 'warning'; break;
                case 'En estación': estadoClase = 'info'; break;
                case 'Fuera de ruta': estadoClase = 'danger'; break;
                default: estadoClase = 'secondary';
            }
            
            let html = `
                <h5 class="card-title">${data.vehiculo.placa} - ${data.vehiculo.tipo}</h5>
                
                <div class="mb-3">
                    <div class="mb-2"><strong>Marca/Modelo:</strong> ${data.vehiculo.marca} ${data.vehiculo.modelo}</div>
                    <div class="mb-2"><strong>Conductor:</strong> ${data.conductor.nombre}</div>
                    <div class="mb-2"><strong>Teléfono:</strong> ${data.conductor.telefono}</div>
                </div>
                
                <div class="mb-3">
                    <div class="mb-2"><strong>Línea:</strong> ${data.asignacion.linea}</div>
                    <div class="mb-2"><strong>Turno:</strong> ${data.asignacion.turno}</div>
                    <div class="mb-2"><strong>Horario:</strong> ${data.asignacion.hora_inicio} - ${data.asignacion.hora_fin}</div>
                    <div class="mb-2"><strong>Tiempo de operación:</strong> ${data.asignacion.tiempo_transcurrido} min</div>
                </div>
                
                <div class="mb-3">
                    <div class="mb-2">
                        <strong>Estado:</strong> 
                        <span class="badge bg-${estadoClase}">${data.ubicacion.estado}</span>
                    </div>
                    <div class="mb-2"><strong>Velocidad:</strong> ${data.ubicacion.velocidad} km/h</div>
                    <div class="mb-2"><strong>Vuelta actual:</strong> ${data.ubicacion.vuelta_actual}</div>
                    <div class="mb-2"><strong>Kilometraje:</strong> ${data.ubicacion.kilometraje} km</div>
                </div>
            `;
            
            // Agregar información de estación cercana si existe
            if (data.ubicacion.estacion_cercana) {
                html += `
                    <div class="mb-3">
                        <div class="mb-2"><strong>Estación cercana:</strong> ${data.ubicacion.estacion_cercana}</div>
                        <div class="mb-2"><strong>Distancia:</strong> ${data.ubicacion.distancia_estacion} metros</div>
                    </div>
                `;
            }
            
            // Agregar historial de estaciones si hay datos
            if (data.historial_estaciones && data.historial_estaciones.length > 0) {
                html += `
                    <div class="mb-3">
                        <h6>Últimas estaciones visitadas:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Estación</th>
                                        <th>Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                data.historial_estaciones.forEach(paso => {
                    const hora = new Date(paso.hora_real).toLocaleTimeString();
                    html += `
                        <tr>
                            <td>${paso.nombre_estacion}</td>
                            <td>${hora}</td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
            
            return html;
        }
        
        /**
         * Carga las estaciones de una línea desde la API
         */
        function cargarEstacionesLinea(lineaId) {
            // Si ya tenemos las estaciones para esta línea, mostrarlas
            if (capasEstaciones[lineaId]) {
                capasEstaciones[lineaId].addTo(mapaMonitoreo);
                return;
            }
            
            // Realizar la petición AJAX
            fetch(`/api/lineas/${lineaId}/estaciones`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar estaciones de la línea');
                    }
                    return response.json();
                })
                .then(data => {
                    // Crear grupo de capas para esta línea
                    const grupoCapas = L.layerGroup();
                    
                    // Crear marcadores para cada estación
                    data.estaciones.forEach(estacion => {
                        // Icono personalizado para estación
                        const icono = L.icon({
                            iconUrl: estacion.es_terminal ? '/img/iconos/terminal.png' : '/img/iconos/estacion.png',
                            iconSize: [24, 24],
                            iconAnchor: [12, 12],
                            popupAnchor: [0, -12]
                        });
                        
                        // Crear marcador
                        const marcador = L.marker([estacion.lat, estacion.lng], {
                            icon: icono,
                            title: estacion.nombre
                        });
                        
                        // Añadir popup
                        marcador.bindPopup(`
                            <div>
                                <h6>${estacion.nombre}</h6>
                                <p class="mb-1"><strong>Km:</strong> ${estacion.kilometro}</p>
                                <p class="mb-0"><strong>Dirección:</strong> ${estacion.direccion}</p>
                            </div>
                        `);
                        
                        // Añadir al grupo de capas
                        marcador.addTo(grupoCapas);
                        
                        // Guardar referencia
                        marcadoresEstaciones[estacion.id] = marcador;
                    });
                    
                    // Crear línea que une las estaciones
                    const puntos = data.estaciones.map(e => [e.lat, e.lng]);
                    const linea = L.polyline(puntos, {
                        color: data.color || '#3388ff',
                        weight: 4,
                        opacity: 0.7
                    }).addTo(grupoCapas);
                    
                    // Guardar referencia al grupo de capas
                    capasEstaciones[lineaId] = grupoCapas;
                    
                    // Añadir al mapa
                    grupoCapas.addTo(mapaMonitoreo);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        /**
         * Muestra u oculta las estaciones en el mapa
         */
        function toggleEstaciones() {
            const mostrarEstaciones = document.getElementById('mostrar-estaciones').checked;
            
            if (mostrarEstaciones && filtroLineaActual) {
                // Si debemos mostrar estaciones y hay una línea seleccionada, cargarlas
                cargarEstacionesLinea(filtroLineaActual);
            } else {
                // Si no, ocultar todas las capas de estaciones
                for (const lineaId in capasEstaciones) {
                    mapaMonitoreo.removeLayer(capasEstaciones[lineaId]);
                }
            }
        }
        
        /**
         * Aplica los filtros seleccionados
         */
        function aplicarFiltros() {
            // Actualizar filtros actuales
            filtroLineaActual = document.getElementById('filtro-linea').value;
            filtroEstadoActual = document.getElementById('filtro-estado').value;
            busquedaVehiculoActual = document.getElementById('buscar-vehiculo').value.trim();
            
            // Ocultar todas las capas de estaciones
            for (const lineaId in capasEstaciones) {
                mapaMonitoreo.removeLayer(capasEstaciones[lineaId]);
            }
            
            // Si hay una línea seleccionada y se deben mostrar estaciones, cargarlas
            if (filtroLineaActual && document.getElementById('mostrar-estaciones').checked) {
                cargarEstacionesLinea(filtroLineaActual);
            }
            
            // Actualizar marcadores y lista con filtros actuales
            if (Object.keys(datosVehiculos).length > 0) {
                actualizarMarcadoresVehiculos(Object.values(datosVehiculos));
                actualizarListaVehiculos(Object.values(datosVehiculos));
            }
        }
        
        /**
         * Muestra la ruta completa de un vehículo
         */
        function mostrarRutaCompleta() {
            if (!vehiculoSeleccionadoId) {
                return;
            }
            
            // Obtener datos del vehículo
            const vehiculo = datosVehiculos[vehiculoSeleccionadoId];
            
            if (!vehiculo) {
                return;
            }
            
            // Mostrar modal
            const modalRuta = new bootstrap.Modal(document.getElementById('modal-ruta'));
            modalRuta.show();
            
            // Crear mapa de ruta
            const mapaRuta = L.map('mapa-ruta').setView([vehiculo.latitud, vehiculo.longitud], 14);
            
            // Añadir capa base de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mapaRuta);
            
            // Añadir escala
            L.control.scale({imperial: false}).addTo(mapaRuta);
            
            // Mostrar indicador de carga
            const mapContainer = document.getElementById('mapa-ruta');
            mapContainer.innerHTML = `
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.2);">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 mb-0">Cargando ruta...</p>
                </div>
            `;
            
            // Realizar la petición AJAX para obtener la ruta completa
            fetch(`/api/vehiculos/${vehiculoSeleccionadoId}/asignaciones/${vehiculo.id_asignacion}/ruta`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar la ruta del vehículo');
                    }
                    return response.json();
                })
                .then(data => {
                    // Limpiar indicador de carga
                    mapContainer.innerHTML = '';
                    
                    // Crear polyline con todos los puntos de la ruta
                    if (data.puntos && data.puntos.length > 0) {
                        const puntos = data.puntos.map(p => [p.lat, p.lng]);
                        
                        // Crear línea de la ruta
                        const rutaLine = L.polyline(puntos, {
                            color: vehiculo.color || '#3388ff',
                            weight: 3,
                            opacity: 0.7
                        }).addTo(mapaRuta);
                        
                        // Ajustar mapa a los límites de la ruta
                        mapaRuta.fitBounds(rutaLine.getBounds());
                        
                        // Añadir marcador para el vehículo en su posición actual
                        const icono = L.icon({
                            iconUrl: '/img/iconos/vehiculo.png',
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });
                        
                        L.marker([vehiculo.latitud, vehiculo.longitud], {
                            icon: icono,
                            title: `${vehiculo.placa} - Posición actual`
                        }).addTo(mapaRuta);
                        
                        // Añadir marcadores para inicio y fin de la ruta
                        if (puntos.length > 0) {
                            const inicioIcon = L.icon({
                                iconUrl: '/img/iconos/inicio.png',
                                iconSize: [24, 24],
                                iconAnchor: [12, 12]
                            });
                            
                            const finIcon = L.icon({
                                iconUrl: '/img/iconos/fin.png',
                                iconSize: [24, 24],
                                iconAnchor: [12, 12]
                            });
                            
                            L.marker(puntos[0], {
                                icon: inicioIcon,
                                title: 'Inicio de ruta'
                            }).addTo(mapaRuta);
                            
                            L.marker(puntos[puntos.length - 1], {
                                icon: finIcon,
                                title: 'Fin de ruta / Posición actual'
                            }).addTo(mapaRuta);
                        }
                    } else {
                        // Mostrar mensaje si no hay puntos
                        mapContainer.innerHTML = `
                            <div class="alert alert-info m-3">
                                No hay suficientes datos para mostrar la ruta completa.
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mapContainer.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle"></i> Error al cargar la ruta: ${error.message}
                        </div>
                    `;
                });
                
            // Limpiar mapa cuando se cierre el modal
            document.getElementById('modal-ruta').addEventListener('hidden.bs.modal', function () {
                if (mapaRuta) {
                    mapaRuta.remove();
                }
            });
        }
        
        /**
         * Centra el mapa en el vehículo seleccionado
         */
        function centrarEnVehiculoSeleccionado() {
            if (!vehiculoSeleccionadoId || !marcadoresVehiculos[vehiculoSeleccionadoId]) {
                return;
            }
            
            // Centrar el mapa en el vehículo seleccionado
            mapaMonitoreo.setView(marcadoresVehiculos[vehiculoSeleccionadoId].getLatLng(), 16);
        }
        
        // Limpiar intervalo de actualización cuando se abandone la página
        window.addEventListener('beforeunload', function() {
            if (intervaloActualizacion) {
                clearInterval(intervaloActualizacion);
            }
        });
    </script>
@endsection