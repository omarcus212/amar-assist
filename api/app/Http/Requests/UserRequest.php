<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        // Para criação (POST), a senha é obrigatória. Para atualização (PUT/PATCH), é opcional.
        $passwordRule = $this->isMethod('POST')
            ? 'required|string|min:6'
            : 'nullable|string|min:6';

        return [
            'name' => 'required|string|max:255',
            // Ao atualizar, ignora o e-mail do próprio usuário na verificação.
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => $passwordRule,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ];
    }
}
