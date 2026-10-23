<?php

namespace App\Domain\Task\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Support\Facades\DB;

/**
 * Caso de uso: criar uma task num projeto.
 *
 * A task nasce em "todo" e a primeira linha do histórico (null → todo) é
 * gravada na MESMA transação. Se qualquer passo falhar, nada fica gravado:
 * não existe task sem histórico.
 */
class CreateTaskAction
{
    /**
     * @param  array{title: string, description?: string|null}  $data
     */
    public function execute(Project $project, User $actor, array $data): Task
    {
        return DB::transaction(function () use ($project, $actor, $data) {
            /** @var Task $task */
            $task = $project->tasks()->create($data);

            $task->statusHistory()->create([
                'user_id' => $actor->id,
                'from_status' => null,
                'to_status' => TaskStatus::Todo,
            ]);

            // refresh() relê a linha para trazer o default do banco e os timestamps.
            return $task->refresh();
        });
    }
}
