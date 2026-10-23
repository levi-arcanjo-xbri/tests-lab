<?php

namespace App\Domain\Task\Actions;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Caso de uso: listar as tasks de um projeto, com filtro opcional por estado.
 *
 * Ordem: pendentes primeiro, depois concluídas; dentro de cada grupo, as mais
 * antigas primeiro (ordem de criação).
 */
class ListTasksAction
{
    /**
     * @return Collection<int, Task>
     */
    public function execute(Project $project, ?TaskStatus $status = null): Collection
    {
        return $project->tasks()
            ->when($status, fn (Builder $query, TaskStatus $value) => $query->where('status', $value))
            ->orderByRaw("CASE WHEN status = 'todo' THEN 0 ELSE 1 END")
            ->oldest('id')
            ->get();
    }
}
