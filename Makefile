.PHONY: up down shell test migrate coverage prepare

up:
	docker compose up -d --build

down:
	docker compose down

shell:
	docker compose exec app sh

prepare:
	docker compose exec app sh -c 'if [ ! -f /app/vendor/autoload.php ]; then composer install; fi'
	docker compose exec app sh -c 'if [ ! -f /app/.env ]; then cp /app/.env.example /app/.env; fi'
	docker compose exec app sh -c 'if ! grep -q "^APP_KEY=base64:" /app/.env; then php artisan key:generate --force; fi'

test: prepare
	docker compose exec app php artisan test

migrate: prepare
	docker compose exec app php artisan migrate

coverage: prepare
	docker compose exec app php artisan test --coverage
