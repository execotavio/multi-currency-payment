# API Documentation — Multi-Currency Payment

## Goal
Documentar cada endpoint (método, URL, parâmetros, exemplos). Usar OpenAPI 3.1 como formato

## Out of scope

## Dependencies
Spec 01, Spec 04, Spec 05, Spec 06

## Decisions (fixas)
- Formato escolhido: docs/openapi.yaml como fonte única da verdade
- Cada endpoint documentado com:
  - summary, operationId
  - requestBody com schema + example
  - responses com status codes e exemplos (inclusive de erro)
- Erros
  - 401 — Unauthorized
  - 403 — Forbidden
  - 404 — Not Found
  - 409 — Conflict
  - 422 — Unprocessable Entity
  - 503 — Service Unavailable

## Deliverables
- Arquivo: docs/openapi.yaml

- Deve conter:
  - info, servers, components, security, paths
  - Todos os endpoints implementados até agora
  - schemas reutilizáveis (ex: PaymentRequest, User, Error)
  - Exemplos reais de:
    - amount_eur (string com 2 casas)
    - rate_source, rate_fetched_at
    - status, reviewed_by, expired_at, etc.

## Acceptance criteria (checável)
[ ] docs/openapi.yaml segue OpenAPI 3.1
[ ] Cada endpoint possui: summary, request body com exemplo, responses para 200/201/401/403/404/409/422/503
[ ] Todos os schemas (PaymentRequest, User, etc.) estão descritos com type, example
[ ] amount_eur é string com 2 casas decimais em exemplos
[ ] Security com bearerAuth (Passport)
[ ] Erros padronizados com message e detalhes quando aplicável

## DoD
[ ] php artisan test verde