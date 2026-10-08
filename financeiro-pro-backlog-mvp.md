# Financeiro Pro — Backlog do MVP

Derivado de [financeiro-pro-spec-v2.md](financeiro-pro-spec-v2.md). As referências `RF-`, `RN-`, `CA-`, `RNF-` e `D` apontam para a spec; o detalhe das regras fica lá e não é repetido aqui.

**Estimativa:** P = até 1 dia · M = 2–3 dias · G = 4–5 dias (uma pessoa, incluindo testes).

---

## Definition of Done (vale para todas as histórias)

- [ ] Endpoint com FormRequest (validação) e Policy (autorização)
- [ ] Model de domínio com escopo de organização + teste de acesso cruzado retornando `404` (CA-01)
- [ ] Regras de negócio em Action/Service com testes Pest cobrindo os casos da RN citada
- [ ] Alterações de entidades de domínio registradas no log de atividades (RF-100)
- [ ] Telas com estados de loading, vazio e erro; funcionais a partir de 360 px
- [ ] Valores e datas formatados em pt-BR
- [ ] CI verde (lint, testes de API, testes de componentes e E2E)

---

## Marcos

| Marco | Objetivo | Histórias | Estimativa |
|---|---|---|---|
| **M1 — Fundação** | Projeto rodando, login funcionando | US-001 a US-006, US-101 a US-105, US-107 | ~19 dias |
| **M2 — Núcleo** | Contas, categorias e lançamentos em conta | US-201 a US-204, US-301 a US-304, US-401 a US-404, US-901 | ~27 dias |
| **M3 — Cartões** | Cartões, faturas, parcelas e pagamento | US-501 a US-511 | ~24 dias |
| **M4 — Recorrências** | Recorrências e refinamentos de lançamento | US-601 a US-606, US-405 a US-407 | ~22 dias |
| **M5 — Visão** | Dashboard, relatórios, onboarding | US-701 a US-707, US-801, US-802, US-106 | ~21 dias |
| **M6 — Lançamento** | LGPD, auditoria, qualidade, produção | US-902, US-903, US-1001 a US-1003, US-1101 a US-1106 | ~20 dias |

Total estimado: **~136 dias de trabalho** (≈ 6 meses para uma pessoa em tempo integral), contando P = 1, M = 2,5 e G = 4,5 dias.

Ao final de cada marco o sistema deve estar utilizável de ponta a ponta para o que já foi entregue.

---

## E0 — Fundação técnica

### US-001 — Estrutura do repositório e ambiente local · M
Como desenvolvedor, quero subir o projeto inteiro com um comando, para começar a trabalhar sem configuração manual.
- [ ] Monorepo com `api/` (Laravel, PHP 8.4+) e `web/` (Nuxt 4, `ssr: false`, TypeScript)
- [ ] Docker Compose de desenvolvimento: Nginx, PHP-FPM, PostgreSQL 16, worker de fila, scheduler, Mailpit
- [ ] `README` com o passo a passo de setup
- [ ] Pest, Larastan, Pint (api) e ESLint, Vitest (web) configurados

### US-002 — Pipeline de CI · P
Como desenvolvedor, quero que cada push rode lint e testes, para não quebrar a branch principal. (D17)
- [ ] Workflow do GitHub Actions para `api/` (com serviço PostgreSQL) e `web/`, em push e pull request
- [ ] Cache de dependências (Composer e npm/pnpm)
- [ ] Proteção da branch `main`: merge apenas com CI verde

### US-006 — Base de testes de ponta a ponta · P
Como desenvolvedor, quero uma suíte Playwright rodando no CI, para que os fluxos críticos nunca quebrem sem aviso. (D20)
- [ ] Playwright configurado em `web/` (Chromium no CI; Chromium, Firefox e WebKit localmente)
- [ ] Job no GitHub Actions que sobe o ambiente Docker, roda migrations e seeders de teste e executa a suíte
- [ ] Captura de e-mails via Mailpit para os fluxos de verificação
- [ ] Traces e screenshots anexados ao CI em caso de falha
- Depende de: US-001, US-002

