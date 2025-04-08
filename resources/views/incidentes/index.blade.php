@extends('layouts.admin')

@section('title', 'Gestión de Incidentes')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Incidentes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Incidentes</li>
    </ol>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total</div>
                            <div class="h3 mb-0">{{ $estadisticas['total'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Reportados</div>
                            <div class="h3 mb-0">{{ $estadisticas['reportados'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">En Atención</div>
                            <div class="h3 mb-0">{{ $estadisticas['en_atencion'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Resueltos</div>
                            <div class="h3 mb-0">{{ $estadisticas['resueltos'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Escalados</div>
                            <div class="h3 mb-0">{{ $estadisticas['escalados'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-level-up-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Críticos</div>
                            <div class="h3 mb-0">{{ $estadisticas['criticos'] }}</div>
                        </div>
                        <div class="fs-2">
                            <i class="fas fa-radiation"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-exclamation-triangle me-1"></i>
                Listado de Incidentes
            </div>
            <a href="{{ route('incidentes.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nuevo Incidente
            </a>
        </div>
        <div class="card-body">
            <!-- Filtros de fecha -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="{{ route('incidentes.index') }}" method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label for="fecha" class="form-label">Fecha específica</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $fecha ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('incidentes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Filtros adicionales -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="{{ route('incidentes.index') }}" method="GET" class="row g-3">
                        @if($fecha)
                            <input type="hidden" name="fecha" value="{{ $fecha }}">
                        @else
                            <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                            <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                        @endif

                        <div class="col-md-3">
                            <label for="tipo_incidente" class="form-label">Tipo</label>
                            <select name="tipo_incidente" id="tipo_incidente" class="form-select">
                                <option value="">Todos los tipos</option>
                                @foreach($tipos as $tipoItem)
                                <option value="{{ $tipoItem }}" {{ $tipo == $tipoItem ? 'selected' : '' }}>{{ $tipoItem }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <label for="impacto" class="form-label">Impacto</label>
                            <select name="impacto" id="impacto" class="form-select">
                                <option value="">Todos los niveles</option>
                                @foreach($impactos as $impactoItem)
                                <option value="{{ $impactoItem }}" {{ $impacto == $impactoItem ? 'selected' : '' }}>{{ $impactoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de incidentes -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Línea/Vehículo</th>
                            <th>Conductor/Estación</th>
                            <th>Impacto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidentes as $incidente)
                        <tr>
                            <td>{{ $incidente->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                $tipoIcono = 'fas fa-question-circle';
                                $tipoColor = 'secondary';
                                
                                switch($incidente->tipo_incidente) {
                                    case 'Accidente': 
                                        $tipoIcono = 'fas fa-car-crash'; 
                                        $tipoColor = 'danger';
                                        break;
                                    case 'Avería': 
                                        $tipoIcono = 'fas fa-tools'; 
                                        $tipoColor = 'warning';
                                        break;
                                    case 'Retraso': 
                                        $tipoIcono = 'fas fa-clock'; 
                                        $tipoColor = 'info';
                                        break;
                                    case 'Seguridad': 
                                        $tipoIcono = 'fas fa-shield-alt'; 
                                        $tipoColor = 'primary';
                                        break;
                                }
                                @endphp
                                <span class="badge bg-{{ $tipoColor }}">
                                    <i class="{{ $tipoIcono }} me-1"></i> {{ $incidente->tipo_incidente }}
                                </span>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($incidente->descripcion, 50) }}</td>
                            <td>
                                @if($incidente->asignacion && $incidente->asignacion->linea)
                                <span class="badge rounded-pill" style="background-color: {{ $incidente->asignacion->linea->color }}">
                                    {{ $incidente->asignacion->linea->nombre }}
                                </span><br>
                                @endif
                                
                                @if($incidente->asignacion && $incidente->asignacion->vehiculo)
                                <small class="text-muted">{{ $incidente->asignacion->vehiculo->placa }}</small>
                                @endif
                            </td>
                            <td>
                                @if($incidente->asignacion && $incidente->asignacion->usuario)
                                {{ $incidente->asignacion->usuario->nombre }} {{ $incidente->asignacion->usuario->apellidos }}<br>
                                @endif
                                
                                @if($incidente->estacion)
                                <small class="text-muted">Est. {{ $incidente->estacion->nombre }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                $impactoClass = 'bg-secondary';
                                switch($incidente->impacto) {
                                    case 'Bajo': $impactoClass = 'bg-success'; break;
                                    case 'Medio': $impactoClass = 'bg-info'; break;
                                    case 'Alto': $impactoClass = 'bg-warning text-dark'; break;
                                    case 'Crítico': $impactoClass = 'bg-danger'; break;
                                }
                                @endphp
                                <span class="badge {{ $impactoClass }}">{{ $incidente->impacto }}</span>
                            </td>
                            <td>
                                @php
                                $estadoClass = 'bg-secondary';
                                switch($incidente->estado) {
                                    case 'Reportado': $estadoClass = 'bg-warning text-dark'; break;
                                    case 'En atención': $estadoClass = 'bg-info'; break;
                                    case 'Resuelto': $estadoClass = 'bg-success'; break;
                                    case 'Escalado': $estadoClass = 'bg-danger'; break;
                                }
                                @endphp
                                <span class="badge {{ $estadoClass }}">{{ $incidente->estado }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('incidentes.show', $incidente->id_incidente) }}" class="btn btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($incidente->estado != 'Resuelto')
                                    <a href="{{ route('incidentes.edit', $incidente->id_incidente) }}" class="btn btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    @if($incidente->estado == 'Reportado')
                                    <button type="button" class="btn btn-success cambiar-estado-btn" 
                                        data-id="{{ $incidente->id_incidente }}"
                                        data-estado="En atención"
                                        title="Marcar en atención">
                                        <i class="fas fa-tools"></i>
                                    </button>
                                    @endif
                                    
                                    @if($incidente->estado == 'En atención')
                                    <button type="button" class="btn btn-success resolver-btn" 
                                        data-id="{{ $incidente->id_incidente }}"
                                        data-tipo="{{ $incidente->tipo_incidente }}"
                                        title="Resolver incidente">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    @endif
                                    
                                    @if($incidente->estado == 'Reportado')
                                    <button type="button" class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal" 
                                        data-id="{{ $incidente->id_incidente }}"
                                        data-tipo="{{ $incidente->tipo_incidente }}"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay incidentes registrados en este período</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-3">
                {{ $incidentes->appends(request()->query())->links() }}
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
                ¿Estás seguro de eliminar el incidente de tipo <span id="deleteTipoIncidente" class="fw-bold"></span>?
                <p class="text-danger mt-2"><small>Esta acción no se puede deshacer.</small></p>
                <p><small>Solo se pueden eliminar incidentes en estado "Reportado".</small></p>
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

<!-- Modal para cambiar estado -->
<div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-labelledby="cambiarEstadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="cambiarEstadoModalLabel">Cambiar Estado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de cambiar el estado del incidente a <span id="nuevoEstado" class="fw-bold"></span>?</p>
                
                <form id="cambiarEstadoForm">
                    <div class="mb-3">
                        <label for="resolucion" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="resolucion" name="resolucion" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="confirmarCambioEstado">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para resolver incidente -->
<div class="modal fade" id="resolverModal" tabindex="-1" aria-labelledby="resolverModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="resolverModalLabel">Resolver Incidente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de marcar como resuelto el incidente de tipo <span id="resolverTipoIncidente" class="fw-bold"></span>?</p>
                
                <form id="resolverForm">
                    <div class="mb-3">
                        <label for="resolucion_texto" class="form-label">Resolución</label>
                        <textarea class="form-control" id="resolucion_texto" name="resolucion" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarResolver">Confirmar Resolución</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar cambio de fechas
        const fechaInput = document.getElementById('fecha');
        if (fechaInput) {
            fechaInput.addEventListener('change', function() {
                if (this.value) {
                    document.getElementById('fecha_inicio').disabled = true;
                    document.getElementById('fecha_fin').disabled = true;
                } else {
                    document.getElementById('fecha_inicio').disabled = false;
                    document.getElementById('fecha_fin').disabled = false;
                }
            });
            
            // Inicializar estado
            if (fechaInput.value) {
                document.getElementById('fecha_inicio').disabled = true;
                document.getElementById('fecha_fin').disabled = true;
            }
        }
        
        // Modal para eliminar
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const tipo = button.getAttribute('data-tipo');
                
                document.getElementById('deleteTipoIncidente').textContent = tipo;
                document.getElementById('deleteForm').action = `/incidentes/${id}`;
            });
        }
        
        // Modal para cambiar estado
        const cambiarEstadoBtns = document.querySelectorAll('.cambiar-estado-btn');
        const cambiarEstadoModal = new bootstrap.Modal(document.getElementById('cambiarEstadoModal'));
        let incidenteId = null;
        let nuevoEstado = null;
        
        cambiarEstadoBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                incidenteId = this.getAttribute('data-id');
                nuevoEstado = this.getAttribute('data-estado');
                
                document.getElementById('nuevoEstado').textContent = nuevoEstado;
                
                cambiarEstadoModal.show();
            });
        });
        
        document.getElementById('confirmarCambioEstado').addEventListener('click', function() {
            const resolucion = document.getElementById('resolucion').value;
            
            // Enviar petición AJAX para cambiar el estado
            fetch(`/incidentes/${incidenteId}/actualizar-estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    estado: nuevoEstado,
                    resolucion: resolucion
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cambiarEstadoModal.hide();
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert('Error al cambiar el estado: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
        
        // Modal para resolver incidente
        const resolverBtns = document.querySelectorAll('.resolver-btn');
        const resolverModal = new bootstrap.Modal(document.getElementById('resolverModal'));
        
        resolverBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                incidenteId = this.getAttribute('data-id');
                const tipo = this.getAttribute('data-tipo');
                
                document.getElementById('resolverTipoIncidente').textContent = tipo;
                
                resolverModal.show();
            });
        });
        
        document.getElementById('confirmarResolver').addEventListener('click', function() {
            const form = document.getElementById('resolverForm');
            const resolucion = document.getElementById('resolucion_texto').value;
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Enviar petición AJAX para resolver el incidente
            fetch(`/incidentes/${incidenteId}/actualizar-estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    estado: 'Resuelto',
                    resolucion: resolucion
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resolverModal.hide();
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert('Error al resolver el incidente: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
    });
</script>
@endsection