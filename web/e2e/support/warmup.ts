import { chromium, type FullConfig } from '@playwright/test'

/**
 * Em dev o Vite compila os módulos sob demanda no primeiro acesso, o que leva dezenas
 * de segundos. Carregar as telas uma vez antes da suíte evita timeouts nos primeiros testes.
 */
export default async function warmup(config: FullConfig) {
  const baseURL = config.projects[0]?.use.baseURL ?? 'http://localhost:8080'
  const browser = await chromium.launch()
  const page = await browser.newPage({ baseURL })
  for (const path of ['/entrar', '/cadastro', '/verificar-email']) {
    await page.goto(path, { timeout: 180_000 })
    await page.locator('main').waitFor({ timeout: 180_000 })
  }
  await browser.close()
}
