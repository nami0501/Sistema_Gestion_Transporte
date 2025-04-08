<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') - Sistema de Gestión de Transporte</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            position: relative;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: #1e1e2d;
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 15px 20px;
            background-color: #1a1a27;
            height: var(--header-height);
            display: flex;
            align-items: center;
        }
        
        .sidebar-header h3 {
            font-size: 1.2rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-content {
            padding: 15px 0;
        }
        
        .nav-link {
            color: #a2a3b7;
            padding: 10px 20px;
            transition: all 0.3s;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            color: #fff;
            background-color: #1a1a27;
            border-left-color: var(--primary-color);
        }
        
        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .nav-heading {
            color: #565674;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 20px 20px 10px;
            pointer-events: none;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--header-height);
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background-color: #fff;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            z-index: 999;
            transition: all 0.3s;
        }
        
        .header-menu {
            display: flex;
            align-items: center;
        }
        
        .dropdown-menu {
            padding: 10px 0;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .dropdown-item {
            padding: 8px 20px;
            color: #6c7293;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .dropdown-item i {
            margin-right: 10px;
            font-size: 1rem;
        }
        
        /* Dashboard Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 20px;
        }
        
        .card-header h6 {
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Utilities */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .badge {
            font-weight: 500;
            padding: 5px 8px;
            border-radius: 5px;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(45deg, #007bff, #0093ff);
        }
        
        .bg-gradient-success {
            background: linear-gradient(45deg, #28a745, #48d368);
        }
        
        .bg-gradient-info {
            background: linear-gradient(45deg, #17a2b8, #2ccce4);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(45deg, #ffc107, #ffce3a);
        }
        
        .bg-gradient-danger {
            background: linear-gradient(45deg, #dc3545, #ff5465);
        }
        
        .bg-gradient-secondary {
            background: linear-gradient(45deg, #6c757d, #868e96);
        }
        
        .bg-gradient-dark {
            background: linear-gradient(45deg, #343a40, #4b545c);
        }
        
        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header {
                left: 0;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
        }
        
        /* Toggle button */
        .sidebar-toggle {
            display: none;
            background: transparent;
            border: none;
            color: #6c7293;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Tables */
        .table {
            font-size: 0.95rem;
        }
        
        .table thead th {
            font-weight: 600;
            color: #6c7293;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table-responsive {
            padding: 0 1px;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Sistema de Transporte</h3>
        </div>
        
        <div class="sidebar-content">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.panel') }}" class="nav-link {{ request()->routeIs('admin.panel') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Panel de Control
                    </a>
                </li>
                
                <div class="nav-heading">Gestión de Sistema</div>
                
                <li class="nav-item">
                    <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i> Roles
                    </a>
                </li>
                
                <div class="nav-heading">Gestión de Transporte</div>
                
                <li class="nav-item">
                    <a href="{{ route('vehiculos.index') }}" class="nav-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i> Vehículos
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('lineas.index') }}" class="nav-link {{ request()->routeIs('lineas.*') ? 'active' : '' }}">
                        <i class="bi bi-signpost-split"></i> Líneas
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('estaciones.index') }}" class="nav-link {{ request()->routeIs('estaciones.*') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt"></i> Estaciones
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('asignaciones.index') }}" class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i> Asignaciones
                    </a>
                </li>
                
                <div class="nav-heading">Operaciones</div>
                
                <li class="nav-item">
                    <a href="{{ route('operador.monitoreo') }}" class="nav-link {{ request()->routeIs('monitoreo.*') ? 'active' : '' }}">
                        <i class="bi bi-map"></i> Monitoreo en vivo
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('incidentes.index') }}" class="nav-link {{ request()->routeIs('incidentes.*') ? 'active' : '' }}">
                        <i class="bi bi-exclamation-triangle"></i> Incidentes
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('mantenimientos.index') }}" class="nav-link {{ request()->routeIs('mantenimientos.*') ? 'active' : '' }}">
                        <i class="bi bi-tools"></i> Mantenimientos
                    </a>
                </li>
                
                <div class="nav-heading">Finanzas</div>
                
                <li class="nav-item">
                    <a href="{{ route('tarifas.index') }}" class="nav-link {{ request()->routeIs('tarifas.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin"></i> Tarifas
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('tarjetas.index') }}" class="nav-link {{ request()->routeIs('tarjetas.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i> Tarjetas
                    </a>
                </li>
                
                <div class="nav-heading">Reportes</div>
                
                <li class="nav-item">
                    <a href="{{ route('reportes.pasajeros') }}" class="nav-link {{ request()->routeIs('reportes.pasajeros') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Pasajeros
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('reportes.operaciones') }}" class="nav-link {{ request()->routeIs('reportes.operaciones') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i> Operaciones
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('reportes.finanzas') }}" class="nav-link {{ request()->routeIs('reportes.finanzas') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i> Finanzas
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Header -->
    <header class="header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="header-menu">
            <div class="dropdown">
                <a class="dropdown-toggle d-flex align-items-center" href="#" role="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell position-relative">
                        @if(isset($notificacionesPendientes) && $notificacionesPendientes > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $notificacionesPendientes > 9 ? '9+' : $notificacionesPendientes }}
                        </span>
                        @endif
                    </i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                    <li><h6 class="dropdown-header">Notificaciones</h6></li>
                    @if(isset($notificaciones) && count($notificaciones) > 0)
                        @foreach($notificaciones as $notificacion)
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ $notificacion['url'] }}">
                                <div class="me-3">
                                    <div class="icon-circle bg-{{ $notificacion['tipo'] }}">
                                        <i class="bi bi-{{ $notificacion['icono'] }} text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">{{ $notificacion['fecha'] }}</div>
                                    <span class="{{ $notificacion['leido'] ? '' : 'fw-bold' }}">{{ $notificacion['mensaje'] }}</span>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    @else
                        <li><a class="dropdown-item" href="#">No hay notificaciones nuevas</a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center small text-gray-500" href="{{ route('operador.monitoreo') }}">Ver todas las notificaciones</a></li>
                </ul>
            </div>
            
            <div class="dropdown ms-3">
                <a class="dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-none d-md-inline me-2">{{ Auth::user()->nombre }}</span>
                    <img class="avatar" src="{{ asset('images/avatar.jpg') }}" alt="Avatar">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('operador.monitoreo') }}">
                            <i class="bi bi-person"></i> Mi Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('operador.monitoreo') }}">
                            <i class="bi bi-gear"></i> Configuración
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('operador.monitoreo') }}">
                            <i class="bi bi-list-check"></i> Actividad
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Sidebar Toggle Function
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const header = document.querySelector('.header');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    if (window.innerWidth > 992) {
                        if (sidebar.classList.contains('active')) {
                            mainContent.style.marginLeft = '0';
                            header.style.left = '0';
                        } else {
                            mainContent.style.marginLeft = 'var(--sidebar-width)';
                            header.style.left = 'var(--sidebar-width)';
                        }
                    }
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 992 && 
                    !sidebar.contains(event.target) && 
                    !sidebarToggle.contains(event.target) && 
                    sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                    mainContent.style.marginLeft = 'var(--sidebar-width)';
                    header.style.left = 'var(--sidebar-width)';
                } else {
                    mainContent.style.marginLeft = '0';
                    header.style.left = '0';
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>