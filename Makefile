.DEFAULT_GOAL := help

VHOST_DIR := /mnt/gandi/vhosts/desarrimage.prigent.tech/htdocs
PROD_URL := https://desarrimage.prigent.tech
RSYNC_OPTS := -avz --no-owner --no-group --chmod=D755,F644
COMPOSE ?= $(shell if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then printf 'docker compose'; elif command -v podman-compose >/dev/null 2>&1; then printf 'podman-compose'; elif [ -x .tmp/podman-compose/bin/podman-compose ]; then printf '.tmp/podman-compose/bin/podman-compose'; elif command -v podman >/dev/null 2>&1 && podman compose version >/dev/null 2>&1; then printf 'podman compose'; else printf 'docker compose'; fi)
APP_VERSION := $(shell cat .version 2>/dev/null || printf '0.1.0')
export APP_VERSION
export VITE_APP_VERSION := $(APP_VERSION)

.PHONY: help install dev dev-detached dev-down dev-logs dev-ps dev-client dev-api \
	compose-bootstrap docker-check check build version changelog release clean mnt-gandi \
	db-migrate db-seed snapshot deploy push-front push-api push deploy-dry status verify verify-api

help:
	@printf '%s\n' \
		'Désarrimage — commandes du projet' \
		'  make install       Installe les dépendances et prépare api/.env' \
		'  make dev           Démarre front + API + MariaDB dans Docker' \
		'  make dev-detached  Idem, en arrière-plan' \
		'  make dev-logs      Suit les logs des conteneurs' \
		'  make dev-down      Arrête les conteneurs (données conservées)' \
		'  make dev-client    Démarre Vue/Vite hors Docker' \
		'  make dev-api       Démarre PHP hors Docker' \
		'  make check         Valide PHP, Composer, le catalogue et le build' \
		'  make build         Génère l’instantané et construit le front' \
		'  make version       Affiche la version SemVer du projet' \
		'  make changelog     Régénère CHANGELOG.md depuis les commits' \
		'  make release       Prépare une version : make release VERSION=0.2.0' \
		'  make db-migrate    Applique les migrations avec api/.env' \
		'  make db-seed       Importe le catalogue avec api/.env' \
		'  make deploy        Déploie la landing et le front sur Gandi' \
		'  make push          Déploie l’API puis le front sur Gandi' \
		'  make deploy-dry    Simule le déploiement du front' \
		'  make status        Affiche les différences avec la production' \
		'  make verify        Vérifie la home de production' \
		'  make verify-api    Vérifie la base via /api/health'

install:
	cd api && composer install
	cd client && npm install
	@test -f api/.env || cp api/.env.example api/.env
	@echo 'Renseigne les accès MySQL dans api/.env avant make db-migrate.'

dev-client:
	cd client && npm run dev

dev-api:
	cd api && php -S localhost:8080 -t public

dev: docker-check
	$(COMPOSE) up --build

dev-detached: docker-check
	$(COMPOSE) up --build -d
	@echo 'Front : http://localhost:5173 — API : http://localhost:8080/api/health'

dev-down:
	$(COMPOSE) down

dev-logs:
	$(COMPOSE) logs -f

dev-ps:
	$(COMPOSE) ps

compose-bootstrap:
	@if $(COMPOSE) version >/dev/null 2>&1; then \
		echo 'Compose est déjà disponible : $(COMPOSE)'; \
	else \
		command -v podman >/dev/null 2>&1 || (echo 'Installe Docker ou Podman pour continuer.' && exit 1); \
		python3 -m venv .tmp/podman-compose; \
		.tmp/podman-compose/bin/pip install --disable-pip-version-check podman-compose; \
		echo 'podman-compose installé localement. Relance make dev.'; \
	fi

docker-check:
	@$(COMPOSE) version >/dev/null 2>&1 || (echo 'Compose est absent. Installe Docker Compose ou lance make compose-bootstrap.' && exit 1)
	@$(COMPOSE) config >/dev/null

