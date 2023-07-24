# Makefile for local development

.DEFAULT_GOAL := help
.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ": ## "}; {printf "\033[36m%-28s\033[0m %s\n", $$1, $$2}' | sed 's/Makefile://g'

build: ## Build a Docker image for local development
	@docker build -t phpcanvas-dev .

run: ## Run the Docker image
	@docker run -d -v `pwd`:/var/www/html --name phpcanvas-dev-1 phpcanvas-dev

test: ## Run tests inside the Docker image
	@docker exec -it phpcanvas-dev-1 vendor/phpunit/phpunit/phpunit

stop: ## Stop the Docker image
	@docker stop phpcanvas-dev-1

remove: ## Remove the Docker image
	@docker rm phpcanvas-dev-1
