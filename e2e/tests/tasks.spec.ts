import { expect, test } from '@playwright/test';

import { addTask, createProject, registerNewUser } from './helpers';

/**
 * Jornadas de tasks: adicionar, concluir/reabrir (máquina de estados),
 * filtrar, ver o histórico, editar e apagar.
 */

test.beforeEach(async ({ page }) => {
  await registerNewUser(page);
  await createProject(page, 'Projeto com tasks');
});

test('conclui uma task, atualiza o progresso e filtra por estado', async ({ page }) => {
  await addTask(page, 'Comprar tinta');
  await addTask(page, 'Pintar a parede');
  await expect(page.getByText('0 de 2 concluídas')).toBeVisible();

  await page.getByRole('checkbox', { name: 'Concluir Comprar tinta' }).click();
  await expect(page.getByRole('checkbox', { name: 'Reabrir Comprar tinta' })).toBeChecked();
  await expect(page.getByText('1 de 2 concluídas · 50%')).toBeVisible();

  await page.getByRole('tab', { name: /Done/ }).click();
  await expect(page.getByText('Comprar tinta')).toBeVisible();
  await expect(page.getByText('Pintar a parede')).toHaveCount(0);

  await page.getByRole('tab', { name: /To do/ }).click();
  await expect(page.getByText('Pintar a parede')).toBeVisible();
  await expect(page.getByText('Comprar tinta')).toHaveCount(0);
});

test('mostra o histórico de estados da task', async ({ page }) => {
  await addTask(page, 'Revisar texto');

  await page.getByRole('checkbox', { name: 'Concluir Revisar texto' }).click();
  await page.getByRole('checkbox', { name: 'Reabrir Revisar texto' }).click();
  await expect(page.getByRole('checkbox', { name: 'Concluir Revisar texto' })).not.toBeChecked();

  await page.getByRole('button', { name: 'Histórico' }).click();

  const history = page.locator('.history li');
  await expect(history).toHaveCount(3);
  await expect(history.nth(0)).toContainText('Criada como To do');
  await expect(history.nth(1)).toContainText('To do → Done');
  await expect(history.nth(2)).toContainText('Done → To do');
});

test('edita o título e apaga a task', async ({ page }) => {
  await addTask(page, 'Título antigo');

  await page.getByRole('button', { name: 'Editar' }).last().click();
  const input = page.getByLabel('Título da task');
  await input.fill('Título novo');
  await input.press('Enter');
  await expect(page.getByText('Título novo', { exact: true })).toBeVisible();

  page.once('dialog', (dialog) => dialog.accept());
  await page.getByRole('button', { name: 'Apagar' }).last().click();

  await expect(page.getByRole('heading', { name: 'Nenhuma task ainda' })).toBeVisible();
});
