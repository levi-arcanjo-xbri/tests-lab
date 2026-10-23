<?php

namespace App\Domain\Task\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Uma linha do histórico de estados de uma task.
 *
 * Registro imutável: só tem `created_at` (UPDATED_AT = null) e é criado
 * pelas Actions na mesma transação da mudança de estado.
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $user_id
 * @property TaskStatus|null $from_status
 * @property TaskStatus $to_status
 * @property Carbon $created_at
 */
class TaskStatusHistory extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'from_status',
        'to_status',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'from_status' => TaskStatus::class,
            'to_status' => TaskStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
