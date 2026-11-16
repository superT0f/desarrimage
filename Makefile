.DEFAULT_GOAL := help

VHOST_DIR := /mnt/gandi/vhosts/desarrimage.prigent.tech/htdocs
PROD_URL := https://desarrimage.prigent.tech
RSYNC_OPTS := -avz --no-owner --no-group --chmod=D755,F644

.PHONY: help install dev dev-client dev-api check build clean mnt-gandi \
	db-migrate db-seed snapshot deploy push-front push-api push deploy-dry status verify verify-api

help:
	@printf '%s\n' \
		'Désarrimage — commandes du projet' \
		'  make install       Installe les dépendances et prépare api/.env' \
		'  make dev-client    Démarre Vue/Vite sur http://localhost:5173' \
		'  make dev-api       Démarre PHP sur http://localhost:8080' \
		'  make check         Valide PHP, Composer, le catalogue et le build' \
		'  make build         Génère l’instantané et construit le front' \
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

dev: dev-client

check:
	cd api && composer validate --strict
	find api/src api/public api/scripts api/db/migrations -name '*.php' -print0 | xargs -0 -n1 php -l
	php -r "json_decode(file_get_contents('data/catalog.json'), true, 512, JSON_THROW_ON_ERROR); echo 'Catalogue JSON valide'.PHP_EOL;"
	$(MAKE) --no-print-directory build

snapshot:
	php api/scripts/export-snapshot.php

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
	@echo 'API déployée ; le fichier api/.env de production est conservé.'

push-front: build mnt-gandi
	rsync $(RSYNC_OPTS) client/dist/assets/ "$(VHOST_DIR)/assets/"
	rsync $(RSYNC_OPTS) --delete \
		--exclude='assets/' \
		--exclude='api/' \
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
		--exclude='api/' --exclude='.env' --exclude='.htaccess' \
		client/dist/ "$(VHOST_DIR)/"

status: mnt-gandi
	rsync $(RSYNC_OPTS) --delete --dry-run \
		--exclude='api/' --exclude='.env' --exclude='.htaccess' \
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
