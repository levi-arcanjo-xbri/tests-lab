import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Task } from '../data/task.models';
import { TaskItem } from './task-item';

describe('TaskItem', () => {
  let fixture: ComponentFixture<TaskItem>;
  let http: HttpTestingController;

  const task: Task = {
    id: 3,
    project_id: 7,
    title: 'Escrever testes',
    description: null,
    status: 'todo',
    status_label: 'To do',
    allowed_transitions: ['done'],
    completed_at: null,
    created_at: '2026-10-23T10:00:00Z',
    updated_at: '2026-10-23T10:00:00Z',
  };

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [TaskItem],
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    fixture = TestBed.createComponent(TaskItem);
    // setInput preenche um input() do componente, como o pai faria no template.
    fixture.componentRef.setInput('task', task);
    http = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  it('pede a transição indicada pela API e emite a task atualizada', () => {
    let emitted: Task | undefined;
    fixture.componentInstance.changed.subscribe((t) => (emitted = t));

    (fixture.nativeElement.querySelector('[role="checkbox"]') as HTMLButtonElement).click();

    const req = http.expectOne('/api/projects/7/tasks/3/status');
    expect(req.request.body).toEqual({ status: 'done' });
    expect(req.request.headers.has('Idempotency-Key')).toBeTrue();
    req.flush({ data: { ...task, status: 'done', allowed_transitions: ['todo'] } });

    expect(emitted?.status).toBe('done');
  });

  it('carrega o histórico só ao abrir', () => {
    const button = fixture.nativeElement.querySelector('[aria-label="Histórico"]') as HTMLButtonElement;
    button.click();
    fixture.detectChanges();

    http.expectOne('/api/projects/7/tasks/3/history').flush({
      data: [{ id: 1, from_status: null, to_status: 'todo', user: { id: 1, name: 'Demo' }, created_at: task.created_at }],
    });
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('Criada como');
  });
});
