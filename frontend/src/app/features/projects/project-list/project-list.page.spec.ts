import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { ProjectListPage } from './project-list.page';

/**
 * Testes de componente: renderizam o template e verificam os estados da
 * tela (carregando, vazio, erro, com dados).
 */
describe('ProjectListPage', () => {
  let fixture: ComponentFixture<ProjectListPage>;
  let http: HttpTestingController;
  let el: HTMLElement;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [ProjectListPage],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    });
    fixture = TestBed.createComponent(ProjectListPage);
    http = TestBed.inject(HttpTestingController);
    el = fixture.nativeElement as HTMLElement;
    fixture.detectChanges();
  });

  it('mostra o carregamento e depois os projetos com o progresso', () => {
    expect(el.querySelector('[aria-busy="true"]')).not.toBeNull();

    http.expectOne('/api/projects').flush({
      data: [{ id: 1, name: 'Site', description: null, tasks_count: 4, done_tasks_count: 1 }],
    });
    fixture.detectChanges();

    expect(el.textContent).toContain('Site');
    expect(el.textContent).toContain('1 de 4 concluídas');
    expect(el.textContent).toContain('25%');
  });

  it('mostra o estado vazio', () => {
    http.expectOne('/api/projects').flush({ data: [] });
    fixture.detectChanges();

    expect(el.textContent).toContain('Nenhum projeto ainda');
  });

  it('mostra o erro e permite tentar de novo', () => {
    http.expectOne('/api/projects').flush({ message: 'falhou' }, { status: 500, statusText: 'Error' });
    fixture.detectChanges();

    expect(el.querySelector('[role="alert"]')?.textContent).toContain('falhou');

    (el.querySelector('.state button') as HTMLButtonElement).click();
    http.expectOne('/api/projects').flush({ data: [] });
    fixture.detectChanges();

    expect(el.textContent).toContain('Nenhum projeto ainda');
  });
});
