@extends('layouts.admin')

@section('title', 'Gestión de Asignaciones')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Asignaciones</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Asignaciones</li>
    </ol>

    <!-- Selector de fecha -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="fechaForm" action="{{ route('asignaciones.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="fecha" class="col-form-label">Fecha:</label>
                </div>
                <div class="col-auto">
                    <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $fecha }}" max="{{ date('Y-m-d', strtotime('+30 days')) }}">
                </div>
                @if($estado)
                    <input type="hidden" name="estado" value="{{ $estado }}">
                @endif
                @if($id_linea)
                    <input type="hidden" name="id_linea" value="{{ $id_linea }}">
                @endif
                @if($id_turno)
                    <input type="hidden" name="id_turno" value="{{ $id_turno }}">
                @endif
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Asignaciones</div>
                            <div class="h3 mb-0">{{ $estadisticas['total'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Programadas</div>
                            <div class="h3 mb-0">{{ $estadisticas['programado'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">En Curso</div>
                            <div class="h3 mb-0">{{ $estadisticas['en_curso'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-play-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Completadas</div>
                            <div class="h3 mb-0">{{ $estadisticas['completado'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-calendar-check me-1"></i>
                Asignaciones del {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
            </div>
            <div>
                <a href="{{ route('asignaciones.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nueva Asignación
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="{{ route('asignaciones.index') }}" method="GET" class="row g-3">
                        <input type="hidden" name="fecha" value="{{ $fecha }}">
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                @foreach($estados as $estadoItem)
                                <option value="{{ $estadoItem }}" {{ $estado == $estadoItem ? 'selected' : '' }}>{{ $estadoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="id_linea" class="form-label">Línea</label>
                            <select name="id_linea" id="id_linea" class="form-select">
                                <option value="">Todas las líneas</option>
                                @foreach($lineas as $lineaItem)
                                <option value="{{ $lineaItem->id_linea }}" {{ $id_linea == $lineaItem->id_linea ? 'selected' : '' }}>{{ $lineaItem->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="id_turno" class="form-label">Turno</label>
                            <select name="id_turno" id="id_turno" class="form-select">
                                <option value="">Todos los turnos</option>
                                @foreach($turnos as $turnoItem)
                                <option value="{{ $turnoItem->id_turno }}" {{ $id_turno == $turnoItem->id_turno ? 'selected' : '' }}>{{ $turnoItem->nombre }} ({{ $turnoItem->hora_inicio->format('H:i') }} - {{ $turnoItem->hora_fin->format('H:i') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('asignaciones.index', ['fecha' => $fecha]) }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de asignaciones -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Conductor</th>
                            <th>Vehículo</th>
                            <th>Línea</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                        <tr>
                            <td>
                                @if($asignacion->usuario)
                                {{ $asignacion->usuario->nombre }} {{ $asignacion->usuario->apellidos }}
                                <div class="text-muted small">{{ $asignacion->usuario->dni }}</div>
                                @else
                                <span class="text-danger">Conductor no asignado</span>
                                @endif
                            </td>
                            <td>
                                @if($asignacion->vehiculo)
                                {{ $asignacion->vehiculo->placa }}
                                <div class="text-muted small">{{ $asignacion->vehiculo->tipo }} - {{ $asignacion->vehiculo->marca }} {{ $asignacion->vehiculo->modelo }}</div>
                                @else
                                <span class="text-danger">Vehículo no asignado</span>
                                @endif
                            </td>
                            <td>
                                @if($asignacion->linea)
                                <span class="badge rounded-pill" style="background-color: {{ $asignacion->linea->color }}">
                                    {{ $asignacion->linea->nombre }}
                                </span>
                                @else
                                <span class="badge bg-secondary">No asignada</span>
                                @endif
                            </td>
                            <td>
                                @if($asignacion->turno)
                                <strong>{{ $asignacion->turno->nombre }}</strong><br>
                                {{ $asignacion->hora_inicio->format('H:i') }} - {{ $asignacion->hora_fin->format('H:i') }}
                                @else
                                {{ $asignacion->hora_inicio->format('H:i') }} - {{ $asignacion->hora_fin->format('H:i') }}
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeClass = 'bg-secondary';
                                switch($asignacion->estado) {
                                    case 'Programado': $badgeClass = 'bg-warning text-dark'; break;
                                    case 'En curso': $badgeClass = 'bg-success'; break;
                                    case 'Completado': $badgeClass = 'bg-info text-dark'; break;
                                    case 'Cancelado': $badgeClass = 'bg-danger'; break;
                                }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $asignacion->estado }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('asignaciones.show', $asignacion->id_asignacion) }}" class="btn btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(in_array($asignacion->estado, ['Programado', 'En curso']))
                                    <a href="{{ route('asignaciones.edit', $asignacion->id_asignacion) }}" class="btn btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    @if($asignacion->estado == 'Programado')
                                    <button type="button" class="btn btn-success iniciar-btn" 
                                        data-id="{{ $asignacion->id_asignacion }}"
                                        data-placa="{{ $asignacion->vehiculo ? $asignacion->vehiculo->placa : 'N/A' }}"
                                        data-km="{{ $asignacion->vehiculo ? $asignacion->vehiculo->kilometraje : 0 }}"
                                        title="Iniciar asignación">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger cancelar-btn" 
                                        data-id="{{ $asignacion->id_asignacion }}"
                                        data-conductor="{{ $asignacion->usuario ? $asignacion->usuario->nombre . ' ' . $asignacion->usuario->apellidos : 'N/A' }}"
                                        data-vehiculo="{{ $asignacion->vehiculo ? $asignacion->vehiculo->placa : 'N/A' }}"
                                        title="Cancelar asignación">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endif
                                    
                                    @if($asignacion->estado == 'En curso')
                                    <button type="button" class="btn btn-success finalizar-btn" 
                                        data-id="{{ $asignacion->id_asignacion }}"
                                        data-placa="{{ $asignacion->vehiculo ? $asignacion->vehiculo->placa : 'N/A' }}"
                                        data-km-inicial="{{ $asignacion->kilometraje_inicial }}"
                                        data-vueltas="{{ $asignacion->vueltas_completas }}"
                                        title="Finalizar asignación">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                    
                                    <a href="{{ route('incidentes.create', ['id_asignacion' => $asignacion->id_asignacion]) }}" class="btn btn-warning" title="Reportar incidente">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </a>
                                    @endif
                                    
                                    @if($asignacion->estado == 'Programado')
                                    <button type="button" class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal" 
                                        data-id="{{ $asignacion->id_asignacion }}"
                                        data-info="{{ ($asignacion->usuario ? $asignacion->usuario->nombre . ' ' . $asignacion->usuario->apellidos : 'N/A') . ' - ' . ($asignacion->vehiculo ? $asignacion->vehiculo->placa : 'N/A') }}"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay asignaciones registradas para esta fecha</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-3">
                {{ $asignaciones->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de eliminar la asignación de <span id="deleteAsignacionInfo" class="fw-bold"></span>?
                <p class="text-danger mt-2"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para iniciar asignación -->
<div class="modal fade" id="iniciarModal" tabindex="-1" aria-labelledby="iniciarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="iniciarModalLabel">Iniciar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Estás por iniciar la asignación con el vehículo: <span id="iniciarVehiculoPlaca" class="fw-bold"></span></p>
                
                <form id="iniciarForm">
                    <div class="mb-3">
                        <label for="kilometraje_inicial" class="form-label">Kilometraje Inicial</label>
                        <input type="number" class="form-control" id="kilometraje_inicial" name="kilometraje_inicial" min="0" required>
                        <div class="form-text">El kilometraje debe ser igual o mayor que el último registrado (<span id="kilometrajeActual"></span> km).</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarIniciar">Iniciar Asignación</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para finalizar asignación -->
<div class="modal fade" id="finalizarModal" tabindex="-1" aria-labelledby="finalizarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="finalizarModalLabel">Finalizar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Estás por finalizar la asignación con el vehículo: <span id="finalizarVehiculoPlaca" class="fw-bold"></span></p>
                
                <form id="finalizarForm">
                    <div class="mb-3">
                        <label for="kilometraje_final" class="form-label">Kilometraje Final</label>
                        <input type="number" class="form-control" id="kilometraje_final" name="kilometraje_final" min="0" required>
                        <div class="form-text">El kilometraje debe ser mayor que el inicial (<span id="kilometrajeInicial"></span> km).</div>
                    </div>
                    <div class="mb-3">
                        <label for="vueltas_completas" class="form-label">Vueltas Completas</label>
                        <input type="number" class="form-control" id="vueltas_completas" name="vueltas_completas" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="confirmarFinalizar">Finalizar Asignación</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cancelar asignación -->
<div class="modal fade" id="cancelarModal" tabindex="-1" aria-labelledby="cancelarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelarModalLabel">Cancelar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Estás por cancelar la asignación:</p>
                <p><strong>Conductor:</strong> <span id="cancelarConductor"></span></p>
                <p><strong>Vehículo:</strong> <span id="cancelarVehiculo"></span></p>
                
                <form id="cancelarForm">
                    <div class="mb-3">
                        <label for="observaciones_cancelacion" class="form-label">Motivo de la cancelación</label>
                        <textarea class="form-control" id="observaciones_cancelacion" name="observaciones" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-danger" id="confirmarCancelar">Cancelar Asignación</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar el modal de eliminación
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const info = button.getAttribute('data-info');
                
                document.getElementById('deleteAsignacionInfo').textContent = info;
                document.getElementById('deleteForm').action = `/asignaciones/${id}`;
            });
        }
        
        // Modal para iniciar asignación
        const iniciarBtns = document.querySelectorAll('.iniciar-btn');
        const iniciarModal = new bootstrap.Modal(document.getElementById('iniciarModal'));
        let asignacionId = null;
        
        iniciarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                asignacionId = this.getAttribute('data-id');
                const placa = this.getAttribute('data-placa');
                const km = this.getAttribute('data-km');
                
                document.getElementById('iniciarVehiculoPlaca').textContent = placa;
                document.getElementById('kilometrajeActual').textContent = km;
                document.getElementById('kilometraje_inicial').value = km;
                document.getElementById('kilometraje_inicial').min = km;
                
                iniciarModal.show();
            });
        });
        
        document.getElementById('confirmarIniciar').addEventListener('click', function() {
            const form = document.getElementById('iniciarForm');
            const kilometrajeInicial = document.getElementById('kilometraje_inicial').value;
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Enviar petición AJAX para iniciar la asignación
            fetch(`/asignaciones/${asignacionId}/iniciar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    kilometraje_inicial: kilometrajeInicial
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    iniciarModal.hide();
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert('Error al iniciar la asignación: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
        
        // Modal para finalizar asignación
        const finalizarBtns = document.querySelectorAll('.finalizar-btn');
        const finalizarModal = new bootstrap.Modal(document.getElementById('finalizarModal'));
        
        finalizarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                asignacionId = this.getAttribute('data-id');
                const placa = this.getAttribute('data-placa');
                const kmInicial = this.getAttribute('data-km-inicial');
                const vueltas = this.getAttribute('data-vueltas');
                
                document.getElementById('finalizarVehiculoPlaca').textContent = placa;
                document.getElementById('kilometrajeInicial').textContent = kmInicial;
                document.getElementById('kilometraje_final').value = kmInicial;
                document.getElementById('kilometraje_final').min = kmInicial;
                document.getElementById('vueltas_completas').value = vueltas;
                
                finalizarModal.show();
            });
        });
        
        document.getElementById('confirmarFinalizar').addEventListener('click', function() {
            const form = document.getElementById('finalizarForm');
            const kilometrajeFinal = document.getElementById('kilometraje_final').value;
            const vueltasCompletas = document.getElementById('vueltas_completas').value;
            const observaciones = document.getElementById('observaciones').value;
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Enviar petición AJAX para finalizar la asignación
            fetch(`/asignaciones/${asignacionId}/finalizar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    kilometraje_final: kilometrajeFinal,
                    vueltas_completas: vueltasCompletas,
                    observaciones: observaciones
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    finalizarModal.hide();
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert('Error al finalizar la asignación: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
        
        // Modal para cancelar asignación
        const cancelarBtns = document.querySelectorAll('.cancelar-btn');
        const cancelarModal = new bootstrap.Modal(document.getElementById('cancelarModal'));
        
        cancelarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                asignacionId = this.getAttribute('data-id');
                const conductor = this.getAttribute('data-conductor');
                const vehiculo = this.getAttribute('data-vehiculo');
                
                document.getElementById('cancelarConductor').textContent = conductor;
                document.getElementById('cancelarVehiculo').textContent = vehiculo;
                
                cancelarModal.show();
            });
        });
        
        document.getElementById('confirmarCancelar').addEventListener('click', function() {
            const form = document.getElementById('cancelarForm');
            const observaciones = document.getElementById('observaciones_cancelacion').value;
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Enviar petición AJAX para cancelar la asignación
            fetch(`/asignaciones/${asignacionId}/cancelar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    observaciones: observaciones
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cancelarModal.hide();
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert('Error al cancelar la asignación: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
        
        // Actualizar fecha automáticamente
        document.getElementById('fecha').addEventListener('change', function() {
            document.getElementById('fechaForm').submit();
        });
    });
</script>
@endsection