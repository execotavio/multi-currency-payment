# API Payment Request — Multi-Currency Payment

## Goal
Endpoint para funcionário criar payment request em moeda local, buscar taxa automaticamente, persistir taxa imutável e retornar valor em EUR

## Out of scope

## Dependencies
Spec 01, Spec 02 e Spec 03

## Decisions (fixas)
- Se rate = EUR->Local, e o usuario envia amount_local, amount_eur = amount_local/rate
- Persistir rate, source, rate_fetched_at (nunca vai ser alterado)
- Status inicial: pending

## Deliverables
- POST /api/payment-requests

- Validaçoes:
      - amount_local > 0
      - currency formato ISO + "supported"

- Testes:
  - Feature: cria com sucesso e grava campos de rate
  - Feature: provider fora -> erro
  - Unit: calculo/rounding

## Acceptance criteria (checável)
[ ] Resposta inclui amount_eur e info do cambio
[ ] Taxa gravada permanece a mesma mesmo se provider mudar depois

## DoD
[ ] php artisan test verde