<?php

namespace App\Domain\Task\Actions;

use App\Domain\Task\Models\Task;

/**
 * Caso de uso: alterar título e descrição de uma task.
 *
 * O estado NÃO muda aqui: mudança de estado tem caso de uso próprio
 * (TransitionTaskStatusAction), com validação de transição e histórico.
 */
class UpdateTaskAction
{
    /**
     * @param  array{title?: string, description?: string|null}  $data
     */
    public function execute(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }
}
