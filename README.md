# personal-financial

Financeiro Pro — aplicativo web para controle financeiro pessoal: contas, cartões e faturas, compras parceladas, recorrências e dashboard mensal.

## Status

Em especificação. O código ainda não foi iniciado.

## Documentação

- [Especificação](financeiro-pro-spec-v2.md) — escopo, regras de negócio, modelo de dados e API
- [Backlog do MVP](financeiro-pro-backlog-mvp.md) — histórias de usuário organizadas em marcos

## Stack

- **API:** Laravel, PHP 8.4+, PostgreSQL 16+, Sanctum
- **Web:** Nuxt 4 (SPA), TypeScript, Nuxt UI, ECharts
- **Qualidade:** Pest, Vitest, Playwright, GitHub Actions, Sentry
- **Infra:** VPS com Docker Compose

## Estrutura

```
api/   # backend Laravel
web/   # frontend Nuxt
```