### US-003 — Base multi-tenant · M
Como desenvolvedor, quero isolamento por organização automático, para que nenhum endpoint vaze dados entre usuários. (D1, seção 15)
- [ ] Migrations de `organizations` e `organization_members`; `users.current_organization_id`
- [ ] Trait `BelongsToOrganization`: escopo global + preenchimento automático de `organization_id`
- [ ] Helper de teste que cria duas organizações e verifica `404` em acesso cruzado (CA-01)
- [ ] Middleware que resolve a organização atual a partir do usuário autenticado

### US-004 — Base de auditoria · P
Como desenvolvedor, quero o log de atividades configurado desde o início, para que cada entidade nova já nasça auditada. (RF-100)
- [ ] `spatie/laravel-activitylog` com coluna `organization_id` e `ip`
- [ ] Trait reutilizável que registra `old`/`new` em create/update/delete
- [ ] Filtro que remove campos sensíveis (`password`, tokens) das propriedades

### US-005 — Shell do frontend · M
Como usuário, quero uma interface consistente em desktop e mobile, para navegar pelo app com facilidade. (seção 13)
- [ ] Nuxt UI instalado; layout com menu lateral (desktop) e barra inferior (mobile)
- [ ] Cliente HTTP com Sanctum (CSRF cookie, tratamento de 401/403/404/409/422)
- [ ] Componentes base: `MoneyInput` (máscara pt-BR), `MoneyDisplay` (cor + sinal), `DatePicker`, `EmptyState`, `ErrorState`, skeletons
- [ ] Utilitários de formatação com `Intl` no locale da organização
- [ ] Middleware de rota que redireciona não autenticados para o login

---

## E1 — Autenticação e perfil

### US-101 — Cadastro · M
Como visitante, quero criar uma conta com nome, e-mail e senha, para começar a usar o app. (RF-01, RF-10)
- [ ] Senha com no mínimo 8 caracteres, hash argon2id
- [ ] E-mail único; mensagem genérica em caso de conflito
- [ ] Organização criada com o usuário como `owner`, moeda BRL, fuso `America/Sao_Paulo`
- [ ] Categorias padrão criadas (depende de US-301 — até lá, seeder vazio)
- [ ] Tela de cadastro

### US-102 — Verificação de e-mail · P
Como usuário recém-cadastrado, quero confirmar meu e-mail, para garantir que posso recuperar a conta. (RF-02)
- [ ] E-mail de verificação enviado no cadastro; reenvio disponível
- [ ] Endpoints de domínio bloqueados até a verificação (apenas perfil e reenvio liberados)
- [ ] Tela "verifique seu e-mail"

### US-103 — Login e logout · P
Como usuário, quero entrar e sair do app com segurança. (RF-03, seção 15)
- [ ] Sessão via Sanctum SPA; cookie `HttpOnly`, `Secure`, `SameSite=Lax`
- [ ] Rate limit de 5 tentativas/min por e-mail + IP
- [ ] Tela de login
- [ ] Teste E2E: cadastro → verificação de e-mail → login → logout (D20, fluxo 1)

### US-104 — Recuperação de senha · P
Como usuário que esqueceu a senha, quero redefini-la por e-mail. (RF-04)
- [ ] Link de uso único com expiração de 60 min
- [ ] Rate limit de 3 solicitações/hora
- [ ] Resposta idêntica para e-mail existente ou não
- [ ] Telas de solicitação e de redefinição

### US-105 — Perfil · P
Como usuário, quero editar nome, e-mail e senha. (RF-05)
- [ ] Troca de e-mail exige nova verificação
- [ ] Troca de senha exige a senha atual e encerra as outras sessões

### US-107 — Configurações da organização · P
Como usuário, quero ajustar nome, moeda, fuso e locale. (RF-11)
- [ ] `GET/PUT /organization`
- [ ] Moeda restrita a códigos ISO 4217; fuso validado contra a lista IANA
- [ ] Tela em Configurações

---

## E2 — Contas

### US-201 — Cadastro de contas · M
Como usuário, quero cadastrar minhas contas bancárias e carteira, para registrar onde meu dinheiro está. (RF-20)
- [ ] CRUD com nome, tipo, instituição, saldo inicial (aceita negativo), data do saldo inicial e cor
- [ ] Tela de lista e formulário

### US-202 — Saldo atual da conta · M
Como usuário, quero ver o saldo atual de cada conta, para saber quanto tenho disponível. (RF-21, RN-01)
- [ ] Saldo calculado conforme RN-01 (apenas `paid`, a partir de `initial_balance_date`)
- [ ] Testes cobrindo receita, despesa, transferência enviada/recebida e `card_payment`
- [ ] Saldo exibido na lista de contas
- Depende de: US-401, US-403

