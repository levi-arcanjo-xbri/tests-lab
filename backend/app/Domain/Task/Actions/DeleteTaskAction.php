<?php

namespace App\Domain\Task\Actions;

use App\Domain\Task\Models\Task;

/**
 * Caso de uso: apagar uma task. O histórico vai junto (ON DELETE CASCADE).
 */
class DeleteTaskAction
{
    public function execute(Task $task): void
    {
        $task->delete();
    }
}
