import { expect, test } from '@playwright/test';

import { createProject, registerNewUser } from './helpers';

/**
 * Jornadas de projetos: criar, buscar, editar e apagar.
 */

test.beforeEach(async ({ page }) => {
  await registerNewUser(page);
});

test('cria, edita e apaga um projeto', async ({ page }) => {
  await createProject(page, 'Viagem de férias', 'Roteiro e reservas');
  await expect(page.getByText('Roteiro e reservas')).toBeVisible();

  await page.getByRole('button', { name: 'Editar' }).click();
  await page.getByLabel('Nome').fill('Viagem de dezembro');
  await page.getByRole('button', { name: 'Salvar' }).click();
  await expect(page.getByRole('heading', { level: 1, name: 'Viagem de dezembro' })).toBeVisible();

  // confirm() nativo: o Playwright aceita o diálogo automaticamente com este handler.
  page.once('dialog', (dialog) => dialog.accept());
  await page.getByRole('button', { name: 'Apagar' }).click();

  await expect(page.getByRole('heading', { name: 'Projetos' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Nenhum projeto ainda' })).toBeVisible();
});

test('recusa nome de projeto repetido', async ({ page }) => {
  await createProject(page, 'Repetido');
  await page.getByRole('link', { name: 'Projetos' }).click();

  await page.getByRole('button', { name: 'Novo projeto' }).first().click();
  await page.getByLabel('Nome').fill('Repetido');
  await page.getByRole('button', { name: 'Criar projeto' }).click();

  await expect(page.getByText(/já está sendo utilizado|já está em uso/)).toBeVisible();
});

test('busca projetos pelo nome', async ({ page }) => {
  await createProject(page, 'Reforma da cozinha');
  await page.getByRole('link', { name: 'Projetos' }).click();
  await createProject(page, 'Curso de inglês');
  await page.getByRole('link', { name: 'Projetos' }).click();

  await page.getByLabel('Buscar projetos').fill('cozinha');

  await expect(page.getByRole('link', { name: /Reforma da cozinha/ })).toBeVisible();
  await expect(page.getByRole('link', { name: /Curso de inglês/ })).toHaveCount(0);
});
