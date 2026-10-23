<?php

namespace App\Domain\Project\Http\Resources;

use App\Domain\Project\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formato JSON de um projeto na API.
 *
 * O Resource separa o contrato da API do schema do banco: podemos renomear
 * colunas sem quebrar o frontend, e só sai o que está listado aqui.
 * As respostas vêm dentro de `data` ({ "data": { ... } }).
 *
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            // whenCounted: só aparece quando a contagem foi carregada com withCount/loadCount.
            'tasks_count' => $this->whenCounted('tasks'),
            'done_tasks_count' => $this->whenCounted('done_tasks'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
