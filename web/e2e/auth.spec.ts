import { expect, test } from '@playwright/test'
import { linkFromLatestEmail, uniqueEmail } from './support/mailpit'

// Fluxo 1 (US-103, D20): cadastro → verificação por e-mail → app → sair → entrar.
test('cadastro, verificação de e-mail e novo login', async ({ page }) => {
  const email = uniqueEmail('auth')
  const password = 'senha-e2e-123'

  await page.goto('/cadastro')
  await page.getByLabel('Nome').fill('Pessoa E2E')
  await page.getByLabel('E-mail').fill(email)
  await page.getByLabel('Senha', { exact: true }).fill(password)
  await page.getByLabel('Confirmar senha').fill(password)
  await page.getByRole('button', { name: 'Criar conta' }).click()

  await expect(page).toHaveURL('/verificar-email')
  await expect(page.getByRole('heading', { name: 'Verifique seu e-mail' })).toBeVisible()
  await expect(page.getByText(email)).toBeVisible()

  // Sem confirmar, as telas do app continuam bloqueadas.
  await page.goto('/configuracoes/perfil')
  await expect(page).toHaveURL('/verificar-email')

  await page.goto(await linkFromLatestEmail(email, '/email/verify/'))
  await expect(page).toHaveURL('/?verified=1')
  await expect(page.getByText('Olá, Pessoa E2E')).toBeVisible()
  await expect(page.getByText('E-mail confirmado').first()).toBeVisible()

  await page.getByRole('button', { name: 'Menu do usuário' }).filter({ visible: true }).click()
  await page.getByRole('menuitem', { name: 'Sair' }).click()
  await expect(page).toHaveURL('/entrar')

  await page.getByLabel('E-mail').fill(email)
  await page.getByLabel('Senha', { exact: true }).fill(password)
  await page.getByRole('button', { name: 'Entrar' }).click()

  await expect(page).toHaveURL('/')
  await expect(page.getByText('Olá, Pessoa E2E')).toBeVisible()
})

test('login com senha errada mostra o erro no campo', async ({ page }) => {
  await page.goto('/entrar')
  await page.getByLabel('E-mail').fill(uniqueEmail('inexistente'))
  await page.getByLabel('Senha', { exact: true }).fill('qualquer-coisa')
  await page.getByRole('button', { name: 'Entrar' }).click()

  await expect(page.getByText('Essas credenciais não foram encontradas em nossos registros.')).toBeVisible()
  await expect(page).toHaveURL('/entrar')
})
