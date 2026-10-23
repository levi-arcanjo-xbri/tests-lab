<?php

namespace App\Domain\Task\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Task\Exceptions\InvalidTaskTransitionException;
use App\Domain\Task\Models\Task;
use App\Domain\Task\States\TaskStateMachine;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Support\Facades\DB;

/**
 * Caso de uso: mudar o estado de uma task (concluir ou reabrir).
 *
 * Passo a passo, tudo dentro de uma transação:
 *
 * 1. Relê a task com `lockForUpdate()` (SELECT ... FOR UPDATE). Se duas
 *    requisições tentarem mudar a mesma task ao mesmo tempo, a segunda
 *    espera a primeira terminar e enxerga o estado já atualizado.
 * 2. Pergunta à máquina de estados se a transição é permitida. Se não for,
 *    lança InvalidTaskTransitionException (HTTP 409) e nada muda.
 * 3. Grava o novo estado e o `completed_at` coerente com ele.
 * 4. Grava a linha de histórico. Se falhar, o passo 3 é desfeito (rollback).
 */
class TransitionTaskStatusAction
{
    /**
     * @throws InvalidTaskTransitionException
     */
    public function execute(Task $task, TaskStatus $to, User $actor): Task
    {
        return DB::transaction(function () use ($task, $to, $actor) {
            /** @var Task $locked */
            $locked = Task::query()->whereKey($task->id)->lockForUpdate()->firstOrFail();

            $from = $locked->status;
            TaskStateMachine::assertCanTransition($from, $to);

            $locked->status = $to;
            $locked->completed_at = $to === TaskStatus::Done ? now() : null;
            $locked->save();

            $locked->statusHistory()->create([
                'user_id' => $actor->id,
                'from_status' => $from,
                'to_status' => $to,
            ]);

            return $locked;
        });
    }
}
