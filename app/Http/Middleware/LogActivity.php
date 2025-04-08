<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Registrar la actividad solo si el usuario está autenticado
        if (Auth::check()) {
            $usuario = Auth::user();
            
            // Datos para el registro de actividad
            $detalles = [
                'url' => $request->fullUrl(),
                'método' => $request->method(),
                'agente' => $request->header('User-Agent'),
            ];

            // Determinar el módulo y la acción
            $ruta = $request->route()->getName() ?? $request->path();
            $modulo = 'Sistema';
            $accion = 'Acceso';

            // Identificar módulo y acción basado en la ruta
            if (strpos($ruta, 'login') !== false) {
                $modulo = 'Autenticación';
                $accion = 'Login';
            } elseif (strpos($ruta, 'logout') !== false) {
                $modulo = 'Autenticación';
                $accion = 'Logout';
            } elseif (strpos($ruta, 'dashboard') !== false) {
                $modulo = 'Dashboard';
                $accion = 'Acceso';
            }

            // Registrar en logs_sistema
            DB::table('logs_sistema')->insert([
                'id_usuario' => $usuario->id_usuario,
                'modulo' => $modulo,
                'accion' => $accion,
                'detalles' => json_encode($detalles),
                'ip_direccion' => $request->ip(),
                'fecha_hora' => now()
            ]);
        }

        return $response;
    }
}