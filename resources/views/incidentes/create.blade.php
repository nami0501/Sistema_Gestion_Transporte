@extends('layouts.admin')

@section('title', 'Reportar Incidente')

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
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Reportar Incidente</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidentes.index') }}">Incidentes</a></li>
        <li class="breadcrumb-item active">Reportar Incidente</li>
    </ol>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Reportar Nuevo Incidente
        </div>
        <div class="card-body">
            <form action="{{ route('incidentes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <!-- Datos Básicos del Incidente -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Información del Incidente</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="tipo_incidente" class="form-label">Tipo de Incidente *</label>
                                    <select class="form-select @error('tipo_incidente') is-invalid @enderror" id="tipo_incidente" name="tipo_incidente" required>
                                        <option value="">Seleccione un tipo</option>
                                        <option value="Accidente" {{ old('tipo_incidente') == 'Accidente' ? 'selected' : '' }}>Accidente</option>
                                        <option value="Avería" {{ old('tipo_incidente') == 'Avería' ? 'selected' : '' }}>Avería</option>
                                        <option value="Retraso" {{ old('tipo_incidente') == 'Retraso' ? 'selected' : '' }}>Retraso</option>
                                        <option value="Seguridad" {{ old('tipo_incidente') == 'Seguridad' ? 'selected' : '' }}>Seguridad</option>
                                        <option value="Otro" {{ old('tipo_incidente') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('tipo_incidente')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción *</label>
                                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3" required>{{ old('descripcion') }}</textarea>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="fecha_hora" class="form-label">Fecha y Hora *</label>
                                        <input type="datetime-local" class="form-control @error('fecha_hora') is-invalid @enderror" id="fecha_hora" name="fecha_hora" value="{{ old('fecha_hora', now()->format('Y-m-d\TH:i')) }}" required>
                                        @error('fecha_hora')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="impacto" class="form-label">Impacto *</label>
                                        <select class="form-select @error('impacto') is-invalid @enderror" id="impacto" name="impacto" required>
                                            <option value="">Seleccione el impacto</option>
                                            <option value="Bajo" {{ old('impacto') == 'Bajo' ? 'selected' : '' }}>Bajo</option>
                                            <option value="Medio" {{ old('impacto') == 'Medio' ? 'selected' : '' }}>Medio</option>
                                            <option value="Alto" {{ old('impacto') == 'Alto' ? 'selected' : '' }}>Alto</option>
                                            <option value="Crítico" {{ old('impacto') == 'Crítico' ? 'selected' : '' }}>Crítico</option>
                                        </select>
                                        @error('impacto')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="retraso_estimado" class="form-label">Retraso Estimado (minutos)</label>
                                    <input type="number" class="form-control @error('retraso_estimado') is-invalid @enderror" id="retraso_estimado" name="retraso_estimado" value="{{ old('retraso_estimado') }}" min="0">
                                    @error('retraso_estimado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="evidencia" class="form-label">Evidencia (Imagen)</label>
                                    <input type="file" class="form-control @error('evidencia') is-invalid @enderror" id="evidencia" name="evidencia" accept="image/*">
                                    @error('evidencia')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Formatos aceptados: JPG, PNG, GIF. Máximo 5MB.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Ubicación y Asignación -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Ubicación y Asignación</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="id_asignacion" class="form-label">Asignación</label>
                                    <select class="form-select @error('id_asignacion') is-invalid @enderror" id="id_asignacion" name="id_asignacion">
                                        <option value="">Sin asignación específica</option>
                                        @foreach($asignaciones as $asignacion)
                                            <option value="{{ $asignacion->id_asignacion }}" 
                                                {{ old('id_asignacion', request()->get('asignacion_id')) == $asignacion->id_asignacion ? 'selected' : '' }}
                                                data-linea="{{ $asignacion->linea->id_linea }}"
                                                data-conductor="{{ $asignacion->usuario->nombre }} {{ $asignacion->usuario->apellidos }}"
                                                data-vehiculo="{{ $asignacion->vehiculo->placa }}"
                                                data-turno="{{ \Carbon\Carbon::parse($asignacion->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($asignacion->hora_fin)->format('H:i') }}">
                                                #{{ $asignacion->id_asignacion }} - {{ $asignacion->linea->nombre }} - {{ $asignacion->vehiculo->placa }} - {{ \Carbon\Carbon::parse($asignacion->fecha)->format('d/m/Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_asignacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="asignacion-details" class="alert alert-info mb-3 {{ old('id_asignacion', request()->get('asignacion_id')) ? '' : 'd-none' }}">
                                    <h6 class="alert-heading">Detalles de la Asignación</h6>
                                    <p class="mb-1"><strong>Conductor:</strong> <span id="conductor-info">-</span></p>
                                    <p class="mb-1"><strong>Vehículo:</strong> <span id="vehiculo-info">-</span></p>
                                    <p class="mb-1"><strong>Turno:</strong> <span id="turno-info">-</span></p>
                                </div>

                                <div class="mb-3">
                                    <label for="id_estacion" class="form-label">Estación</label>
                                    <select class="form-select @error('id_estacion') is-invalid @enderror" id="id_estacion" name="id_estacion">
                                        <option value="">Sin estación específica</option>
                                        @foreach($estaciones as $estacion)
                                            <option value="{{ $estacion->id_estacion }}" 
                                                {{ old('id_estacion') == $estacion->id_estacion ? 'selected' : '' }}
                                                data-lat="{{ $estacion->latitud }}"
                                                data-lng="{{ $estacion->longitud }}">
                                                {{ $estacion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_estacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="usar_ubicacion_actual" name="usar_ubicacion_actual" {{ old('usar_ubicacion_actual') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="usar_ubicacion_actual">
                                        Usar mi ubicación actual
                                    </label>
                                </div>

                                <div id="map-container" class="mb-3">
                                    <div id="map" class="map-container"></div>
                                    <input type="hidden" id="latitud" name="latitud" value="{{ old('latitud') }}">
                                    <input type="hidden" id="longitud" name="longitud" value="{{ old('longitud') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Estado Inicial</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                        <option value="Reportado" {{ old('estado') == 'Reportado' ? 'selected' : '' }}>Reportado</option>
                                        <option value="En atención" {{ old('estado') == 'En atención' ? 'selected' : '' }}>En atención</option>
                                        <option value="Resuelto" {{ old('estado') == 'Resuelto' ? 'selected' : '' }}>Resuelto</option>
                                        <option value="Escalado" {{ old('estado') == 'Escalado' ? 'selected' : '' }}>Escalado</option>
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="div-resolucion" class="mb-3 {{ old('estado') == 'Resuelto' ? '' : 'd-none' }}">
                                    <label for="resolucion" class="form-label">Resolución</label>
                                    <textarea class="form-control @error('resolucion') is-invalid @enderror" id="resolucion" name="resolucion" rows="3">{{ old('resolucion') }}</textarea>
                                    @error('resolucion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('incidentes.index') }}" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-times-circle me-1"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Reportar Incidente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS para el mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
    // Inicializar mapa
    const map = L.map('map').setView([-12.046374, -77.042793], 13); // Coordenadas de Lima como ejemplo
    let marker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Función para agregar o mover marcador
    function setMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            
            // Actualizar coordenadas cuando se arrastra el marcador
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                document.getElementById('latitud').value = position.lat;
                document.getElementById('longitud').value = position.lng;
            });
        }
        
        // Actualizar campos ocultos
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;
        
        // Centrar mapa
        map.setView([lat, lng], 15);
    }

    // Obtener coordenadas al hacer clic en el mapa
    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });

    // Obtener ubicación actual
    document.getElementById('usar_ubicacion_actual').addEventListener('change', function() {
        if (this.checked) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        setMarker(lat, lng);
                    },
                    function(error) {
                        console.error('Error obteniendo ubicación: ', error);
                        alert('No se pudo obtener su ubicación actual. Por favor, haga clic en el mapa para indicar la ubicación del incidente.');
                        this.checked = false;
                    }
                );
            } else {
                alert('Su navegador no soporta geolocalización. Por favor, haga clic en el mapa para indicar la ubicación del incidente.');
                this.checked = false;
            }
        }
    });

    // Obtener ubicación de estación seleccionada
    document.getElementById('id_estacion').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            const lat = option.getAttribute('data-lat');
            const lng = option.getAttribute('data-lng');
            if (lat && lng) {
                setMarker(parseFloat(lat), parseFloat(lng));
                
                // Desmarcar opción de ubicación actual
                document.getElementById('usar_ubicacion_actual').checked = false;
            }
        }
    });

    // Mostrar detalles de la asignación seleccionada
    document.getElementById('id_asignacion').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const detailsContainer = document.getElementById('asignacion-details');
        
        if (option && option.value) {
            // Actualizar información
            document.getElementById('conductor-info').textContent = option.getAttribute('data-conductor');
            document.getElementById('vehiculo-info').textContent = option.getAttribute('data-vehiculo');
            document.getElementById('turno-info').textContent = option.getAttribute('data-turno');
            
            // Mostrar contenedor
            detailsContainer.classList.remove('d-none');
            
            // Actualizar selector de estaciones para mostrar solo las de la línea seleccionada
            const idLinea = option.getAttribute('data-linea');
            if (idLinea) {
                filterEstacionesByLinea(idLinea);
            }
        } else {
            // Ocultar contenedor
            detailsContainer.classList.add('d-none');
            
            // Restaurar todas las estaciones
            resetEstacionesFilter();
        }
    });

    // Filtrar estaciones por línea
    function filterEstacionesByLinea(idLinea) {
        const selectEstacion = document.getElementById('id_estacion');
        const options = selectEstacion.options;
        
        // Guardar valor seleccionado actual
        const currentValue = selectEstacion.value;
        let currentValueExists = false;
        
        // Ocultar estaciones que no pertenecen a la línea
        for (let i = 0; i < options.length; i++) {
            if (i === 0) continue; // Mantener la opción "Sin estación específica"
            
            const option = options[i];
            const lineaIds = option.getAttribute('data-lineas') || '';
            const belongsToLine = lineaIds.split(',').includes(idLinea);
            
            if (belongsToLine) {
                option.style.display = '';
                if (option.value === currentValue) {
                    currentValueExists = true;
                }
            } else {
                option.style.display = 'none';
                if (option.value === currentValue) {
                    currentValueExists = false;
                }
            }
        }
        
        // Si el valor actual ya no existe en las opciones filtradas, resetear
        if (!currentValueExists) {
            selectEstacion.value = '';
        }
    }

    // Restaurar filtro de estaciones
    function resetEstacionesFilter() {
        const selectEstacion = document.getElementById('id_estacion');
        const options = selectEstacion.options;
        
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = '';
        }
    }

    // Mostrar/ocultar campo de resolución según el estado
    document.getElementById('estado').addEventListener('change', function() {
        const divResolucion = document.getElementById('div-resolucion');
        if (this.value === 'Resuelto') {
            divResolucion.classList.remove('d-none');
        } else {
            divResolucion.classList.add('d-none');
        }
    });

    // Inicializar la página con los valores actuales
    window.addEventListener('load', function() {
        // Activar cambios en selección de asignación si hay una preseleccionada
        const asignacionSelect = document.getElementById('id_asignacion');
        if (asignacionSelect.value) {
            asignacionSelect.dispatchEvent(new Event('change'));
        }
        
        // Activar cambios en selección de estación si hay una preseleccionada
        const estacionSelect = document.getElementById('id_estacion');
        if (estacionSelect.value) {
            estacionSelect.dispatchEvent(new Event('change'));
        }
        
        // Activar cambios en estado si hay uno preseleccionado
        const estadoSelect = document.getElementById('estado');
        if (estadoSelect.value) {
            estadoSelect.dispatchEvent(new Event('change'));
        }
        
        // Si hay coordenadas predefinidas, mostrar marcador
        const latInput = document.getElementById('latitud');
        const lngInput = document.getElementById('longitud');
        if (latInput.value && lngInput.value) {
            setMarker(parseFloat(latInput.value), parseFloat(lngInput.value));
        }
    });
</script>
@endsection