<?php

namespace App\Domain\Project\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Policies\ProjectPolicy;
use App\Domain\Task\Models\Task;
use App\Domain\Task\States\TaskStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Projeto: agrupa tasks e pertence a um usuário.
 *
 * Os atributos #[UseFactory] e #[UsePolicy] ligam o model à factory e à
 * policy explicitamente. Sem eles, o Laravel tenta adivinhar pelo namespace
 * padrão (App\Models), que não usamos na organização por domínio.
 *
 * `user_id` não está em $fillable: o dono vem do usuário autenticado
 * (na Action), nunca do corpo da requisição.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $tasks_count Preenchido por withCount().
 * @property int|null $done_tasks_count Preenchido por withCount().
 */
#[UseFactory(ProjectFactory::class)]
#[UsePolicy(ProjectPolicy::class)]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Definição das contagens de tasks exibidas junto com o projeto.
     *
     * Usada com withCount() (listagem) e loadCount() (um projeto). As contagens
     * viram subconsultas no SQL, sem carregar as tasks em memória.
     *
     * @return array<int|string, mixed>
     */
    public static function taskCounts(): array
    {
        return [
            'tasks',
            'tasks as done_tasks_count' => fn (Builder $query) => $query->where('status', TaskStatus::Done),
        ];
    }
}
