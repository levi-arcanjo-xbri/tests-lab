import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Observable, map, tap } from 'rxjs';

import { API_BASE_URL, ApiData } from '../api/api.config';
import { AuthResponse, LoginRequest, RegisterRequest, User } from './auth.models';
import { TokenStorageService } from './token-storage.service';

/**
 * Sessão do usuário: login, cadastro, logout e usuário atual.
 *
 * O estado fica em signals:
 * - `token`: o token Bearer (também persistido no localStorage);
 * - `user`: o usuário logado, preenchido no login/cadastro ou por loadUser().
 * Componentes leem `isAuthenticated()` e `user()` e são atualizados
 * automaticamente quando esses valores mudam.
 *
 * `providedIn: 'root'` cria uma única instância para toda a aplicação.
 */
@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly storage = inject(TokenStorageService);
  private readonly token = signal<string | null>(this.storage.get());
  private readonly currentUser = signal<User | null>(null);

  readonly isAuthenticated = computed(() => this.token() !== null);
  readonly user = this.currentUser.asReadonly();

  login(credentials: LoginRequest): Observable<User> {
    return this.http
      .post<AuthResponse>(`${API_BASE_URL}/auth/login`, credentials)
      .pipe(map((response) => this.startSession(response)));
  }

  register(data: RegisterRequest): Observable<User> {
    return this.http
      .post<AuthResponse>(`${API_BASE_URL}/auth/register`, data)
      .pipe(map((response) => this.startSession(response)));
  }

  /** Busca o usuário dono do token atual (usado ao recarregar a página). */
  loadUser(): Observable<User> {
    return this.http.get<ApiData<User>>(`${API_BASE_URL}/auth/me`).pipe(
      map((response) => response.data),
      tap((user) => this.currentUser.set(user)),
    );
  }

  logout(): Observable<void> {
    return this.http
      .post<void>(`${API_BASE_URL}/auth/logout`, {})
      .pipe(tap(() => this.clearSession()));
  }

  getToken(): string | null {
    return this.token();
  }

  /** Esquece a sessão localmente (logout ou token recusado pelo servidor). */
  clearSession(): void {
    this.storage.clear();
    this.token.set(null);
    this.currentUser.set(null);
  }

  private startSession(response: AuthResponse): User {
    this.storage.set(response.token);
    this.token.set(response.token);
    this.currentUser.set(response.user);
    return response.user;
  }
}
