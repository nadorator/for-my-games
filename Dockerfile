FROM debian:bookworm-20241223
LABEL maintainer="nhmerouane@hotmail.com"
LABEL version="0.1"
LABEL description="For My Games podman dockerfile"
ARG DEBIAN_FRONTEND=noninteractive
ENV TZ="Europe/Paris"
RUN apt-get update && apt-get -y upgrade && apt-get update
RUN apt-get -y install systemd systemd-sysv locales locales-all nano vim iproute2 iputils-ping telnet apt-utils
RUN apt-get -y install wget imagemagick webp vim lsb-release gcc make automake autoconf locate git curl gcc make
RUN apt-get -y install rsyslog
RUN apt-get -y install ntp net-tools
RUN apt-get -y install apache2 libapache2-mod-php
RUN apt-get -y install php composer php-zip php-json php-mbstring php-bcmath php-intl  php-common php-mysql php-cli php-curl php-xml php-xmlrpc php-soap
RUN apt-get -y install php-ldap openssl php8.2-sqlite sqlite3 php-imagick php-xdebug php-gd php-json composer
RUN apt-get -y install php-dev libpcre3-dev build-essential
RUN apt-get -y install postfix
VOLUME ["/opt/formygames/"]
VOLUME ["/var/lib/mysql"]
RUN a2enmod rewrite
RUN a2enmod headers
RUN rm -f /etc/php/8.2/apache2/conf.d/20-json.ini
RUN rm -f /etc/php/8.2/apache2/php.ini
RUN rm -f /etc/php/8.2/cli/php.ini
RUN ln -s /opt/formygames/src/vm/conf/php/php8.2.ini /etc/php/8.2/apache2/php.ini
RUN ln -s /opt/formygames/src/vm/conf/php/cli8.2.ini /etc/php/8.2/cli/php.ini
RUN rm /etc/php/8.2/apache2/conf.d/20-xdebug.ini
RUN ln -s /opt/formygames/src/vm/conf/php/xdebug.ini /etc/php/8.2/apache2/conf.d/20-xdebug.ini
RUN rm /etc/postfix/main.cf
RUN ln -s /opt/formygames/src/vm/conf/main.cf /etc/postfix/main.cf
RUN apt-get -y install curl software-properties-common dirmngr
RUN pecl channel-update pecl.php.net
RUN pecl install phalcon
RUN apt-get update && apt-get -y install mariadb-server mariadb-client
RUN chown -R mysql:mysql /var/lib/mysql
RUN mysql_install_db --user=mysql --ldata=/var/lib/mysql/
RUN mkdir -p /data
RUN cd /data
RUN ln -s /opt/formygames/src/public /data/public
RUN ln -s /opt/formygames/src/api /data/api
RUN ln -s /opt/formygames/src/vendor /data/vendor
RUN ln -s /opt/formygames/src/app /data/bin
RUN ln -s /opt/formygames/src/composer.json /data/composer.json
RUN ln -s /opt/formygames/src/composer.lock /data/composer.lock
RUN ln -s /opt/formygames/src/vm/conf/apache2/000-default82.conf /etc/apache2/sites-enabled/100-default.conf
RUN sed -i 's/APACHE_RUN_\\(USER\\|GROUP\\)=www-data/APACHE_RUN_\\1=podman/' /etc/apache2/envvars
RUN rm /etc/apache2/sites-enabled/000-default.conf
RUN ln -s /usr/bin/convert /usr/local/bin/convert
RUN echo Listen 8909 | tee -a /etc/apache2/ports.conf
RUN apt-get -y install phpmyadmin
RUN echo 'phpmyadmin phpmyadmin/dbconfig-install boolean false' | debconf-set-selections
RUN echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
RUN echo '#!/bin/bash' | tee -a /bin/startup
RUN echo 'mkdir -p /opt/formygames/src/vendor' | tee -a /bin/startup
RUN echo '/etc/init.d/mariadb start' | tee -a /bin/startup
RUN echo '/etc/init.d/apache2 start' | tee -a /bin/startup
RUN echo '/etc/init.d/rsyslog start' | tee -a /bin/startup
RUN echo '/etc/init.d/postfix start' | tee -a /bin/startup
RUN echo 'chmod 755 /bin/prepare' | tee -a /bin/startup
RUN chmod 755 /bin/startup
RUN ln -s /opt/formygames/src/prepare.sh /bin/prepare
EXPOSE 8909
CMD [ "/sbin/init" ]