FROM php:7.4-apache
RUN apt-get update && apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
        imagemagick libmagickwand-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick
ADD ./php/php.ini /usr/local/etc/php/php.ini
ADD ./html /var/www/html
ADD ./web/sites-available/diary.conf /etc/apache2/sites-available/diary.conf
RUN a2ensite diary
RUN chown -R www-data:www-data /var/www/html