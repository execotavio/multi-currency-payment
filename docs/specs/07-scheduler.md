# Scheduler — Multi-Currency Payment

## Goal
Expirar automaticamente payment requests pending com mais de 48 horas

## Out of scope

## Dependencies
Spec 02 e Spec 05

## Decisions (fixas)
- Implementar via Laravel Scheduler
- Implementar como artisan command (ex.: payments:expire-pending)
- Rodar via cron no container/host 
- bulk update por chunks

## Deliverables
- Command payments:expire-pending

- Agendamento no scheduler

- Testes:
  - Feature: cria pendente com created_at antigo, roda command, vira expired
  - Feature: usa travel/time

## Acceptance criteria (checável)
[ ] Requests com mais de 48 horas expirados


## DoD
[ ] php artisan test verde