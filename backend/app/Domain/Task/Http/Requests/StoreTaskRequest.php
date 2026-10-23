<?php

namespace App\Domain\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida a criação de task. O estado não é aceito aqui: toda task nasce em
 * "todo" e só muda pela rota de transição.
 */
class StoreTaskRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
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
