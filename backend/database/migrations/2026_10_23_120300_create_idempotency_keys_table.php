<?php

/**
 * Chaves de idempotência das requisições de escrita.
 *
 * O cliente envia o cabeçalho `Idempotency-Key`. A primeira requisição com
 * aquela chave reserva a linha (response_status nulo = "em andamento") e, ao
 * terminar com sucesso, guarda a resposta. Um reenvio com a mesma chave recebe
 * a resposta guardada, sem executar a ação de novo.
 *
 * O índice único (user_id, key) é o que garante um único efeito mesmo quando
 * duas requisições iguais chegam ao mesmo tempo: só uma consegue inserir.
 * Ver App\Infrastructure\Idempotency\EnsureIdempotency.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('method', 10);
            $table->string('path');
            // SHA-256 do método, caminho e corpo: detecta a mesma chave usada em outra requisição.
            $table->char('request_hash', 64);
            $table->unsignedSmallInteger('response_status')->nullable();
            // `json` (e não `jsonb`) porque guarda o texto como veio, na mesma ordem
            // das chaves: o reenvio recebe exatamente a mesma resposta.
            $table->json('response_body')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
