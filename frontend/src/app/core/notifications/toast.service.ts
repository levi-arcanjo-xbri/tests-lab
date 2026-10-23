import { Injectable, signal } from '@angular/core';

export type ToastKind = 'success' | 'error';

export interface Toast {
  id: number;
  message: string;
  kind: ToastKind;
}

/**
 * Notificações rápidas ("toasts") no canto da tela.
 *
 * O estado é um signal com a lista de toasts visíveis. O componente
 * ToastContainer lê esse signal e se atualiza sozinho quando ele muda.
 * Qualquer tela injeta o serviço e chama success() ou error().
 */
@Injectable({ providedIn: 'root' })
export class ToastService {
  private nextId = 1;
  private readonly items = signal<Toast[]>([]);

  /** Somente leitura para quem consome; só o serviço altera a lista. */
  readonly toasts = this.items.asReadonly();

  success(message: string): void {
    this.show(message, 'success');
  }

  error(message: string): void {
    this.show(message, 'error');
  }

  dismiss(id: number): void {
    this.items.update((list) => list.filter((toast) => toast.id !== id));
  }

  private show(message: string, kind: ToastKind): void {
    const toast: Toast = { id: this.nextId++, message, kind };
    this.items.update((list) => [...list, toast]);
    setTimeout(() => this.dismiss(toast.id), 4000);
  }
}
