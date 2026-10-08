# Financeiro Pro — Especificação de Produto (v2)

| | |
|---|---|
| **Tipo** | Aplicativo web para controle financeiro pessoal |
| **Stack** | Laravel + Nuxt 4 (SPA) + PostgreSQL |
| **Versão** | 2.0 — revisão da v1 de 08/10/2026 |
| **Status** | Pronta para backlog — todas as decisões tomadas |

---

## 0. Decisões registradas

Decisões que condicionam escopo, modelo de dados e regras. Qualquer mudança aqui deve ser refletida nas seções afetadas.

| ID | Decisão | Status |
|---|---|---|
| D1 | **MVP de uso individual.** Cada usuário tem uma organização criada automaticamente, da qual é o único membro (owner). O schema já é multi-tenant (`organization_id` em todas as tabelas) para permitir família/multiusuário na Fase 4 sem migração estrutural. Motivo: o produto nasce para uso pessoal; papéis e convites triplicam a superfície de autorização sem benefício no MVP. | decidido |
| D2 | **Regime de competência** para receitas e despesas: o lançamento conta no mês da sua `date`. Compra no cartão conta no mês da compra; cada parcela conta no mês da própria parcela. A visão de caixa (pelo pagamento da fatura) entra na Fase 2. Motivo: é o que responde "quanto gastei em março", e as parcelas distribuem o impacto ao longo dos meses em que de fato pesam no orçamento. | decidido |
| D3 | **"Segmento" removido.** A análise de gastos usa a árvore de categorias (categoria → subcategoria) e o estabelecimento (`merchant_name`). Motivo: na v1 a lista de segmentos repetia as categorias; dois eixos quase iguais confundem o usuário e duplicam relatórios. | decidido |
| D4 | Banco de dados: **PostgreSQL 16+**. | decidido |
| D5 | Frontend como **SPA (`ssr: false`)**: o app fica atrás de login, não precisa de SEO, e isso simplifica a autenticação por cookie do Sanctum. | decidido |
| D6 | UI com **Nuxt UI** (já inclui Tailwind) e gráficos com **ECharts**. | decidido |
| D7 | Valores monetários em `numeric(15,2)`, **sempre positivos**; o efeito no saldo é derivado do `type`. | decidido |
| D8 | **Faturas geradas automaticamente** a partir do cartão e das transações; o total é calculado, nunca digitado. | decidido |
| D9 | Detecção de duplicidade **avisa e pede confirmação**, não bloqueia. | decidido |
| D10 | Recorrências materializam ocorrências com **horizonte de 3 meses**. | decidido |
| D11 | **Compra no dia do fechamento vai para a fatura seguinte** (RN-10), que é o comportamento da maioria dos emissores; divergências pontuais são corrigidas movendo o lançamento (RF-73). | decidido |
| D12 | **Pagamento de fatura limitado ao saldo devedor.** Pagamento acima do saldo é recusado (`422`). Crédito para a fatura seguinte fica fora do MVP. | decidido |
| D13 | **Importação (Fase 3) começa por OFX e CSV genérico** com mapeamento de colunas salvo por conta. OFX é exportado pela maioria dos bancos brasileiros; o CSV cobre os demais sem integração específica. Importação de PDF vem depois. | decidido |
| D14 | **Hospedagem: uma VPS com Docker Compose** (Nginx servindo a SPA e a API, PHP-FPM, PostgreSQL, worker de fila e scheduler). E-mail transacional por um provedor SMTP (ex.: Amazon SES). Backup com `pg_dump` diário enviado para object storage compatível com S3, fora da VPS. Custo-alvo baixo, compatível com uso pessoal. | decidido |
| D15 | **Exclusão de conta com 7 dias de carência** (RN-60), para proteger contra exclusão acidental ou por sessão comprometida. | decidido |
| D16 | **Monorepo** no GitHub (`dayvsonmarques/personal-financial`) com `api/` (Laravel) e `web/` (Nuxt). Um único repositório mantém contrato da API, CI e versão sincronizados. | decidido |
| D17 | **CI com GitHub Actions**: lint e testes de `api/` e `web/` a cada push e pull request; deploy automatizado da branch `main`. | decidido |
| D18 | **Monitoramento com Sentry** (erros da API, dos jobs e do frontend) e monitor externo de uptime. O DSN vem de variável de ambiente; sem ele, a aplicação funciona normalmente e apenas não envia eventos. | decidido |
| D19 | **Uma única data por lançamento.** Em lançamentos em conta, `date` é ao mesmo tempo a competência e o vencimento (ex.: a conta de luz de setembro que vence em 10/10 conta em outubro). Separar as datas complicaria todos os formulários sem ganho relevante para uso pessoal. | decidido |
| D20 | **Testes de ponta a ponta com Playwright**, restritos a 3 fluxos críticos: (1) cadastro, verificação de e-mail e login; (2) compra parcelada no cartão até o pagamento da fatura; (3) criação de recorrência até o pagamento de uma ocorrência. O restante fica coberto por testes de API (Pest) e de componentes (Vitest). Rodam no GitHub Actions contra o ambiente Docker. | decidido |

---

## 1. Visão geral

O Financeiro Pro permite gerenciar finanças pessoais de forma centralizada e visualmente clara, com foco em:

- contas fixas e recorrentes (aluguel, luz, água, internet, assinaturas)
- faturas de cartão de crédito, incluindo compras parceladas
- análise de gastos por categoria
- dashboard com indicadores e gráficos mensais

