import { Component, DestroyRef, OnInit, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import {
  Subject,
  catchError,
  debounceTime,
  distinctUntilChanged,
  map,
  merge,
  of,
  startWith,
  switchMap,
  tap,
} from 'rxjs';

import { apiErrorMessage, apiFieldErrors } from '../../../core/api/api-error';
import { IdempotencyKeyHolder } from '../../../core/api/idempotency';
import { ToastService } from '../../../core/notifications/toast.service';
import { Icon } from '../../../shared/components/icon/icon';
import { Project, ProjectInput } from '../data/project.models';
import { ProjectsService } from '../data/projects.service';
import { ProjectForm } from '../project-form/project-form';

/**
 * Home: lista de projetos do usuário, com busca e criação.
 *
 * Trata os três estados obrigatórios de toda tela:
 * - carregando (`loading`): mostra blocos "skeleton";
 * - erro (`error`): mostra a mensagem e um botão para tentar de novo;
 * - vazio: lista vazia, com chamada para criar o primeiro projeto.
 */
@Component({
  selector: 'app-project-list-page',
  imports: [ReactiveFormsModule, RouterLink, Icon, ProjectForm],
  templateUrl: './project-list.page.html',
  styleUrl: './project-list.page.scss',
})
export class ProjectListPage implements OnInit {
  private readonly service = inject(ProjectsService);
  private readonly toast = inject(ToastService);
  private readonly router = inject(Router);
  private readonly destroyRef = inject(DestroyRef);

  /** Dispara um novo carregamento (botão "tentar de novo"). */
  private readonly reload$ = new Subject<void>();
  /** Uma chave de idempotência por tentativa de criação (ver core/api/idempotency.ts). */
  private readonly createKey = new IdempotencyKeyHolder();

  protected readonly search = new FormControl('', { nonNullable: true });
  protected readonly projects = signal<Project[]>([]);
  protected readonly loading = signal(true);
  protected readonly error = signal<string | null>(null);

  protected readonly showCreate = signal(false);
  protected readonly creating = signal(false);
  protected readonly createErrors = signal<Record<string, string>>({});

  ngOnInit(): void {
    this.watchSearch();
  }

  protected retry(): void {
    this.reload$.next();
  }

  protected create(input: ProjectInput): void {
    this.creating.set(true);
    this.createErrors.set({});

    this.service.create(input, this.createKey.current()).subscribe({
      next: (project) => {
        this.createKey.reset();
        this.creating.set(false);
        this.toast.success('Projeto criado.');
        void this.router.navigate(['/projects', project.id]);
      },
      error: (err: unknown) => {
        // A chave NÃO é trocada: se o usuário reenviar, o backend reconhece a tentativa.
        this.creating.set(false);
        const fieldErrors = apiFieldErrors(err);
        this.createErrors.set(fieldErrors);
        if (!Object.keys(fieldErrors).length) {
          this.toast.error(apiErrorMessage(err, 'Não foi possível criar o projeto.'));
        }
      },
    });
  }

  protected progress(project: Project): number {
    return project.tasks_count ? Math.round((project.done_tasks_count / project.tasks_count) * 100) : 0;
  }

  /**
   * Busca reativa com RxJS:
   * - debounceTime espera o usuário parar de digitar por 300 ms;
   * - distinctUntilChanged ignora o mesmo termo repetido;
   * - startWith faz a primeira carga sem esperar digitação;
   * - merge junta a digitação com o botão "tentar de novo";
   * - switchMap cancela a requisição anterior se um termo novo chegar
   *   (uma resposta antiga nunca sobrescreve a mais nova);
   * - catchError fica DENTRO do switchMap: um erro encerra só aquela
   *   requisição, e a busca continua funcionando depois;
   * - takeUntilDestroyed encerra a inscrição quando o componente sai da tela.
   */
  private watchSearch(): void {
    const term$ = this.search.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      startWith(this.search.value),
    );
    const retry$ = this.reload$.pipe(map(() => this.search.value));

    merge(term$, retry$)
      .pipe(
        tap(() => {
          this.loading.set(true);
          this.error.set(null);
        }),
        switchMap((term) =>
          this.service.list(term.trim()).pipe(
            catchError((err: unknown) => {
              this.error.set(apiErrorMessage(err, 'Não foi possível carregar os projetos.'));
              return of(null);
            }),
          ),
        ),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe((projects) => {
        if (projects) {
          this.projects.set(projects);
        }
        this.loading.set(false);
      });
  }
}
