<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    private function getClient()
    {
        $client = new Google_Client();
        $clientIds = explode(',', config('services.google.client_ids'));
        if (count($clientIds) > 0) {
            $client->setClientId(trim($clientIds[0]));
        }
        return $client;
    }

private function verifyGoogleToken($idToken)
{
    // Obtenemos el string del config y lo separamos manualmente
    $clientIdsString = config('services.google.client_ids');
    $clientIds = array_filter(array_map('trim', explode(',', $clientIdsString)));

    $lastException = null;

    foreach ($clientIds as $clientId) {
        $client = new Google_Client(['client_id' => $clientId]);

        $guzzle = new \GuzzleHttp\Client(['verify' => false]);
        $client->setHttpClient($guzzle);

        try {
            $payload = $client->verifyIdToken($idToken);
            if ($payload) {
                return $payload; // éxito
            }
        } catch (\Exception $e) {
            $lastException = $e;
        }
    }

    // Si llegamos aquí, ningún client ID validó el token
    if ($lastException && app()->environment('local')) {
        // Para depuración podés escribir en el log en vez de retornar JSON
        \Log::error('Google token verification failed: ' . $lastException->getMessage());
    }

    return null;
}


    public function verify(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        $payload = $this->verifyGoogleToken($request->id_token);

        if (!$payload) {
            return response()->json(['message' => 'Token de Google inválido'], 401);
        }

        $email = $payload['email'];
        $user = Usuario::where('correo', $email)->first();

        if ($user) {
            $token = $user->createToken('mobile')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => $this->formatUser($user)
            ]);
        }

        return response()->json([
            'status' => 'needs_phone',
            'message' => 'Se requiere número de teléfono para completar el registro.',
            'google_data' => [
                'email' => $email,
                'nombre' => $payload['name'] ?? '',
                'foto' => $payload['picture'] ?? ''
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'telefono' => 'required|string|max:50'
        ]);

        $payload = $this->verifyGoogleToken($request->id_token);

        if (!$payload) {
            return response()->json(['message' => 'Token de Google inválido'], 401);
        }

        $email = $payload['email'];
        $user = Usuario::where('correo', $email)->first();

        if ($user) {
            return response()->json(['message' => 'El usuario ya está registrado'], 400);
        }

        $baseUsername = strtolower(explode('@', $email)[0]);
        $username = $baseUsername;
        $counter = 1;
        while (Usuario::where('nombreUsuario', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = Usuario::create([
            'nombre' => $payload['name'] ?? 'Usuario Google',
            'nombreUsuario' => $username,
            'correo' => $email,
            'foto' => $payload['picture'] ?? null,
            'telefono' => $request->telefono,
            'password' => Hash::make(Str::random(16)),
            'idRol' => 2,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registro y login exitoso',
            'token' => $token,
            'user' => $this->formatUser($user)
        ], 201);
    }

    private function formatUser(Usuario $user): array
    {
        return [
            'nombreUsuario' => $user->nombreUsuario,
            'nombres' => $user->nombre,
            'correo' => $user->correo,
            'telefono' => $user->telefono,
            'foto'          => $user->foto,
            'rol' => optional($user->rol)->nombre ?? 'Cliente',
        ];
    }
}
