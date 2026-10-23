/**
 * Tipos do contexto de projetos, espelhando o ProjectResource do backend.
 */
export interface Project {
  id: number;
  name: string;
  description: string | null;
  tasks_count: number;
  done_tasks_count: number;
  created_at: string;
  updated_at: string;
}

/** Corpo aceito na criação e na edição. */
export interface ProjectInput {
  name: string;
  description: string | null;
}
