@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Crear Nuevo Vehículo</h3>
                <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
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

            <form action="{{ route('vehiculos.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="placa" class="form-label">Placa *</label>
                        <input type="text" class="form-control" id="placa" name="placa" value="{{ old('placa') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="Bus" {{ old('tipo') == 'Bus' ? 'selected' : '' }}>Bus</option>
                            <option value="Articulado" {{ old('tipo') == 'Articulado' ? 'selected' : '' }}>Articulado</option>
                            <option value="Biarticulado" {{ old('tipo') == 'Biarticulado' ? 'selected' : '' }}>Biarticulado</option>
                            <option value="Tren" {{ old('tipo') == 'Tren' ? 'selected' : '' }}>Tren</option>
                            <option value="Vagón" {{ old('tipo') == 'Vagón' ? 'selected' : '' }}>Vagón</option>
                            <option value="Minibus" {{ old('tipo') == 'Minibus' ? 'selected' : '' }}>Minibus</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="marca" class="form-label">Marca *</label>
                        <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" value="{{ old('modelo') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="año_fabricacion" class="form-label">Año de Fabricación *</label>
                        <input type="number" class="form-control" id="año_fabricacion" name="año_fabricacion" value="{{ old('año_fabricacion') }}" required min="1900" max="{{ date('Y') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="capacidad_pasajeros" class="form-label">Capacidad de Pasajeros *</label>
                        <input type="number" class="form-control" id="capacidad_pasajeros" name="capacidad_pasajeros" value="{{ old('capacidad_pasajeros') }}" required min="1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición *</label>
                        <input type="date" class="form-control" id="fecha_adquisicion" name="fecha_adquisicion" value="{{ old('fecha_adquisicion') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="kilometraje" class="form-label">Kilometraje Inicial</label>
                        <input type="number" class="form-control" id="kilometraje" name="kilometraje" value="{{ old('kilometraje', 0) }}" min="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Activo" {{ old('estado') == 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="En mantenimiento" {{ old('estado') == 'En mantenimiento' ? 'selected' : '' }}>En mantenimiento</option>
                            <option value="En reparación" {{ old('estado') == 'En reparación' ? 'selected' : '' }}>En reparación</option>
                            <option value="Fuera de servicio" {{ old('estado') == 'Fuera de servicio' ? 'selected' : '' }}>Fuera de servicio</option>
                            <option value="Dado de baja" {{ old('estado') == 'Dado de baja' ? 'selected' : '' }}>Dado de baja</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_linea" class="form-label">Línea Asignada</label>
                        <select class="form-select" id="id_linea" name="id_linea">
                            <option value="">Sin asignación</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}" {{ old('id_linea') == $linea->id_linea ? 'selected' : '' }}>
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="reset" class="btn btn-outline-secondary me-md-2">Limpiar</button>
                    <button type="submit" class="btn btn-primary">Guardar Vehículo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection