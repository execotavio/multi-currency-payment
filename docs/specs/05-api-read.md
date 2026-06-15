# API Read — Multi-Currency Payment

## Goal
Permitir ler detalhe e listar payment requests com filtro de status

## Out of scope

## Dependencies
Spec 04

## Decisions (fixas)
- Employee:
  - lista apenas seus requests
  - acessa apenas seus detalhes
- Finance:
  - lista todos
  - acessa qualquer detalhe
- Filtro status via query param

## Deliverables
- GET /api/payment-requests?status=pending

- GET /api/payment-requests/{id}

- Policy/authorization

- Testes:
  - Feature: employee vê só os seus
  - Feature: finance vê todos
  - Feature: employee tentando ver de outro -> erro 403
  - Feature: filtro status funciona

## Acceptance criteria (checável)
[ ] Lista com filtro por status


## DoD
[ ] php artisan test verde