FROM debian:trixie
ARG MOODLE_DATABASE_ROOT_PASSWORD
ARG MOODLE_DATABASE_NAME
ARG MOODLE_DATABASE_USER
ARG MOODLE_DATABASE_PASSWORD
ARG MOODLE_DATABASE_HOST
ARG MOODLE_BASE_DIR
ARG MOODLE_BASE_DIR_DATA

ENV MOODLE_DATABASE_ROOT_PASSWORD ${MOODLE_DATABASE_ROOT_PASSWORD}
ENV MOODLE_DATABASE_NAME ${MOODLE_DATABASE_NAME}
ENV MOODLE_DATABASE_USER ${MOODLE_DATABASE_USER}
ENV MOODLE_DATABASE_PASSWORD ${MOODLE_DATABASE_PASSWORD}
ENV MOODLE_DATABASE_HOST ${MOODLE_DATABASE_HOST}
ENV MOODLE_BASE_DIR ${MOODLE_BASE_DIR}
ENV MOODLE_BASE_DIR_DATA ${MOODLE_BASE_DIR_DATA}

# Installing necessary packages
RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y apache2 php libapache2-mod-php php-mysqli php-mysql php-xml php-pdo php-pdo-mysql mariadb-client mariadb-server wget unzip python3 python3-pip iputils-ping curl php-mbstring graphviz aspell ghostscript clamav php8.2-pspell php8.2-curl php8.2-gd php8.2-intl php8.2-mysql php8.2-xml php8.2-xmlrpc php8.2-ldap php8.2-zip php8.2-soap php8.2-mbstring && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Setting necessary php params
RUN echo "mysql.default_socket=/run/mysqld/mysqld.sock" >> /etc/php/8.2/cli/php.ini
RUN echo "max_input_vars = 5000" >> /etc/php/8.2/cli/php.ini

# Starting Apache server
RUN systemctl start apache2

# Installing Moodle
RUN mkdir -p ${MOODLE_BASE_DIR} && \
    wget -qO-  https://packaging.moodle.org/stable403/moodle-4.3.4.tgz | tar xz -C ${MOODLE_BASE_DIR} --strip-components=1 \

# Setting correct acces rules
RUN chown -R www-data:www-data ${MOODLE_BASE_DIR} && \
    chmod -R 755 ${MOODLE_BASE_DIR} && \
    mkdir -p ${MOODLE_BASE_DIR_DATA} && \
    chown -R www-data:www-data ${MOODLE_BASE_DIR_DATA} && \
    chmod -R 755 ${MOODLE_BASE_DIR_DATA}

# Copying of beeing developed Plugin
COPY yourplugin ${MOODLE_BASE_DIR}/local/yourplugin

# Setting correct acces rules for Plugin
RUN chown -R www-data:www-data ${MOODLE_BASE_DIR}/local/yourplugin && \
    chmod -R 755 ${MOODLE_BASE_DIR}/local/yourplugin

# Making Symlink for MariaDB Socket
RUN ln -s /run/mysqld/mysqld.sock /tmp/mysql.sock

#RUN php ${MOODLE_BASE_DIR}/admin/cli/install_database.php  --adminpass=${MOODLE_DATABASE_ROOT_PASSWORD} --agree-license

#  Apache Settings
RUN echo "<VirtualHost *:80>\n" \
        "ServerName localhost\n" \
        "ServerAlias www.moodle.loc\n" \
        "DocumentRoot /var/www/html/moodle\n" \
        "<Directory /var/www/html/moodle>\n" \
        "  Options +FollowSymlinks\n" \
        "  AllowOverride All\n" \
        "  Require all granted\n" \
        "</Directory>\n" \
        "ErrorLog \${APACHE_LOG_DIR}/error.log\n" \
        "CustomLog \${APACHE_LOG_DIR}/access.log combined\n" \
        "</VirtualHost>\n" \
    > /etc/apache2/sites-enabled/000-default.conf

# Setting Apache
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Installing Moodle non interactive
#RUN sleep 4 && php ${MOODLE_BASE_DIR}/admin/cli/install.php --wwwroot=http://0.0.0.0 --dataroot=${MOODLE_BASE_DIR_DATA} --dbtype=mariadb --dbname=${MOODLE_DATABASE_NAME}  --dbuser=${MOODLE_DATABASE_USER} --dbpass=${MOODLE_DATABASE_PASSWORD} --dbhost=${MOODLE_DATABASE_HOST} --adminpass=${MOODLE_DATABASE_ROOT_PASSWORD} --fullname="Moodle Site" --shortname="Moodle" --agree-license --non-interactive
RUN chmod -R 755 /var/www/html/moodle
RUN chown -R www-data:www-data /var/www/html/moodle

#Opening ports
EXPOSE 80 443

# Команда запуска Apache
CMD ["apachectl", "-D", "FOREGROUND"]
