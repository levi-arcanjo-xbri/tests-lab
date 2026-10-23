<?php

/**
 * Rotas da API. Todas recebem o prefixo /api automaticamente
 * (configurado em bootstrap/app.php).
 *
 * Middlewares usados aqui:
 * - auth:sanctum   exige o token Bearer válido (401 sem ele);
 * - can:x,project  chama a ProjectPolicy (404 se o usuário não for o dono);
 * - idempotent     aceita o cabeçalho Idempotency-Key nas escritas;
 * - throttle:auth  limita tentativas de login/cadastro (ver AppServiceProvider).
 *
 * `{project}` e `{task}` usam route model binding: o Laravel converte o id
 * da URL no model. Com scopeBindings(), a task é buscada dentro do projeto.
 */

use App\Domain\Auth\Http\Controllers\LoginController;
use App\Domain\Auth\Http\Controllers\LogoutController;
use App\Domain\Auth\Http\Controllers\MeController;
use App\Domain\Auth\Http\Controllers\RegisterController;
use App\Domain\Project\Http\Controllers\DestroyProjectController;
use App\Domain\Project\Http\Controllers\ListProjectsController;
use App\Domain\Project\Http\Controllers\ShowProjectController;
use App\Domain\Project\Http\Controllers\StoreProjectController;
use App\Domain\Project\Http\Controllers\UpdateProjectController;
use App\Domain\Task\Http\Controllers\DestroyTaskController;
use App\Domain\Task\Http\Controllers\ListTaskHistoryController;
use App\Domain\Task\Http\Controllers\ListTasksController;
use App\Domain\Task\Http\Controllers\StoreTaskController;
use App\Domain\Task\Http\Controllers\TransitionTaskStatusController;
use App\Domain\Task\Http\Controllers\UpdateTaskController;
use Illuminate\Support\Facades\Route;

// --- Autenticação -----------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('register', RegisterController::class)->middleware('throttle:auth');
    Route::post('login', LoginController::class)->middleware('throttle:auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', LogoutController::class);
        Route::get('me', MeController::class);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // --- Projetos -----------------------------------------------------------
    Route::get('projects', ListProjectsController::class);
    Route::post('projects', StoreProjectController::class)->middleware('idempotent');

    Route::prefix('projects/{project}')->group(function () {
        Route::get('/', ShowProjectController::class)->middleware('can:view,project');
        Route::patch('/', UpdateProjectController::class)->middleware('can:update,project');
        Route::delete('/', DestroyProjectController::class)->middleware('can:delete,project');

        // --- Tasks do projeto -----------------------------------------------
        // Mexer nas tasks exige poder alterar o projeto; ler exige poder vê-lo.
        Route::get('tasks', ListTasksController::class)->middleware('can:view,project');
        Route::post('tasks', StoreTaskController::class)->middleware(['can:update,project', 'idempotent']);

        Route::prefix('tasks/{task}')->scopeBindings()->group(function () {
            Route::patch('/', UpdateTaskController::class)->middleware('can:update,project');
            Route::delete('/', DestroyTaskController::class)->middleware('can:update,project');
            Route::patch('status', TransitionTaskStatusController::class)
                ->middleware(['can:update,project', 'idempotent']);
            Route::get('history', ListTaskHistoryController::class)->middleware('can:view,project');
        });
    });
});
