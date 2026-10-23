import { HttpHeaders } from '@angular/common/http';

/**
 * Idempotência no cliente.
 *
 * Em cada ação de criação, o frontend gera uma chave única (UUID) e envia no
 * cabeçalho `Idempotency-Key`. Se a rede falhar e o usuário tentar de novo,
 * a MESMA chave é reenviada: o backend devolve o resultado da primeira
 * tentativa em vez de criar um registro duplicado.
 *
 * A chave só é trocada depois de um sucesso (ver IdempotencyKeyHolder).
 */
export const IDEMPOTENCY_HEADER = 'Idempotency-Key';

export function idempotencyHeaders(key: string): HttpHeaders {
  return new HttpHeaders({ [IDEMPOTENCY_HEADER]: key });
}

/**
 * Guarda a chave da "operação atual" de um formulário.
 *
 * - current(): devolve a chave pendente ou gera uma nova;
 * - reset(): chamada após sucesso, para a próxima operação ter chave nova.
 */
export class IdempotencyKeyHolder {
  private key: string | null = null;

  current(): string {
    this.key ??= generateUuid();
    return this.key;
  }

  reset(): void {
    this.key = null;
  }
}

/**
 * Gera um UUID v4.
 *
 * crypto.randomUUID() só existe em "contexto seguro" (HTTPS ou localhost).
 * Acessando por http://<ip-ou-nome>:porta, como no E2E dentro do Docker,
 * ele não está disponível; nesse caso montamos o UUID com
 * crypto.getRandomValues(), que funciona em qualquer contexto.
 */
export function generateUuid(): string {
  if (typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }

  const bytes = crypto.getRandomValues(new Uint8Array(16));
  bytes[6] = (bytes[6] & 0x0f) | 0x40; // versão 4
  bytes[8] = (bytes[8] & 0x3f) | 0x80; // variante RFC 4122
  const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');

  return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}
