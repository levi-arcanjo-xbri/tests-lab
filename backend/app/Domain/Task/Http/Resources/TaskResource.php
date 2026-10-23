<?php

namespace App\Domain\Task\Http\Resources;

use App\Domain\Task\Models\Task;
use App\Domain\Task\States\TaskStateMachine;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formato JSON de uma task.
 *
 * `allowed_transitions` vem da máquina de estados: o frontend usa essa lista
 * para decidir quais ações mostrar, sem repetir a regra no cliente.
 *
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'allowed_transitions' => array_map(
                fn (TaskStatus $status) => $status->value,
                TaskStateMachine::allowedFrom($this->status),
            ),
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
