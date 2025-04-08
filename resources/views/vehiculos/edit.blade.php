@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar Vehículo: {{ $vehiculo->placa }}</h3>
                <div>
                    <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
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

            <form action="{{ route('vehiculos.update', $vehiculo->id_vehiculo) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="placa" class="form-label">Placa *</label>
                        <input type="text" class="form-control" id="placa" name="placa" value="{{ old('placa', $vehiculo->placa) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="Bus" {{ old('tipo', $vehiculo->tipo) == 'Bus' ? 'selected' : '' }}>Bus</option>
                            <option value="Articulado" {{ old('tipo', $vehiculo->tipo) == 'Articulado' ? 'selected' : '' }}>Articulado</option>
                            <option value="Biarticulado" {{ old('tipo', $vehiculo->tipo) == 'Biarticulado' ? 'selected' : '' }}>Biarticulado</option>
                            <option value="Tren" {{ old('tipo', $vehiculo->tipo) == 'Tren' ? 'selected' : '' }}>Tren</option>
                            <option value="Vagón" {{ old('tipo', $vehiculo->tipo) == 'Vagón' ? 'selected' : '' }}>Vagón</option>
                            <option value="Minibus" {{ old('tipo', $vehiculo->tipo) == 'Minibus' ? 'selected' : '' }}>Minibus</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="marca" class="form-label">Marca *</label>
                        <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca', $vehiculo->marca) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" value="{{ old('modelo', $vehiculo->modelo) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="año_fabricacion" class="form-label">Año de Fabricación *</label>
                        <input type="number" class="form-control" id="año_fabricacion" name="año_fabricacion" value="{{ old('año_fabricacion', $vehiculo->año_fabricacion) }}" required min="1900" max="{{ date('Y') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="capacidad_pasajeros" class="form-label">Capacidad de Pasajeros *</label>
                        <input type="number" class="form-control" id="capacidad_pasajeros" name="capacidad_pasajeros" value="{{ old('capacidad_pasajeros', $vehiculo->capacidad_pasajeros) }}" required min="1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición *</label>
                        <input type="date" class="form-control" id="fecha_adquisicion" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', $vehiculo->fecha_adquisicion) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="kilometraje" class="form-label">Kilometraje Actual *</label>
                        <input type="number" class="form-control" id="kilometraje" name="kilometraje" value="{{ old('kilometraje', $vehiculo->kilometraje) }}" required min="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Activo" {{ old('estado', $vehiculo->estado) == 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="En mantenimiento" {{ old('estado', $vehiculo->estado) == 'En mantenimiento' ? 'selected' : '' }}>En mantenimiento</option>
                            <option value="En reparación" {{ old('estado', $vehiculo->estado) == 'En reparación' ? 'selected' : '' }}>En reparación</option>
                            <option value="Fuera de servicio" {{ old('estado', $vehiculo->estado) == 'Fuera de servicio' ? 'selected' : '' }}>Fuera de servicio</option>
                            <option value="Dado de baja" {{ old('estado', $vehiculo->estado) == 'Dado de baja' ? 'selected' : '' }}>Dado de baja</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_linea" class="form-label">Línea Asignada</label>
                        <select class="form-select" id="id_linea" name="id_linea">
                            <option value="">Sin asignación</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}" {{ old('id_linea', $vehiculo->id_linea) == $linea->id_linea ? 'selected' : '' }}>
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="{{ route('vehiculos.show', $vehiculo->id_vehiculo) }}" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Vehículo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection