FROM debian:bookworm-20241223

ARG PHP_VERSION=8.2
ARG PHALCON_VERSION=5.8.0
ARG MYSQL_ROOT_PASSWORD
ARG ENVIRONMENT=development

RUN set -ex \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        gnupg2 \
        ca-certificates \
        apt-transport-https \
        software-properties-common \
        lsb-release \
        wget \
        vim \
    && curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-common \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-dev \
        php${PHP_VERSION}-soap \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-xdebug \
        php${PHP_VERSION}-imagick \
        php8.4-dom \
        imagemagick \
        libmagickwand-dev \
        apache2 \
        mariadb-server \
        mariadb-client \
        build-essential \
        libpcre3-dev \
    && chown -R mysql:mysql /var/lib/mysql \
    && cd /tmp \
    && curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz \
    && tar xzf v${PHALCON_VERSION}.tar.gz \
    && cd cphalcon-${PHALCON_VERSION}/build \
    && ./install \
    && echo "extension=phalcon.so" > /etc/php/${PHP_VERSION}/mods-available/phalcon.ini \
    && phpenmod phalcon \
    && phpenmod soap \
    && phpenmod imagick \
    && phpenmod intl \
    && phpenmod xdebug \
    && a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && mysql_install_db --user=mysql --ldata=/var/lib/mysql/ \
    && mkdir -p /data \
    && ln -s /opt/formygames/src/public /data/public \
    && ln -s /opt/formygames/src/api /data/api \
    && ln -s /opt/formygames/src/vendor /data/vendor \
    && ln -s /opt/formygames/src/app /data/bin \
    && ln -s /opt/formygames/src/composer.json /data/composer.json \
    && ln -s /opt/formygames/src/composer.lock /data/composer.lock \
    && ln -s /usr/bin/convert /usr/local/bin/convert \
    && echo Listen 8909 | tee -a /etc/apache2/ports.conf \
    && chmod +x /opt/formygames/src/docker/phpmyadmin.sh

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /opt/formygames/src
COPY . .

# Create entrypoint script
RUN echo '#!/bin/bash\n\
cp /usr/bin/convert /usr/local/bin/convert\n\
# Start MariaDB\n\
service mariadb start\n\
\n\
# Wait for MariaDB to be ready\n\
while ! mysqladmin ping -h localhost --silent; do\n\
    sleep 1\n\
done\n\
\n\
# Start Apache\n\
apache2ctl -D FOREGROUND' > /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8909

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]