### US-203 — Extrato da conta · M
Como usuário, quero ver o extrato de uma conta com saldo acumulado dia a dia, para conferir com o banco. (RF-22)
- [ ] `GET /accounts/{id}/statement?from=&to=` com saldo de abertura do período e saldo por dia
- [ ] Tela de detalhe da conta
- Depende de: US-202

### US-204 — Arquivar e excluir conta · P
Como usuário, quero arquivar contas que não uso mais sem perder o histórico. (RF-23, RN-40, CA-12)
- [ ] Exclusão retorna `409` se houver lançamentos
- [ ] Contas arquivadas somem dos formulários e permanecem nos relatórios
- [ ] Desarquivar disponível

---

## E3 — Categorias e tags

### US-301 — Categorias padrão · P
Como novo usuário, quero começar com categorias prontas, para não precisar configurar tudo. (RF-41, seção 7.5.1)
- [ ] Seeder por organização com a árvore da seção 7.5.1, `is_system = true`
- [ ] Executado no cadastro (US-101)

### US-302 — Gerenciar categorias · M
Como usuário, quero criar, renomear e recolorir categorias e subcategorias. (RF-40, RF-42)
- [ ] Árvore com no máximo 2 níveis; subcategoria herda o `type` da categoria pai
- [ ] Nome único por (organização, pai)
- [ ] Tela com a árvore editável, separada por receita/despesa

### US-303 — Excluir e arquivar categorias · P
Como usuário, quero remover categorias sem deixar lançamentos órfãos. (RF-43, RN-41)
- [ ] Exclusão de categoria em uso exige `reassign_to` do mesmo tipo
- [ ] Categorias `is_system` só podem ser arquivadas
- [ ] Excluir categoria pai exige tratar as subcategorias (reatribuir junto)

### US-304 — Tags · P
Como usuário, quero marcar lançamentos com tags livres (ex.: "viagem-praia"), para agrupá-los fora da árvore de categorias. (RF-44)
- [ ] CRUD de tags; nome único por organização
- [ ] Tabela `transaction_tag`

---

## E4 — Lançamentos

### US-401 — Receitas e despesas em conta · G
Como usuário, quero registrar receitas e despesas nas minhas contas. (RF-50, RF-51, RN-02, RN-05, RN-06)
- [ ] Migration completa de `transactions` com `CHECK` por tipo (tabela de RF-51)
- [ ] `income` e `expense` em conta com categoria do tipo correspondente
- [ ] Status `pending`/`paid`/`canceled`; "agendado" e "vencido" derivados na resposta da API
- [ ] Tags e observações
- [ ] Formulário de lançamento (drawer)

### US-402 — Pagar e desfazer · P
Como usuário, quero marcar uma conta como paga (ou um recebimento como recebido) e desfazer se errar. (RF-53)
- [ ] `POST /transactions/{id}/pay` com `paid_at` e `amount` opcional; `POST /unpay`
- [ ] Ação rápida na listagem

### US-403 — Transferência entre contas · M
Como usuário, quero registrar transferências entre minhas contas sem que elas apareçam como receita ou despesa. (RN-03, CA-06)
- [ ] Tipo `transfer` com `account_id` e `to_account_id` diferentes
- [ ] Excluído de qualquer soma de receitas/despesas
- [ ] Teste do CA-06

### US-404 — Listagem e filtros · G
Como usuário, quero encontrar lançamentos rapidamente por período, categoria, conta e outros filtros. (RF-54, seção 13)
- [ ] Paginação, filtros (período, tipo, categoria incluindo subcategorias, conta, cartão, fatura, status, tag, texto) e ordenação
- [ ] Totais do conjunto filtrado (receitas, despesas, saldo)
- [ ] Filtros persistidos na URL
- [ ] Índices da seção 9 criados; teste de performance básico

### US-405 — Criação rápida · P
Como usuário, quero lançar uma despesa de qualquer tela em poucos segundos. (RF-55)
- [ ] Botão "+" fixo e atalho `N`
- [ ] Último tipo, conta/cartão e data usados vêm pré-selecionados

