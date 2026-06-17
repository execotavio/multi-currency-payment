# multi-currency-payment

## Como rodar (setup rápido)

1. Suba os containers com build:
   - `make up`
2. Prepare a aplicação (dependências + `.env` + `APP_KEY`) e rode testes:
   - `make test`
3. Execute migrações e, se quiser, carregue dados demo:
   - `make migrate`
   - `docker compose exec app php artisan db:seed`
4. Instale e compile os assets do frontend:
   - `make npm-install`
   - `make npm-build`
5. Acesse a aplicação:
   - `http://localhost:8080`
6. Durante desenvolvimento frontend, rode o Vite dev server:
   - `make npm-dev`
   - Vite: `http://localhost:5173`
7. Rode coverage:
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

- `docker compose exec app php artisan payments:expire-pending`

Em ambiente com scheduler ativo, o Laravel executa esse comando de hora em hora via `routes/console.php`.
Para rodar o scheduler localmente:

- `docker compose exec app php artisan schedule:work`

## Docker e Coverage

O container PHP instala a extensão **PCOV** para cobertura de testes (mais leve que Xdebug).

- Extensão instalada no `Dockerfile`: `pcov`
- Coverage via: `php artisan test --coverage`

## Comandos disponíveis

- `make up` — sobe containers com build
- `make down` — derruba containers
- `make shell` — abre shell no container app
- `make test` — prepara app e executa testes
- `make migrate` — prepara app e executa migrações
- `make coverage` — prepara app e executa coverage
- `make npm-install` — instala dependências frontend no container `node`
- `make npm-dev` — roda Vite no container `node` em foreground
- `make npm-build` — gera assets em `public/build`

## Seeders

Rode os dados de demonstração com:

- `php artisan db:seed`
- ou via Docker: `docker compose exec app php artisan db:seed`

Credenciais seed:

- Finance: `finance@example.com` / `password123`
- Employees:
  - `employee.br@example.com` / `password123`
  - `employee.us@example.com` / `password123`
  - `employee.gb@example.com` / `password123`
  - `employee.jp@example.com` / `password123`
  - `employee.ca@example.com` / `password123`

Os payment requests seedados usam taxas fixas locais com `rate_source = seed` e não chamam API externa.
