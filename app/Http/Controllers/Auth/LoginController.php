<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'nombre_usuario';
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->input($this->username()),
            'password' => $request->input('password'),
            'estado' => 'Activo'
        ];
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Actualizar último acceso
        $user->ultimo_acceso = Carbon::now();
        $user->intentos_fallidos = 0;
        $user->save();

        // Registrar log de actividad
        \DB::table('logs_sistema')->insert([
            'id_usuario' => $user->id_usuario,
            'modulo' => 'Autenticación',
            'accion' => 'Inicio de Sesión',
            'detalles' => 'Inicio de sesión exitoso',
            'ip_direccion' => $request->ip(),
            'fecha_hora' => Carbon::now()
        ]);

        // Redirigir según el rol del usuario
        if ($user->rol) {
            switch ($user->rol->nombre) {
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
                    return redirect()->route('dashboard');
            }
        }
        return redirect()->route('dashboard');
    }

    /**
         * Get the guard to be used during authentication.
         *
         * @return \Illuminate\Contracts\Auth\StatefulGuard
         */
        protected function guard()
        {
            return Auth::guard('web'); // Asegúrate que este guard esté configurado para usar el modelo Usuario
        }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return 'password';
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // Buscar el usuario
        $user = \App\Models\Usuario::where($this->username(), $request->input($this->username()))->first();

        // Registrar intento fallido
        if ($user && $user->estado == 'Activo') {
            // Incrementar contador de intentos fallidos
            if (!$this->attemptLogin($request)) {
                $user->intentos_fallidos = $user->intentos_fallidos + 1;
                
                // Bloquear usuario después de 5 intentos fallidos
                if ($user->intentos_fallidos >= 5) {
                    $user->estado = 'Bloqueado';
                    
                    // Registrar bloqueo en log
                    \DB::table('logs_sistema')->insert([
                        'id_usuario' => $user->id_usuario,
                        'modulo' => 'Autenticación',
                        'accion' => 'Bloqueo de Cuenta',
                        'detalles' => 'Cuenta bloqueada después de 5 intentos fallidos',
                        'ip_direccion' => $request->ip(),
                        'fecha_hora' => Carbon::now()
                    ]);
                }
                
                $user->save();
            }
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Registrar log de cierre de sesión si hay usuario autenticado
        if (Auth::check()) {
            \DB::table('logs_sistema')->insert([
                'id_usuario' => Auth::user()->id_usuario,
                'modulo' => 'Autenticación',
                'accion' => 'Cierre de Sesión',
                'detalles' => 'Cierre de sesión manual',
                'ip_direccion' => $request->ip(),
                'fecha_hora' => Carbon::now()
            ]);
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('login')->with('message', 'Has cerrado sesión correctamente.');
    }
}