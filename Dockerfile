FROM php:7.4-apache
ADD ./html /var/www/html
ADD ./web/sites-available/diary.conf /etc/apache2/sites-available/diary.conf
RUN a2ensite diary
RUN chown -R www-data:www-data /var/www/html