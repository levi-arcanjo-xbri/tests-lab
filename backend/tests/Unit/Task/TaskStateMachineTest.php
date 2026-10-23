<?php

/**
 * Testes de unidade da máquina de estados: só lógica, sem banco nem HTTP.
 * Cobrem todas as transições válidas e a recusa das inválidas.
 */

use App\Domain\Task\Exceptions\InvalidTaskTransitionException;
use App\Domain\Task\States\TaskStateMachine;
use App\Domain\Task\States\TaskStatus;

it('permite as transições declaradas', function (TaskStatus $from, TaskStatus $to) {
    expect(TaskStateMachine::canTransition($from, $to))->toBeTrue();
})->with([
    'concluir (todo → done)' => [TaskStatus::Todo, TaskStatus::Done],
    'reabrir (done → todo)' => [TaskStatus::Done, TaskStatus::Todo],
]);

it('recusa transições para o mesmo estado', function (TaskStatus $status) {
    expect(TaskStateMachine::canTransition($status, $status))->toBeFalse();

    TaskStateMachine::assertCanTransition($status, $status);
})->with([TaskStatus::Todo, TaskStatus::Done])
    ->throws(InvalidTaskTransitionException::class);

it('informa os destinos permitidos a partir de cada estado', function () {
    expect(TaskStateMachine::allowedFrom(TaskStatus::Todo))->toBe([TaskStatus::Done])
        ->and(TaskStateMachine::allowedFrom(TaskStatus::Done))->toBe([TaskStatus::Todo]);
});
