import { Injectable } from '@angular/core';

const TOKEN_KEY = 'auth_token';

/**
 * Guarda o token Bearer no localStorage, para a sessão sobreviver a um
 * recarregamento da página.
 *
 * Isolar o acesso ao storage num serviço facilita trocar a estratégia
 * depois (ex.: sessionStorage) e simular nos testes.
 */
@Injectable({ providedIn: 'root' })
export class TokenStorageService {
  get(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  }

  set(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
  }

  clear(): void {
    localStorage.removeItem(TOKEN_KEY);
  }
}
