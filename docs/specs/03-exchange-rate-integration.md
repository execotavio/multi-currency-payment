# Exchange rate integration— Multi-Currency Payment

## Goal
Integrar com uma API pública gratuita para obter taxa EUR → moeda local no momento de criação do payment request

## Out of scope

## Dependencies
Spec 00 e Spec 01

## Decisions (fixas)
- O provider utilizado por padr±ao deve ser o [Exchange rate](https://www.exchangerate-api.com)
- Base sempre EUR
- A taxa EUR → moeda local deve ser buscada no provider a cada criação de payment request
- Cache Redis pode ser usado para dados estáveis do provider, como lista de moedas suportadas
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

- Sem cache para taxa de conversão persistida em payment requests

- Testes:
  - Unit: busca no provider a cada chamada (mock HTTP)
  - Unit: tratamento de falha/timeouts (mock HTTP)

## Acceptance criteria (checável)
[ ] Para qualquer moeda, serviço retorna taxa > 0, source, timestamp
[ ] Não reutiliza taxa antiga para novas criações de payment request

## DoD
[ ] php artisan test verde