check:
	cd api && composer validate --strict
	find api/src api/public api/scripts api/db/migrations scripts -name '*.php' -print0 | xargs -0 -n1 php -l
	php -r "json_decode(file_get_contents('data/catalog.json'), true, 512, JSON_THROW_ON_ERROR); echo 'Catalogue JSON valide'.PHP_EOL;"
	php scripts/check-version.php "$$(cat .version)"
	$(MAKE) --no-print-directory build

snapshot:
	php api/scripts/export-snapshot.php

version:
	@printf '%s\n' "$(APP_VERSION)"

changelog:
	php scripts/generate-changelog.php

release:
	@test -n "$(VERSION)" || (echo 'Usage : make release VERSION=0.2.0' && exit 1)
	php scripts/check-version.php "$(VERSION)" --validate-only
	printf '%s\n' "$(VERSION)" > .version
	cd client && npm version "$(VERSION)" --no-git-tag-version --ignore-scripts
	$(MAKE) --no-print-directory changelog
	@echo 'Version préparée. Vérifie, committe, puis crée le tag v$(VERSION).'

build: snapshot
	cd client && npm run build

db-migrate:
	cd api && vendor/bin/phinx migrate

db-seed:
	cd api && php scripts/seed.php

mnt-gandi:
	@test -d "$(VHOST_DIR)" || (echo 'Montage ou vhost Gandi absent : $(VHOST_DIR)' && exit 1)

push-api: mnt-gandi
	cd api && composer install --no-dev --optimize-autoloader --no-interaction
	rsync $(RSYNC_OPTS) --delete \
		--exclude='.env' \
		--exclude='.env.example' \
		--exclude='*.log' \
		api/ "$(VHOST_DIR)/api/"
	rsync $(RSYNC_OPTS) --delete data/ "$(VHOST_DIR)/data/"
	@echo 'API et données déployées ; le fichier api/.env de production est conservé.'

push-front: build mnt-gandi
	rsync $(RSYNC_OPTS) client/dist/assets/ "$(VHOST_DIR)/assets/"
	rsync $(RSYNC_OPTS) --delete \
		--exclude='assets/' \
		--exclude='api/' \
		--exclude='data/' \
		--exclude='.env' \
		--exclude='.htaccess' \
		--exclude='index.html' \
		client/dist/ "$(VHOST_DIR)/"
	rsync $(RSYNC_OPTS) .htaccess "$(VHOST_DIR)/.htaccess"
	rsync $(RSYNC_OPTS) client/dist/index.html "$(VHOST_DIR)/index.html"
	@find "$(VHOST_DIR)/assets" -type f -mtime +7 -delete 2>/dev/null || true
	@echo 'Front déployé sur $(PROD_URL)'

deploy: push-front

push: push-api push-front

deploy-dry: build mnt-gandi
	rsync $(RSYNC_OPTS) --delete --dry-run \
		--exclude='api/' --exclude='data/' --exclude='.env' --exclude='.htaccess' \
		client/dist/ "$(VHOST_DIR)/"

status: mnt-gandi
	rsync $(RSYNC_OPTS) --delete --dry-run \
		--exclude='api/' --exclude='data/' --exclude='.env' --exclude='.htaccess' \
		client/dist/ "$(VHOST_DIR)/"

verify:
	@body=$$(curl -sS "$(PROD_URL)/?v=$$(date +%s)"); \
		printf '%s' "$$body" | grep -q '<title>Désarrimage' || (echo 'La home ne contient pas la nouvelle application.' && exit 1)
	@curl -sS "$(PROD_URL)/images/logo.svg" -o /dev/null
	@echo 'Home, contenu et logo accessibles.'

verify-api:
	@curl -sS --fail "$(PROD_URL)/api/health?v=$$(date +%s)"
	@echo

clean:
	rm -rf client/dist
