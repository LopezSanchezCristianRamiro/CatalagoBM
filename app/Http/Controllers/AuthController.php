<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Iniciar sesión y devolver token + datos del usuario.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $validated['username'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'correo' : 'nombreUsuario';

        $user = Usuario::where($field, $login)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'   => $this->formatUser($user),
        ]);
    }

    /**
     * Obtener los datos del usuario autenticado (vía token).
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    /**
     * Cerrar sesión revocando el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    /**
     * Formatear los datos del usuario para el frontend.
     */
    private function formatUser(Usuario $user): array
    {
        return [
            'nombreUsuario' => $user->nombre,
            'nombres'       => $user->nombre,
            'correo'        => $user->correo,
            'rol'           => optional($user->rol)->nombre ?? 'Cliente',
        ];
    }
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'nombreUsuario' => 'required|string|max:100|unique:Usuario,nombreUsuario',
            'correo' => 'required|email|max:50|unique:Usuario,correo',
            'password' => 'required|string|min:6',
        ]);

        $user = Usuario::create([
            'nombre' => $validated['nombre'],
            'nombreUsuario' => $validated['nombreUsuario'],
            'correo' => $validated['correo'],
            'password' => Hash::make($validated['password']),
            'idRol' => 2, // Rol "Cliente" por defecto (ajusta según tu base)
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }
}