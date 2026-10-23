import { Component, input } from '@angular/core';

/**
 * Nomes dos ícones disponíveis. Desenhos no estilo "outline" (traço),
 * escritos à mão em SVG para não depender de biblioteca de ícones.
 */
export type IconName =
  | 'plus'
  | 'check'
  | 'trash'
  | 'edit'
  | 'history'
  | 'search'
  | 'arrow-left'
  | 'logout'
  | 'folder';

const PATHS: Record<IconName, string> = {
  plus: 'M12 5v14M5 12h14',
  check: 'M5 13l4 4L19 7',
  trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3',
  edit: 'M4 20h4L19 9l-4-4L4 16v4zM14 6l4 4',
  history: 'M12 8v4l3 2M3 12a9 9 0 109-9 9 9 0 00-7 3.4M3 4v4h4',
  search: 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
  'arrow-left': 'M19 12H5M12 19l-7-7 7-7',
  logout: 'M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3M10 17l-5-5 5-5M5 12h11',
  folder: 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
};

/**
 * Ícone SVG. Uso: <app-icon name="trash" />
 *
 * `input.required<IconName>()` é a API de inputs baseada em signals do
 * Angular: o valor é lido como função, `name()`.
 */
@Component({
  selector: 'app-icon',
  template: `
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <path [attr.d]="path()" />
    </svg>
  `,
  styles: `
    :host {
      display: inline-flex;
    }
    svg {
      width: 1em;
      height: 1em;
    }
  `,
})
export class Icon {
  readonly name = input.required<IconName>();

  protected path(): string {
    return PATHS[this.name()];
  }
}
