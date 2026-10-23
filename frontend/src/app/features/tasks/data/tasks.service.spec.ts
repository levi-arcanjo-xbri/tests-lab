import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { TasksService } from './tasks.service';

describe('TasksService', () => {
  let service: TasksService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(TasksService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('cria a task com a chave de idempotência no cabeçalho', () => {
    service.create(7, { title: 'Nova' }, 'chave-1').subscribe();

    const req = http.expectOne('/api/projects/7/tasks');
    expect(req.request.method).toBe('POST');
    expect(req.request.headers.get('Idempotency-Key')).toBe('chave-1');
    req.flush({ data: {} });
  });

  it('pede a transição de estado na rota /status', () => {
    let status = '';
    service.transition(7, 3, 'done', 'chave-2').subscribe((task) => (status = task.status));

    const req = http.expectOne('/api/projects/7/tasks/3/status');
    expect(req.request.method).toBe('PATCH');
    expect(req.request.body).toEqual({ status: 'done' });
    req.flush({ data: { status: 'done' } });

    expect(status).toBe('done');
  });
});
