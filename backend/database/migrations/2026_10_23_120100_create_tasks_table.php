<?php

/**
 * Tabela de tasks.
 *
 * O estado da task ("todo" ou "done") é uma máquina de estados (ver
 * App\Domain\Task\States\TaskStateMachine). Aqui o banco reforça o que a
 * aplicação já garante:
 * - `enum` no PostgreSQL vira uma coluna varchar com CHECK, então nenhum
 *   valor fora de "todo"/"done" entra, nem por SQL manual;
 * - o CHECK `tasks_completed_at_matches_status` amarra os dois campos:
 *   task concluída sempre tem `completed_at`, task pendente nunca tem.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'done'])->default('todo');
            $table->timestampTz('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        // O Schema Builder não tem API para CHECK com expressão; usamos SQL direto.
        DB::statement(<<<'SQL'
            ALTER TABLE tasks
            ADD CONSTRAINT tasks_completed_at_matches_status
            CHECK ((status = 'done') = (completed_at IS NOT NULL))
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
