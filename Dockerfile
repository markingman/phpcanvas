ARG PHP_VERSION=8.3
ARG COMPOSER_VERSION=2.9.2
ARG XDEBUG_VERSION=3.4.7

FROM php:${PHP_VERSION}-alpine AS base
ARG XDEBUG_VERSION

RUN set -eux; \
	apk add --update --no-cache \
		$PHPIZE_DEPS \
		unzip \
		linux-headers \
		tidyhtml-dev; \
	pecl channel-update pecl.php.net; \
	pecl install xdebug-${XDEBUG_VERSION}; \
	docker-php-ext-enable xdebug; \
	docker-php-ext-install tidy; \
	pecl clear-cache;

RUN cat <<EOF > /usr/local/etc/php/conf.d/99-xdebug.ini
xdebug.mode=coverage
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
EOF

FROM composer:${COMPOSER_VERSION} AS composer

FROM base

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN /usr/bin/composer install --prefer-dist --no-interaction
