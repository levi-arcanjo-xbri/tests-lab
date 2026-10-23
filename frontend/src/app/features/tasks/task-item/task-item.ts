import { DatePipe } from '@angular/common';
import { Component, inject, input, output, signal } from '@angular/core';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';

import { apiErrorMessage } from '../../../core/api/api-error';
import { IdempotencyKeyHolder } from '../../../core/api/idempotency';
import { ToastService } from '../../../core/notifications/toast.service';
import { Icon } from '../../../shared/components/icon/icon';
import { Task, TaskHistoryEntry } from '../data/task.models';
import { TasksService } from '../data/tasks.service';

/**
 * Uma linha da lista de tasks, com as ações sobre ela:
 * concluir/reabrir, editar o título, ver o histórico e apagar.
 *
 * O componente fala com a API e avisa a página pelos outputs `changed`
 * (task atualizada) e `removed` (task apagada), para ela atualizar a lista
 * e os contadores.
 */
@Component({
  selector: 'app-task-item',
  imports: [ReactiveFormsModule, DatePipe, Icon],
  templateUrl: './task-item.html',
  styleUrl: './task-item.scss',
})
export class TaskItem {
  private readonly service = inject(TasksService);
  private readonly toast = inject(ToastService);
  private readonly transitionKey = new IdempotencyKeyHolder();

  readonly task = input.required<Task>();
  readonly changed = output<Task>();
  readonly removed = output<number>();

  protected readonly busy = signal(false);
  protected readonly editing = signal(false);
  protected readonly title = new FormControl('', {
    nonNullable: true,
    validators: [Validators.required, Validators.maxLength(200)],
  });

  protected readonly showHistory = signal(false);
  protected readonly history = signal<TaskHistoryEntry[] | null>(null);
  protected readonly historyError = signal(false);

  /**
   * Conclui ou reabre. O destino vem de `allowed_transitions`, calculado pela
   * máquina de estados no backend: o frontend não decide a regra.
   */
  protected toggle(): void {
    const task = this.task();
    const target = task.allowed_transitions[0];
    if (!target || this.busy()) {
      return;
    }

    this.busy.set(true);
    this.service.transition(task.project_id, task.id, target, this.transitionKey.current()).subscribe({
      next: (updated) => {
        this.transitionKey.reset();
        this.busy.set(false);
        this.history.set(null); // o histórico mudou; recarrega se for aberto
        this.changed.emit(updated);
      },
      error: (err: unknown) => {
        this.busy.set(false);
        this.toast.error(apiErrorMessage(err, 'Não foi possível mudar o estado da task.'));
      },
    });
  }

  protected startEdit(): void {
    this.title.setValue(this.task().title);
    this.editing.set(true);
  }

  protected cancelEdit(): void {
    this.editing.set(false);
  }

  protected saveEdit(): void {
    // Enter e blur podem disparar juntos; ignora o segundo enquanto salva.
    if (this.busy()) {
      return;
    }
    const task = this.task();
    const title = this.title.value.trim();
    if (this.title.invalid || !title || title === task.title) {
      this.editing.set(false);
      return;
    }

    this.busy.set(true);
    this.service.update(task.project_id, task.id, { title }).subscribe({
      next: (updated) => {
        this.busy.set(false);
        this.editing.set(false);
        this.changed.emit(updated);
      },
      error: (err: unknown) => {
        this.busy.set(false);
        this.toast.error(apiErrorMessage(err, 'Não foi possível salvar a task.'));
      },
    });
  }

  protected remove(): void {
    const task = this.task();
    // confirm() nativo: simples e acessível. Uma tela maior poderia usar um modal próprio.
    if (!confirm(`Apagar a task "${task.title}"?`)) {
      return;
    }

    this.busy.set(true);
    this.service.delete(task.project_id, task.id).subscribe({
      next: () => {
        this.toast.success('Task apagada.');
        this.removed.emit(task.id);
      },
      error: (err: unknown) => {
        this.busy.set(false);
        this.toast.error(apiErrorMessage(err, 'Não foi possível apagar a task.'));
      },
    });
  }

  /** Abre/fecha o histórico. Carrega da API só na primeira abertura. */
  protected toggleHistory(): void {
    this.showHistory.update((open) => !open);
    if (this.showHistory() && this.history() === null) {
      this.loadHistory();
    }
  }

  protected loadHistory(): void {
    const task = this.task();
    this.historyError.set(false);
    this.service.history(task.project_id, task.id).subscribe({
      next: (entries) => this.history.set(entries),
      error: () => this.historyError.set(true),
    });
  }

  protected statusLabel(status: string | null): string {
    return status === 'done' ? 'Done' : status === 'todo' ? 'To do' : '—';
  }
}
