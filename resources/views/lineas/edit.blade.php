@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar Línea: {{ $linea->nombre }}</h3>
                <div>
                    <a href="{{ route('lineas.show', $linea->id_linea) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="{{ route('lineas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('lineas.update', $linea->id_linea) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre de la Línea *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $linea->nombre) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="color" class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', $linea->color) }}">
                            <input type="text" class="form-control" id="color_text" name="color_text" value="{{ old('color_text', $linea->color) }}" placeholder="Nombre del color">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="hora_inicio" class="form-label">Hora de Inicio *</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', $linea->hora_inicio) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="hora_fin" class="form-label">Hora de Fin *</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="{{ old('hora_fin', $linea->hora_fin) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="frecuencia_min" class="form-label">Frecuencia (min) *</label>
                        <input type="number" class="form-control" id="frecuencia_min" name="frecuencia_min" value="{{ old('frecuencia_min', $linea->frecuencia_min) }}" required min="1">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $linea->descripcion) }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Activa" {{ old('estado', $linea->estado) == 'Activa' ? 'selected' : '' }}>Activa</option>
                            <option value="Suspendida" {{ old('estado', $linea->estado) == 'Suspendida' ? 'selected' : '' }}>Suspendida</option>
                            <option value="En mantenimiento" {{ old('estado', $linea->estado) == 'En mantenimiento' ? 'selected' : '' }}>En mantenimiento</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="{{ route('lineas.show', $linea->id_linea) }}" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Línea</button>
                </div>
            </form>

            <!-- Sección para gestionar estaciones de la línea -->
            <div class="card mt-5">
                <div class="card-header bg-light">
                    <h4 class="mb-0">Estaciones de la Línea</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Gestione las estaciones asociadas a esta línea, incluyendo el orden y dirección.
                    </div>

                    <!-- Lista de estaciones actuales -->
                    @if(count($estaciones_linea) > 0)
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Estación</th>
                                        <th>Dirección</th>
                                        <th>Km</th>
                                        <th>Tiempo al siguiente</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($estaciones_linea as $el)
                                        <tr>
                                            <td>{{ $el->orden }}</td>
                                            <td>{{ $el->estacion->nombre }}</td>
                                            <td>{{ $el->direccion }}</td>
                                            <td>{{ $el->kilometro_ruta }} km</td>
                                            <td>{{ $el->tiempo_estimado_siguiente ?? '-' }} min</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning editar-estacion" 
                                                    data-id="{{ $el->id_estacion_linea }}"
                                                    data-estacion="{{ $el->id_estacion }}"
                                                    data-orden="{{ $el->orden }}"
                                                    data-direccion="{{ $el->direccion }}"
                                                    data-km="{{ $el->kilometro_ruta }}"
                                                    data-tiempo="{{ $el->tiempo_estimado_siguiente }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('estaciones-lineas.destroy', $el->id_estacion_linea) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro que desea eliminar esta estación de la línea?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay estaciones asociadas a esta línea.</p>
                    @endif

                    <!-- Formulario para agregar estación -->
                    <form action="{{ route('estaciones-lineas.store') }}" method="POST" id="formEstacionLinea">
                        @csrf
                        <input type="hidden" name="id_linea" value="{{ $linea->id_linea }}">
                        <input type="hidden" name="id_estacion_linea" id="id_estacion_linea">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="id_estacion" class="form-label">Estación *</label>
                                <select class="form-select" id="id_estacion" name="id_estacion" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($estaciones as $estacion)
                                        <option value="{{ $estacion->id_estacion }}">{{ $estacion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="orden" class="form-label">Orden *</label>
                                <input type="number" class="form-control" id="orden" name="orden" required min="1">
                            </div>
                            <div class="col-md-2">
                                <label for="direccion" class="form-label">Dirección *</label>
                                <select class="form-select" id="direccion" name="direccion" required>
                                    <option value="Norte-Sur">Norte-Sur</option>
                                    <option value="Sur-Norte">Sur-Norte</option>
                                    <option value="Este-Oeste">Este-Oeste</option>
                                    <option value="Oeste-Este">Oeste-Este</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="kilometro_ruta" class="form-label">Km Ruta *</label>
                                <input type="number" class="form-control" id="kilometro_ruta" name="kilometro_ruta" step="0.01" required min="0">
                            </div>
                            <div class="col-md-2">
                                <label for="tiempo_estimado_siguiente" class="form-label">Tiempo (min)</label>
                                <input type="number" class="form-control" id="tiempo_estimado_siguiente" name="tiempo_estimado_siguiente" min="1">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="btnAccionEstacion">Agregar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Script para sincronizar el valor de color entre el input color y el texto
    document.getElementById('color').addEventListener('change', function() {
        const colorHex = this.value;
        document.getElementById('color_text').value = colorHexToName(colorHex);
    });

    function colorHexToName(hex) {
        // Simple mapping de colores comunes
        const colorMap = {
            '#FF0000': 'Rojo',
            '#00FF00': 'Verde',
            '#0000FF': 'Azul',
            '#FFFF00': 'Amarillo',
            '#FF00FF': 'Magenta',
            '#00FFFF': 'Cyan',
            '#FFA500': 'Naranja',
            '#800080': 'Púrpura',
            '#008000': 'Verde oscuro',
            '#808080': 'Gris',
            '#3366CC': 'Azul'
        };
        
        return colorMap[hex.toUpperCase()] || 'Color personalizado';
    }

    // Gestión de estaciones
    document.querySelectorAll('.editar-estacion').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const estacion = this.getAttribute('data-estacion');
            const orden = this.getAttribute('data-orden');
            const direccion = this.getAttribute('data-direccion');
            const km = this.getAttribute('data-km');
            const tiempo = this.getAttribute('data-tiempo');
            
            document.getElementById('id_estacion_linea').value = id;
            document.getElementById('id_estacion').value = estacion;
            document.getElementById('orden').value = orden;
            document.getElementById('direccion').value = direccion;
            document.getElementById('kilometro_ruta').value = km;
            document.getElementById('tiempo_estimado_siguiente').value = tiempo;
            
            document.getElementById('btnAccionEstacion').textContent = 'Actualizar';
            document.getElementById('formEstacionLinea').action = "{{ url('estaciones-lineas') }}/" + id;
            document.getElementById('formEstacionLinea').insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');
        });
    });

    // Resetear formulario para agregar nueva estación
    document.getElementById('btnNuevaEstacion').addEventListener('click', function() {
        document.getElementById('id_estacion_linea').value = '';
        document.getElementById('formEstacionLinea').reset();
        document.getElementById('btnAccionEstacion').textContent = 'Agregar';
        document.getElementById('formEstacionLinea').action = "{{ route('estaciones-lineas.store') }}";
        
        // Eliminar el método PUT si existe
        const methodInput = document.querySelector('#formEstacionLinea input[name="_method"]');
        if (methodInput) methodInput.remove();
    });
</script>
@endsection
@endsection