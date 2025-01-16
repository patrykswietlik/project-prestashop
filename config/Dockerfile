FROM patrykswietlik/project-prestashop:1.2
COPY config/backup /tmp
WORKDIR /tmp

#ssl
COPY config/ssl/localhost.crt /etc/ssl/certs/
RUN chmod 777 -R /etc/ssl/certs/
COPY config/ssl/localhost.key /etc/ssl/private/
RUN chmod 777 -R /etc/ssl/private/
COPY config/ssl/my-ssl.conf /etc/apache2/sites-available/my-ssl.conf
RUN a2enmod ssl && \
    a2enmod rewrite && \
    a2dissite 000-default default-ssl && \
    a2ensite my-ssl

#install memcached
RUN apt-get update && apt-get install -y \
    libmemcached-dev \
    zlib1g-dev \
    && pecl install memcached \
    && docker-php-ext-enable memcached


#COPY presta files
COPY prestashop/html/app/config/parameters.yml.dist /var/www/html/app/config/parameters.yml.dist
COPY prestashop/html/themes /var/www/html/themes
COPY prestashop/html/img /var/www/html/img
COPY prestashop/html/themes/marvelTheme /var/www/html/themes/marvelTheme


CMD /bin/sh ./load_backup2.sh && apache2-foreground