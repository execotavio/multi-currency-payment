# Project Setup — Multi-Currency Payment

## Goal
Garantir que qualquer reviewer consiga rodar o projeto localmente em poucos comandos, com MySQL + Redis, e que testes/coverage possam ser executados desde o início

## Out of scope
- Implementar qualquer endpoint do negócio (payment requests, exchange rates)
- Implementar UI/páginas
- Implementar seeders do domínio (apenas o mínimo para subir app)

## Dependencies
Nenhuma

## Decisions (fixas)
- Laravel 12 + PHP 8.2+
- DB: MySQL
- Redis: para cache/queue (mesmo que ainda não usado)
- Inertia.js + React (apenas preparar; não construir páginas aqui)
- Test runner padrão: php artisan test

## Deliverables
Docker
  - Dockerfile (PHP 8.2 + extensões necessárias)
  - docker-compose.yml com serviços:
      - app (php-fpm)
      - web (nginx)
      - mysql
      - redis

Bootstrap do Laravel
  - Projeto Laravel criado
  - .env.example alinhado com Docker (DB_HOST=mysql, REDIS_HOST=redis)
  - APP_URL consistente com Nginx (ex.: http://localhost:8080)

Makefile
  - make up
  - make down
  - make shell
  - make test
  - make migrate

Test & Coverage readiness
  - Garantir que dentro do container é possível rodar:
      - php artisan test
      - php artisan test --coverage ou gerar coverage.xml/clover.xml

## Acceptance criteria (checável)
[ ] docker compose up -d sobe sem erro
[ ] A home do Laravel responde em http://localhost:8080
[ ] make test roda e passa
[ ] Um comando de coverage executa sem quebrar

## DoD
[ ] README (mínimo) com “como rodar” (3–5 passos)
[ ] .env.example completo para subir no Docker
[ ] make up + make test funcionam em máquina limpa
[ ] Coverage command disponível