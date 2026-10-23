<?php

namespace App\Domain\Task\Models;

use App\Domain\Project\Models\Project;
use App\Domain\Task\States\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Task de um projeto.
 *
 * O model só descreve dados e relacionamentos; a regra de negócio (criar,
 * mudar de estado) fica nas Actions do contexto Task.
 *
 * Note que `status` e `completed_at` NÃO estão em $fillable: eles só mudam
 * pela máquina de estados (TransitionTaskStatusAction), nunca por
 * atribuição em massa vinda da requisição.
 *
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string|null $description
 * @property TaskStatus $status
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[UseFactory(TaskFactory::class)]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Valor padrão em memória, igual ao default da coluna no banco.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'todo',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * Ao salvar uma task, atualiza o `updated_at` do projeto. Assim a lista de
     * projetos pode ser ordenada por atividade recente.
     *
     * @var list<string>
     */
    protected $touches = ['project'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<TaskStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(TaskStatusHistory::class);
    }
}
