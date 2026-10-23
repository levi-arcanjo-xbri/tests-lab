<?php

namespace App\Infrastructure\Idempotency;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Middleware de idempotência (alias `idempotent`, registrado em bootstrap/app.php).
 *
 * Garante que reenviar a mesma requisição de escrita produz um único efeito.
 * O cliente gera uma chave única (ex.: UUID) e manda no cabeçalho
 * `Idempotency-Key`. Fluxo:
 *
 * 1. Sem cabeçalho: segue normalmente (a idempotência é opcional).
 * 2. Tenta RESERVAR a chave inserindo uma linha. O índice único
 *    (user_id, key) garante que só uma requisição consegue, mesmo que duas
 *    cheguem juntas.
 * 3. Se reservou: executa a ação. Em sucesso (2xx), guarda status e corpo
 *    da resposta. Em erro, apaga a reserva para o cliente poder tentar de novo.
 * 4. Se a chave já existia:
 *    - mesma requisição e já concluída → devolve a resposta guardada
 *      (cabeçalho `Idempotent-Replayed: true`), sem executar nada;
 *    - mesma requisição ainda em andamento → 409;
 *    - outra requisição com a mesma chave → 422 (uso indevido da chave).
 *
 * Fica na camada de infraestrutura porque é um mecanismo técnico, reutilizável
 * por qualquer contexto, e não uma regra de negócio.
 */
class EnsureIdempotency
{
    public const HEADER = 'Idempotency-Key';

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header(self::HEADER);

        if ($key === null || $request->user() === null) {
            return $next($request);
        }

        if (strlen($key) > 255) {
            return $this->error('Idempotency-Key deve ter no máximo 255 caracteres.', 400);
        }

        $hash = $this->fingerprint($request);
        $record = $this->reserve($request, $key, $hash);

        if (! $record->wasRecentlyCreated) {
            return $this->replay($record, $hash);
        }

        return $this->executeAndStore($request, $next, $record);
    }

    /**
     * Insere a reserva ou devolve a linha que já existe.
     *
     * createOrFirst() tenta o INSERT primeiro e, se o índice único recusar,
     * busca a linha existente. É seguro sob concorrência (diferente de
     * firstOrCreate, que faz SELECT e depois INSERT).
     */
    private function reserve(Request $request, string $key, string $hash): IdempotencyKey
    {
        return IdempotencyKey::query()->createOrFirst(
            ['user_id' => $request->user()->id, 'key' => $key],
            ['method' => $request->method(), 'path' => $request->path(), 'request_hash' => $hash],
        );
    }

    private function executeAndStore(Request $request, Closure $next, IdempotencyKey $record): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $record->delete();
            throw $e;
        }

        if (! $response->isSuccessful()) {
            // Erros (validação, conflito, etc.) não são guardados: o cliente corrige e reenvia.
            $record->delete();

            return $response;
        }

        $record->update([
            'response_status' => $response->getStatusCode(),
            'response_body' => json_decode((string) $response->getContent(), true),
        ]);

        return $response;
    }

    private function replay(IdempotencyKey $record, string $hash): Response
    {
        if (! hash_equals($record->request_hash, $hash)) {
            return $this->error('Esta Idempotency-Key já foi usada em outra requisição.', 422);
        }

        if (! $record->isCompleted()) {
            return $this->error('Uma requisição com esta Idempotency-Key ainda está em andamento.', 409);
        }

        return response()
            ->json($record->response_body, $record->response_status)
            ->header('Idempotent-Replayed', 'true');
    }

    /**
     * "Impressão digital" da requisição: método, caminho e corpo. Serve para
     * detectar a mesma chave reaproveitada numa requisição diferente.
     */
    private function fingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->method(),
            $request->path(),
            json_encode($request->all()),
        ]));
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
