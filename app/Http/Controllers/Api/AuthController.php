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
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas son incorrectas.'
            ], 401);
        }

        if ((int)$user->active !== 1) {
            return response()->json([
                'message' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $firstName  = $user->FIRSTNAME ?? $user->firstName ?? '';
        $lastName   = $user->LASTNAME ?? $user->lastName ?? '';
        $middleName = $user->MIDDLENAME ?? $user->middleName ?? '';

        $fullName = trim("{$firstName} {$lastName} {$middleName}");

        return response()->json([
            'message'      => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id_user'    => $user->ID_USER ?? $user->id_user,
                'name'       => $fullName !== '' ? $fullName : ($user->EMAIL ?? $user->email),
                'firstName'  => $firstName,
                'lastName'   => $lastName,
                'middleName' => $middleName,
                'email'      => $user->EMAIL ?? $user->email,
                'nivel'      => $user->NIVEL ?? $user->nivel,
                'unidad'     => $user->UNIDAD ?? $user->unidad,
                'idType'     => $user->IDTYPE ?? $user->idType,
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
