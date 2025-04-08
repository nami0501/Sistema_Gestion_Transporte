<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Str;

class UsuarioProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];

        // Compara con el campo 'contrasena' en lugar de 'password'
        return $this->hasher->check($plain, $user->contrasena);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            Str::contains($this->firstCredentialKey($credentials), 'password'))) {
            return null;
        }

        // Primero creamos una consulta base
        $query = $this->newModelQuery();

        // Iteramos a través de las credenciales, excepto la contraseña
        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            // Si el key es 'nombre_usuario' o 'email', intentamos buscar en ambos
            if ($key === 'nombre_usuario' || $key === 'email') {
                $query->where(function ($query) use ($value) {
                    $query->where('nombre_usuario', $value)
                          ->orWhere('email', $value);
                });
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}