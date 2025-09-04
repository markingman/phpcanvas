# Makefile for local development

.DEFAULT_GOAL := help
.PHONY: help
NAME=phpcanvas-test

help:
	@grep -E '^[a-zA-Z0-9._-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ": ## "}; {printf "\033[36m%-28s\033[0m %s\n", $$1, $$2}' | sed 's/Makefile://g'

build8.3: ## Build a PHP 8.3 Docker image for local development
	@docker build --build-arg PHP_VERSION=8.3 -t $(NAME) .

build8.4: ## Build a PHP 8.4 Docker image for local development
	@docker build --build-arg PHP_VERSION=8.4 -t $(NAME) .

run: ## Run the Docker image
	@docker run -d --rm -v `pwd`:/var/www -p 80:80 --name $(NAME) $(NAME)

# runint: ## Run the Docker image for integration tests
# 	@docker run -d --rm -v `pwd`:/var/www/html --name $(NAME) $(NAME)

stop: ## Clean up
	@docker stop $(NAME)

install: ## Set up environment in container
	@docker exec -it $(NAME) /usr/bin/composer install

update: ## Update existing environment in container
	@docker exec -it $(NAME) /usr/bin/composer update

test: ## Run tests inside the container
	@docker exec -it $(NAME) vendor/bin/phpunit

analyse: ## Start container to run analyse
	@docker exec -it $(NAME) vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 256M

ssh: ## SSH into container
	@docker exec -it $(NAME) sh

clean: ## Clean up
	@rm -Rf vendor .phpunit.result.cache *-coverage
