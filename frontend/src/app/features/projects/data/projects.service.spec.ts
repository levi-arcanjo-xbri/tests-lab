import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { ProjectsService } from './projects.service';

describe('ProjectsService', () => {
  let service: ProjectsService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(ProjectsService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('lista projetos desembrulhando o envelope data', () => {
    let names: string[] = [];
    service.list('casa').subscribe((projects) => (names = projects.map((p) => p.name)));

    const req = http.expectOne((r) => r.url === '/api/projects');
    expect(req.request.params.get('search')).toBe('casa');
    req.flush({ data: [{ id: 1, name: 'Casa' }] });

    expect(names).toEqual(['Casa']);
  });

  it('envia a Idempotency-Key ao criar', () => {
    service.create({ name: 'Novo', description: null }, 'k-1').subscribe();

    const req = http.expectOne('/api/projects');
    expect(req.request.headers.get('Idempotency-Key')).toBe('k-1');
    req.flush({ data: {} });
  });
});
