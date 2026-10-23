import { Component, OnInit, inject } from '@angular/core';
import { Router, RouterLink, RouterOutlet } from '@angular/router';
import { finalize } from 'rxjs';

import { AuthService } from '../../core/auth/auth.service';
import { Icon } from '../../shared/components/icon/icon';

/**
 * Layout das páginas autenticadas: barra superior com a marca, o nome do
 * usuário e o botão de sair. O conteúdo da página filha aparece no
 * <router-outlet> (rotas "children" em app.routes.ts).
 */
@Component({
  selector: 'app-shell',
  imports: [RouterOutlet, RouterLink, Icon],
  templateUrl: './shell.html',
  styleUrl: './shell.scss',
})
export class Shell implements OnInit {
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  protected readonly user = this.auth.user;

  ngOnInit(): void {
    // Ao recarregar a página só temos o token; buscamos quem é o usuário.
    // Se o token tiver expirado, o interceptor trata o 401 e volta ao login.
    if (!this.user()) {
      this.auth.loadUser().subscribe({ error: () => undefined });
    }
  }

  protected logout(): void {
    this.auth
      .logout()
      .pipe(
        // finalize roda com sucesso ou erro: a sessão local sempre é encerrada.
        finalize(() => {
          this.auth.clearSession();
          void this.router.navigate(['/login']);
        }),
      )
      .subscribe({ error: () => undefined });
  }
}
