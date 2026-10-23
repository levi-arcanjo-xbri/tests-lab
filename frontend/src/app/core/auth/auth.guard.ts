import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { AuthService } from './auth.service';

/**
 * Guards de rota (funções chamadas pelo roteador antes de abrir uma página).
 *
 * São só conveniência de navegação: quem protege os dados de verdade é o
 * backend, que valida o token e a titularidade em cada requisição.
 */

/** Páginas que exigem login: sem token, manda para /login. */
export const authGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  return auth.isAuthenticated() ? true : inject(Router).createUrlTree(['/login']);
};

/** Login e cadastro: quem já está logado vai direto para a home. */
export const guestGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  return auth.isAuthenticated() ? inject(Router).createUrlTree(['/']) : true;
};
