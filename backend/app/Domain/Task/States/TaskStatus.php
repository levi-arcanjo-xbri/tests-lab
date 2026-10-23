<?php

namespace App\Domain\Task\States;

/**
 * Estados possíveis de uma task.
 *
 * Um "backed enum" do PHP: cada caso tem o valor gravado no banco ("todo"/"done").
 * O model Task faz o cast da coluna `status` para este enum, então no código
 * trabalhamos com TaskStatus::Done em vez de strings soltas.
 */
enum TaskStatus: string
{
    case Todo = 'todo';
    case Done = 'done';

    /**
     * Rótulo para exibição.
     */
    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To do',
            self::Done => 'Done',
        };
    }

    /**
     * Lista dos valores aceitos, usada nas regras de validação.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
