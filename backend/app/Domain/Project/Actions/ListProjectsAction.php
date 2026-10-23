<?php

namespace App\Domain\Project\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Caso de uso: listar os projetos do usuário.
 *
 * A consulta parte de `$user->projects()`, então já nasce filtrada pelo dono:
 * não há como listar projetos de outra pessoa por engano.
 */
class ListProjectsAction
{
    /**
     * @return Collection<int, Project>
     */
    public function execute(User $user, ?string $search = null): Collection
    {
        return $user->projects()
            ->when($search, fn (Builder $query, string $term) => $query->whereLike('name', "%{$term}%"))
            // Total de tasks e de concluídas, como subconsultas (ver Project::taskCounts()).
            ->withCount(Project::taskCounts())
            ->latest('updated_at')
            ->get();
    }
}
