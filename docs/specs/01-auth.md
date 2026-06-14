# Authentication — Multi-Currency Payment

## Goal
Implementar autenticação completa (Register, Login, Logout) com Laravel Passport e controle de papel (employee/finance)

## Out of scope


## Dependencies
Spec 00

## Decisions (fixas)
- Auth via Bearer token 
- Papéis: employee e finance

## Deliverables
- Migration users com:
    - name, email, password, role

- Endpoints:
    - POST /api/auth/register
    - POST /api/auth/login
    - POST /api/auth/logout

- Middleware/Policy para checar role

- Testes:
    - Feature: register/login/logout (HTTP)
    - Unit: regra simples de role (ex.: User::isFinance()), se existir lógica

## Acceptance criteria (checável)
[ ] Usuário registra e recebe token
[ ] Usuário loga e recebe token
[ ] Logout revoga token
[ ] Endpoints protegidos retornam 401 sem token

## DoD
[ ] php artisan test verde