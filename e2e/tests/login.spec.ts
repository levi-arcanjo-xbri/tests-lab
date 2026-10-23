import { expect, test } from '@playwright/test';

import { registerNewUser } from './helpers';

/**
 * Jornadas de autenticação: login com o usuário do seed, credenciais
 * inválidas, cadastro e proteção das rotas.
 */

// Usuário criado pelo seed (backend/database/seeders/DatabaseSeeder.php).
const DEMO_EMAIL = 'demo@example.com';
const DEMO_PASSWORD = 'password';

test('usuário demo entra, vê seus projetos e sai', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveURL(/\/login$/);

  await page.getByLabel('E-mail').fill(DEMO_EMAIL);
  await page.getByLabel('Senha').fill(DEMO_PASSWORD);
  await page.getByRole('button', { name: 'Entrar' }).click();

  await expect(page.getByRole('heading', { name: 'Projetos' })).toBeVisible();
  await expect(page.getByRole('link', { name: /Estudo de arquitetura/ })).toBeVisible();

  await page.getByRole('button', { name: 'Sair' }).click();
  await expect(page).toHaveURL(/\/login$/);
});

test('credenciais inválidas mostram erro', async ({ page }) => {
  await page.goto('/login');

  await page.getByLabel('E-mail').fill(DEMO_EMAIL);
  await page.getByLabel('Senha').fill('senha-errada');
  await page.getByRole('button', { name: 'Entrar' }).click();

  await expect(page.getByRole('alert')).toContainText('Não foi possível entrar');
});

test('cadastro entra direto e mostra o estado vazio', async ({ page }) => {
  await registerNewUser(page, 'Ana Nova');

  await expect(page.getByText('Ana Nova')).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Nenhum projeto ainda' })).toBeVisible();
});

test('a sessão sobrevive ao recarregar a página', async ({ page }) => {
  await registerNewUser(page, 'Bruno Reload');

  await page.reload();

  await expect(page.getByRole('heading', { name: 'Projetos' })).toBeVisible();
  await expect(page.getByText('Bruno Reload')).toBeVisible();
});
