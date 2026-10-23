<?php

namespace App\Domain\Project\Http\Requests;

use App\Domain\Project\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida a edição de projeto (PATCH: os campos são opcionais).
 *
 * `sometimes` valida o campo só quando ele vem na requisição. O `ignore`
 * permite salvar o projeto mantendo o próprio nome.
 */
class UpdateProjectRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:120',
                Rule::unique('projects', 'name')
                    ->where('user_id', $this->user()?->id)
                    ->ignore($project?->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nome', 'description' => 'descrição'];
    }
}
