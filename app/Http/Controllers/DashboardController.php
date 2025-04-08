<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class DashboardController extends Controller
{
    /**
     * Redirigir al usuario al dashboard correspondiente según su rol.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuario = Auth::user();

        if (!$usuario->rol) {
            return redirect()->route('login')->with('error', 'No tienes un rol asignado en el sistema.');
        }

        switch ($usuario->rol->nombre) {
            case 'Administrador':
                return redirect()->route('admin.panel');
            case 'Supervisor':
                return redirect()->route('supervisor.supervision');
            case 'Operador':
                return redirect()->route('operador.monitoreo');
            case 'Conductor':
                return redirect()->route('conductor.horarios');
            case 'Consulta':
                return redirect()->route('consulta.informacion');
            default:
                return redirect()->route('login')->with('error', 'Tu rol no tiene acceso al sistema.');
        }
    }

    /**
     * Mostrar perfil del usuario.
     *
     * @return \Illuminate\Http\Response
     */
    public function perfil()
    {
        $usuario = Auth::user();
        return view('perfil.index', compact('usuario'));
    }

    /**
     * Mostrar configuración del usuario.
     *
     * @return \Illuminate\Http\Response
     */
    public function configuracion()
    {
        $usuario = Auth::user();
        return view('perfil.configuracion', compact('usuario'));
    }
}