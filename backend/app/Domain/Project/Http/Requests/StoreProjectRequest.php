<?php

namespace App\Domain\Project\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida a criação de projeto.
 *
 * A regra `unique` restrita ao dono dá uma mensagem amigável (422) quando o
 * nome já existe. O índice único no banco continua sendo a garantia final,
 * caso duas requisições passem pela validação ao mesmo tempo.
 *
 * O `?->` só existe porque o Scramble avalia estas regras sem usuário logado
 * para gerar a documentação; numa requisição real o usuário sempre existe.
 *
 * Não há authorize(): qualquer usuário autenticado pode criar projetos, e o
 * método padrão do FormRequest já permite.
 */
class StoreProjectRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('projects', 'name')->where('user_id', $this->user()?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Nomes dos campos usados nas mensagens de erro.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nome', 'description' => 'descrição'];
    }
}
