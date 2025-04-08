@extends('layouts.admin')

@section('title', 'Panel de Control - Administrador')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Bienvenido al Panel de Control</h6>
                </div>
                <div class="card-body">
                    <p>Bienvenido, {{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}. Tienes acceso completo al sistema como Administrador.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Estadísticas Generales -->
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Vehículos Activos</p>
                                <h5 class="font-weight-bolder">{{ $vehiculosActivos }}</h5>
                                <p class="mb-0">
                                    de un total de {{ $totalVehiculos }}
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="bi bi-bus-front text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Usuarios Registrados</p>
                                <h5 class="font-weight-bolder">{{ $totalUsuarios }}</h5>
                                <p class="mb-0">
                                    {{ $usuariosUltimaSemana }} nuevos esta semana
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="bi bi-people text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Incidentes Pendientes</p>
                                <h5 class="font-weight-bolder">{{ $incidentesPendientes }}</h5>
                                <p class="mb-0">
                                    <span class="text-danger text-sm font-weight-bolder">{{ $incidentesCriticos }}</span> críticos
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="bi bi-exclamation-triangle text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Mantenimientos Programados</p>
                                <h5 class="font-weight-bolder">{{ $mantenimientosProgramados }}</h5>
                                <p class="mb-0">
                                    para los próximos 7 días
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                <i class="bi bi-tools text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Próximas Asignaciones -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Incidentes Recientes</h6>
                            <p class="text-sm mb-0">
                                <i class="bi bi-exclamation-circle text-warning" aria-hidden="true"></i>
                                <span class="font-weight-bold ms-1">Últimas 24 horas</span>
                            </p>
                        </div>
                        <div class="col-lg-6 col-5 text-end">
                            <a href="{{ route('incidentes.index') }}" class="btn btn-sm btn-outline-danger">Ver todos</a>
                        </div> class="col-lg-6 col-7">
                            <h6>Próximas Asignaciones</h6>
                            <p class="text-sm mb-0">
                                <i class="bi bi-calendar-check text-info" aria-hidden="true"></i>
                                <span class="font-weight-bold ms-1">Hoy y mañana</span>
                            </p>
                        </div>
                        <div class="col-lg-6 col-5 text-end">
                            <a href="{{ route('asignaciones.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Conductor</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Vehículo</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Línea</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Horario</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proximasAsignaciones as $asignacion)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $asignacion->conductor }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $asignacion->placa }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $asignacion->tipo_vehiculo }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $asignacion->linea }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ date('H:i', strtotime($asignacion->hora_inicio)) }} - {{ date('H:i', strtotime($asignacion->hora_fin)) }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ date('d/m/Y', strtotime($asignacion->fecha)) }}</p>
                                    </td>
                                    <td class="align-middle text-sm">
                                        <span class="badge badge-sm bg-gradient-{{ $asignacion->estado == 'Programado' ? 'info' : ($asignacion->estado == 'En curso' ? 'success' : 'secondary') }}">{{ $asignacion->estado }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Incidentes Recientes -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div