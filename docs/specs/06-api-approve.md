# API Approve — Multi-Currency Payment

## Goal
Finance aprova ou rejeita apenas requests pendentes

## Out of scope

## Dependencies
Spec 05

## Decisions (fixas)
- Endpoints:
  - PATCH /api/payment-requests/{id}/approve
  - PATCH /api/payment-requests/{id}/reject
- Se não estiver pending: retornar conflito (409) com mensagem clara
- Marcar reviewed_by e reviewed_at

## Deliverables
- Implementação endpoints + authorization finance-only

- Testes:
  - Feature: finance aprova pendente
  - Feature: finance rejeita pendente
  - Feature: employee não pode (403)
  - Feature: aprovar/rejeitar não-pendente (409)

## Acceptance criteria (checável)
[ ] Endpoints implementados
[ ] Apenas finance aprova requests


## DoD
[ ] php artisan test verde