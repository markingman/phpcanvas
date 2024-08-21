FROM php:8.3-apache

RUN pecl install xdebug
RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN apt-get update && apt-get install -y unzip p7zip ssl-cert

RUN a2enmod rewrite ssl headers expires
RUN a2ensite default-ssl.conf
