@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Crear Nueva Línea</h3>
                <a href="{{ route('lineas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
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

            <form action="{{ route('lineas.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre de la Línea *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="color" class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', '#3366CC') }}">
                            <input type="text" class="form-control" id="color_text" name="color_text" value="{{ old('color_text', 'Azul') }}" placeholder="Nombre del color">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="hora_inicio" class="form-label">Hora de Inicio *</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="hora_fin" class="form-label">Hora de Fin *</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="{{ old('hora_fin') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="frecuencia_min" class="form-label">Frecuencia (min) *</label>
                        <input type="number" class="form-control" id="frecuencia_min" name="frecuencia_min" value="{{ old('frecuencia_min') }}" required min="1">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Activa" {{ old('estado') == 'Activa' ? 'selected' : '' }}>Activa</option>
                            <option value="Suspendida" {{ old('estado') == 'Suspendida' ? 'selected' : '' }}>Suspendida</option>
                            <option value="En mantenimiento" {{ old('estado') == 'En mantenimiento' ? 'selected' : '' }}>En mantenimiento</option>
                        </select>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Estaciones</h4>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Las estaciones de la línea se podrán configurar después de crear la línea.
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="reset" class="btn btn-outline-secondary me-md-2">Limpiar</button>
                    <button type="submit" class="btn btn-primary">Guardar Línea</button>
                </div>
            </form>
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
        // Simple mapping de colores comunes - se podría expandir o usar una librería
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
            // Se pueden agregar más colores según se necesite
        };
        
        return colorMap[hex.toUpperCase()] || 'Color personalizado';
    }
</script>
@endsection
@endsection