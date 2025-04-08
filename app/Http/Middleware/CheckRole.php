<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Obtener el rol del usuario
        $rol = $user->rol;
        
        // Si no se solicita un rol específico o el usuario tiene el rol solicitado
        if (empty($roles) || in_array($rol->nombre, $roles)) {
            return $next($request);
        }
        
        // Redirigir según el rol del usuario
        switch ($rol->nombre) {
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
                return redirect('/');
        }
    }
}