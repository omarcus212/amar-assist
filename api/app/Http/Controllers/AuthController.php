<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class AuthController
{

    public function login(AuthRequest $request)
    {
        $validatedUser = $request->validated();

        $user = User::where('email', $validatedUser['email'])->first();

        // Verifica senha e existência do usuário
        if (!$user || !Hash::check($validatedUser['password'], $user->password)) {
            return Response::error('Credenciais inválidas', 401);
        }

        // Verifica se usuário está ativo (caso tenha esse campo na tabela users)
        if (isset($user->active) && !$user->active) {
            return Response::error('Usuário inativo', 401);
        }

        // Cria token Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        return Response::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login realizado com sucesso');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return Response::success(null, 'Logout realizado com sucesso');

    }
}