import { Component, OnInit, inject, input, output } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { ProjectInput } from '../data/project.models';

/**
 * Formulário de projeto, reutilizado na criação (lista) e na edição (detalhe).
 *
 * Componente "de apresentação": não chama a API. Recebe dados por inputs e
 * avisa o pai por outputs; quem decide o que fazer com os dados (criar ou
 * atualizar) é a página. Isso deixa o componente simples de reutilizar e testar.
 *
 * - input()  : dados que vêm do pai (valores iniciais, estado de envio, erros);
 * - output() : eventos para o pai (`save` com os dados, `cancelled`).
 */
@Component({
  selector: 'app-project-form',
  imports: [ReactiveFormsModule],
  templateUrl: './project-form.html',
  styleUrl: './project-form.scss',
})
export class ProjectForm implements OnInit {
  readonly initial = input<ProjectInput | null>(null);
  readonly submitting = input(false);
  readonly errors = input<Record<string, string>>({});
  readonly submitLabel = input('Salvar');

  readonly save = output<ProjectInput>();
  readonly cancelled = output<void>();

  protected readonly form = inject(FormBuilder).nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(120)]],
    description: [''],
  });

  ngOnInit(): void {
    const initial = this.initial();
    if (initial) {
      this.form.setValue({ name: initial.name, description: initial.description ?? '' });
    }
  }

  protected submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const { name, description } = this.form.getRawValue();
    // Descrição vazia vira null, que é como o backend guarda "sem descrição".
    this.save.emit({ name: name.trim(), description: description.trim() || null });
  }
}
