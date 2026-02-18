DC := docker compose

.PHONY: up down restart ps logs shell artisan init key migrate seed-admin

up:
	$(DC) up -d --build

down:
	$(DC) down

restart:
	$(DC) down
	$(DC) up -d --build

ps:
	$(DC) ps

logs:
	$(DC) logs -f --tail=200

shell:
	$(DC) exec app sh

artisan:
	$(DC) exec app php artisan $(cmd)

init:
	$(DC) up -d --build
	$(DC) exec app php artisan key:generate --force
	$(DC) exec app php artisan migrate --force

key:
	$(DC) exec app php artisan key:generate --force

migrate:
	$(DC) exec app php artisan migrate --force

seed-admin:
	$(DC) exec app php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder --force
