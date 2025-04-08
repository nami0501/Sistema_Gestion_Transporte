@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar Mantenimiento</h3>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
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

            <form action="{{ route('mantenimientos.update', $mantenimiento->id_mantenimiento) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_vehiculo" class="form-label">Vehículo *</label>
                        <select class="form-select" id="id_vehiculo" name="id_vehiculo" required>
                            <option value="">Seleccionar vehículo...</option>
                            @foreach($vehiculos as $v)
                                <option value="{{ $v->id_vehiculo }}" {{ (old('id_vehiculo', $mantenimiento->id_vehiculo) == $v->id_vehiculo) ? 'selected' : '' }}>
                                    {{ $v->placa }} - {{ $v->marca }} {{ $v->modelo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo_mantenimiento" class="form-label">Tipo de Mantenimiento *</label>
                        <select class="form-select" id="tipo_mantenimiento" name="tipo_mantenimiento" required>
                            <option value="">Seleccionar tipo...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo_mantenimiento', $mantenimiento->tipo_mantenimiento) == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción *</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required>{{ old('descripcion', $mantenimiento->descripcion) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_programada" class="form-label">Fecha Programada *</label>
                        <input type="date" class="form-control" id="fecha_programada" name="fecha_programada" value="{{ old('fecha_programada', $mantenimiento->fecha_programada ? date('Y-m-d', strtotime($mantenimiento->fecha_programada)) : '') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_realizada" class="form-label">Fecha Realizada</label>
                        <input type="date" class="form-control" id="fecha_realizada" name="fecha_realizada" value="{{ old('fecha_realizada', $mantenimiento->fecha_realizada ? date('Y-m-d', strtotime($mantenimiento->fecha_realizada)) : '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="costo" class="form-label">Costo (USD)</label>
                        <input type="number" class="form-control" id="costo" name="costo" value="{{ old('costo', $mantenimiento->costo) }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="proveedor" class="form-label">Proveedor</label>
                        <input type="text" class="form-control" id="proveedor" name="proveedor" value="{{ old('proveedor', $mantenimiento->proveedor) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="resultado" class="form-label">Resultado *</label>
                        <select class="form-select" id="resultado" name="resultado" required>
                            @foreach($resultados as $resultado)
                                <option value="{{ $resultado }}" {{ old('resultado', $mantenimiento->resultado) == $resultado ? 'selected' : '' }}>
                                    {{ $resultado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2">{{ old('observaciones', $mantenimiento->observaciones) }}</textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Mantenimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection