<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    /**
     * Redirecciona al usuario según su rol.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        if (!$user->rol) {
            Auth::logout();
            return redirect('/login')->with('error', 'No tienes un rol asignado. Contacta al administrador.');
        }
        
        switch ($user->rol->nombre) {
            case 'Administrador':
                return redirect()->route('admin.dashboard');
            case 'Supervisor':
                return redirect()->route('supervisor.dashboard');
            case 'Operador':
                return redirect()->route('operador.dashboard');
            case 'Conductor':
                return redirect()->route('conductor.dashboard');
            case 'Consulta':
                return redirect()->route('consulta.dashboard');
            default:
                return redirect('/');
        }
    }
}