### US-406 — Aviso de duplicidade · M
Como usuário, quero ser avisado quando estiver lançando algo que parece repetido. (RN-37, RN-38, CA-11)
- [ ] Busca de candidatos conforme RN-37; `409` com a lista
- [ ] Reenvio com `force=true` cria o lançamento
- [ ] Não se aplica a lançamentos gerados pelo sistema
- [ ] Modal no frontend mostrando os candidatos

### US-407 — Editar e excluir com escopo · M
Como usuário, quero escolher se uma alteração em parcela ou ocorrência vale só para ela, para as seguintes ou para todas. (RF-56)
- [ ] Parâmetro `scope=this|following|all` em `PUT` e `DELETE`
- [ ] Para parcelas: recalcula as faturas afetadas
- [ ] Para ocorrências: edição com `scope=this` marca `is_detached`
- [ ] Modal de escolha de escopo
- Depende de: US-505, US-602

---

## E5 — Cartões e faturas

### US-501 — Cadastro de cartões · M
Como usuário, quero cadastrar meus cartões com dia de fechamento, vencimento e limite. (RF-30, RF-33)
- [ ] CRUD com os campos de RF-30; `closing_day` e `due_day` de 1 a 31
- [ ] Arquivar/desarquivar; exclusão bloqueada com lançamentos (RN-40)
- [ ] Tela de lista e formulário

### US-502 — Motor de ciclo de fatura · M
Como desenvolvedor, quero um serviço puro que diga a qual fatura pertence uma data, para que todas as regras de cartão partam de um único ponto. (RN-10, RN-11, D11)
- [ ] `BillCycleCalculator`: dado cartão e data → `period_start`, `closing_date`, `due_date`, `reference_month`
- [ ] Ajuste de fim de mês (fechamento dia 31 em fevereiro)
- [ ] Testes: CA-02, CA-03, virada de ano, ano bissexto, `closing_day` = `due_day`

### US-503 — Despesa no cartão e fatura automática · G
Como usuário, quero lançar compras no cartão e vê-las agrupadas na fatura certa automaticamente. (RF-70, RN-04, RN-13)
- [ ] `expense` com `card_id` sem status; `bill_id` atribuído via US-502
- [ ] Fatura criada sob demanda (única por cartão + mês de referência)
- [ ] `total_amount` recalculado em create/update/delete; fatura `paid` volta a `partial` se o total subir
- [ ] Alterar `closing_day`/`due_day` reatribui apenas a fatura `open` atual (RN-15)

### US-504 — Estorno no cartão · P
Como usuário, quero registrar um estorno que abata a fatura e o gasto da categoria. (RF-51, CA-07)
- [ ] Tipo `refund` com `card_id` e categoria de despesa
- [ ] Subtrai do total da fatura e das despesas da categoria
- [ ] Teste do CA-07

### US-505 — Compra parcelada · G
Como usuário, quero lançar uma compra parcelada uma única vez e ver cada parcela na fatura certa. (RF-52, RN-20 a RN-23, CA-04)
- [ ] Campo "parcelas" (2 a 48) no formulário de despesa no cartão
- [ ] Gera N lançamentos com `installment_group_id`, arredondamento na primeira parcela, sufixo "(k/N)"
- [ ] Datas com ajuste de fim de mês; cada parcela na fatura correspondente
- [ ] Teste do CA-04 e de compra parcelada em 31/01

### US-506 — Detalhe da fatura · M
Como usuário, quero ver a fatura com totais por categoria e por estabelecimento, para entender para onde foi o dinheiro. (RF-71)
- [ ] `GET /bills/{id}` com lançamentos, totais por categoria e por `merchant_name`
- [ ] Status derivado "vencida" na resposta
- [ ] Tela de fatura com gráfico por categoria e lista

### US-507 — Pagamento de fatura · M
Como usuário, quero registrar o pagamento total ou parcial da fatura a partir de uma conta. (RF-72, RN-12, RN-12a, D12, CA-05)
- [ ] `POST /bills/{id}/payments` cria `card_payment` (conta padrão do cartão pré-selecionada)
- [ ] `422` se o valor exceder o saldo devedor
- [ ] Status `partial`/`paid` atualizado; saldo da conta reduzido
- [ ] Teste do CA-05 (sem dupla contagem)
- [ ] Teste E2E: compra parcelada em 3× → conferência nas 3 faturas → pagamento da primeira fatura → saldo da conta atualizado (D20, fluxo 2)

