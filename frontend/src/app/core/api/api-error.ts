import { HttpErrorResponse } from '@angular/common/http';

/**
 * Formato de erro devolvido pelo Laravel:
 * - 422 (validação): { message, errors: { campo: ["mensagem", ...] } }
 * - demais erros:    { message }
 */
interface LaravelError {
  message?: string;
  errors?: Record<string, string[]>;
}

/**
 * Extrai uma mensagem legível de um erro HTTP para mostrar na tela.
 * Prioriza a primeira mensagem de validação, depois a `message` geral.
 */
export function apiErrorMessage(error: unknown, fallback: string): string {
  if (!(error instanceof HttpErrorResponse)) {
    return fallback;
  }
  if (error.status === 0) {
    return 'Sem conexão com o servidor. Tente novamente.';
  }
  if (error.status === 429) {
    return 'Muitas tentativas. Aguarde um minuto e tente novamente.';
  }

  const body = error.error as LaravelError | null;
  const firstValidation = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;

  return firstValidation ?? body?.message ?? fallback;
}

/**
 * Erros de validação por campo, para mostrar abaixo de cada input.
 */
export function apiFieldErrors(error: unknown): Record<string, string> {
  if (!(error instanceof HttpErrorResponse) || error.status !== 422) {
    return {};
  }

  const errors = (error.error as LaravelError | null)?.errors ?? {};
  return Object.fromEntries(Object.entries(errors).map(([field, messages]) => [field, messages[0]]));
}