Aplicação web responsiva, para uso em desktop e mobile, com conteúdo em português brasileiro.

## 2. Problema

Planilhas e aplicativos genéricos tratam mal:

- recorrências mensais com valor variável (luz, água)
- faturas de cartão: fechamento, parcelas, pagamento
- dupla contagem: compra no cartão + pagamento da fatura
- comparação entre meses
- visão de saldo atual vs. saldo previsto

## 3. Objetivos

- Registrar receitas, despesas e transferências.
- Gerenciar contas fixas e recorrentes sem esquecimentos.
- Acompanhar faturas de cartão, parcelas e limite disponível.
- Analisar gastos por categoria e estabelecimento.
- Comparar meses e acompanhar o fluxo de caixa.

## 4. Público-alvo

- Pessoas físicas que querem organizar a vida financeira doméstica.
- Famílias (a partir da Fase 4 — ver D1).

## 5. Escopo

### 5.1 MVP (Fase 1)

- cadastro, login, recuperação de senha, verificação de e-mail
- onboarding (moeda, fuso, primeira conta, categorias padrão)
- contas bancárias e carteira
- cartões de crédito
- categorias e subcategorias (árvore com 2 níveis), tags
- lançamentos: receita, despesa, transferência, estorno, pagamento de fatura
- compras parceladas no cartão
- recorrências (receitas e despesas)
- faturas automáticas, com pagamento total ou parcial
- dashboard com KPIs e gráficos
- filtros por período, categoria, conta, cartão, status e tag
- exportação CSV
- log de atividades
- configurações da conta e da organização
- exportação e exclusão de dados (LGPD)

### 5.2 Fases seguintes

