<?php

namespace App\Domain\Task\Http\Requests;

use App\Domain\Task\States\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida o pedido de mudança de estado: { "status": "done" }.
 *
 * Aqui só checamos se o valor é um estado conhecido (422 se não for). Se a
 * transição é permitida a partir do estado atual é decisão da máquina de
 * estados, na Action (409 se não for).
 */
class TransitionTaskStatusRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ];
    }

    public function targetStatus(): TaskStatus
    {
        return $this->enum('status', TaskStatus::class);
    }
}
