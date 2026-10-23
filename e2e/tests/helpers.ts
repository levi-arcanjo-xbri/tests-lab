import { Page, expect } from '@playwright/test';

/**
 * Funções reaproveitadas pelos testes E2E.
 *
 * Os testes rodam em paralelo contra o mesmo banco, então cada um cria o
 * próprio usuário (e-mail único). Assim um teste nunca enxerga os dados
 * de outro.
 */
export async function registerNewUser(page: Page, name = 'Pessoa Teste'): Promise<string> {
  const email = `e2e-${Date.now()}-${Math.random().toString(36).slice(2, 8)}@example.com`;

  await page.goto('/register');
  await page.getByLabel('Nome').fill(name);
  await page.getByLabel('E-mail').fill(email);
  await page.getByLabel('Senha', { exact: true }).fill('senha-segura');
  await page.getByLabel('Confirmar senha').fill('senha-segura');
  await page.getByRole('button', { name: 'Criar conta' }).click();

  await expect(page.getByRole('heading', { name: 'Projetos' })).toBeVisible();
  return email;
}

/** Cria um projeto pela tela de lista e espera abrir a página de detalhe. */
export async function createProject(page: Page, name: string, description = ''): Promise<void> {
  await page.getByRole('button', { name: 'Novo projeto' }).first().click();
  await page.getByLabel('Nome').fill(name);
  if (description) {
    await page.getByLabel('Descrição').fill(description);
  }
  await page.getByRole('button', { name: 'Criar projeto' }).click();

  await expect(page.getByRole('heading', { level: 1, name })).toBeVisible();
}

/** Adiciona uma task na página de detalhe do projeto. */
export async function addTask(page: Page, title: string): Promise<void> {
  await page.getByLabel('Nova task').fill(title);
  await page.getByRole('button', { name: 'Adicionar' }).click();
  await expect(page.getByText(title, { exact: true })).toBeVisible();
}