### US-508 — Mover lançamento de fatura · P
Como usuário, quero mover uma compra para a fatura anterior ou seguinte quando o banco a lançou em outro ciclo. (RF-73, D11)
- [ ] `POST /transactions/{id}/move-bill` restrito a faturas do mesmo cartão não pagas
- [ ] Totais das duas faturas recalculados

### US-509 — Limite disponível · P
Como usuário, quero ver quanto ainda tenho de limite em cada cartão, considerando as parcelas futuras. (RF-31, RN-14)
- [ ] Cálculo conforme RN-14
- [ ] Exibido na lista de cartões, com barra de uso

### US-510 — Atualização automática de status · P
Como usuário, quero que a fatura feche sozinha na data de fechamento. (seção 10.1)
- [ ] Job diário `UpdateBillStatuses` (`open` → `closed`) no fuso da organização
- [ ] Idempotente; teste com data simulada

### US-511 — Faturas do cartão · P
Como usuário, quero navegar pelas faturas anteriores, atual e futuras de um cartão. (RF-32)
- [ ] `GET /cards/{id}/bills`
- [ ] Tela de detalhe do cartão com navegação entre faturas

---

## E6 — Recorrências

### US-601 — Cadastro de recorrências · M
Como usuário, quero cadastrar contas fixas e receitas recorrentes, como aluguel e salário. (RF-60, RF-61)
- [ ] CRUD com os campos de RF-60; conta **ou** cartão
- [ ] Frequências semanal, mensal e anual com intervalo; `anchor_day` gravado
- [ ] Formulário

### US-602 — Geração de ocorrências · G
Como usuário, quero que as ocorrências futuras apareçam sozinhas nos lançamentos, para nunca esquecer um vencimento. (RN-30, RN-31, RN-35, D10, CA-08)
- [ ] Job diário `GenerateRecurringOccurrences` até hoje + 3 meses; também executado ao criar a recorrência
- [ ] Chave única (`recurrence_id`, `occurrence_date`); execução repetida não duplica
- [ ] Ajuste de fim de mês preservando `anchor_day`
- [ ] Recorrência no cartão gera despesa na fatura correspondente
- [ ] Testes do CA-08, de intervalo > 1 e de `end_date`

### US-603 — Pagar e pular ocorrência · M
Como usuário, quero pagar uma ocorrência informando o valor real, ou pular um mês. (RF-64, RN-34, RN-36)
- [ ] Pagar ocorrência estimada abre o campo de valor real; o valor da recorrência não muda
- [ ] `POST /transactions/{id}/skip` marca `canceled`; não é regenerada
- [ ] Teste E2E: criar recorrência com valor estimado → ocorrências geradas → pagar uma com o valor real (D20, fluxo 3)

### US-604 — Editar recorrência · M
Como usuário, quero reajustar o aluguel e ver o novo valor nas próximas ocorrências, sem alterar as já pagas. (RN-32, CA-09)
- [ ] Atualiza apenas ocorrências `pending`, futuras e com `is_detached = false`
- [ ] Teste do CA-09

### US-605 — Pausar, retomar e encerrar · P
Como usuário, quero pausar uma assinatura temporariamente. (RF-63, RN-33, CA-10)
- [ ] Pausar remove pendentes futuras não editadas; retomar regenera a partir de hoje
- [ ] Encerrar define `end_date` = hoje e status `ended`
- [ ] Teste do CA-10

### US-606 — Tela de recorrências · M
Como usuário, quero ver todas as minhas recorrências, as próximas ocorrências e o total do mês. (RF-65)
- [ ] Lista com próxima ocorrência, status e valor
- [ ] Total de recorrências do mês selecionado
- [ ] Ações de pagar/pular direto da lista

---

## E7 — Dashboard

### US-701 — KPIs do mês (API) · G
Como desenvolvedor, quero um endpoint único com os indicadores do mês, calculados exatamente como definido na spec. (RN-50, RN-51, CA-13)
- [ ] `GET /dashboard/summary?month=` com saldo atual, saldo previsto, receitas, despesas, recorrentes, faturas a vencer e limite disponível
- [ ] Testes para cada KPI, incluindo CA-05, CA-06, CA-07 e CA-13
- [ ] Datas no fuso da organização

