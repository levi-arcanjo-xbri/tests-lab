import { Component, OnInit, computed, inject, input, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { forkJoin } from 'rxjs';

import { apiErrorMessage, apiFieldErrors } from '../../../core/api/api-error';
import { IdempotencyKeyHolder } from '../../../core/api/idempotency';
import { ToastService } from '../../../core/notifications/toast.service';
import { Icon } from '../../../shared/components/icon/icon';
import { Task, TaskStatus } from '../../tasks/data/task.models';
import { TasksService } from '../../tasks/data/tasks.service';
import { TaskItem } from '../../tasks/task-item/task-item';
import { Project, ProjectInput } from '../data/project.models';
import { ProjectsService } from '../data/projects.service';
import { ProjectForm } from '../project-form/project-form';

type Filter = 'all' | TaskStatus;

/**
 * Detalhe de um projeto: cabeçalho com progresso, edição e exclusão do
 * projeto, criação de tasks, filtro por estado e a lista de tasks.
 *
 * `id` chega como input porque o roteador está configurado com
 * withComponentInputBinding(): o parâmetro `:id` da URL vira input.
 *
 * Todas as tasks são carregadas de uma vez e o filtro é feito no cliente
 * (signals `computed`), assim os contadores das abas ficam sempre corretos.
 * A API também aceita `?status=` para listas grandes.
 */
@Component({
  selector: 'app-project-detail-page',
  imports: [ReactiveFormsModule, RouterLink, Icon, ProjectForm, TaskItem],
  templateUrl: './project-detail.page.html',
  styleUrl: './project-detail.page.scss',
})
export class ProjectDetailPage implements OnInit {
  private readonly projects = inject(ProjectsService);
  private readonly tasksApi = inject(TasksService);
  private readonly toast = inject(ToastService);
  private readonly router = inject(Router);
  private readonly createKey = new IdempotencyKeyHolder();

  readonly id = input.required<string>();

  protected readonly project = signal<Project | null>(null);
  protected readonly tasks = signal<Task[]>([]);
  protected readonly loading = signal(true);
  protected readonly error = signal<string | null>(null);
  protected readonly filter = signal<Filter>('all');

  protected readonly editing = signal(false);
  protected readonly saving = signal(false);
  protected readonly editErrors = signal<Record<string, string>>({});

  protected readonly adding = signal(false);
  /**
   * O <form> precisa de [formGroup]: é essa diretiva que emite (ngSubmit) e
   * impede o submit nativo do navegador (que recarregaria a página).
   */
  protected readonly newTaskForm = new FormGroup({
    title: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(200)],
    }),
  });
  protected readonly newTitle = this.newTaskForm.controls.title;

  /** Contadores derivados da lista: recalculados sozinhos quando `tasks` muda. */
  protected readonly counts = computed(() => {
    const all = this.tasks();
    const done = all.filter((task) => task.status === 'done').length;
    return { all: all.length, todo: all.length - done, done };
  });

  protected readonly progress = computed(() => {
    const { all, done } = this.counts();
    return all ? Math.round((done / all) * 100) : 0;
  });

  protected readonly visibleTasks = computed(() => {
    const filter = this.filter();
    return filter === 'all' ? this.tasks() : this.tasks().filter((task) => task.status === filter);
  });

  protected readonly filters: { value: Filter; label: string }[] = [
    { value: 'all', label: 'Todas' },
    { value: 'todo', label: 'To do' },
    { value: 'done', label: 'Done' },
  ];

  ngOnInit(): void {
    this.load();
  }

  /** Busca projeto e tasks em paralelo (forkJoin espera as duas respostas). */
  protected load(): void {
    const id = Number(this.id());
    this.loading.set(true);
    this.error.set(null);

    forkJoin({ project: this.projects.get(id), tasks: this.tasksApi.list(id) }).subscribe({
      next: ({ project, tasks }) => {
        this.project.set(project);
        this.tasks.set(tasks);
        this.loading.set(false);
      },
      error: (err: unknown) => {
        this.error.set(apiErrorMessage(err, 'Não foi possível carregar o projeto.'));
        this.loading.set(false);
      },
    });
  }

  protected addTask(): void {
    const title = this.newTitle.value.trim();
    const project = this.project();
    if (!title || !project || this.adding()) {
      return;
    }

    this.adding.set(true);
    this.tasksApi.create(project.id, { title }, this.createKey.current()).subscribe({
      next: (task) => {
        this.createKey.reset();
        this.adding.set(false);
        this.newTitle.reset();
        this.tasks.update((list) => [...list, task]);
        this.filter.set(this.filter() === 'done' ? 'all' : this.filter());
      },
      error: (err: unknown) => {
        this.adding.set(false);
        this.toast.error(apiErrorMessage(err, 'Não foi possível criar a task.'));
      },
    });
  }

  /** Substitui a task alterada e reordena: pendentes primeiro, como a API. */
  protected onTaskChanged(updated: Task): void {
    this.tasks.update((list) =>
      list
        .map((task) => (task.id === updated.id ? updated : task))
        .sort((a, b) => Number(a.status === 'done') - Number(b.status === 'done') || a.id - b.id),
    );
  }

  protected onTaskRemoved(id: number): void {
    this.tasks.update((list) => list.filter((task) => task.id !== id));
  }

  protected saveProject(input: ProjectInput): void {
    const project = this.project();
    if (!project) {
      return;
    }

    this.saving.set(true);
    this.editErrors.set({});
    this.projects.update(project.id, input).subscribe({
      next: (updated) => {
        this.project.set(updated);
        this.saving.set(false);
        this.editing.set(false);
        this.toast.success('Projeto atualizado.');
      },
      error: (err: unknown) => {
        this.saving.set(false);
        this.editErrors.set(apiFieldErrors(err));
      },
    });
  }

  protected deleteProject(): void {
    const project = this.project();
    if (!project || !confirm(`Apagar o projeto "${project.name}" e todas as suas tasks?`)) {
      return;
    }

    this.projects.delete(project.id).subscribe({
      next: () => {
        this.toast.success('Projeto apagado.');
        void this.router.navigate(['/']);
      },
      error: (err: unknown) => this.toast.error(apiErrorMessage(err, 'Não foi possível apagar o projeto.')),
    });
  }
}
