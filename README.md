# multi-currency-payment

## Como rodar (setup rápido)

Pré-requisitos:

- Docker com Docker Compose
- `make`
- Chaves para as integrações externas, se for usar cadastro e criação de requests pela UI:
  - `EXCHANGE_RATE_API_KEY`
  - `REST_COUNTRIES_API_KEY`

Passos:

1. Suba os containers com build:
   - `make up`
2. Prepare a aplicação e rode os testes:
   - `make test`
3. Rode prepare e configure as chaves externas no `.env` criadas:
   - `make prepare`
4. Execute migrações e carregue dados demo:
   - `make migrate`
   - `make seed`
5. Instale e compile os assets do frontend:
   - `make npm-install`
   - `make npm-build`
6. Acesse a aplicação:
   - `http://localhost:8080`
7. Durante desenvolvimento frontend, rode o Vite dev server:
   - `make npm-dev`
   - Vite: `http://localhost:5173`
8. Rode coverage:
   - `make coverage`

## Documentação da API

A especificação OpenAPI com métodos, URLs, parâmetros e exemplos está em:

- `docs/openapi.yaml`

## Integrações externas

Configure as chaves no `.env`:

- `EXCHANGE_RATE_API_KEY` para taxas EUR → moeda local.
- `REST_COUNTRIES_API_KEY` para listar países no cadastro. A API atual do REST Countries v5 exige bearer token.

## Scheduler

Payment requests pendentes por mais de 48 horas são expirados pelo comando:

- `make expire-pending`

Em ambiente com scheduler ativo, o Laravel executa esse comando de hora em hora via `routes/console.php`.
Para rodar o scheduler localmente:

- `make schedule-work`

## Docker e Coverage

O container PHP instala a extensão **PCOV** para cobertura de testes (mais leve que Xdebug).

- Extensão instalada no `Dockerfile`: `pcov`
- Coverage via: `make coverage`

## Comandos disponíveis

- `make up` — sobe containers com build
- `make down` — derruba containers
- `make install` — instala dependências PHP com Composer
- `make shell` — abre shell no container app
- `make prepare` — instala Composer se necessário, cria `.env` e gera `APP_KEY`
- `make test` — prepara app e executa testes
- `make migrate` — prepara app e executa migrações
- `make seed` — prepara app e executa seeders
- `make coverage` — prepara app e executa coverage
- `make expire-pending` — prepara app e expira payment requests pendentes há mais de 48 horas
- `make schedule-work` — prepara app e executa o scheduler em foreground
- `make setup` — executa `prepare`, `npm-install` e `migrate`
- `make npm-install` — instala dependências frontend no container `node`
- `make npm-dev` — roda Vite no container `node` em foreground
- `make npm-build` — gera assets em `public/build`

## Seeders

Rode os dados de demonstração com:

- `make seed`

Credenciais seed:

- Finance: `finance@example.com` / `password123`
- Employees:
  - `employee.br@example.com` / `password123`
  - `employee.us@example.com` / `password123`
  - `employee.gb@example.com` / `password123`
  - `employee.jp@example.com` / `password123`
  - `employee.ca@example.com` / `password123`

Os payment requests seedados usam taxas fixas locais com `rate_source = seed` e não chamam API externa.
