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
    apt-get install -y apache2 php libapache2-mod-php php-mysqli php-mysql php-xml php-pdo php-pdo-mysql mariadb-client mariadb-server wget unzip p7zip-full python3 python3-pip iputils-ping php-mbstring graphviz aspell ghostscript clamav php8.2-pspell php8.2-curl php8.2-gd php8.2-intl php8.2-mysql php8.2-xml php8.2-xmlrpc php8.2-ldap php8.2-zip php8.2-soap php8.2-mbstring openssl git nano supervisor php-xdebug ca-certificates && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Setting necessary php params
#RUN echo "mysql.default_socket=/run/mysqld/mysqld.sock" >> /etc/php/8.2/cli/php.ini
RUN echo "mysql.default_socket=/run/mysqld/mysqld.sock" >> /etc/php/8.2/apache2/php.ini
RUN echo "max_input_vars = 5000" >> /etc/php/8.2/apache2/php.ini
RUN echo "max_input_vars = 5000" >> /etc/php/8.2/cli/php.ini

# Setting Xdebug
#RUN echo "zend_extension=xdebug.so" >> /etc/php/8.2/apache2/php.ini
#RUN echo "xdebug.mode=debug" >> /etc/php/8.2/apache2/php.ini
#RUN echo "xdebug.start_with_request=yes" >> /etc/php/8.2/apache2/php.ini
#RUN echo "xdebug.client_host=host.docker.internal" >> /etc/php/8.2/apache2/php.ini
#RUN echo "xdebug.client_port=9003" >> /etc/php/8.2/apache2/php.ini

# Starting Apache server
RUN chmod 1777 /tmp

# Add necessary packages for adding Python 3 repository and installing curl
RUN apt-get update && apt-get install -y curl software-properties-common gnupg2 dirmngr --no-install-recommends

# Add the repository
RUN add-apt-repository 'deb https://deb.debian.org/debian/dists/trixie/ trixie main'

# Update package lists after adding the repository
RUN apt-get update

# Installing necessary Python dependencies & Flask
RUN apt-get install -y python3-venv python3-pip python3-matplotlib
RUN python3 -m venv /opt/myenv
RUN /opt/myenv/bin/python3 -m pip install --upgrade pip
#RUN /opt/myenv/bin/python3 -m pip install matplotlib Flask torch sklearn-learn
COPY requirements.txt /opt/myenv/

WORKDIR /var/www/html/moodle
RUN /opt/myenv/bin/python3 -m pip install -r /opt/myenv/requirements.txt
RUN /opt/myenv/bin/python3 -m pip install --upgrade setuptools wheel

# Copy the api.py script into the container
COPY api.py /var/www/html/moodle/api.py
COPY install_moodle.sh /var/www/html/moodle/install_moodle.sh
RUN chmod +x /var/www/html/moodle/install_moodle.sh

RUN mkdir -p /asyst
# Download and extract the ASYST archive
#RUN curl -o asyst.zip -L https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST/-/archive/main/ASYST-main.zip && \
#    7z x asyst.zip -o/asyst && \
#    mv /asyst/ASYST-main/* /asyst && \
#    rm -rf /asyst/ASYST-main asyst.zip

# Set permissions (if necessary)
RUN chown -R www-data:www-data /asyst
RUN chmod -R 755 /asyst

COPY ./asyst /var/www/html/moodle/asyst
RUN ln -s /var/www/html/moodle/asyst/Source/Skript /var/www/html/moodle/Skript

# Installing Moodle Setting correct acces rules
RUN mkdir -p ${MOODLE_BASE_DIR} && \
    wget -qO-  https://packaging.moodle.org/stable403/moodle-4.3.4.tgz | tar xz -C ${MOODLE_BASE_DIR} --strip-components=1

RUN chown -R www-data:www-data ${MOODLE_BASE_DIR} && \
    find ${MOODLE_BASE_DIR} -type d -exec chmod 755 {} \; && \
    find ${MOODLE_BASE_DIR} -type f -exec chmod 644 {} \; && \
    mkdir -p ${MOODLE_BASE_DIR_DATA} && \
    chown -R www-data:www-data ${MOODLE_BASE_DIR_DATA} && \
    chmod -R 755 ${MOODLE_BASE_DIR_DATA}

