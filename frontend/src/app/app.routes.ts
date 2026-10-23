import { Routes } from '@angular/router';

import { authGuard, guestGuard } from './core/auth/auth.guard';

/**
 * Mapa de rotas do frontend.
 *
 * `loadComponent` com import() faz "lazy loading": o código de cada página
 * só é baixado quando o usuário navega até ela, deixando a carga inicial menor.
 *
 * As páginas autenticadas são filhas do Shell (layout com a barra superior).
 */
export const routes: Routes = [
  {
    path: 'login',
    canActivate: [guestGuard],
    loadComponent: () => import('./features/auth/login/login.page').then((m) => m.LoginPage),
  },
  {
    path: 'register',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./features/auth/register/register.page').then((m) => m.RegisterPage),
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () => import('./layout/shell/shell').then((m) => m.Shell),
    children: [
      {
        path: '',
        title: 'Projetos · Tasks',
        loadComponent: () =>
          import('./features/projects/project-list/project-list.page').then((m) => m.ProjectListPage),
      },
      {
        path: 'projects/:id',
        title: 'Projeto · Tasks',
        loadComponent: () =>
          import('./features/projects/project-detail/project-detail.page').then(
            (m) => m.ProjectDetailPage,
          ),
      },
    ],
  },
  { path: '**', redirectTo: '' },
];
