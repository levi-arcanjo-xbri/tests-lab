import { registerLocaleData } from '@angular/common';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import localePt from '@angular/common/locales/pt';
import {
  ApplicationConfig,
  LOCALE_ID,
  provideBrowserGlobalErrorListeners,
  provideZoneChangeDetection,
} from '@angular/core';
import { provideRouter, withComponentInputBinding } from '@angular/router';

import { routes } from './app.routes';
import { authInterceptor } from './core/auth/auth.interceptor';

// Datas e números no formato brasileiro (ex.: o pipe `date` mostra 23/10/2026).
registerLocaleData(localePt);

/**
 * Providers globais da aplicação (equivale ao antigo AppModule).
 *
 * - provideRouter: rotas; withComponentInputBinding() entrega parâmetros
 *   da URL (ex.: `:id`) como inputs dos componentes de página;
 * - provideHttpClient: HttpClient com o interceptor que injeta o token;
 * - LOCALE_ID: idioma usado pelos pipes de data e número.
 */
export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes, withComponentInputBinding()),
    provideHttpClient(withInterceptors([authInterceptor])),
    { provide: LOCALE_ID, useValue: 'pt-BR' },
  ],
};
