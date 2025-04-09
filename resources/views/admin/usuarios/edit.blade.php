@extends('layouts.admin')

@section('title', 'Editar Usuario')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Editar Usuario</h6>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('usuarios.update', $usuario->id_usuario) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre_usuario">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="contrasena">Contraseña (dejar en blanco para mantener la actual)</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="dni">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" value="{{ old('dni', $usuario->dni) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ old('apellidos', $usuario->apellidos) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fecha_ingreso">Fecha de Ingreso</label>
                                    <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', $usuario->fecha_ingreso->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="{{ old('direccion', $usuario->direccion) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="id_rol">Rol</label>
                                    <select class="form-control" id="id_rol" name="id_rol" required>
                                        <option value="">Seleccione un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id_rol }}" {{ (old('id_rol', $usuario->id_rol) == $rol->id_rol) ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="estado">Estado</label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="Activo" {{ (old('estado', $usuario->estado) == 'Activo') ? 'selected' : '' }}>Activo</option>
                                        <option value="Bloqueado" {{ (old('estado', $usuario->estado) == 'Bloqueado') ? 'selected' : '' }}>Bloqueado</option>
                                        <option value="Inactivo" {{ (old('estado', $usuario->estado) == 'Inactivo') ? 'selected' : '' }}>Inactivo</option>
                                        <option value="De vacaciones" {{ (old('estado', $usuario->estado) == 'De vacaciones') ? 'selected' : '' }}>De vacaciones</option>
                                        <option value="Con licencia" {{ (old('estado', $usuario->estado) == 'Con licencia') ? 'selected' : '' }}>Con licencia</option>
                                        <option value="Suspendido" {{ (old('estado', $usuario->estado) == 'Suspendido') ? 'selected' : '' }}>Suspendido</option>
                                        <option value="Retirado" {{ (old('estado', $usuario->estado) == 'Retirado') ? 'selected' : '' }}>Retirado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="es_conductor" name="es_conductor" {{ old('es_conductor', $usuario->es_conductor) ? 'checked' : '' }}>
                            <label class="form-check-label" for="es_conductor">Es Conductor</label>
                        </div>

                        <div id="info_conductor" style="{{ old('es_conductor', $usuario->es_conductor) ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="numero_licencia">Número de Licencia</label>
                                        <input type="text" class="form-control" id="numero_licencia" name="numero_licencia" value="{{ old('numero_licencia', $usuario->numero_licencia) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tipo_licencia">Tipo de Licencia</label>
                                        <select class="form-control" id="tipo_licencia" name="tipo_licencia">
                                        <option value="">Seleccione un tipo</option>
                                            <option value="A-I" {{ old('tipo_licencia', $usuario->tipo_licencia) == 'A-I' ? 'selected' : '' }}>A-I</option>
                                            <option value="A-II" {{ old('tipo_licencia', $usuario->tipo_licencia) == 'A-II' ? 'selected' : '' }}>A-II</option>
                                            <option value="A-III" {{ old('tipo_licencia', $usuario->tipo_licencia) == 'A-III' ? 'selected' : '' }}>A-III</option>
                                            <option value="B-I" {{ old('tipo_licencia', $usuario->tipo_licencia) == 'B-I' ? 'selected' : '' }}>B-I</option>
                                            <option value="B-II" {{ old('tipo_licencia', $usuario->tipo_licencia) == 'B-II' ? 'selected' : '' }}>B-II</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const conductorCheckbox = document.getElementById('es_conductor');
        const infoConductor = document.getElementById('info_conductor');
        
        conductorCheckbox.addEventListener('change', function() {
            if (this.checked) {
                infoConductor.style.display = 'block';
            } else {
                infoConductor.style.display = 'none';
            }
        });
    });
</script>
@endsection