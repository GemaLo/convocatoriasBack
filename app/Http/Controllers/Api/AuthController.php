<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar correo y contraseña recibidos desde React
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // 2. Buscar al usuario por correo
        $user = User::where('email', $credentials['email'])->first();

        // 3. Verificar si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas son incorrectas.'
            ], 401);
        }

        // 4. Verificar que la cuenta esté activa
        if ((int)$user->active !== 1) {
            return response()->json([
                'message' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.'
            ], 403);
        }

        // 5. Crear el token de autenticación de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Retornar la respuesta esperada por el Frontend
        return response()->json([
            'message'      => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id_user'    => $user->id_user,
                'name'       => trim("{$user->firstName} {$user->lastName} {$user->middleName}"),
                'firstName'  => $user->firstName,
                'lastName'   => $user->lastName,
                'middleName' => $user->middleName,
                'email'      => $user->email,
                'nivel'      => $user->nivel,
                'unidad'     => $user->unidad,
                'idType'     => $user->idType,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
}