import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';

import { API_BASE_URL, ApiData } from '../../../core/api/api.config';
import { idempotencyHeaders } from '../../../core/api/idempotency';
import { Project, ProjectInput } from './project.models';

/**
 * Chamadas HTTP da API de projetos.
 *
 * Os componentes não usam HttpClient diretamente: falam com este serviço,
 * que conhece as URLs e desembrulha o envelope `{ data }` do Laravel.
 * O token é acrescentado pelo authInterceptor, não aqui.
 */
@Injectable({ providedIn: 'root' })
export class ProjectsService {
  private readonly http = inject(HttpClient);
  private readonly url = `${API_BASE_URL}/projects`;

  list(search = ''): Observable<Project[]> {
    const params = search ? new HttpParams().set('search', search) : undefined;
    return this.http.get<ApiData<Project[]>>(this.url, { params }).pipe(map((r) => r.data));
  }

  get(id: number): Observable<Project> {
    return this.http.get<ApiData<Project>>(`${this.url}/${id}`).pipe(map((r) => r.data));
  }

  /** Cria um projeto. A chave de idempotência evita duplicar em reenvios. */
  create(input: ProjectInput, idempotencyKey: string): Observable<Project> {
    return this.http
      .post<ApiData<Project>>(this.url, input, { headers: idempotencyHeaders(idempotencyKey) })
      .pipe(map((r) => r.data));
  }

  update(id: number, input: Partial<ProjectInput>): Observable<Project> {
    return this.http.patch<ApiData<Project>>(`${this.url}/${id}`, input).pipe(map((r) => r.data));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
