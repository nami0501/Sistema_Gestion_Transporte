<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Gestión de Transporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #edbb99;
        }
        .register-container {
            margin-top: 50px;
            margin-bottom: 50px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .card-header {
            background-color: #f5cba7;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(45deg, #e59866, #fad7a0);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #e59866, #fad7a0);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }
        .logo {
            max-width: 100px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(248, 196, 113));
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
                        <h3>Sistema de Gestión de Transporte</h3>
                        <p class="mb-0">Registro de nuevo usuario</p>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required autofocus placeholder="Ingrese nombre de usuario">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="Ingrese su correo electrónico">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Ingrese su contraseña">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirme su contraseña">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required placeholder="Ingrese su nombre">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required placeholder="Ingrese sus apellidos">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI/Identificación</label>
                                    <input type="text" class="form-control" id="dni" name="dni" value="{{ old('dni') }}" required placeholder="Ingrese su identificación">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" value="{{ old('direccion') }}" required placeholder="Ingrese su dirección completa">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required placeholder="Ingrese su número de teléfono">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="id_rol" class="form-label">Rol</label>
                                    <select class="form-select" id="id_rol" name="id_rol" required>
                                        <option value="">Seleccione un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="es_conductor" name="es_conductor" {{ old('es_conductor') ? 'checked' : '' }}>
                                <label class="form-check-label" for="es_conductor">
                                    Soy conductor
                                </label>
                            </div>

                            <div id="datos_conductor" class="row" style="{{ old('es_conductor') ? '' : 'display: none;' }}">
                                <div class="col-md-6 mb-3">
                                    <label for="numero_licencia" class="form-label">Número de Licencia</label>
                                    <input type="text" class="form-control" id="numero_licencia" name="numero_licencia" value="{{ old('numero_licencia') }}" placeholder="Ingrese su número de licencia">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_licencia" class="form-label">Tipo de Licencia</label>
                                    <input type="text" class="form-control" id="tipo_licencia" name="tipo_licencia" value="{{ old('tipo_licencia') }}" placeholder="Ingrese el tipo de licencia">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">Registrarse</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3 bg-light border-0 rounded-bottom">
                        <p class="mb-0">¿Ya tiene una cuenta? <a href="{{ route('login') }}">Inicie sesión</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const esConductorCheckbox = document.getElementById('es_conductor');
            const datosConductorDiv = document.getElementById('datos_conductor');
            
            esConductorCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    datosConductorDiv.style.display = 'flex';
                } else {
                    datosConductorDiv.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>