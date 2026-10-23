<?php

namespace App\Domain\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida a edição de task (PATCH: campos opcionais). Mudança de estado não
 * passa por aqui; um campo `status` enviado é simplesmente ignorado, porque
 * só usamos os dados validados ($request->validated()).
 */
class UpdateTaskRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['title' => 'título', 'description' => 'descrição'];
    }
}
