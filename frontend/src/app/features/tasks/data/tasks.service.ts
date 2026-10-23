import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';

import { API_BASE_URL, ApiData } from '../../../core/api/api.config';
import { idempotencyHeaders } from '../../../core/api/idempotency';
import { Task, TaskHistoryEntry, TaskInput, TaskStatus } from './task.models';

/**
 * Chamadas HTTP da API de tasks. As tasks são um recurso aninhado no
 * projeto: /api/projects/{projectId}/tasks/{taskId}.
 */
@Injectable({ providedIn: 'root' })
export class TasksService {
  private readonly http = inject(HttpClient);

  list(projectId: number): Observable<Task[]> {
    return this.http.get<ApiData<Task[]>>(this.base(projectId)).pipe(map((r) => r.data));
  }

  create(projectId: number, input: TaskInput, idempotencyKey: string): Observable<Task> {
    return this.http
      .post<ApiData<Task>>(this.base(projectId), input, {
        headers: idempotencyHeaders(idempotencyKey),
      })
      .pipe(map((r) => r.data));
  }

  update(projectId: number, taskId: number, input: Partial<TaskInput>): Observable<Task> {
    return this.http
      .patch<ApiData<Task>>(`${this.base(projectId)}/${taskId}`, input)
      .pipe(map((r) => r.data));
  }

  delete(projectId: number, taskId: number): Observable<void> {
    return this.http.delete<void>(`${this.base(projectId)}/${taskId}`);
  }

  /**
   * Pede a mudança de estado. Se a transição não for permitida, a API
   * responde 409 e o Observable emite erro.
   */
  transition(projectId: number, taskId: number, status: TaskStatus, idempotencyKey: string): Observable<Task> {
    return this.http
      .patch<ApiData<Task>>(
        `${this.base(projectId)}/${taskId}/status`,
        { status },
        { headers: idempotencyHeaders(idempotencyKey) },
      )
      .pipe(map((r) => r.data));
  }

  history(projectId: number, taskId: number): Observable<TaskHistoryEntry[]> {
    return this.http
      .get<ApiData<TaskHistoryEntry[]>>(`${this.base(projectId)}/${taskId}/history`)
      .pipe(map((r) => r.data));
  }

  private base(projectId: number): string {
    return `${API_BASE_URL}/projects/${projectId}/tasks`;
  }
}
