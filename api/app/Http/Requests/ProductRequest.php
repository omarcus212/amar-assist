<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'active' => 'boolean',
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'cost.required' => 'O custo é obrigatório.',
            'price.required' => 'O preço de venda é obrigatório.',
            'images.*.mimes' => 'As imagens devem ser nos formatos jpg ou png.',
            'images.*.max' => 'Cada imagem deve ter no máximo 2MB.',
            'images.max' => 'Você pode enviar no máximo 4 imagens.',
        ];
    }
}
