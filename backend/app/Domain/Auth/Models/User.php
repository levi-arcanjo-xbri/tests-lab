<?php

namespace App\Domain\Auth\Models;

use App\Domain\Project\Models\Project;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Usuário autenticável.
 *
 * - HasApiTokens (Sanctum) dá os métodos createToken(), tokens() e
 *   currentAccessToken(), usados no login e no logout.
 * - O cast 'hashed' em `password` aplica o hash automaticamente ao atribuir,
 *   então nunca gravamos senha em texto puro.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Projetos dos quais o usuário é dono.
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