### US-702 — Tela do dashboard e seletor de mês · M
Como usuário, quero ver meus números do mês assim que entro no app. (RF-80, RF-81)
- [ ] Cards de KPI com cor + sinal
- [ ] Seletor de mês (padrão: atual) refletido na URL

### US-703 — Receitas × despesas (12 meses) · M
Como usuário, quero comparar receitas e despesas mês a mês. (RF-82)
- [ ] `GET /dashboard/cash-flow?months=12` (série contínua, meses sem dados com zero)
- [ ] Gráfico de barras ECharts

### US-704 — Despesas por categoria e comparação · M
Como usuário, quero ver em quais categorias mais gasto e como isso mudou em relação ao mês anterior. (RF-82)
- [ ] `spending-by-category` (agrupado por categoria pai, com detalhamento por subcategoria) e `month-comparison`
- [ ] Gráficos de barras horizontais com variação percentual

### US-705 — Evolução do saldo · P
Como usuário, quero ver como meu saldo evolui ao longo do mês. (RF-82)
- [ ] `GET /dashboard/balance-evolution?month=` com saldo diário (realizado até hoje, previsto depois)
- [ ] Gráfico de linha distinguindo realizado e previsto

### US-706 — Top 10 despesas e recorrências do mês · P
Como usuário, quero ver meus maiores gastos e o status das contas fixas do mês. (RF-82)
- [ ] `GET /dashboard/top-expenses`
- [ ] Lista de recorrências do mês com status (reaproveita US-606)

### US-707 — Alertas de vencimento · P
Como usuário, quero ser lembrado no app das contas e faturas que vencem nos próximos dias. (RN-50)
- [ ] `GET /dashboard/alerts` com vencidos e próximos 5 dias (antecedência em `organizations.settings`)
- [ ] Bloco de alertas no dashboard com ação de pagar

---

## E8 — Relatórios

### US-801 — Relatório agrupado · M
Como usuário, quero agrupar meus lançamentos por categoria, mês, conta ou cartão com os mesmos filtros da listagem. (RF-90)
- [ ] `GET /reports/summary?group_by=` reaproveitando o filtro de US-404
- [ ] Tela de relatórios com tabela e totais

### US-802 — Exportação CSV · P
Como usuário, quero exportar meus lançamentos para planilha. (RF-91, CA-14)
- [ ] CSV UTF-8 com BOM, separador `;`, valores pt-BR, mesmos filtros da listagem
- [ ] Teste do CA-14

---

## E9 — Auditoria

### US-901 — Auditoria das entidades de domínio · P
Como usuário, quero que alterações nos meus dados fiquem registradas. (RF-100, CA-15)
- [ ] Trait de US-004 aplicado em lançamentos, recorrências, contas, cartões, categorias e organização
- [ ] Teste do CA-15
- Observação: aplicar em cada entidade ao criá-la; esta história garante a cobertura completa

### US-902 — Eventos de segurança · P
Como usuário, quero saber quando houve login, falha de login ou troca de senha na minha conta. (RF-101)
- [ ] Listeners para login, falha, troca de senha, exportação e exclusão de dados

### US-903 — Tela do log de atividades · P
Como usuário, quero consultar o histórico de alterações. (RF-102)
- [ ] `GET /activity-logs` com filtros de período e entidade
- [ ] Tela em Configurações com o "antes e depois" de cada alteração
- [ ] Rotina de retenção de 12 meses

---

## E1 (continuação) — Onboarding

### US-106 — Onboarding · M
Como novo usuário, quero ser guiado na configuração inicial, para começar a usar em poucos minutos. (RF-12)
- [ ] 3 passos: moeda e fuso → primeira conta com saldo inicial → cartão (opcional, "pular")
- [ ] `POST /onboarding/complete`; usuário sem onboarding concluído é redirecionado
- Depende de: US-107, US-201, US-501

---

## E10 — Privacidade (LGPD)

### US-1001 — Exportar meus dados · M
Como usuário, quero baixar todos os meus dados. (RF-06)
- [ ] `POST /me/export` gera um ZIP (JSON + CSVs) em job; link por e-mail com expiração de 24 h
- [ ] Registrado no log (US-902)

