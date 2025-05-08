FROM php:8.3-alpine

ENV XDEBUG_MODE=coverage

RUN apk add --update --no-cache \
	unzip \
	linux-headers \
	tidyhtml-dev \
	&& docker-php-ext-install tidy

RUN apk add --no-cache ${PHPIZE_DEPS} \ 
	&& pecl install xdebug-3.3.2 \
	&& docker-php-ext-enable xdebug

RUN cat <<EOF > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
zend_extension=xdebug.so
xdebug.mode=debug.
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9001
EOF

#RUN apt-get update \
#	&& apt-get install -y \
#	unzip \
#	p7zip
#
#RUN pecl install xdebug
#RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini
#RUN echo "xdebug.xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini
#RUN echo "xdebug.xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini
#RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:2.8.1 /usr/bin/composer /usr/bin/composer

# TODO  get composer from image here

#RUN a2enmod rewrite ssl headers expires
#RUN a2ensite default-ssl.conf

WORKDIR /var/www

CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/tests/fixtures/app/html", "/var/www/tests/fixtures/app/router.php"]

EXPOSE 80
