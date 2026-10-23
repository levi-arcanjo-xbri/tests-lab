<?php

namespace App\Domain\Task\Exceptions;

use App\Domain\Task\States\TaskStatus;
use DomainException;
use Illuminate\Http\JsonResponse;

/**
 * Erro de domínio: a transição de estado pedida não é permitida.
 *
 * O método render() é chamado automaticamente pelo handler de exceções do
 * Laravel, então o controller não precisa de try/catch: a API responde
 * 409 Conflict (o pedido conflita com o estado atual do recurso).
 */
class InvalidTaskTransitionException extends DomainException
{
    public function __construct(
        public readonly TaskStatus $from,
        public readonly TaskStatus $to,
    ) {
        parent::__construct("Transição de estado inválida: {$from->value} → {$to->value}.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'from' => $this->from->value,
            'to' => $this->to->value,
        ], 409);
    }
}
