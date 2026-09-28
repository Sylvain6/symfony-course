# Raccourcis pour ne pas retaper "docker compose exec app" toute la journée.
# Tout s'exécute DANS le conteneur, jamais sur votre poste.

.DEFAULT_GOAL := help
DC  = docker compose
EXE = $(DC) exec app

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

build: ## Reconstruit l'image de l'application
	$(DC) build

up: ## Démarre les conteneurs en arrière-plan
	$(DC) up -d

down: ## Arrête les conteneurs (les données de la base sont conservées)
	$(DC) down

reset: ## Arrête tout ET supprime la base de données
	$(DC) down -v

logs: ## Suit les logs de l'application
	$(DC) logs -f app

sh: ## Ouvre un shell dans le conteneur de l'application
	$(EXE) sh

install: ## Installe les dépendances PHP
	$(EXE) composer install

migrate: ## Applique les migrations Doctrine
	$(EXE) php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

fixtures: ## Recharge les données de démonstration
	$(EXE) php bin/console doctrine:fixtures:load --no-interaction

cc: ## Vide le cache Symfony
	$(EXE) php bin/console cache:clear

console: ## Lance une commande Symfony : make console c="debug:router"
	$(EXE) php bin/console $(c)

.PHONY: help build up down reset logs sh install migrate fixtures cc console
