<?php

/**
 * Histórico de transições de estado das tasks.
 *
 * Cada mudança de estado grava uma linha aqui, na mesma transação da mudança
 * (ver TransitionTaskStatusAction). Se a gravação do histórico falhar, a
 * mudança de estado é desfeita: não existe estado sem histórico.
 *
 * `from_status` é nulo na primeira linha, que registra a criação da task.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            // Quem fez a transição. Se o usuário for apagado, o histórico fica sem autor.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('from_status', ['todo', 'done'])->nullable();
            $table->enum('to_status', ['todo', 'done']);
            // Histórico é imutável: só existe a data de criação.
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['task_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_status_histories');
    }
};
