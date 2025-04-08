@extends('layouts.app')

@section('title', 'Gestión de Horarios')

@section('styles')
<style>
    .filter-card {
        margin-bottom: 20px;
    }
    .table-container {
        overflow-x: auto;
    }
    .horario-badge {
        font-size: 0.85rem;
        padding: 3px 8px;
    }
    .status-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .status-llegada {
        background-color: #28a745;
    }
    .status-salida {
        background-color: #007bff;
    }
    .tipo-regular {
        background-color: #28a745;
    }
    .tipo-expreso {
        background-color: #007bff;
    }
    .tipo-economico {
        background-color: #6c757d;
    }
    .tipo-especial {
        background-color: #17a2b8;
    }
    .dia-hoy {
        background-color: #fff3cd;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Horarios</h1>
        <div>
            <a href="{{ route('horarios.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Horario
            </a>
            <a href="{{ route('horarios.programacion') }}" class="btn btn-sm btn-success shadow-sm ml-2">
                <i class="fas fa-clock fa-sm text-white-50"></i> Programación Masiva
            </a>
        </div>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <div class="card shadow filter-card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('horarios.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="linea_id">Línea:</label>
                        <select class="form-control" id="linea_id" name="linea_id">
                            <option value="">Todas las líneas</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}" {{ request('linea_id') == $linea->id_linea ? 'selected' : '' }}>
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="estacion_id">Estación:</label>
                        <select class="form-control" id="estacion_id" name="estacion_id">
                            <option value="">Todas las estaciones</option>
                            @foreach($estaciones as $estacion)
                                <option value="{{ $estacion->id_estacion }}" {{ request('estacion_id') == $estacion->id_estacion ? 'selected' : '' }}>
                                    {{ $estacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label for="dia_semana">Día:</label>
                        <select class="form-control" id="dia_semana" name="dia_semana">
                            <option value="">Todos los días</option>
                            <option value="1" {{ request('dia_semana') == '1' ? 'selected' : '' }}>Lunes</option>
                            <option value="2" {{ request('dia_semana') == '2' ? 'selected' : '' }}>Martes</option>
                            <option value="3" {{ request('dia_semana') == '3' ? 'selected' : '' }}>Miércoles</option>
                            <option value="4" {{ request('dia_semana') == '4' ? 'selected' : '' }}>Jueves</option>
                            <option value="5" {{ request('dia_semana') == '5' ? 'selected' : '' }}>Viernes</option>
                            <option value="6" {{ request('dia_semana') == '6' ? 'selected' : '' }}>Sábado</option>
                            <option value="7" {{ request('dia_semana') == '7' ? 'selected' : '' }}>Domingo</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label for="tipo_hora">Tipo de Hora:</label>
                        <select class="form-control" id="tipo_hora" name="tipo_hora">
                            <option value="">Todos</option>
                            <option value="Llegada" {{ request('tipo_hora') == 'Llegada' ? 'selected' : '' }}>Llegada</option>
                            <option value="Salida" {{ request('tipo_hora') == 'Salida' ? 'selected' : '' }}>Salida</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label for="tipo_servicio">Tipo de Servicio:</label>
                        <select class="form-control" id="tipo_servicio" name="tipo_servicio">
                            <option value="">Todos</option>
                            <option value="Regular" {{ request('tipo_servicio') == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Expreso" {{ request('tipo_servicio') == 'Expreso' ? 'selected' : '' }}>Expreso</option>
                            <option value="Económico" {{ request('tipo_servicio') == 'Económico' ? 'selected' : '' }}>Económico</option>
                            <option value="Especial" {{ request('tipo_servicio') == 'Especial' ? 'selected' : '' }}>Especial</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search fa-sm"></i> Filtrar
                        </button>
                        <a href="{{ route('horarios.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-sync-alt fa-sm"></i> Limpiar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Horarios</h6>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-bordered table-striped" id="tabla-horarios" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Línea</th>
                            <th>Estación</th>
                            <th>Día</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Servicio</th>
                            <th>Feriado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $diaActual = date('N');
                        @endphp
                        @forelse($horarios as $horario)
                            <tr class="{{ $horario->dia_semana == $diaActual ? 'dia-hoy' : '' }}">
                                <td>{{ $horario->id_horario }}</td>
                                <td>{{ $horario->linea->nombre }}</td>
                                <td>{{ $horario->estacion->nombre }}</td>
                                <td>{{ $horario->getNombreDiaAttribute() }}</td>
                                <td>{{ $horario->getHoraFormateadaAttribute() }}</td>
                                <td>
                                    <span class="badge {{ $horario->tipo_hora == 'Llegada' ? 'badge-success' : 'badge-primary' }} horario-badge">
                                        <span class="status-dot status-{{ strtolower($horario->tipo_hora) }}"></span>
                                        {{ $horario->tipo_hora }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge horario-badge tipo-{{ strtolower(str_replace('ó', 'o', $horario->tipo_servicio)) }}">
                                        {{ $horario->tipo_servicio }}
                                    </span>
                                </td>
                                <td>
                                    @if($horario->es_feriado)
                                        <span class="badge badge-warning">Sí</span>
                                    @else
                                        <span class="badge badge-light">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('horarios.edit', $horario->id_horario) }}" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('horarios.destroy', $horario->id_horario) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro que desea eliminar este horario?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No se encontraron horarios</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6 text-left">
                    <p>Mostrando {{ $horarios->count() }} de {{ $horarios->total() }} registros</p>
                </div>
                <div class="col-md-6">
                    <div class="pagination justify-content-end">
                        {{ $horarios->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar select2 si está disponible
        if ($.fn.select2) {
            $('#linea_id, #estacion_id').select2({
                placeholder: 'Seleccione una opción'
            });
        }
        
        // Función para actualizar estaciones según la línea seleccionada
        $('#linea_id').change(function() {
            var lineaId = $(this).val();
            if (!lineaId) {
                return;
            }
            
            // Obtener estaciones de la línea
            $.ajax({
                url: '/api/horarios/estaciones-por-linea',
                type: 'GET',
                data: {
                    linea_id: lineaId
                },
                success: function(response) {
                    var estaciones = response.estaciones;
                    var select = $('#estacion_id');
                    
                    // Limpiar select
                    select.empty();
                    select.append('<option value="">Todas las estaciones</option>');
                    
                    // Agregar opciones de estaciones
                    estaciones.forEach(function(estacion) {
                        select.append('<option value="' + estacion.id_estacion + '">' + estacion.nombre + '</option>');
                    });
                    
                    // Refrescar select2 si está disponible
                    if ($.fn.select2) {
                        select.trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar estaciones:', error);
                }
            });
        });
    });
</script>
@endsection