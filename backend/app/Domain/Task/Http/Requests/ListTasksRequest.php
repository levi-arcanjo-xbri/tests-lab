<?php

namespace App\Domain\Task\Http\Requests;

use App\Domain\Task\States\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida o filtro da listagem de tasks (?status=todo|done).
 */
class ListTasksRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
        ];
    }

    /**
     * Converte o filtro validado para o enum (ou null quando ausente).
     */
    public function status(): ?TaskStatus
    {
        return $this->enum('status', TaskStatus::class);
    }
}
