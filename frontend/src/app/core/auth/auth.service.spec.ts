import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { AuthService } from './auth.service';

/**
 * Testes unitários do AuthService. O HttpTestingController intercepta as
 * requisições: o teste verifica a URL chamada e devolve uma resposta falsa
 * com flush(), sem precisar do backend rodando.
 */
describe('AuthService', () => {
  let service: AuthService;
  let http: HttpTestingController;
  const user = { id: 1, name: 'Demo', email: 'demo@example.com' };

  beforeEach(() => {
    localStorage.clear();
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(AuthService);
    http = TestBed.inject(HttpTestingController);
  });

  // Garante que nenhuma requisição ficou sem resposta.
  afterEach(() => http.verify());

  it('guarda o token e o usuário após o login', () => {
    service.login({ email: user.email, password: 'password' }).subscribe();

    http.expectOne('/api/auth/login').flush({ token: 'abc', token_type: 'Bearer', user });

    expect(service.getToken()).toBe('abc');
    expect(service.isAuthenticated()).toBeTrue();
    expect(service.user()).toEqual(user);
    expect(localStorage.getItem('auth_token')).toBe('abc');
  });

  it('inicia a sessão após o cadastro', () => {
    service
      .register({ name: 'Demo', email: user.email, password: '12345678', password_confirmation: '12345678' })
      .subscribe();

    const req = http.expectOne('/api/auth/register');
    expect(req.request.method).toBe('POST');
    req.flush({ token: 'novo', token_type: 'Bearer', user });

    expect(service.getToken()).toBe('novo');
  });

  it('carrega o usuário a partir do token', () => {
    service.loadUser().subscribe();

    http.expectOne('/api/auth/me').flush({ data: user });

    expect(service.user()).toEqual(user);
  });

  it('limpa a sessão no logout', () => {
    localStorage.setItem('auth_token', 'abc');
    service.logout().subscribe();

    http.expectOne('/api/auth/logout').flush(null);

    expect(service.isAuthenticated()).toBeFalse();
    expect(service.user()).toBeNull();
    expect(localStorage.getItem('auth_token')).toBeNull();
  });
});
