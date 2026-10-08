import { expect, test } from '@playwright/test'

test('a aplicação carrega com o título do produto', async ({ page }) => {
  await page.goto('/')
  await expect(page).toHaveTitle(/Financeiro Pro/)
})
