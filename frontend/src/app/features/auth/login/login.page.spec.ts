import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { LoginPage } from './login.page';

/**
 * Teste de componente da página de login: preenche o formulário como um
 * usuário faria e verifica a mensagem de erro quando a API recusa.
 */
describe('LoginPage', () => {
  beforeEach(() => {
    localStorage.clear();
    TestBed.configureTestingModule({
      imports: [LoginPage],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    });
  });

  it('mostra a mensagem de erro quando o login falha', () => {
    const fixture = TestBed.createComponent(LoginPage);
    const el = fixture.nativeElement as HTMLElement;
    fixture.detectChanges();

    const email = el.querySelector<HTMLInputElement>('#email')!;
    const password = el.querySelector<HTMLInputElement>('#password')!;
    email.value = 'demo@example.com';
    email.dispatchEvent(new Event('input'));
    password.value = 'errada';
    password.dispatchEvent(new Event('input'));
    el.querySelector('form')!.dispatchEvent(new Event('submit'));

    TestBed.inject(HttpTestingController)
      .expectOne('/api/auth/login')
      .flush({ message: 'erro' }, { status: 422, statusText: 'Unprocessable Content' });
    fixture.detectChanges();

    expect(el.querySelector('[role="alert"]')?.textContent).toContain('Não foi possível entrar');
  });
});