### US-1002 — Excluir minha conta · M
Como usuário, quero excluir minha conta e todos os meus dados. (RF-07, RN-60, D15, CA-16)
- [ ] `DELETE /me` com senha; inicia carência de 7 dias e envia e-mail
- [ ] Login durante a carência oferece cancelar a exclusão
- [ ] Job `PurgeDeletedAccounts` remove definitivamente todos os dados da organização (inclusive soft-deleted e logs)
- [ ] Teste do CA-16

### US-1003 — Aceite de termos e privacidade · P
Como visitante, quero ler e aceitar os termos e a política de privacidade ao me cadastrar. (seção 15)
- [ ] Checkbox obrigatório no cadastro; data e versão do aceite gravadas
- [ ] Páginas de termos e privacidade
- **Bloqueio parcial:** o texto depende da seção 18 da spec; implementar com texto provisório

---

## E11 — Qualidade e lançamento

### US-1101 — Revisão de acessibilidade e responsividade · M
Como usuário, quero usar o app no celular e por teclado sem obstáculos. (RNF-03, RNF-08)
- [ ] Todas as telas revisadas em 360 px
- [ ] Navegação por teclado, foco visível, labels, contraste AA

### US-1102 — Performance · M
Como usuário, quero respostas rápidas mesmo com anos de histórico. (RNF-01)
- [ ] Seeder com 50 mil lançamentos
- [ ] p95 < 500 ms na listagem e nos endpoints de dashboard; ajustar índices ou cache se necessário

### US-1103 — Cabeçalhos de segurança · P
Como usuário, quero o app protegido contra ataques comuns no navegador. (seção 15)
- [ ] CSP, HSTS, `X-Content-Type-Options`, `Referrer-Policy`
- [ ] Regra de lint proibindo `v-html`

### US-1104 — Backup e restauração · P
Como usuário, quero que meus dados sobrevivam a uma falha do servidor. (RNF-05, D14)
- [ ] `pg_dump` diário para object storage fora da VPS, retenção de 30 dias
- [ ] Restauração testada e documentada

### US-1105 — Deploy em produção · M
Como usuário, quero acessar o app pela internet com HTTPS. (seção 10.3, D14)
- [ ] VPS com Docker Compose de produção, HTTPS, e-mail transacional configurado
- [ ] Deploy automatizado a partir da branch principal
- **Bloqueado:** depende do domínio e do provedor (seção 18 da spec)

### US-1106 — Monitoramento · M
Como usuário, quero que falhas sejam detectadas antes de eu perceber, especialmente nos jobs que fecham faturas e geram recorrências. (D18, RNF-09)
- [ ] `sentry/sentry-laravel` na API e `@sentry/nuxt` no frontend, com DSN via variável de ambiente (sem DSN, nada é enviado e o app funciona normalmente)
- [ ] Ambiente e versão (commit) enviados em cada evento; dados pessoais removidos (`send_default_pii = false`)
- [ ] Monitoramento dos jobs agendados (Sentry Cron Monitors) para alertar se um job não rodar
- [ ] Monitor externo de uptime da API e do frontend
- **Pendência:** configurar o DSN/token do Sentry (a cargo do usuário)

---

## Mapa de dependências principais

```
US-001 → US-002, US-003, US-005
US-002 → US-006 → E2E em US-103, US-507, US-603
US-1105 → US-1106
US-003 → todas as histórias de domínio
US-004 → US-901
US-301 → US-101 (seed no cadastro), US-302
US-401 → US-202, US-402, US-403, US-404
US-502 → US-503 → US-504, US-505, US-506, US-507, US-508, US-509, US-510
US-601 → US-602 → US-603, US-604, US-605, US-606
US-505 + US-602 → US-407
US-404 → US-801, US-802
US-202 + US-503 + US-602 → US-701 → US-702 a US-707
US-107 + US-201 + US-501 → US-106
```

## Rastreabilidade — critérios de aceitação da spec

| CA | História |
|---|---|
| CA-01 | US-003 (e DoD) |
| CA-02, CA-03 | US-502 |
| CA-04 | US-505 |
| CA-05 | US-507, US-701 |
| CA-06 | US-403, US-701 |
| CA-07 | US-504, US-701 |
| CA-08 | US-602 |
| CA-09 | US-604 |
| CA-10 | US-605 |
| CA-11 | US-406 |
| CA-12 | US-204 |
| CA-13 | US-701 |
| CA-14 | US-802 |
| CA-15 | US-901 |
| CA-16 | US-1002 |
