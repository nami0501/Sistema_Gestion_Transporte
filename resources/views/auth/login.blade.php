<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Gestión de Transporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            width: 100%;
        }

        .card {
            background-color: #1e1e1e;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background-color: #1e1e1e;
            border-bottom: 1px solid #333;
            padding: 25px 20px;
            text-align: center;
        }

        .card-body {
            padding: 30px;
        }

        .login-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.8rem;
            background: linear-gradient(90deg, #007bff, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-subtitle {
            color: #85c1e9 ;
            font-size: 0.9rem;
        }

        .form-label {
            color: #85c1e9 ;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            background-color: #2d2d2d;
            border: 1px solid #444;
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: #333;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            color: white;
        }

        .input-group-text {
            background-color: #2d2d2d;
            border: 1px solid #444;
            color: #888;
            border-radius: 8px 0 0 8px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #007bff, #00c6ff);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #0069d9, #00b4e6);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .logo {
            max-width: 80px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(0, 123, 255, 0.5));
            border-radius: 50%;
            object-fit: cover;
        }


        .card-footer {
            background-color: #1e1e1e;
            border-top: 1px solid #333;
            color: #aaaaaa;
        }

        .card-footer a {
            color: #007bff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .card-footer a:hover {
            color: #00c6ff;
            text-decoration: underline;
        }

        .image-container {
            height: 100%;
            background-position: center;
            background-size: cover;
            border-radius: 0 15px 15px 0;
            position: relative;
            overflow: hidden;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.7), rgba(0, 198, 255, 0.4));
            mix-blend-mode: hard-light;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
        }

        @media (max-width: 992px) {
            .image-container {
                min-height: 200px;
                border-radius: 0 0 15px 15px;
                background-size: 150%;
                background-position: center;
                background-repeat: no-repeat;
            }
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #4cd964;
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 8px;
            margin-bottom: 20px;
        }

    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container login-container">
        <!-- Colocar el mensaje aquí dentro del contenedor -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="card-header">
                                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
                                <h1 class="login-title">Sistema de Gestión de Transporte</h1>
                                <p class="login-subtitle mb-0">Inicie sesión para acceder al sistema</p>
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

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="nombre_usuario" class="form-label">Usuario o Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required autofocus placeholder="Ingrese su usuario o email">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required placeholder="Ingrese su contraseña">
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer text-center py-3">
                                <p class="mb-0">¿No tiene una cuenta? <a href="{{ route('register') }}">Regístrese</a></p>
                            </div>
                        </div>
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="image-container" style="background-image: url('{{ asset('images/bus.jpg') }}');">
                                <div class="image-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <script>
    // Hacer que la alerta desaparezca automáticamente
    document.addEventListener('DOMContentLoaded', function() {
        var successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(function() {
                var alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                alert.close();
            }, 5000); // 5000 ms = 5 segundos
        }
    });
</script>
</body>
</html>