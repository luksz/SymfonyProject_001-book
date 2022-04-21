FROM phpdockerio/php:8.0-fpm
WORKDIR "/application"

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        git \ 
        php8.0-bcmath \ 
        php8.0-bz2 \ 
        php8.0-decimal \ 
        php8.0-http \ 
        php8.0-ldap \ 
        php8.0-mcrypt \ 
        php8.0-memcached \ 
        php8.0-oauth \ 
        php8.0-pgsql \ 
        php8.0-psr \ 
        php8.0-redis \ 
        php8.0-uuid \ 
        php8.0-xdebug \ 
        php8.0-yaml; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN git config --global user.email "you@example.com" \ 
    && git config --global user.name "Your Name"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer    

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony