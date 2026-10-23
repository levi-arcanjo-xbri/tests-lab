import { HttpClient, provideHttpClient, withInterceptors } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Router, provideRouter } from '@angular/router';

import { authInterceptor } from './auth.interceptor';
import { AuthService } from './auth.service';

/**
 * Testes do interceptor: o HttpClient é configurado com o interceptor real e
 * o HttpTestingController captura a requisição para inspecionar os cabeçalhos.
 */
describe('authInterceptor', () => {
  let http: HttpClient;
  let controller: HttpTestingController;

  beforeEach(() => {
    localStorage.setItem('auth_token', 'token-123');
    TestBed.configureTestingModule({
      providers: [
        provideRouter([]),
        provideHttpClient(withInterceptors([authInterceptor])),
        provideHttpClientTesting(),
      ],
    });
    http = TestBed.inject(HttpClient);
    controller = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    controller.verify();
    localStorage.clear();
  });

  it('envia o token como Bearer nas chamadas à API', () => {
    http.get('/api/auth/me').subscribe();

    const req = controller.expectOne('/api/auth/me');
    expect(req.request.headers.get('Authorization')).toBe('Bearer token-123');
    req.flush({});
  });

  it('não envia o token para outras origens', () => {
    http.get('https://example.com/data').subscribe();

    const req = controller.expectOne('https://example.com/data');
    expect(req.request.headers.has('Authorization')).toBeFalse();
    req.flush({});
  });

  it('encerra a sessão e redireciona ao receber 401', () => {
    const router = TestBed.inject(Router);
    spyOn(router, 'navigate').and.resolveTo(true);

    http.get('/api/auth/me').subscribe({ error: () => undefined });
    controller.expectOne('/api/auth/me').flush({}, { status: 401, statusText: 'Unauthorized' });

    expect(TestBed.inject(AuthService).isAuthenticated()).toBeFalse();
    expect(router.navigate).toHaveBeenCalledWith(['/login']);
  });
});
