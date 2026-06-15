# Exchange rate integration— Multi-Currency Payment

## Goal
Integrar com uma API pública gratuita para obter taxa EUR → moeda local no momento de criação do payment request

## Out of scope

## Dependencies
Spec 00 e Spec 01

## Decisions (fixas)
- O provider utilizado por padr±ao deve ser o [Exchange rate](https://www.exchangerate-api.com)
- Base sempre EUR
- Cache usando Redis para evitar rate limits
- Redis: para cache/queue (mesmo que ainda não usado)
- IA API provider deve ser configurável via .env
- Em falha do provider: retornar erro HTTP

## Deliverables
- ExchangeRateService com método tipo getEurTo(string $currency): RateDTO

- RateDTO com:
      - rate
      - source
      - fetchedAt

- Implementação usando Http:: do Laravel

- Cache Redis (TTL configurável)

- Testes:
  - Unit: cache hit/miss (mock HTTP)
  - Unit: tratamento de falha/timeouts (mock HTTP)

## Acceptance criteria (checável)
[ ] Para qualquer moeda, serviço retorna taxa > 0, source, timestamp
[ ] Cache funcionando

## DoD
[ ] php artisan test verde