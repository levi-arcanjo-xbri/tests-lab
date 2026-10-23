import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { apiErrorMessage, apiFieldErrors } from '../../../core/api/api-error';
import { AuthService } from '../../../core/auth/auth.service';

/**
 * Página de cadastro. Após cadastrar, o backend já devolve um token e o
 * usuário entra direto na home.
 *
 * Os erros de validação do backend (ex.: e-mail já cadastrado) aparecem
 * abaixo de cada campo, usando `fieldErrors`.
 */
@Component({
  selector: 'app-register-page',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './register.page.html',
})
export class RegisterPage {
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  protected readonly loading = signal(false);
  protected readonly error = signal<string | null>(null);
  protected readonly fieldErrors = signal<Record<string, string>>({});

  protected readonly form = inject(FormBuilder).nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(120)]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  protected submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading.set(true);
    this.error.set(null);
    this.fieldErrors.set({});

    this.auth.register(this.form.getRawValue()).subscribe({
      next: () => void this.router.navigate(['/']),
      error: (err: unknown) => {
        this.fieldErrors.set(apiFieldErrors(err));
        this.error.set(apiErrorMessage(err, 'Não foi possível concluir o cadastro.'));
        this.loading.set(false);
      },
    });
  }
}
