@extends('layouts.app')

@section('title', 'Programación Masiva de Horarios')

@section('styles')
<style>
    .section-divider {
        margin-top: 30px;
        margin-bottom: 20px;
        border-top: 1px solid #eee;
    }
    .card-horarios {
        margin-bottom: 20px;
    }
    .dias-semana .form-check {
        margin-bottom: 10px;
    }
    .time-picker {
        max-width: 150px;
    }
    .programacion-container {
        position: relative;
        min-height: 300px;
    }
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
    }
    .vista-previa {
        padding: 20px;
        margin-top: 20px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .tabla-horarios {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
    }
    .tabla-horarios th, 
    .tabla-horarios td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        text-align: center;
    }
    .tabla-horarios thead th {
        background-color: #f2f2f2;
        position: sticky;
        top: 0;
        z-index: 5;
    }
    .tabla-horarios tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }
    .estacion-nombre {
        text-align: left;
        font-weight: bold;
        min-width: 150px;
    }
    .horario-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        margin: 2px;
    }
    .llegada-badge {
        background-color: #28a745;
        color: white;
    }
    .salida-badge {
        background-color: #007bff;
        color: white;
    }
    .servicio-regular {
        border: 2px solid #28a745;
    }
    .servicio-expreso {
        border: 2px solid #007bff;
    }
    .servicio-economico {
        border: 2px solid #6c757d;
    }
    .servicio-especial {
        border: 2px solid #17a2b8;
    }
    .btn-scroll {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 100;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Programación Masiva de Horarios</h1>
        <a href="{{ route('horarios.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
        </a>
    </div>
    
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <div class="row">
        <!-- Formulario de Programación -->
        <div class="col-lg-6">
            <div class="card shadow card-horarios">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Datos para Programación</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('horarios.generar') }}" id="form-programacion">
                        @csrf
                        
                        <!-- Selección de Línea y Estación -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_linea" class="font-weight-bold">Línea:</label>
                                <select class="form-control" id="id_linea" name="id_linea" required>
                                    <option value="">Seleccione una línea</option>
                                    @foreach($lineas as $linea)
                                        <option value="{{ $linea->id_linea }}" {{ old('id_linea') == $linea->id_linea ? 'selected' : '' }}>
                                            {{ $linea->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="id_estacion" class="font-weight-bold">Estación:</label>
                                <select class="form-control" id="id_estacion" name="id_estacion" required>
                                    <option value="">Seleccione una estación</option>
                                    @foreach($estaciones as $estacion)
                                        <option value="{{ $estacion->id_estacion }}" {{ old('id_estacion') == $estacion->id_estacion ? 'selected' : '' }}>
                                            {{ $estacion->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Selección de Días de la Semana -->
                        <div class="form-group">
                            <label class="font-weight-bold">Días de la Semana:</label>
                            <div class="row dias-semana">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-1" value="1" {{ in_array(1, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-1">Lunes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-2" value="2" {{ in_array(2, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-2">Martes</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-3" value="3" {{ in_array(3, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-3">Miércoles</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-4" value="4" {{ in_array(4, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-4">Jueves</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-5" value="5" {{ in_array(5, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-5">Viernes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-6" value="6" {{ in_array(6, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-6">Sábado</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dias[]" id="dia-7" value="7" {{ in_array(7, old('dias', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dia-7">Domingo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configuración de Horarios -->
                        <div class="section-divider"></div>
                        <h5 class="text-primary mb-3">Configuración de Horarios</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_inicio" class="font-weight-bold">Hora de Inicio:</label>
                                <input type="time" class="form-control time-picker" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', '06:00') }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="hora_fin" class="font-weight-bold">Hora de Fin:</label>
                                <input type="time" class="form-control time-picker" id="hora_fin" name="hora_fin" value="{{ old('hora_fin', '22:00') }}" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="frecuencia" class="font-weight-bold">Frecuencia (minutos):</label>
                                <input type="number" class="form-control" id="frecuencia" name="frecuencia" min="1" max="120" value="{{ old('frecuencia', 15) }}" required>
                                <small class="form-text text-muted">Intervalo en minutos entre cada horario.</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tipo_hora" class="font-weight-bold">Tipo de Horario:</label>
                                <select class="form-control" id="tipo_hora" name="tipo_hora" required>
                                    <option value="Llegada" {{ old('tipo_hora') == 'Llegada' ? 'selected' : '' }}>Llegada</option>
                                    <option value="Salida" {{ old('tipo_hora') == 'Salida' ? 'selected' : '' }}>Salida</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo_servicio" class="font-weight-bold">Tipo de Servicio:</label>
                                <select class="form-control" id="tipo_servicio" name="tipo_servicio" required>
                                    <option value="Regular" {{ old('tipo_servicio') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="Expreso" {{ old('tipo_servicio') == 'Expreso' ? 'selected' : '' }}>Expreso</option>
                                    <option value="Económico" {{ old('tipo_servicio') == 'Económico' ? 'selected' : '' }}>Económico</option>
                                    <option value="Especial" {{ old('tipo_servicio') == 'Especial' ? 'selected' : '' }}>Especial</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="es_feriado" name="es_feriado" {{ old('es_feriado') ? 'checked' : '' }}>
                                    <label class="form-check-label font-weight-bold" for="es_feriado">
                                        Aplicar para días feriados
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observaciones" class="font-weight-bold">Observaciones:</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2">{{ old('observaciones') }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="reemplazar_existentes" name="reemplazar_existentes" {{ old('reemplazar_existentes') ? 'checked' : '' }}>
                                <label class="form-check-label font-weight-bold text-danger" for="reemplazar_existentes">
                                    Reemplazar horarios existentes para la línea, estación y días seleccionados
                                </label>
                                <small class="form-text text-muted">ADVERTENCIA: Esta opción eliminará todos los horarios existentes que coincidan con los criterios seleccionados.</small>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="button" id="btn-vista-previa" class="btn btn-info mr-2">
                                <i class="fas fa-eye"></i> Vista Previa
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Horarios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Vista Previa de Horarios -->
        <div class="col-lg-6">
            <div class="card shadow card-horarios">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Vista Previa de Horarios</h6>
                    <div>
                        <button type="button" id="btn-consultar-horarios" class="btn btn-sm btn-primary">
                            <i class="fas fa-clock"></i> Consultar Horarios Existentes
                        </button>
                    </div>
                </div>
                <div class="card-body programacion-container">
                    <div id="loading-overlay" class="loading-overlay d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                    
                    <div id="vista-previa-horarios" class="vista-previa">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <p>Seleccione una línea y estación, y haga clic en "Vista Previa" o "Consultar Horarios Existentes" para ver los horarios.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button id="btn-scroll-top" class="btn btn-primary btn-scroll d-none">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar select2 si está disponible
        if ($.fn.select2) {
            $('#id_linea, #id_estacion').select2({
                placeholder: 'Seleccione una opción'
            });
        }
        
        // Función para actualizar estaciones según la línea seleccionada
        $('#id_linea').change(function() {
            var lineaId = $(this).val();
            if (!lineaId) {
                return;
            }
            
            // Mostrar cargando
            $('#loading-overlay').removeClass('d-none');
            
            // Obtener estaciones de la línea
            $.ajax({
                url: '/api/horarios/estaciones-por-linea',
                type: 'GET',
                data: {
                    linea_id: lineaId
                },
                success: function(response) {
                    var estaciones = response.estaciones;
                    var select = $('#id_estacion');
                    
                    // Limpiar select
                    select.empty();
                    select.append('<option value="">Seleccione una estación</option>');
                    
                    // Agregar opciones de estaciones
                    estaciones.forEach(function(estacion) {
                        select.append('<option value="' + estacion.id_estacion + '">' + estacion.nombre + '</option>');
                    });
                    
                    // Refrescar select2 si está disponible
                    if ($.fn.select2) {
                        select.trigger('change');
                    }
                    
                    // Ocultar cargando
                    $('#loading-overlay').addClass('d-none');
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar estaciones:', error);
                    alert('Error al cargar estaciones: ' + error);
                    $('#loading-overlay').addClass('d-none');
                }
            });
        });
        
        // Función para mostrar vista previa de horarios a generar
        $('#btn-vista-previa').click(function() {
            var horaInicio = $('#hora_inicio').val();
            var horaFin = $('#hora_fin').val();
            var frecuencia = $('#frecuencia').val();
            var tipoHora = $('#tipo_hora').val();
            var tipoServicio = $('#tipo_servicio').val();
            
            // Validar campos obligatorios
            if (!horaInicio || !horaFin || !frecuencia) {
                alert('Por favor complete los campos de hora inicio, hora fin y frecuencia');
                return;
            }
            
            // Validar que la hora fin sea posterior a la hora inicio
            if (horaInicio >= horaFin) {
                alert('La hora de fin debe ser posterior a la hora de inicio');
                return;
            }
            
            // Generar vista previa
            var vistaPrevia = generarVistaPrevia(horaInicio, horaFin, frecuencia, tipoHora, tipoServicio);
            $('#vista-previa-horarios').html(vistaPrevia);
        });
        
        // Función para consultar horarios existentes
        $('#btn-consultar-horarios').click(function() {
            var lineaId = $('#id_linea').val();
            var dia = $('input[name="dias[]"]:checked').first().val();
            
            if (!lineaId) {
                alert('Por favor seleccione una línea');
                return;
            }
            
            if (!dia) {
                alert('Por favor seleccione al menos un día de la semana');
                return;
            }
            
            // Mostrar cargando
            $('#loading-overlay').removeClass('d-none');
            
            // Consultar horarios existentes
            $.ajax({
                url: '/api/horarios/horarios-por-linea-dia',
                type: 'GET',
                data: {
                    linea_id: lineaId,
                    dia: dia,
                    es_feriado: $('#es_feriado').prop('checked')
                },
                success: function(response) {
                    // Generar HTML con los horarios existentes
                    var html = generarTablaHorariosExistentes(response);
                    $('#vista-previa-horarios').html(html);
                    
                    // Ocultar cargando
                    $('#loading-overlay').addClass('d-none');
                },
                error: function(xhr, status, error) {
                    console.error('Error al consultar horarios:', error);
                    alert('Error al consultar horarios: ' + error);
                    $('#loading-overlay').addClass('d-none');
                }
            });
        });
        
        // Función para generar vista previa de horarios
        function generarVistaPrevia(horaInicio, horaFin, frecuencia, tipoHora, tipoServicio) {
            var html = '<h5 class="text-primary mb-3">Horarios a Generar</h5>';
            
            // Calcular todos los horarios
            var horarios = [];
            var start = new Date('2023-01-01T' + horaInicio + ':00');
            var end = new Date('2023-01-01T' + horaFin + ':00');
            var current = new Date(start);
            
            while (current <= end) {
                var hora = current.getHours().toString().padStart(2, '0') + ':' + 
                           current.getMinutes().toString().padStart(2, '0');
                horarios.push(hora);
                current.setMinutes(current.getMinutes() + parseInt(frecuencia));
            }
            
            // Obtener días seleccionados
            var diasSeleccionados = [];
            $('input[name="dias[]"]:checked').each(function() {
                var diaValue = $(this).val();
                var diaLabel = $(this).next('label').text();
                diasSeleccionados.push({
                    value: diaValue,
                    label: diaLabel
                });
            });
            
            // Generar tabla si hay horarios
            if (horarios.length === 0) {
                return '<div class="alert alert-warning">No se generarán horarios con los parámetros seleccionados</div>';
            }
            
            if (diasSeleccionados.length === 0) {
                return '<div class="alert alert-warning">Seleccione al menos un día de la semana</div>';
            }
            
            html += '<div class="alert alert-info">';
            html += '<p><strong>Total de horarios a generar:</strong> ' + (horarios.length * diasSeleccionados.length) + '</p>';
            html += '<p><strong>Días seleccionados:</strong> ' + diasSeleccionados.map(d => d.label).join(', ') + '</p>';
            html += '</div>';
            
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead>';
            html += '<tr class="bg-light">';
            html += '<th>Día</th>';
            html += '<th>Horarios</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            // Generar filas para cada día
            diasSeleccionados.forEach(function(dia) {
                html += '<tr>';
                html += '<td class="font-weight-bold">' + dia.label + '</td>';
                html += '<td>';
                
                // Mostrar todos los horarios para este día
                var horariosHtml = '';
                horarios.forEach(function(hora) {
                    var badgeClass = 'llegada-badge';
                    if (tipoHora === 'Salida') {
                        badgeClass = 'salida-badge';
                    }
                    
                    var servicioClass = 'servicio-regular';
                    if (tipoServicio === 'Expreso') {
                        servicioClass = 'servicio-expreso';
                    } else if (tipoServicio === 'Económico') {
                        servicioClass = 'servicio-economico';
                    } else if (tipoServicio === 'Especial') {
                        servicioClass = 'servicio-especial';
                    }
                    
                    horariosHtml += '<span class="horario-badge ' + badgeClass + ' ' + servicioClass + '">' + hora + '</span>';
                });
                
                html += horariosHtml;
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            
            return html;
        }
        
        // Función para generar tabla de horarios existentes
        function generarTablaHorariosExistentes(data) {
            var estaciones = data.estaciones || [];
            var horariosPorEstacion = data.horarios_por_estacion || {};
            var horasList = data.horas_list || [];
            
            if (estaciones.length === 0 || horasList.length === 0) {
                return '<div class="alert alert-info">No hay horarios programados para la línea y día seleccionados</div>';
            }
            
            var html = '<h5 class="text-primary mb-3">Horarios Existentes</h5>';
            
            html += '<div class="table-responsive">';
            html += '<table class="tabla-horarios">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>Estación</th>';
            
            // Crear encabezados para cada hora
            horasList.forEach(function(hora) {
                html += '<th>' + hora + '</th>';
            });
            
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            // Crear filas para cada estación
            estaciones.forEach(function(estacion) {
                html += '<tr>';
                html += '<td class="estacion-nombre">' + estacion.nombre + '</td>';
                
                // Para cada hora, verificar si hay horarios para esta estación
                horasList.forEach(function(hora) {
                    html += '<td>';
                    
                    // Verificar llegadas
                    if (horariosPorEstacion[estacion.id_estacion] && 
                        horariosPorEstacion[estacion.id_estacion].llegadas) {
                        
                        var llegada = horariosPorEstacion[estacion.id_estacion].llegadas.find(h => h.hora === hora);
                        if (llegada) {
                            var servicioClass = '';
                            if (llegada.tipo_servicio === 'Regular') {
                                servicioClass = 'servicio-regular';
                            } else if (llegada.tipo_servicio === 'Expreso') {
                                servicioClass = 'servicio-expreso';
                            } else if (llegada.tipo_servicio === 'Económico') {
                                servicioClass = 'servicio-economico';
                            } else if (llegada.tipo_servicio === 'Especial') {
                                servicioClass = 'servicio-especial';
                            }
                            
                            html += '<span class="horario-badge llegada-badge ' + servicioClass + '" title="' + llegada.tipo_servicio + '">L</span>';
                        }
                    }
                    
                    // Verificar salidas
                    if (horariosPorEstacion[estacion.id_estacion] && 
                        horariosPorEstacion[estacion.id_estacion].salidas) {
                        
                        var salida = horariosPorEstacion[estacion.id_estacion].salidas.find(h => h.hora === hora);
                        if (salida) {
                            var servicioClass = '';
                            if (salida.tipo_servicio === 'Regular') {
                                servicioClass = 'servicio-regular';
                            } else if (salida.tipo_servicio === 'Expreso') {
                                servicioClass = 'servicio-expreso';
                            } else if (salida.tipo_servicio === 'Económico') {
                                servicioClass = 'servicio-economico';
                            } else if (salida.tipo_servicio === 'Especial') {
                                servicioClass = 'servicio-especial';
                            }
                            
                            html += '<span class="horario-badge salida-badge ' + servicioClass + '" title="' + salida.tipo_servicio + '">S</span>';
                        }
                    }
                    
                    html += '</td>';
                });
                
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            
            // Leyenda
            html += '<div class="mt-3">';
            html += '<h6 class="font-weight-bold">Leyenda:</h6>';
            html += '<div class="d-flex flex-wrap">';
            html += '<div class="mr-3 mb-2"><span class="horario-badge llegada-badge">L</span> Llegada</div>';
            html += '<div class="mr-3 mb-2"><span class="horario-badge salida-badge">S</span> Salida</div>';
            html += '<div class="mr-3 mb-2"><span class="horario-badge servicio-regular">T</span> Regular</div>';
            html += '<div class="mr-3 mb-2"><span class="horario-badge servicio-expreso">T</span> Expreso</div>';
            html += '<div class="mr-3 mb-2"><span class="horario-badge servicio-economico">T</span> Económico</div>';
            html += '<div class="mr-3 mb-2"><span class="horario-badge servicio-especial">T</span> Especial</div>';
            html += '</div>';
            html += '</div>';
            
            return html;
        }
        
        // Mostrar/ocultar botón para volver arriba
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#btn-scroll-top').removeClass('d-none');
            } else {
                $('#btn-scroll-top').addClass('d-none');
            }
        });
        
        // Acción del botón para volver arriba
        $('#btn-scroll-top').click(function() {
            $('html, body').animate({scrollTop: 0}, 500);
            return false;
        });
        
        // Validación del formulario antes de enviar
        $('#form-programacion').submit(function(e) {
            // Verificar que al menos un día esté seleccionado
            if ($('input[name="dias[]"]:checked').length === 0) {
                alert('Por favor seleccione al menos un día de la semana');
                e.preventDefault();
                return false;
            }
            
            // Verificar que hora fin sea mayor que hora inicio
            var horaInicio = $('#hora_inicio').val();
            var horaFin = $('#hora_fin').val();
            
            if (horaInicio >= horaFin) {
                alert('La hora de fin debe ser posterior a la hora de inicio');
                e.preventDefault();
                return false;
            }
            
            // Confirmar si se reemplazarán horarios existentes
            if ($('#reemplazar_existentes').prop('checked')) {
                if (!confirm('ADVERTENCIA: Se eliminarán todos los horarios existentes para la línea, estación y días seleccionados. ¿Desea continuar?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>
@endsection