<?php

namespace App\Domain\Project\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;
use Illuminate\Auth\Access\Response;

/**
 * Autorização no servidor, recurso a recurso: só o dono acessa o projeto
 * (e, por consequência, as tasks dele).
 *
 * As rotas usam o middleware `can:<ability>,project`, que chama estes
 * métodos antes do controller.
 *
 * Usamos denyAsNotFound() em vez de deny(): quem não é dono recebe 404,
 * como se o projeto não existisse. Assim a API não revela que existe um
 * projeto com aquele id pertencente a outra pessoa.
 */
class ProjectPolicy
{
    public function view(User $user, Project $project): Response
    {
        return $this->ownerOnly($user, $project);
    }

    public function update(User $user, Project $project): Response
    {
        return $this->ownerOnly($user, $project);
    }

    public function delete(User $user, Project $project): Response
    {
        return $this->ownerOnly($user, $project);
    }

    private function ownerOnly(User $user, Project $project): Response
    {
        return $project->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
