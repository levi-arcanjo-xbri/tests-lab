/**
 * Ponto de entrada do frontend: inicia a aplicação Angular com o componente
 * raiz (App) e os providers globais definidos em app.config.ts.
 */
import { bootstrapApplication } from '@angular/platform-browser';

import { App } from './app/app';
import { appConfig } from './app/app.config';

bootstrapApplication(App, appConfig).catch((err) => console.error(err));
