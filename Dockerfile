FROM php:7.1.33-apache-stretch

RUN set -eux; \
	if grep -q "stretch" /etc/apt/sources.list; then \
		sed -i \
			-e 's/deb.debian.org/archive.debian.org/g' \
			-e 's/security.debian.org/archive.debian.org/g' \
			-e 's/stretch-updates/stretch/g' \
			/etc/apt/sources.list; \
	fi; \
	apt-get -o Acquire::Check-Valid-Until=false update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libmcrypt-dev \
		libpng-dev \
		libzip-dev \
		zlib1g-dev; \
	docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
	docker-php-ext-install gd mbstring mcrypt mysqli pdo_mysql zip; \
	a2enmod rewrite headers; \
	rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/koperasi.ini
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
