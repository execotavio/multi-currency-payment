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
