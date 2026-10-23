<?php

namespace App\Domain\Task\Http\Resources;

use App\Domain\Task\Models\TaskStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formato JSON de uma linha do histórico de estados.
 *
 * @mixin TaskStatusHistory
 */
class TaskStatusHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_status' => $this->from_status?->value,
            'to_status' => $this->to_status->value,
            // whenLoaded: só inclui o autor se a relação foi carregada (with('user')).
            'user' => $this->whenLoaded('user', fn () => $this->user === null ? null : [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
