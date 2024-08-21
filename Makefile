# Makefile for local development

.DEFAULT_GOAL := help
.PHONY: help
NAME=phpcanvas-test

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ": ## "}; {printf "\033[36m%-28s\033[0m %s\n", $$1, $$2}' | sed 's/Makefile://g'

build: ## Build a Docker image for local development
	@docker build -t $(NAME) .

run: ## Run the Docker image
	@docker run -d -v `pwd`:/var/www/html --name $(NAME) $(NAME)

setup: ## Set up environment in container
	@docker exec -it $(NAME) curl https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer | php -- --quiet
	@docker exec -it $(NAME) php composer.phar install

update: ## Update existing environment in container
	@docker exec -it $(NAME) php composer.phar update

start: ## Start container to run tests (if container built and stopped)
	@docker container start $(NAME)

stop: ## Stop current container (if running)
	@docker stop $(NAME)

test: ## Run tests inside the container
	@docker exec -it $(NAME) vendor/bin/phpunit --display-deprecations --display-warnings

ssh: ## SSH into container
	@docker exec -it $(NAME) sh

clean: ## Clean up
	@docker stop $(NAME)
	@rm -Rf vendor .phpunit.result.cache *-coverage
	@docker rm $(NAME)
