/**
 * Tipos do contexto de tasks, espelhando o TaskResource do backend.
 */
export type TaskStatus = 'todo' | 'done';

export interface Task {
  id: number;
  project_id: number;
  title: string;
  description: string | null;
  status: TaskStatus;
  status_label: string;
  /** Estados para os quais a task pode ir agora (vem da máquina de estados do backend). */
  allowed_transitions: TaskStatus[];
  completed_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface TaskInput {
  title: string;
  description?: string | null;
}

/** Uma linha do histórico de estados. */
export interface TaskHistoryEntry {
  id: number;
  from_status: TaskStatus | null;
  to_status: TaskStatus;
  user: { id: number; name: string } | null;
  created_at: string;
}
