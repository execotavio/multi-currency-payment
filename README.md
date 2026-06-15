# multi-currency-payment

## Como rodar (setup rápido)

1. Suba os containers com build:
   - `make up`
2. Prepare a aplicação (dependências + `.env` + `APP_KEY`) e rode testes:
   - `make test`
3. Execute migrações:
   - `make migrate`
4. Acesse a aplicação:
   - `http://localhost:8080`
5. Rode coverage:
   - `make coverage`

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
