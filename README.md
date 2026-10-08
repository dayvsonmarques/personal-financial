# personal-financial

Financeiro Pro — aplicativo web para controle financeiro pessoal: contas, cartões e faturas, compras parceladas, recorrências e dashboard mensal.

## Status

M1 (fundação) concluído: ambiente, CI, multi-tenant, auditoria, autenticação completa e configurações de perfil e organização. Próximo marco: M2.

## Rodando localmente

Requisitos: Docker com Compose.

```bash
cp api/.env.example api/.env
make build
docker compose run --rm --no-deps php composer install
docker compose run --rm --no-deps php php artisan key:generate
make up                                  # o container web instala as dependências do Nuxt
make migrate
```

| Endereço | O quê |
|---|---|
| http://localhost:8080 | app (SPA e API na mesma origem) |
| http://localhost:8026 | Mailpit (e-mails de verificação e senha) |

```bash
make test                                # Pest (API) + Vitest (web)
make lint                                # Pint, Larastan, ESLint
make e2e                                 # Playwright, todos os navegadores
make e2e p="--project=chromium"          # só Chromium, como no CI
```

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

## Créditos

Desenvolvido com assistência do Claude Code.

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
