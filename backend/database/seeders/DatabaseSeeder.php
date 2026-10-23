<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Actions\CreateProjectAction;
use App\Domain\Task\Actions\CreateTaskAction;
use App\Domain\Task\Actions\TransitionTaskStatusAction;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Dados de exemplo para rodar a jornada do zero (`php artisan db:seed`, que o
 * entrypoint do container executa na subida).
 *
 * Os dados são criados pelas mesmas Actions usadas pela API, então o
 * histórico de estados fica coerente com o que um usuário real produziria.
 *
 * Pode rodar mais de uma vez: se o usuário demo já tem projetos, não faz nada.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Projetos de exemplo: nome, descrição e tasks ([título, concluída?]).
     *
     * @var list<array{name: string, description: string, tasks: list<array{0: string, 1: bool}>}>
     */
    private const PROJECTS = [
        [
            'name' => 'Estudo de arquitetura',
            'description' => 'Roteiro para entender as camadas deste projeto.',
            'tasks' => [
                ['Ler o README e o CLAUDE.md', true],
                ['Seguir uma requisição da rota até a Action', true],
                ['Entender a máquina de estados da task', false],
                ['Testar a idempotência com o cabeçalho Idempotency-Key', false],
                ['Rodar os testes E2E com Playwright', false],
            ],
        ],
        [
            'name' => 'Mudança de apartamento',
            'description' => 'Tudo o que precisa ser feito até o dia da mudança.',
            'tasks' => [
                ['Pedir orçamento de transportadora', true],
                ['Separar caixas', false],
                ['Transferir a internet', false],
            ],
        ],
        [
            'name' => 'Leituras do trimestre',
            'description' => null,
            'tasks' => [],
        ],
    ];

    public function run(
        CreateProjectAction $createProject,
        CreateTaskAction $createTask,
        TransitionTaskStatusAction $transition,
    ): void {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Usuário Demo', 'password' => 'password'],
        );

        if ($user->projects()->exists()) {
            return;
        }

        foreach (self::PROJECTS as $data) {
            $project = $createProject->execute($user, [
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            foreach ($data['tasks'] as [$title, $done]) {
                $task = $createTask->execute($project, $user, ['title' => $title]);

                if ($done) {
                    $transition->execute($task, TaskStatus::Done, $user);
                }
            }
        }
    }
}
