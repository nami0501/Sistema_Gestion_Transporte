<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Muestra una lista de los usuarios.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtener todos los usuarios con sus roles
        $usuarios = Usuario::with('rol')->paginate(10);
        
        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener todos los roles disponibles
        $roles = Rol::all();
        
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre_usuario' => 'required|unique:usuarios,nombre_usuario',
            'contrasena' => 'required|min:6',
            'dni' => 'required|unique:usuarios,dni',
            'nombre' => 'required',
            'apellidos' => 'required',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'required',
            'telefono' => 'required',
            'email' => 'required|email|unique:usuarios,email',
            'id_rol' => 'required|exists:roles,id_rol',
            'fecha_ingreso' => 'required|date'
        ]);

        // Crear el nuevo usuario
        $usuario = new Usuario();
        $usuario->nombre_usuario = $request->nombre_usuario;
        $usuario->contrasena = Hash::make($request->contrasena);
        $usuario->dni = $request->dni;
        $usuario->nombre = $request->nombre;
        $usuario->apellidos = $request->apellidos;
        $usuario->fecha_nacimiento = $request->fecha_nacimiento;
        $usuario->direccion = $request->direccion;
        $usuario->telefono = $request->telefono;
        $usuario->email = $request->email;
        $usuario->id_rol = $request->id_rol;
        $usuario->es_conductor = $request->has('es_conductor');
        $usuario->numero_licencia = $request->numero_licencia;
        $usuario->tipo_licencia = $request->tipo_licencia;
        $usuario->fecha_ingreso = $request->fecha_ingreso;
        $usuario->estado = 'Activo';
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra la información de un usuario específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);
        
        return view('admin.usuarios.show', compact('usuario'));
    }

    /**
     * Muestra el formulario para editar un usuario.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();
        
        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualiza un usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Validar los datos del formulario
        $request->validate([
            'nombre_usuario' => 'required|unique:usuarios,nombre_usuario,'.$id.',id_usuario',
            'dni' => 'required|unique:usuarios,dni,'.$id.',id_usuario',
            'nombre' => 'required',
            'apellidos' => 'required',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'required',
            'telefono' => 'required',
            'email' => 'required|email|unique:usuarios,email,'.$id.',id_usuario',
            'id_rol' => 'required|exists:roles,id_rol',
            'fecha_ingreso' => 'required|date',
            'estado' => 'required'
        ]);

        // Actualizar el usuario
        $usuario->nombre_usuario = $request->nombre_usuario;
        // Solo actualizar la contraseña si se proporcionó una nueva
        if ($request->filled('contrasena')) {
            $usuario->contrasena = Hash::make($request->contrasena);
        }
        $usuario->dni = $request->dni;
        $usuario->nombre = $request->nombre;
        $usuario->apellidos = $request->apellidos;
        $usuario->fecha_nacimiento = $request->fecha_nacimiento;
        $usuario->direccion = $request->direccion;
        $usuario->telefono = $request->telefono;
        $usuario->email = $request->email;
        $usuario->id_rol = $request->id_rol;
        $usuario->es_conductor = $request->has('es_conductor');
        $usuario->numero_licencia = $request->numero_licencia;
        $usuario->tipo_licencia = $request->tipo_licencia;
        $usuario->fecha_ingreso = $request->fecha_ingreso;
        $usuario->estado = $request->estado;
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}