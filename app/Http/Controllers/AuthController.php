<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar intento de login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nombre_usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        // Intentar autenticar con nombre de usuario o email
        // Intentar autenticar sin la opción "remember"
        if (Auth::attempt([
            'nombre_usuario' => $credentials['nombre_usuario'], 
            'password' => $credentials['password']
        ], false)) { // El segundo parámetro "false" explícitamente desactiva la funcionalidad de recordar
            $request->session()->regenerate();
            
            // Incrementar contador de intentos fallidos a 0
            $usuario = Auth::user();
            $usuario->intentos_fallidos = 0;
            $usuario->ultimo_acceso = now();
            $usuario->save();
            
            return redirect()->intended('dashboard');
        }

        // Si la autenticación falla, registrar intento fallido
        $usuario = Usuario::where('nombre_usuario', $credentials['nombre_usuario'])
            ->orWhere('email', $credentials['nombre_usuario'])
            ->first();
            
        if ($usuario) {
            $usuario->intentos_fallidos += 1;
            
            // Bloquear cuenta si hay demasiados intentos (opcional)
            if ($usuario->intentos_fallidos >= 5) {
                $usuario->estado = 'Bloqueado';
            }
            
            $usuario->save();
        }

        return back()->withErrors([
            'login' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput($request->only('nombre_usuario'));
    }

    /**
     * Mostrar formulario de registro
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $roles = Rol::where('nombre', '!=', 'Administrador')->get();
        return view('auth.register', compact('roles'));
    }

    /**
     * Registrar nuevo usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|string|max:50|unique:usuarios',
            'password' => 'required|string|min:8|confirmed',
            'dni' => 'required|string|max:20|unique:usuarios',
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|email|max:100|unique:usuarios',
            'id_rol' => 'required|exists:roles,id_rol',
            'es_conductor' => 'boolean',
            'numero_licencia' => 'nullable|string|max:50|unique:usuarios',
            'tipo_licencia' => 'nullable|string|max:20',
        ]);

        $usuario = Usuario::create([
            'nombre_usuario' => $request->nombre_usuario,
            'contrasena' => Hash::make($request->password),
            'dni' => $request->dni,
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'id_rol' => $request->id_rol,
            'es_conductor' => $request->es_conductor ?? false,
            'numero_licencia' => $request->numero_licencia,
            'tipo_licencia' => $request->tipo_licencia,
            'fecha_ingreso' => now(),
            'estado' => 'Activo',
        ]);

        Auth::login($usuario);

        return redirect()->route('dashboard');
    }

    /**
     * Cerrar sesión
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}