Ver o roadmap na [seção 17](#17-roadmap).

### 5.3 Fora de escopo

- uso empresarial / PJ (ERP, notas fiscais, centro de custo)
- múltiplas moedas por organização: compras internacionais são lançadas pelo valor em reais da fatura
- cálculo automático de juros do rotativo e de encargos
- investimentos (cotação, rentabilidade)

---

## 6. Glossário

| Termo | Definição |
|---|---|
| **Organização** | Contêiner dos dados financeiros. No MVP, 1 por usuário. |
| **Conta** | Conta bancária, poupança, carteira (dinheiro) ou outra fonte de saldo. |
| **Lançamento / transação** | Movimento financeiro. Tipos: `income`, `expense`, `transfer`, `refund`, `card_payment`. |
| **Competência** | Mês ao qual o lançamento pertence para fins de análise: o mês da sua `date`. |
| **Fatura** | Agrupamento das transações de um cartão em um ciclo entre dois fechamentos. |
| **Mês de referência da fatura** | Mês da data de vencimento da fatura. |
| **Recorrência** | Regra que gera lançamentos periodicamente. |
| **Ocorrência** | Lançamento gerado por uma recorrência para uma data específica. É a ocorrência que é paga, não a recorrência. |
| **Parcelamento** | Compra no cartão dividida em N lançamentos, um por fatura. |
| **Estorno** | Crédito no cartão que abate a fatura e o gasto da categoria. |

---

## 7. Requisitos funcionais

### 7.1 Autenticação e perfil

- **RF-01** Cadastro com nome, e-mail e senha (mínimo de 8 caracteres).
- **RF-02** Verificação de e-mail obrigatória antes do primeiro uso.
- **RF-03** Login e logout.
- **RF-04** Recuperação de senha por e-mail, com link de uso único e expiração de 60 minutos.
- **RF-05** Edição de perfil (nome, e-mail, senha).
- **RF-06** Exportação dos próprios dados (JSON e CSV).
- **RF-07** Exclusão da conta, com confirmação por senha.

### 7.2 Organização e onboarding

- **RF-10** No cadastro, o sistema cria a organização do usuário com ele como `owner`.
- **RF-11** A organização possui nome, moeda (padrão BRL), fuso horário (padrão `America/Sao_Paulo`) e locale (padrão `pt-BR`).
- **RF-12** Onboarding em 3 passos: confirmar moeda e fuso; cadastrar a primeira conta com saldo inicial; cadastrar um cartão (opcional). As categorias padrão são criadas automaticamente.

### 7.3 Contas

- **RF-20** CRUD de contas com nome, tipo (`checking`, `savings`, `cash`, `other`), instituição, saldo inicial, data do saldo inicial e cor.
- **RF-21** Exibir o saldo atual de cada conta (RN-01).
- **RF-22** Extrato da conta com saldo acumulado por dia.
- **RF-23** Arquivar conta (RN-40).

### 7.4 Cartões de crédito

- **RF-30** CRUD de cartões com nome, emissor, últimos 4 dígitos (opcional), dia de fechamento, dia de vencimento, limite, conta padrão de pagamento e cor.
- **RF-31** Exibir o limite disponível (RN-14).
- **RF-32** Listar as faturas do cartão: anteriores, atual e futuras (com parcelas).
- **RF-33** Arquivar cartão (RN-40).

### 7.5 Categorias e tags

- **RF-40** Árvore de categorias com no máximo 2 níveis, separada por tipo (receita/despesa).
- **RF-41** Categorias padrão criadas no onboarding (seção 7.5.1). O usuário pode renomeá-las, recolori-las e arquivá-las.
- **RF-42** Criar categorias e subcategorias personalizadas.
- **RF-43** Ao excluir uma categoria em uso, exigir uma categoria de destino para os lançamentos (RN-41).
- **RF-44** CRUD de tags; um lançamento pode ter várias tags.

#### 7.5.1 Categorias padrão

**Despesas**

| Categoria | Subcategorias |
|---|---|
| Moradia | Aluguel, Condomínio, Energia, Água, Gás, Internet, Manutenção |
| Alimentação | Mercado, Restaurante, Delivery, Padaria |
| Transporte | Combustível, App de corrida, Transporte público, Estacionamento, Manutenção do veículo |
| Saúde | Farmácia, Plano de saúde, Consultas, Academia |
| Educação | Mensalidade, Cursos, Livros |
| Lazer | Streaming, Viagens, Passeios |
| Serviços | Celular, Assinaturas, Seguros, Tarifas bancárias |
| Compras | Vestuário, Eletrônicos, Casa |
| Impostos e taxas | IPTU, IPVA, IOF, Anuidade, Juros |
| Outros | — |

**Receitas**

| Categoria | Subcategorias |
|---|---|
| Salário | — |
| Renda extra | Freelance, Vendas |
| Rendimentos | — |
| Reembolsos | — |
| Outros | — |

> "Cartão" não é categoria: é um meio de pagamento. O pagamento da fatura é do tipo `card_payment` (RN-03).

### 7.6 Lançamentos

- **RF-50** Registrar lançamento com: tipo, data, valor, descrição, categoria, conta **ou** cartão, estabelecimento (opcional), status, tags e observações.
- **RF-51** Tipos e campos obrigatórios:

| Tipo | Origem | Destino | Categoria | Afeta receitas/despesas? |
|---|---|---|---|---|
| `income` | — | conta | obrigatória (receita) | sim (receita) |
| `expense` | conta **ou** cartão | — | obrigatória (despesa) | sim (despesa) |
| `refund` | — | cartão | obrigatória (despesa) | abate a despesa da categoria |
| `transfer` | conta | outra conta | não se aplica | não |
| `card_payment` | conta | fatura | não se aplica | não |

- **RF-52** Compra parcelada: em `expense` no cartão, informar o número de parcelas (2 a 48). O sistema cria N lançamentos (RN-20).
- **RF-53** Marcar lançamento em conta como pago/recebido e desfazer, registrando a data de pagamento.
- **RF-54** Listagem paginada com filtros (período, tipo, categoria com subcategorias, conta, cartão, fatura, status, tag, texto na descrição/estabelecimento) e ordenação por data ou valor.
- **RF-55** Criação rápida acessível de qualquer tela (botão "+" e atalho de teclado `N`).
- **RF-56** Ao editar ou excluir uma parcela ou ocorrência de recorrência, perguntar o escopo: **só esta**, **esta e as seguintes** ou **todas**.

### 7.7 Recorrências

- **RF-60** CRUD de recorrências com: tipo (receita/despesa), descrição, categoria, conta ou cartão, valor, valor estimado (sim/não), frequência, intervalo, data de início, data de término (opcional) e observações (opcional).
- **RF-61** Frequências: semanal, mensal e anual, com intervalo (ex.: a cada 2 meses).
- **RF-62** O sistema gera as ocorrências automaticamente (RN-30).
- **RF-63** Pausar, retomar e encerrar recorrências.
- **RF-64** Em cada ocorrência: pagar (ajustando o valor real, se estimado), pular ou editar.
- **RF-65** Exibir o total de recorrências do mês: soma das ocorrências com `date` no mês.

### 7.8 Faturas

- **RF-70** As faturas são criadas automaticamente quando a primeira transação cai no seu ciclo (RN-10).
- **RF-71** Detalhe da fatura: período, fechamento, vencimento, total, valor pago, saldo devedor, status, lançamentos e totais por categoria e por estabelecimento.
- **RF-72** Pagar a fatura (total ou parcial) a partir de uma conta, o que gera um `card_payment` (RN-12).
- **RF-73** Mover um lançamento para outra fatura do mesmo cartão (para ajustar divergências com o banco).

### 7.9 Dashboard

- **RF-80** Seletor de mês (padrão: mês atual). Todos os indicadores seguem o regime de competência (D2).
- **RF-81** KPIs (definições em RN-50):
  - saldo atual
  - saldo previsto ao fim do mês
  - receitas do mês
  - despesas do mês
  - despesas recorrentes do mês
  - faturas a vencer no mês e limite disponível dos cartões
  - alertas de vencimento
- **RF-82** Gráficos:
  - receitas × despesas nos últimos 12 meses (barras)
  - despesas por categoria no mês (barras horizontais ou rosca)
  - comparação por categoria entre o mês atual e o anterior
  - evolução do saldo no mês (linha diária)
  - top 10 maiores despesas do mês
  - recorrências do mês com status (lista)

### 7.10 Relatórios e exportação

- **RF-90** Relatório de lançamentos com os mesmos filtros de RF-54 e totais agrupados por categoria, mês, conta ou cartão.
- **RF-91** Exportação CSV do resultado filtrado (UTF-8 com BOM, separador `;`, valores no formato pt-BR).

### 7.11 Log de atividades

- **RF-100** Registrar criação, edição e exclusão de lançamentos, recorrências, contas, cartões, categorias e configurações, com valores anteriores e novos.
- **RF-101** Registrar eventos de segurança: login, falha de login, troca de senha, exportação e exclusão de dados.
- **RF-102** Tela de consulta do log com filtro por período e entidade.

---

## 8. Regras de negócio

### 8.1 Saldos e tipos

- **RN-01** Saldo da conta = `initial_balance` + Σ `income` pagos − Σ `expense` pagos + Σ `transfer` recebidas − Σ `transfer` enviadas − Σ `card_payment`. Só entram lançamentos com status `paid` e `date` ≥ `initial_balance_date`.
- **RN-02** Lançamento de `expense` tem exatamente um entre `account_id` e `card_id` (restrição no banco).
- **RN-03** `transfer` e `card_payment` **nunca** entram em receitas ou despesas. Isso evita contar duas vezes a compra no cartão e o pagamento da fatura.
- **RN-04** Lançamentos em cartão (`expense` e `refund`) não têm status próprio: a quitação acontece pela fatura.
- **RN-05** Status de lançamentos em conta: `pending`, `paid`, `canceled`. Os estados "agendado" (pending com data futura) e "vencido" (pending com data passada) são derivados, não armazenados. Para lançamentos em conta, `date` é a data de vencimento/recebimento (D19).
- **RN-06** Valores sempre positivos, com 2 casas decimais, na moeda da organização.

### 8.2 Faturas

- **RN-10** **Ciclo da fatura.** Para um cartão com `closing_day` F, a data de fechamento no mês M é o dia F de M (ou o último dia do mês, se M tiver menos de F dias). Uma transação com data `d` pertence à fatura cujo fechamento é a primeira data de fechamento **estritamente maior** que `d`. Ou seja: a compra feita no dia do fechamento vai para a fatura seguinte.
- **RN-11** **Vencimento.** Se `due_day` > `closing_day`, o vencimento cai no mesmo mês do fechamento; caso contrário, no mês seguinte. O mês de referência da fatura é o mês do vencimento.
- **RN-12** **Pagamento.** `paid_amount` = Σ dos `card_payment` vinculados à fatura. Status:
  - `open`: antes da data de fechamento
  - `closed`: fechada, sem pagamento
  - `partial`: 0 < `paid_amount` < total
  - `paid`: `paid_amount` ≥ total
  - "vencida" é derivado: `closed` ou `partial` com vencimento passado.
- **RN-12a** O valor de um `card_payment` não pode exceder o saldo devedor da fatura (`total_amount` − `paid_amount`) (D12).
- **RN-13** O total da fatura = Σ `expense` − Σ `refund` vinculados. É recalculado a cada alteração. Se a fatura estava `paid` e o total aumentar, ela volta para `partial`.
- **RN-14** Limite disponível = `limit` − Σ dos totais não pagos de todas as faturas do cartão (incluindo futuras, com parcelas).
- **RN-15** Alterar `closing_day` ou `due_day` afeta apenas as faturas ainda não criadas e a fatura `open` atual.
- **RN-16** O saldo não pago de uma fatura **não** é transferido automaticamente para a próxima (rotativo fora de escopo). O usuário pode lançar juros manualmente na categoria "Impostos e taxas › Juros".

### 8.3 Parcelamento

- **RN-20** Compra de valor V em N parcelas gera N lançamentos `expense` com o mesmo `installment_group_id`, `installment_number` de 1 a N e `installment_total` = N.
- **RN-21** Valor de cada parcela = V / N truncado em 2 casas; a diferença de centavos vai para a **primeira** parcela. Exemplo: R$ 1.000,00 em 3× = 333,34 + 333,33 + 333,33.
- **RN-22** A parcela k tem `date` = data da compra + (k − 1) meses (com ajuste de fim de mês) e cai na fatura correspondente pela RN-10. Pela D2, cada parcela conta no mês da sua própria data.
- **RN-23** A descrição recebe o sufixo "(k/N)".

### 8.4 Recorrências

- **RN-30** Um job diário materializa as ocorrências de cada recorrência ativa até **hoje + 3 meses**, como lançamentos `pending` com `source = recurrence`. A geração é idempotente: há uma chave única em (`recurrence_id`, `occurrence_date`).
- **RN-31** Dia inexistente no mês (ex.: dia 31 em fevereiro) usa o último dia do mês. A recorrência mantém o dia original nos meses seguintes.
- **RN-32** Editar a recorrência atualiza as ocorrências `pending` futuras que não foram editadas manualmente. Ocorrências pagas ou editadas manualmente (`is_detached = true`) nunca são alteradas.
- **RN-33** Pausar remove as ocorrências `pending` futuras não editadas. Retomar volta a gerá-las a partir de hoje.
- **RN-34** Pular uma ocorrência a marca como `canceled`; ela não é regenerada.
- **RN-35** Recorrência no cartão (ex.: streaming) gera `expense` no cartão, que entra na fatura pela RN-10.
- **RN-36** Recorrência com valor estimado: ao pagar a ocorrência, o usuário informa o valor real. O valor da recorrência não muda.

### 8.5 Duplicidade

- **RN-37** Ao criar manualmente um lançamento, o sistema procura outros com a mesma conta/cartão, o mesmo valor, a mesma descrição normalizada (minúsculas, sem acentos e sem espaços extras) e data a ±1 dia. Se encontrar, a API responde `409` com os candidatos; o usuário confirma para criar mesmo assim.
- **RN-38** A verificação não se aplica a ocorrências de recorrência nem a parcelas geradas pelo sistema.

### 8.6 Exclusão e arquivamento

- **RN-40** Conta ou cartão com lançamentos não pode ser excluído, apenas arquivado. Itens arquivados somem dos formulários, mas continuam nos relatórios.
- **RN-41** A exclusão de uma categoria em uso exige uma categoria de destino. Categorias padrão (`is_system`) podem ser arquivadas, mas não excluídas.
- **RN-42** Lançamentos usam soft delete. A exclusão definitiva acontece apenas na exclusão da conta do usuário (RN-60).

### 8.7 Indicadores (dashboard)

- **RN-50** Definições para o mês M:
  - **Saldo atual:** Σ dos saldos (RN-01) das contas não arquivadas, na data de hoje.
  - **Saldo previsto ao fim de M:** saldo atual + `income` pendentes em M − `expense` pendentes em conta em M − saldo devedor das faturas que vencem em M.
  - **Receitas de M:** Σ `income` com `date` em M (pagos e pendentes).
  - **Despesas de M:** Σ `expense` com `date` em M (em conta e em cartão) − Σ `refund` com `date` em M.
  - **Despesas recorrentes de M:** parte das despesas de M com `source = recurrence`.
  - **Alertas:** lançamentos `pending` em conta vencidos ou que vencem nos próximos 5 dias, e faturas `closed`/`partial` que vencem nos próximos 5 dias.
- **RN-51** Datas de competência são do tipo `date` (sem hora), interpretadas no fuso da organização. "Hoje" é calculado nesse fuso.

### 8.8 Dados pessoais

- **RN-60** A exclusão da conta é efetivada após 7 dias de carência, com e-mail de confirmação. Durante a carência, o usuário pode cancelar fazendo login. Após o prazo, todos os dados da organização são removidos definitivamente. Nos backups, os dados expiram junto com a retenção (30 dias).
- **RN-61** O sistema não coleta CPF nem número completo de cartão: armazena apenas os últimos 4 dígitos, de forma opcional.

---

## 9. Modelo de dados

Convenções: chaves `bigint`; `created_at`/`updated_at` em todas as tabelas; `deleted_at` (soft delete) onde indicado; todas as tabelas de domínio têm `organization_id` com índice e escopo global de tenant.

### users
| Campo | Tipo | Observação |
|---|---|---|
| id | bigint | |
| name | varchar(120) | |
| email | varchar(255) | único |
| email_verified_at | timestamp null | |
| password | varchar | hash argon2id |
| current_organization_id | bigint FK | organização ativa |
| deletion_requested_at | timestamp null | RN-60 |

### organizations
| Campo | Tipo | Observação |
|---|---|---|
| id | bigint | |
| name | varchar(120) | |
| currency | char(3) | ISO 4217, padrão BRL |
| timezone | varchar(64) | padrão America/Sao_Paulo |
| locale | varchar(10) | padrão pt-BR |
| settings | jsonb | preferências (ex.: dias de antecedência dos alertas) |

### organization_members
| Campo | Tipo | Observação |
|---|---|---|
| organization_id | bigint FK | PK composta |
| user_id | bigint FK | PK composta |
| role | varchar(20) | MVP: apenas `owner`. Fase 4: `admin`, `editor`, `viewer` |

### accounts — soft delete
| Campo | Tipo | Observação |
|---|---|---|
| id, organization_id | bigint | |
| name | varchar(80) | |
| type | varchar(20) | checking, savings, cash, other |
| institution | varchar(80) null | |
| initial_balance | numeric(15,2) | pode ser negativo (cheque especial) |
| initial_balance_date | date | |
| color | varchar(7) | hex |
| archived_at | timestamp null | |

### credit_cards — soft delete
| Campo | Tipo | Observação |
|---|---|---|
| id, organization_id | bigint | |
| name | varchar(80) | |
| issuer | varchar(80) null | |
| last4 | char(4) null | |
| closing_day | smallint | 1–31 |
| due_day | smallint | 1–31 |
| limit_amount | numeric(15,2) | |
| default_payment_account_id | bigint FK null | |
| color | varchar(7) | |
| archived_at | timestamp null | |

### categories — soft delete
| Campo | Tipo | Observação |
|---|---|---|
| id, organization_id | bigint | |
| parent_id | bigint FK null | no máximo 2 níveis |
| type | varchar(10) | income, expense |
| name | varchar(60) | único em (organization_id, parent_id, name) |
| color, icon | varchar | |
| is_system | boolean | categoria padrão |
| archived_at | timestamp null | |

### bills
| Campo | Tipo | Observação |
|---|---|---|
| id, organization_id | bigint | |
| card_id | bigint FK | |
| reference_month | date | 1º dia do mês do vencimento; único em (card_id, reference_month) |
| period_start | date | fechamento anterior |
| closing_date | date | |
| due_date | date | |
| total_amount | numeric(15,2) | cache de RN-13 |
| paid_amount | numeric(15,2) | cache de RN-12 |
| status | varchar(10) | open, closed, partial, paid |

### recurrences — soft delete
| Campo | Tipo | Observação |
|---|---|---|
| id, organization_id | bigint | |
| type | varchar(10) | income, expense |
| description | varchar(140) | |
| category_id | bigint FK | |
| account_id / card_id | bigint FK null | exatamente um |
| amount | numeric(15,2) | |
| is_estimate | boolean | RN-36 |
| frequency | varchar(10) | weekly, monthly, yearly |
| interval | smallint | padrão 1 |
| anchor_day | smallint | dia original (RN-31) |
| start_date | date | |
| end_date | date null | |
| generated_until | date | controle do job (RN-30) |
| status | varchar(10) | active, paused, ended |
| notes | text null | |

### transactions — soft delete
| Campo | Tipo | Observação |
|---|---|---|
| id, organization_id | bigint | |
| created_by | bigint FK users | |
| type | varchar(15) | income, expense, refund, transfer, card_payment |
| date | date | competência |
| amount | numeric(15,2) | > 0 |
| description | varchar(140) | |
| merchant_name | varchar(120) null | |
| category_id | bigint FK null | obrigatório para income/expense/refund |
| account_id | bigint FK null | origem (expense, transfer, card_payment) ou destino (income) |
| to_account_id | bigint FK null | destino de transfer |
| card_id | bigint FK null | expense/refund no cartão |
| bill_id | bigint FK null | fatura do lançamento no cartão ou fatura paga (card_payment) |
| status | varchar(10) null | pending, paid, canceled; null para lançamentos no cartão |
| paid_at | date null | |
| source | varchar(15) | manual, recurrence, installment, import |
| recurrence_id | bigint FK null | |
| occurrence_date | date null | único com recurrence_id |
| is_detached | boolean | ocorrência editada manualmente (RN-32) |
| installment_group_id | uuid null | |
| installment_number / installment_total | smallint null | |
| notes | text null | |

Índices principais: (organization_id, date), (organization_id, category_id, date), (bill_id), (account_id, status, date), (installment_group_id).
Constraints `CHECK` por tipo, conforme a tabela de RF-51.

### tags / transaction_tag
| tags | transaction_tag |
|---|---|
| id, organization_id, name (único por org), color | transaction_id, tag_id (PK composta) |

### activity_logs
Via `spatie/laravel-activitylog`: id, organization_id, causer_id, subject_type, subject_id, event, properties (jsonb com `old`/`new`), ip, created_at. Retenção de 12 meses.

### Fases futuras (fora do MVP)
`imports`, `import_rules` (Fase 3), `budgets` (Fase 2), `invitations` (Fase 4).

---

## 10. Arquitetura

### 10.1 Backend

- Laravel (fixar a versão estável vigente no início do projeto, 12.x ou superior), PHP 8.4+
- PostgreSQL 16+
- Laravel Sanctum no modo SPA (cookie de sessão + CSRF)
- `spatie/laravel-activitylog`
- Filas com driver `database` no MVP; Redis quando houver necessidade medida (cache do dashboard, volume de jobs)
- Mail para verificação de e-mail, recuperação de senha e exclusão de conta
- Testes com Pest
- Sentry (`sentry/sentry-laravel`) para exceções da API e falhas de jobs agendados (D18)

**Jobs agendados** (no fuso de cada organização):

| Job | Frequência | Função |
|---|---|---|
| `GenerateRecurringOccurrences` | diário | RN-30 |
| `UpdateBillStatuses` | diário | `open` → `closed` após `closing_date` |
| `PurgeDeletedAccounts` | diário | RN-60 |

**Módulos de domínio:** Auth, Organizations, Accounts, Cards & Bills, Categories & Tags, Transactions (parcelas, transferências, duplicidade), Recurrences, Dashboard & Reports, Audit.

As regras de negócio ficam em Actions/Services testáveis (ex.: `AssignTransactionToBill`, `CreateInstallmentPurchase`, `PayBill`), nunca nos controllers.

### 10.2 Frontend

- Nuxt 4 com `ssr: false`, TypeScript, Pinia
- Nuxt UI
- ECharts (`vue-echarts`)
- Sentry (`@sentry/nuxt`) para erros do frontend (D18)
- Playwright para os testes de ponta a ponta dos fluxos críticos (D20)
- Formatação com `Intl.NumberFormat` / `Intl.DateTimeFormat` no locale da organização

### 10.3 Deploy

- SPA e API sob o mesmo domínio raiz (ex.: `app.dominio.com` e `api.dominio.com`), exigência do cookie do Sanctum; configurar `SANCTUM_STATEFUL_DOMAINS` e `SESSION_DOMAIN`
- GitHub Actions rodando lint e testes a cada push e pull request; deploy automatizado da branch `main` (D17)
- Monitor externo de uptime da API e do frontend (D18)
- Infraestrutura conforme D14
- HTTPS obrigatório

---

## 11. API (v1)

Convenções:

- Prefixo `/api/v1`. JSON. Datas em ISO 8601 (`YYYY-MM-DD`). Valores monetários como string decimal (`"1234.56"`).
- A organização vem do `current_organization_id` do usuário autenticado; não trafega na URL.
- Paginação: `?page=&per_page=` (padrão 25, máximo 100). Filtros: `?filter[campo]=valor`. Ordenação: `?sort=-date`.
- Erros: `401` não autenticado, `403` sem permissão, `404` inexistente ou de outra organização, `409` possível duplicidade (RN-37), `422` validação.

### Autenticação e perfil
```
GET    /sanctum/csrf-cookie
POST   /auth/register
POST   /auth/login
POST   /auth/logout
POST   /auth/forgot-password
POST   /auth/reset-password
POST   /auth/email/verification-notification
GET    /me
PUT    /me
PUT    /me/password
POST   /me/export            # gera arquivo e envia link por e-mail
DELETE /me                   # inicia a carência (RN-60)
```

### Organização
```
GET    /organization
PUT    /organization
POST   /onboarding/complete
```

### Contas
```
GET    /accounts
POST   /accounts
GET    /accounts/{id}
PUT    /accounts/{id}
DELETE /accounts/{id}        # 409 se houver lançamentos (RN-40)
POST   /accounts/{id}/archive
POST   /accounts/{id}/unarchive
GET    /accounts/{id}/statement?from=&to=
```

### Cartões e faturas
```
GET    /cards
POST   /cards
GET    /cards/{id}
PUT    /cards/{id}
DELETE /cards/{id}
POST   /cards/{id}/archive
POST   /cards/{id}/unarchive
GET    /cards/{id}/bills
GET    /bills/{id}           # com lançamentos e totais por categoria
POST   /bills/{id}/payments  # { account_id, amount, date } → cria card_payment; 422 se exceder o saldo (RN-12a)
```

### Categorias e tags
```
GET    /categories           # árvore
POST   /categories
PUT    /categories/{id}
DELETE /categories/{id}?reassign_to={id}
POST   /categories/{id}/archive
GET    /tags
POST   /tags
PUT    /tags/{id}
DELETE /tags/{id}
```

### Lançamentos
```
GET    /transactions
POST   /transactions         # aceita installments (parcelas) e force (duplicidade)
GET    /transactions/{id}
PUT    /transactions/{id}?scope=this|following|all
DELETE /transactions/{id}?scope=this|following|all
POST   /transactions/{id}/pay      # { paid_at, amount? }
POST   /transactions/{id}/unpay
POST   /transactions/{id}/skip     # ocorrência de recorrência (RN-34)
POST   /transactions/{id}/move-bill   # { bill_id } (RF-73)
```

### Recorrências
```
GET    /recurrences
POST   /recurrences
GET    /recurrences/{id}     # com as próximas ocorrências
PUT    /recurrences/{id}
DELETE /recurrences/{id}
POST   /recurrences/{id}/pause
POST   /recurrences/{id}/resume
```

### Dashboard
```
GET    /dashboard/summary?month=YYYY-MM
GET    /dashboard/cash-flow?months=12
GET    /dashboard/spending-by-category?month=YYYY-MM
GET    /dashboard/month-comparison?month=YYYY-MM
GET    /dashboard/balance-evolution?month=YYYY-MM
GET    /dashboard/top-expenses?month=YYYY-MM&limit=10
GET    /dashboard/alerts
```

### Relatórios e log
```
GET    /reports/summary?group_by=category|month|account|card&filter[...]
GET    /reports/export?format=csv&filter[...]
GET    /activity-logs?filter[from]=&filter[to]=&filter[subject_type]=
```

---

## 12. Telas

| Tela | Conteúdo principal |
|---|---|
| Login / Cadastro / Recuperar senha | — |
| Onboarding | 3 passos (RF-12) |
| Dashboard | KPIs, gráficos e alertas (RF-80 a RF-82) |
| Lançamentos | Lista com filtros persistentes e totais do filtro; criação/edição em drawer |
| Recorrências | Lista com próximas ocorrências e total mensal; pagar/pular ocorrência |
| Contas | Lista com saldos; detalhe com extrato |
| Cartões | Lista com fatura atual e limite disponível; detalhe com faturas |
| Fatura | Totais por categoria e estabelecimento, lançamentos, pagamento |
| Categorias e tags | Árvore editável |
| Relatórios | Agrupamentos, filtros e exportação CSV |
| Configurações | Organização, perfil, segurança, meus dados (LGPD), log de atividades |

---

## 13. UX e UI

- Navegação por menu lateral no desktop e barra inferior no mobile.
- Botão "+" de criação rápida sempre visível (RF-55).
- Filtros sempre acessíveis e mantidos na URL (compartilháveis e preservados ao voltar).
- Estados de loading (skeleton), vazio (com chamada para a ação) e erro em todas as listas e gráficos.
- Campo de valor com máscara pt-BR (`1.234,56`); data padrão = hoje.
- Cores por tipo, **sempre acompanhadas de ícone ou sinal** (não depender só de cor):
  - receita: verde, com "+"
  - despesa: vermelho, com "−"
  - recorrência: azul, com ícone de repetição
  - pendente: amarelo, com ícone de relógio
- Confirmação explícita antes de exclusões; desfazer (toast) quando possível.

---

## 14. Requisitos não funcionais

| ID | Requisito |
|---|---|
| RNF-01 | p95 < 500 ms em listagens paginadas e endpoints do dashboard, com até 50 mil lançamentos por organização |
| RNF-02 | Feedback visual em até 100 ms após uma ação do usuário (estado otimista ou loading) |
| RNF-03 | Layout funcional a partir de 360 px de largura |
| RNF-04 | Últimas 2 versões de Chrome, Firefox, Safari e Edge |
| RNF-05 | Backup diário do banco, retenção de 30 dias, RPO 24 h, RTO 4 h, teste de restauração mensal |
| RNF-06 | Cobertura de testes automatizados em 100% das regras de negócio (seção 8); os 3 fluxos críticos de D20 cobertos por testes de ponta a ponta |
| RNF-07 | Conteúdo em pt-BR, com textos externalizados (preparado para i18n) |
| RNF-08 | Acessibilidade: navegação por teclado, contraste WCAG 2.1 AA, labels em todos os campos |
| RNF-09 | Erros não tratados e falhas de jobs agendados notificados via Sentry; indisponibilidade detectada pelo monitor de uptime em até 5 min |

---

## 15. Segurança e privacidade

- Sanctum no modo SPA: cookie `HttpOnly`, `Secure`, `SameSite=Lax`, proteção CSRF.
- Isolamento de tenant: escopo global por `organization_id` em todos os models de domínio e policies em todos os endpoints, **com testes automatizados de acesso cruzado entre organizações**.
- Rate limit: login 5 tentativas/min por e-mail + IP; recuperação de senha 3/hora.
- Senhas com argon2id; verificação de e-mail obrigatória.
- Validação de entrada via FormRequest em todos os endpoints.
- SQL injection: apenas Eloquent/Query Builder com bindings.
- XSS: proibido `v-html` com dados do usuário; cabeçalhos CSP.
- Logs sem dados sensíveis (senha, tokens, cookies).
- Segredos apenas em variáveis de ambiente.
- 2FA (TOTP) na Fase 2.

**LGPD**

- Política de privacidade e termos aceitos no cadastro.
- Exportação dos dados (RF-06) e exclusão da conta (RF-07, RN-60).
- Minimização de dados (RN-61).

---

## 16. Critérios de aceitação

**CA-01 — Isolamento**
Dado dois usuários em organizações diferentes, quando o usuário A requisita qualquer recurso do usuário B pelo id, então a API responde `404`.

**CA-02 — Ciclo da fatura**
Dado um cartão com fechamento no dia 5 e vencimento no dia 12:
- compra em 04/03 → fatura com fechamento em 05/03 e vencimento em 12/03;
- compra em 05/03 → fatura com fechamento em 05/04 e vencimento em 12/04.

**CA-03 — Vencimento no mês seguinte**
Dado um cartão com fechamento no dia 28 e vencimento no dia 5, uma compra em 10/03 vai para a fatura com fechamento em 28/03, vencimento em 05/04 e mês de referência abril.

**CA-04 — Parcelamento**
Dada uma compra de R$ 1.000,00 em 3× em 10/03, são criados 3 lançamentos (333,34; 333,33; 333,33) com datas 10/03, 10/04 e 10/05, cada um em uma fatura consecutiva, e a soma é exatamente R$ 1.000,00.

**CA-05 — Sem dupla contagem**
Dada uma compra de R$ 100,00 no cartão em março e o pagamento dessa fatura a partir da conta, então as despesas de março somam R$ 100,00 (não R$ 200,00) e o saldo da conta diminui R$ 100,00.

**CA-06 — Transferência**
Uma transferência de R$ 500,00 entre contas não altera receitas nem despesas do mês, e o saldo total permanece igual.

**CA-07 — Estorno**
Um estorno de R$ 50,00 na categoria Lazer reduz em R$ 50,00 o total da fatura e as despesas de Lazer do mês.

**CA-08 — Recorrência em fim de mês**
Uma recorrência mensal iniciada em 31/01 gera ocorrências em 28/02 (ou 29/02 em ano bissexto), 31/03 e 30/04.

**CA-09 — Edição de recorrência**
Dada uma recorrência com uma ocorrência paga e duas pendentes, uma delas editada manualmente, ao alterar o valor da recorrência apenas a pendente não editada muda.

**CA-10 — Pausa**
Pausar uma recorrência remove as ocorrências pendentes futuras não editadas; retomar volta a gerá-las a partir de hoje, sem duplicar.

**CA-11 — Duplicidade**
Ao criar um lançamento igual a outro (conta, valor e descrição) com 1 dia de diferença, a API responde `409`; reenviado com `force=true`, o lançamento é criado.

**CA-12 — Arquivamento**
Uma conta com lançamentos não pode ser excluída (`409`), mas pode ser arquivada, e seus lançamentos continuam nos relatórios.

**CA-13 — Saldo previsto**
Dado saldo atual de R$ 2.000,00, salário pendente de R$ 5.000,00 em M, aluguel pendente de R$ 1.500,00 em M e fatura de R$ 800,00 vencendo em M, o saldo previsto ao fim de M é R$ 4.700,00.

**CA-14 — Exportação**
O CSV exportado contém exatamente os lançamentos da listagem com os mesmos filtros.

**CA-15 — Auditoria**
Editar o valor de um lançamento gera um registro no log com o valor antigo e o novo, o usuário e a data.

**CA-16 — Exclusão de conta**
Após 7 dias da solicitação sem cancelamento, nenhum dado da organização permanece no banco.

---

## 17. Roadmap

### Fase 1 — MVP
Todo o escopo da seção 5.1.

### Fase 2 — Análise e controle
- visão de caixa no dashboard e nos relatórios (alternativa à D2)
- orçamentos mensais por categoria
- exportação PDF e XLSX
- alertas de vencimento por e-mail
- 2FA

### Fase 3 — Automação
- importação de extratos (OFX e CSV)
- regras de classificação automática (por descrição/estabelecimento)
- importação de fatura em PDF

### Fase 4 — Expansão
- família/multiusuário: convites, papéis `admin`/`editor`/`viewer` (Spatie Permission com teams), filtro por usuário
- metas e previsões
- Open Finance
- categorização assistida por IA
- alertas por WhatsApp

---

## 18. Questões em aberto

Nenhuma questão de produto em aberto. Itens que dependem apenas de execução:

- escolher o nome de domínio e o provedor da VPS (D14);
- redigir a política de privacidade e os termos de uso (seção 15).
