FROM php:7.4-fpm-alpine

RUN apk update && apk add libzip-dev

RUN docker-php-ext-install zip

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu

RUN docker-php-ext-install pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && mv composer /usr/local/bin/composer


COPY ./app /usr/src/app

RUN chown -R 1000 /usr/src/app
RUN chgrp -R 1000 /usr/src/app

WORKDIR /usr/src/app

RUN mkdir /.composer && chown 1000 /.composer && chgrp 1000 /.composer

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.9.0/wait /tmp/wait
RUN chmod +x /tmp/wait &&\
    chown 1000 /tmp/wait &&\
    chgrp 1000 /tmp/wait

RUN apk --no-cache add curl
RUN curl -L https://github.com/a8m/envsubst/releases/download/v1.1.0/envsubst-`uname -s`-`uname -m` -o /tmp/envsubst && \
    chmod u+x /tmp/envsubst && \
    chown 1000 /tmp/envsubst &&\
    chgrp 1000 /tmp/envsubst &&\
    mv /tmp/envsubst /usr/local/bin

USER 1000:1000

RUN composer install

RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

CMD ["/bin/sh", "-c", "/tmp/wait &&\
envsubst < .env.template > .env.local &&\
envsubst < .env.test.template > .env.test &&\
bin/console doctrine:database:create --if-not-exists;\
bin/console doctrine:migrations:migrate; \
php-fpm"]