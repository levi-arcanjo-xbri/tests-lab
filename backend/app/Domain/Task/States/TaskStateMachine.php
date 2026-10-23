<?php

namespace App\Domain\Task\States;

use App\Domain\Task\Exceptions\InvalidTaskTransitionException;

/**
 * Máquina de estados da task: o ÚNICO lugar onde as transições permitidas
 * são declaradas.
 *
 *     todo ──concluir──▶ done
 *     done ──reabrir───▶ todo
 *
 * Qualquer transição fora desta tabela (inclusive "done → done") é recusada
 * e o estado não muda. Para adicionar um estado novo (ex.: "doing"), basta
 * acrescentar o caso no enum e as setas aqui; o resto do código consulta
 * esta classe.
 */
final class TaskStateMachine
{
    /**
     * Mapa "estado de origem => estados de destino permitidos".
     *
     * @var array<string, list<TaskStatus>>
     */
    private const TRANSITIONS = [
        'todo' => [TaskStatus::Done],
        'done' => [TaskStatus::Todo],
    ];

    public static function canTransition(TaskStatus $from, TaskStatus $to): bool
    {
        return in_array($to, self::allowedFrom($from), true);
    }

    /**
     * Lança exceção quando a transição não é permitida.
     *
     * @throws InvalidTaskTransitionException
     */
    public static function assertCanTransition(TaskStatus $from, TaskStatus $to): void
    {
        if (! self::canTransition($from, $to)) {
            throw new InvalidTaskTransitionException($from, $to);
        }
    }

    /**
     * Destinos permitidos a partir de um estado. A API expõe essa lista para o
     * frontend saber quais ações mostrar, sem duplicar a regra no cliente.
     *
     * @return list<TaskStatus>
     */
    public static function allowedFrom(TaskStatus $from): array
    {
        return self::TRANSITIONS[$from->value] ?? [];
    }
}
