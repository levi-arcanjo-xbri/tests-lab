<?php

namespace App\Domain\Project\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida a query string da listagem de projetos (?search=...).
 *
 * FormRequests também servem para GET: a validação fica fora do controller
 * e a documentação OpenAPI (Scramble) lê estas regras para descrever os
 * parâmetros.
 */
class ListProjectsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
        ];
    }
}
