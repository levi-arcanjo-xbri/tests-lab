import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

import { API_BASE_URL } from '../api/api.config';
import { AuthService } from './auth.service';

/**
 * Interceptor HTTP: toda requisição feita com HttpClient passa por aqui.
 *
 * 1. Nas chamadas à nossa API, acrescenta `Authorization: Bearer <token>`.
 *    Chamadas a outros domínios não recebem o token (evita vazá-lo).
 * 2. Se a API responder 401 (token inválido ou revogado), encerra a sessão
 *    local e manda o usuário para o login.
 *
 * É registrado em app.config.ts com provideHttpClient(withInterceptors(...)).
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthService);
  const router = inject(Router);
  const token = auth.getToken();

  const isApiCall = req.url.startsWith(API_BASE_URL);
  const request =
    isApiCall && token
      ? req.clone({ setHeaders: { Authorization: `Bearer ${token}`, Accept: 'application/json' } })
      : req;

  return next(request).pipe(
    catchError((error: unknown) => {
      if (isApiCall && error instanceof HttpErrorResponse && error.status === 401) {
        auth.clearSession();
        void router.navigate(['/login']);
      }
      return throwError(() => error);
    }),
  );
};
