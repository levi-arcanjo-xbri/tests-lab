<?php

/**
 * Tabela de projetos.
 *
 * Cada projeto pertence a um usuário (dono). As invariantes ficam também no banco,
 * não só na validação da aplicação:
 * - a chave estrangeira garante que o dono existe e, ao apagar o usuário,
 *   apaga os projetos dele (cascade);
 * - o índice único (user_id, name) impede dois projetos com o mesmo nome
 *   para o mesmo dono, mesmo sob concorrência.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            // A listagem ordena por atividade recente do dono.
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
