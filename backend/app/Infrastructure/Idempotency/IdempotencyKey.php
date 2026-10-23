<?php

namespace App\Infrastructure\Idempotency;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de uma chave de idempotência e da resposta guardada para ela.
 *
 * MassPrunable: o comando `php artisan model:prune` (agendado em
 * routes/console.php) apaga as chaves mais velhas que o prazo configurado.
 * Depois disso, a mesma chave pode ser usada de novo.
 *
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property string $method
 * @property string $path
 * @property string $request_hash
 * @property int|null $response_status Nulo enquanto a requisição original está em andamento.
 * @property array<string, mixed>|null $response_body
 */
class IdempotencyKey extends Model
{
    use MassPrunable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'key',
        'method',
        'path',
        'request_hash',
        'response_status',
        'response_body',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_body' => 'array',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->response_status !== null;
    }

    /**
     * Chaves que podem ser apagadas pelo model:prune.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $hours = (int) config('idempotency.ttl_hours');

        return static::query()->where('created_at', '<', now()->subHours($hours));
    }
}
