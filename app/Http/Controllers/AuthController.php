<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($validated['username']);
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
            'user' => $this->formatUser($user),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:150',
                'nombreUsuario' => 'required|string|max:100|unique:Usuario,nombreUsuario',
                'correo' => 'required|email|max:50|unique:Usuario,correo',
                'password' => 'required|string|min:6',
                'telefono' => 'required|string|max:50',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Algunos datos ya se encuentran registrados o son inválidos.',
            ], 422);
        }

        $user = Usuario::create([
            'nombre' => $validated['nombre'],
            'nombreUsuario' => $validated['nombreUsuario'],
            'correo' => strtolower(trim($validated['correo'])),
            'telefono' => $validated['telefono'],
            'password' => Hash::make($validated['password']),
            'idRol' => 2,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function forgotEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $correo = strtolower(trim($validated['email']));

        $user = Usuario::where('correo', $correo)->first();

        if ($user) {
            PasswordResetCode::where('correo', $correo)
                ->where('used', false)
                ->update(['used' => true]);

            $code = (string) random_int(100000, 999999);

            PasswordResetCode::create([
                'correo' => $correo,
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
                'used' => false,
            ]);

            Mail::to($correo)->send(new PasswordResetCodeMail($code));
        }

        return response()->json([
            'message' => 'Si el correo está asociado a una cuenta, se enviará un código.',
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $correo = strtolower(trim($validated['email']));

        $resetCode = PasswordResetCode::where('correo', $correo)
            ->where('code', trim($validated['code']))
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$resetCode) {
            return response()->json([
                'message' => 'Código incorrecto o expirado.',
            ], 422);
        }

        return response()->json([
            'message' => 'Código verificado correctamente.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $correo = strtolower(trim($validated['email']));

        $resetCode = PasswordResetCode::where('correo', $correo)
            ->where('code', trim($validated['code']))
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$resetCode) {
            return response()->json([
                'message' => 'Código incorrecto o expirado.',
            ], 422);
        }

        $user = Usuario::where('correo', $correo)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        $resetCode->used = true;
        $resetCode->save();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    private function formatUser(Usuario $user): array
    {
        return [
            'nombreUsuario' => $user->nombreUsuario,
            'nombres' => $user->nombre,
            'correo' => $user->correo,
            'rol' => optional($user->rol)->nombre ?? 'Cliente',
        ];
    }
}