import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';

import { ToastContainer } from './shared/components/toast-container/toast-container';

/**
 * Componente raiz. Só contém o <router-outlet>, onde o roteador desenha a
 * página atual, e o container de notificações, comum a todas as telas.
 */
@Component({
  selector: 'app-root',
  imports: [RouterOutlet, ToastContainer],
  template: `
    <router-outlet />
    <app-toast-container />
  `,
})
export class App {}
