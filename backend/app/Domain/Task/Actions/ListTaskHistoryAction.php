<?php

namespace App\Domain\Task\Actions;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskStatusHistory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Caso de uso: listar o histórico de estados de uma task, do mais antigo ao
 * mais recente, com o autor de cada transição.
 */
class ListTaskHistoryAction
{
    /**
     * @return Collection<int, TaskStatusHistory>
     */
    public function execute(Task $task): Collection
    {
        // with('user') carrega os autores numa única consulta extra (evita N+1).
        return $task->statusHistory()->with('user')->oldest('id')->get();
    }
}
