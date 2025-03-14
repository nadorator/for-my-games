#!/bin/bash
cd /opt/formygames/src
cat <<EOF | mysql
ALTER USER 'root'@'localhost' IDENTIFIED BY '$(grep MYSQL_ROOT_PASSWORD .env | cut -d'=' -f2)';
FLUSH PRIVILEGES;
EOF
add-apt-repository ppa:phpmyadmin/ppa
apt-get -y install phpmyadmin
mkdir /opt/phpmyadmin
mkdir /opt/phpmyadmin/install
wget -P /opt/phpmyadmin https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz
wget -P /opt/phpmyadmin https://files.phpmyadmin.net/phpmyadmin.keyring
gpg --import /opt/phpmyadmin/phpmyadmin.keyring
wget -P /opt/phpmyadmin https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.tar.gz.asc
gpg --verify /opt/phpmyadmin/phpMyAdmin-latest-all-languages.tar.gz.asc
tar xvf /opt/phpmyadmin/phpMyAdmin-latest-all-languages.tar.gz --strip-components=1 -C /opt/phpmyadmin/install
cp -R /opt/phpmyadmin/install/vendor /usr/share/phpmyadmin/vendor
cp /usr/share/phpmyadmin/config.sample.inc.php /usr/share/phpmyadmin/config.inc.php
echo 'phpmyadmin phpmyadmin/dbconfig-install boolean false' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
/etc/init.d/apache2 reload