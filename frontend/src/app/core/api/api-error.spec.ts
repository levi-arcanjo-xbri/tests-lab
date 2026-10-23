import { HttpErrorResponse } from '@angular/common/http';

import { apiErrorMessage, apiFieldErrors } from './api-error';

describe('apiErrorMessage / apiFieldErrors', () => {
  const validation = new HttpErrorResponse({
    status: 422,
    error: { message: 'Dados inválidos', errors: { name: ['O nome já está em uso.'] } },
  });

  it('prioriza a primeira mensagem de validação', () => {
    expect(apiErrorMessage(validation, 'fallback')).toBe('O nome já está em uso.');
  });

  it('usa a message geral quando não há erros de campo', () => {
    const conflict = new HttpErrorResponse({ status: 409, error: { message: 'Transição inválida' } });
    expect(apiErrorMessage(conflict, 'fallback')).toBe('Transição inválida');
  });

  it('identifica falta de conexão', () => {
    expect(apiErrorMessage(new HttpErrorResponse({ status: 0 }), 'fallback')).toContain('Sem conexão');
  });

  it('usa o fallback para erros que não são HTTP', () => {
    expect(apiErrorMessage(new Error('x'), 'fallback')).toBe('fallback');
  });

  it('mapeia os erros por campo só em respostas 422', () => {
    expect(apiFieldErrors(validation)).toEqual({ name: 'O nome já está em uso.' });
    expect(apiFieldErrors(new HttpErrorResponse({ status: 500 }))).toEqual({});
  });
});
