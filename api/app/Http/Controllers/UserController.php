<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use DB;
use Hash;
use Auth;

class UserController
{

    public function index(Request $request)
    {
        $query = User::query();

        // Filtro por Nome 
        if ($request->has('name') && $request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filtro por Email 
        if ($request->has('email') && $request->email) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $query->orderBy('id', 'desc');

        $users = $query->paginate(10);

        return Response::success($users, 'Usuários listados com sucesso');
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            return Response::success($user, 'Usuário encontrado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Usuário não encontrado.', 404);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar usuário: ' . $e->getMessage());
            return Response::error('Erro ao buscar usuário.', 500);

        }
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Hash da senha
            $data['password'] = Hash::make($data['password']);

            // Criar usuário
            $user = User::create($data);

            DB::commit();

            return Response::success($user, 'Usuário criado com sucesso', 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erro ao criar usuário: ' . $e->getMessage());
            return Response::error('Erro ao criar usuário.', 500);
        }
    }

    public function update(UserRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $data = $request->validated();

            // Hash da senha apenas se estiver enviando nova
            if (isset($data['password']) && $data['password']) {
                $data['password'] = Hash::make($data['password']);

            } else {
                unset($data['password']);
            }

            // Atualizar usuário
            $user->update($data);

            DB::commit();

            return Response::success($user, 'Usuário atualizado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Usuário não encontrado.', 404);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            return Response::error('Erro ao atualizar usuário.', 500);
        }
    }

    public function desactive($id)
    {
        try {

            $user = User::findOrFail($id);

            if ($user->id === Auth::id()) {
                return Response::error('Você não pode desativado a si mesmo.', 403);
            }

            $user->update(['active' => false]);

            return Response::success(null, 'Usuário desativado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Usuário não encontrado.', 404);

        } catch (\Exception $e) {
            \Log::error('Erro ao desativar usuário: ' . $e->getMessage());
            return Response::error('Erro ao desativar usuário.', 500);
        }
    }
}
