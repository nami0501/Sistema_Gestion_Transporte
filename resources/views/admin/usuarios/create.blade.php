<!-- resources/views/admin/usuarios/create.blade.php -->
@extends('layouts.admin')

@section('title', 'Crear Usuario')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Crear Nuevo Usuario</h6>
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

                    <form action="{{ route('usuarios.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre_usuario">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="contrasena">Contraseña</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="dni">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" value="{{ old('dni') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fecha_ingreso">Fecha de Ingreso</label>
                                    <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="{{ old('direccion') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_rol">Rol</label>
                                    <select class="form-control" id="id_rol" name="id_rol" required>
                                        <option value="">Seleccione un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="es_conductor" name="es_conductor" {{ old('es_conductor') ? 'checked' : '' }}>
                            <label class="form-check-label" for="es_conductor">Es Conductor</label>
                        </div>

                        <div id="info_conductor" style="{{ old('es_conductor') ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="numero_licencia">Número de Licencia</label>
                                        <input type="text" class="form-control" id="numero_licencia" name="numero_licencia" value="{{ old('numero_licencia') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tipo_licencia">Tipo de Licencia</label>
                                        <select class="form-control" id="tipo_licencia" name="tipo_licencia">
                                            <option value="">Seleccione un tipo</option>
                                            <option value="A-I" {{ old('tipo_licencia') == 'A-I' ? 'selected' : '' }}>A-I</option>
                                            <option value="A-II" {{ old('tipo_licencia') == 'A-II' ? 'selected' : '' }}>A-II</option>
                                            <option value="A-III" {{ old('tipo_licencia') == 'A-III' ? 'selected' : '' }}>A-III</option>
                                            <option value="B-I" {{ old('tipo_licencia') == 'B-I' ? 'selected' : '' }}>B-I</option>
                                            <option value="B-II" {{ old('tipo_licencia') == 'B-II' ? 'selected' : '' }}>B-II</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
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