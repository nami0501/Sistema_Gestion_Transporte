@extends('layouts.admin')

@section('title', 'Editar Asignación')

@section('styles')
<style>
    .badge-linea {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .estado-badge {
        font-size: 0.9rem;
        padding: 0.35em 0.65em;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Asignación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('asignaciones.index') }}">Asignaciones</a></li>
        <li class="breadcrumb-item active">Editar Asignación #{{ $asignacion->id_asignacion }}</li>
    </ol>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-edit me-1"></i>
            Editar Asignación #{{ $asignacion->id_asignacion }}
        </div>
        <div class="card-body">
            <form action="{{ route('asignaciones.update', $asignacion->id_asignacion) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Fecha de Asignación:</strong> {{ \Carbon\Carbon::parse($asignacion->fecha)->format('d/m/Y') }}</p>
                                    <p class="mb-1"><strong>Horario:</strong> {{ \Carbon\Carbon::parse($asignacion->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($asignacion->hora_fin)->format('H:i') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Conductor:</strong> {{ $asignacion->usuario->nombre }} {{ $asignacion->usuario->apellidos }}</p>
                                    <p class="mb-1"><strong>Vehículo:</strong> {{ $asignacion->vehiculo->placa }} ({{ $asignacion->vehiculo->tipo }})</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <p class="mb-1"><strong>Línea:</strong> 
                                        <span class="badge badge-linea" style="background-color: {{ $asignacion->linea->color }};">{{ $asignacion->linea->nombre }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Estado Actual:</strong> 
                                        <span class="badge estado-badge 
                                            {{ $asignacion->estado == 'Programado' ? 'bg-secondary' : 
                                              ($asignacion->estado == 'En curso' ? 'bg-primary' : 
                                              ($asignacion->estado == 'Completado' ? 'bg-success' : 'bg-danger')) }}">
                                            {{ $asignacion->estado }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', $asignacion->fecha) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_turno" class="form-label">Turno</label>
                        <select class="form-select" id="id_turno" name="id_turno" required>
                            <option value="">Seleccione un turno</option>
                            @foreach($turnos as $turno)
                                <option value="{{ $turno->id_turno }}" 
                                    {{ (old('id_turno') == $turno->id_turno || $asignacion->id_turno == $turno->id_turno) ? 'selected' : '' }}>
                                    {{ $turno->nombre }} ({{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', \Carbon\Carbon::parse($asignacion->hora_inicio)->format('H:i')) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="hora_fin" class="form-label">Hora de Fin</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="{{ old('hora_fin', \Carbon\Carbon::parse($asignacion->hora_fin)->format('H:i')) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_linea" class="form-label">Línea</label>
                        <select class="form-select" id="id_linea" name="id_linea" required>
                            <option value="">Seleccione una línea</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}" 
                                    {{ (old('id_linea') == $linea->id_linea || $asignacion->id_linea == $linea->id_linea) ? 'selected' : '' }}
                                    data-color="{{ $linea->color }}">
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="id_usuario" class="form-label">Conductor</label>
                        <select class="form-select" id="id_usuario" name="id_usuario" required>
                            <option value="">Seleccione un conductor</option>
                            @foreach($conductores as $conductor)
                                <option value="{{ $conductor->id_usuario }}" 
                                    {{ (old('id_usuario') == $conductor->id_usuario || $asignacion->id_usuario == $conductor->id_usuario) ? 'selected' : '' }}>
                                    {{ $conductor->nombre }} {{ $conductor->apellidos }} ({{ $conductor->dni }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_vehiculo" class="form-label">Vehículo</label>
                        <select class="form-select" id="id_vehiculo" name="id_vehiculo" required>
                            <option value="">Seleccione un vehículo</option>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id_vehiculo }}" 
                                    {{ (old('id_vehiculo') == $vehiculo->id_vehiculo || $asignacion->id_vehiculo == $vehiculo->id_vehiculo) ? 'selected' : '' }}
                                    data-kilometraje="{{ $vehiculo->kilometraje }}">
                                    {{ $vehiculo->placa }} ({{ $vehiculo->tipo }} - {{ $vehiculo->marca }} {{ $vehiculo->modelo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Programado" {{ (old('estado') == 'Programado' || $asignacion->estado == 'Programado') ? 'selected' : '' }}>Programado</option>
                            <option value="En curso" {{ (old('estado') == 'En curso' || $asignacion->estado == 'En curso') ? 'selected' : '' }}>En curso</option>
                            <option value="Completado" {{ (old('estado') == 'Completado' || $asignacion->estado == 'Completado') ? 'selected' : '' }}>Completado</option>
                            <option value="Cancelado" {{ (old('estado') == 'Cancelado' || $asignacion->estado == 'Cancelado') ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="kilometraje_inicial" class="form-label">Kilometraje Inicial</label>
                        <input type="number" class="form-control" id="kilometraje_inicial" name="kilometraje_inicial" value="{{ old('kilometraje_inicial', $asignacion->kilometraje_inicial) }}" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="kilometraje_final" class="form-label">Kilometraje Final</label>
                        <input type="number" class="form-control" id="kilometraje_final" name="kilometraje_final" value="{{ old('kilometraje_final', $asignacion->kilometraje_final) }}" min="0" {{ $asignacion->estado != 'Completado' ? 'disabled' : '' }}>
                        <div class="form-text">Solo disponible cuando el estado es "Completado"</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="vueltas_completas" class="form-label">Vueltas Completas</label>
                        <input type="number" class="form-control" id="vueltas_completas" name="vueltas_completas" value="{{ old('vueltas_completas', $asignacion->vueltas_completas) }}" min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $asignacion->observaciones) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('asignaciones.show', $asignacion->id_asignacion) }}" class="btn btn-secondary">
                        <i class="fas fa-times-circle me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Actualiza la disponibilidad del campo de kilometraje final según el estado
    document.getElementById('estado').addEventListener('change', function() {
        const kilometrajeFinal = document.getElementById('kilometraje_final');
        if (this.value === 'Completado') {
            kilometrajeFinal.removeAttribute('disabled');
        } else {
            kilometrajeFinal.setAttribute('disabled', 'disabled');
            kilometrajeFinal.value = '';
        }
    });

    // Actualiza el valor del kilometraje inicial cuando se cambia el vehículo
    document.getElementById('id_vehiculo').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const kilometrajeActual = selectedOption.getAttribute('data-kilometraje');
            
            // Solo actualizar si el campo no ha sido modificado manualmente o está vacío
            const kmInicial = document.getElementById('kilometraje_inicial');
            if (!kmInicial.value || parseInt(kmInicial.value) === 0) {
                kmInicial.value = kilometrajeActual;
            }
        }
    });

    // Validación para asegurar que el kilometraje final sea mayor que el inicial
    document.querySelector('form').addEventListener('submit', function(e) {
        const kmInicial = parseInt(document.getElementById('kilometraje_inicial').value) || 0;
        const kmFinal = parseInt(document.getElementById('kilometraje_final').value) || 0;
        const estado = document.getElementById('estado').value;
        
        if (estado === 'Completado' && kmFinal > 0 && kmFinal <= kmInicial) {
            e.preventDefault();
            alert('El kilometraje final debe ser mayor que el kilometraje inicial.');
            return false;
        }
    });
</script>
@endsection