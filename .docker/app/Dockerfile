FROM php:8.4-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html
COPY .docker/app/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/app/crontab /etc/cron.d/expense-schedule
COPY .docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN docker-php-ext-install pdo_mysql bcmath
RUN apt-get update
RUN apt-get install -y git zip unzip cron
RUN chmod 0644 /etc/cron.d/expense-schedule && crontab /etc/cron.d/expense-schedule
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
