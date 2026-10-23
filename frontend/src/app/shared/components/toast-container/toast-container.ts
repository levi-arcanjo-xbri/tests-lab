import { Component, inject } from '@angular/core';

import { ToastService } from '../../../core/notifications/toast.service';

/**
 * Exibe os toasts do ToastService. Fica uma única vez no componente raiz.
 *
 * `aria-live="polite"` faz leitores de tela anunciarem as mensagens novas.
 */
@Component({
  selector: 'app-toast-container',
  template: `
    <div class="toasts" aria-live="polite">
      @for (toast of toasts(); track toast.id) {
        <div class="toast" [class.error]="toast.kind === 'error'" role="status">
          <span>{{ toast.message }}</span>
          <button type="button" class="close" aria-label="Fechar" (click)="dismiss(toast.id)">×</button>
        </div>
      }
    </div>
  `,
  styles: `
    .toasts {
      position: fixed;
      right: 1rem;
      bottom: 1rem;
      z-index: 100;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .toast {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 240px;
      max-width: 360px;
      padding: 0.75rem 0.9rem;
      border-radius: var(--radius-sm);
      border-left: 4px solid var(--success);
      background: var(--surface);
      box-shadow: var(--shadow-lg);
      font-size: 0.9rem;
      animation: slide-in 0.2s ease-out;
    }
    .toast.error {
      border-left-color: var(--danger);
    }
    .close {
      margin-left: auto;
      border: 0;
      background: none;
      color: var(--text-muted);
      font-size: 1.2rem;
      cursor: pointer;
    }
    @keyframes slide-in {
      from {
        transform: translateY(8px);
        opacity: 0;
      }
    }
  `,
})
export class ToastContainer {
  private readonly service = inject(ToastService);

  protected readonly toasts = this.service.toasts;

  protected dismiss(id: number): void {
    this.service.dismiss(id);
  }
}