# Copying of beeing developed Plugin
COPY asystgrade ${MOODLE_BASE_DIR}/local/asystgrade

# Setting correct acces rules for Plugin
#RUN chown -R www-data:www-data ${MOODLE_BASE_DIR}/local/asystgrade && \
RUN chmod -R 755 ${MOODLE_BASE_DIR}/local/asystgrade

# Making Symlink for MariaDB Socket
RUN ln -s /run/mysqld/mysqld.sock /tmp/mysql.sock

RUN echo "ServerName modhost" >> /etc/apache2/apache2.conf

# Apache Settings for HTTP
RUN echo  \
    "<VirtualHost *:80>\n" \
        "ServerName www.moodle.loc\n" \
        "ServerAlias www.moodle.loc\n" \
        "DocumentRoot ${MOODLE_BASE_DIR}\n" \
        "<Directory ${MOODLE_BASE_DIR}>\n" \
        "  Options +FollowSymlinks\n" \
        "  AllowOverride All\n" \
        "  Require all granted\n" \
        "</Directory>\n" \
    "</VirtualHost>\n" \
        "ErrorLog /var/log/apache2/error.log\n" \
        "CustomLog /var/log/apache2/access.log combined\n" \
#        "RewriteEngine On\n" \
#        "RewriteCond %{HTTPS} !=on\n" \
#        "RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R,L]\n" \
    > /etc/apache2/sites-enabled/000-default.conf

# Setting Apache
RUN a2enmod rewrite

# Generate a self-signed SSL certificate (replace example.com with your domain)
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/apache-selfsigned.key \
    -out /etc/ssl/certs/apache-selfsigned.crt \
    -subj "/C=EU/ST=Berlin/L=Berlin/O=HFT/CN=www.moodle.loc"

# Установка корневых сертификатов
RUN update-ca-certificates

RUN curl -O https://curl.se/ca/cacert.pem \
    && mv cacert.pem /etc/ssl/certs/cacert.pem \

# Настройка php.ini для OpenSSL
RUN echo "openssl.cafile=/etc/ssl/certs/ca-certificates.crt" >> /etc/php/8.2/cli/php.ini \
    && echo "openssl.capath=/etc/ssl/certs" >> /etc/php/8.2/cli/php.ini \
    && echo "openssl.cafile=/etc/ssl/certs/ca-certificates.crt" >> /etc/php/8.2/apache2/php.ini \
    && echo "openssl.capath=/etc/ssl/certs" >> /etc/php/8.2/apache2/php.ini \

# Configure SSL virtual host
RUN echo  \
    "<VirtualHost *:443>\n" \
        "ServerName www.moodle.loc\n" \
        "ServerAlias www.moodle.loc\n" \
        "DocumentRoot ${MOODLE_BASE_DIR}\n" \
        "<Directory ${MOODLE_BASE_DIR}>\n" \
        "  Options +FollowSymlinks\n" \
        "  AllowOverride All\n" \
        "  Require all granted\n" \
        "</Directory>\n" \
        "SSLEngine on\n" \
        "SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt\n" \
        "SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key\n" \
        "ErrorLog /var/log/apache2/error.log\n" \
        "CustomLog /var/log/apache2/access.log combined\n" \
    "</VirtualHost>\n" \
    > /etc/apache2/sites-enabled/default-ssl.conf

# Opening 000-default.conf for edit
RUN sed -i '/<\/VirtualHost>/i \
RewriteEngine On \n\
RewriteCond %{SERVER_PORT} 80 \n\
RewriteRule ^(.*)$ https://www.moodle.loc/$1 [R,L] \n\
' /etc/apache2/sites-enabled/000-default.conf

# Enable SSL module in Apache
RUN a2enmod ssl

# Adding entry to /etc/hosts
#RUN echo "127.0.0.1 ${MOODLE_WWWROOT##https://}" >> /etc/hosts

#Opening ports
EXPOSE 80 443 5000

# Setting up supervisord
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Running supervisord
CMD ["/usr/bin/supervisord"]
