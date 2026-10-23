import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthService } from '../../../core/auth/auth.service';

/**
 * Página de login.
 *
 * Usa Reactive Forms: o formulário é declarado no TypeScript (`form`) e
 * ligado ao HTML com [formGroup] e formControlName. As validações do
 * cliente (required, email) só melhoram a experiência; o backend valida
 * de novo.
 *
 * Estados da tela em signals: `loading` desabilita o botão durante a
 * requisição e `error` mostra a falha.
 */
@Component({
  selector: 'app-login-page',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './login.page.html',
})
export class LoginPage {
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  protected readonly loading = signal(false);
  protected readonly error = signal<string | null>(null);

  protected readonly form = inject(FormBuilder).nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required]],
  });

  protected submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading.set(true);
    this.error.set(null);

    this.auth.login(this.form.getRawValue()).subscribe({
      next: () => void this.router.navigate(['/']),
      error: () => {
        // Mensagem genérica de propósito: não revela se o e-mail existe.
        this.error.set('Não foi possível entrar. Verifique o e-mail e a senha.');
        this.loading.set(false);
      },
    });
  }
}
