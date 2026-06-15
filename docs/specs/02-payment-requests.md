# Payment requests — Multi-Currency Payment

## Goal
Criar o modelo de dados e regras de estado para Payment Requests

## Out of scope


## Dependencies
Spec 01

## Decisions (fixas)
- Status possíveis: pending, approved, rejected, expired
- Transições:
  - Pending -> approved (finance)
  - Pending -> rejected (finance)
  - pending -> expired (scheduler)
  - Estados nao voltam
- Campos de cambio sao imutaveis após a criação:
  - eur_to_local_rate, rate_source, rate_fetched_at, amount_eur

## Deliverables
Migration payment_requests com:
  - user_id
  - amount_local (decimal)
  - currency (char(3))
  - amount_eur (decimal)
  - eur_to_local_rate (decimal)
  - rate_source (string)
  - rate_fetched_at (timestamp)
  - status (enum/string)
  - reviewed_by, reviewed_at (nullable)
  - expired_at (nullable)

Model PaymentRequest + casts + helpers de estado (ex.: isPending())

Testes unitários para:
  - regras de transição (método/service)
  - imutabilidade

## Acceptance criteria (checável)
[ ] migration está rodando
[ ] model está representando corretamente os estados
[ ] sem endpoint só dominio

## DoD
[ ] php artisan test verde