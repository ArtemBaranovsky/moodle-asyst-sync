#!/bin/bash

# Import environment variables from .env
set -a
. .env
set +a

# Ensure correct ownership and permissions before installation
docker-compose exec moodle chown -R www-data:www-data ${MOODLE_BASE_DIR}
docker-compose exec moodle chmod -R 755 ${MOODLE_BASE_DIR}

# Ensure necessary directories in moodledata exist
docker-compose exec moodle mkdir -p ${MOODLE_BASE_DIR_DATA}
docker-compose exec moodle mkdir -p ${MOODLE_BASE_DIR_DATA}/localcache
docker-compose exec moodle mkdir -p ${MOODLE_BASE_DIR_DATA}/sessions
docker-compose exec moodle mkdir -p ${MOODLE_BASE_DIR_DATA}/temp
docker-compose exec moodle mkdir -p ${MOODLE_BASE_DIR_DATA}/trashdir
docker-compose exec moodle chown -R www-data:www-data ${MOODLE_BASE_DIR_DATA}
docker-compose exec moodle chmod -R 775 ${MOODLE_BASE_DIR_DATA}

# Install Moodle
sleep 5
docker-compose exec moodle php ${MOODLE_BASE_DIR}/admin/cli/install.php \
                               --wwwroot="${MOODLE_WWWROOT}" \
                               --phpunit_dataroot="${MOODLE_WWWROOT}" \
                               --dataroot="${MOODLE_BASE_DIR_DATA}" \
                               --dbtype="mariadb" \
                               --dbname="${MOODLE_DATABASE_NAME}" \
                               --dbuser="${MOODLE_DATABASE_USER}" \
                               --dbpass="${MOODLE_DATABASE_PASSWORD}" \
                               --dbhost="${MOODLE_DATABASE_HOST}" \
                               --adminpass="${MOODLE_DATABASE_ROOT_PASSWORD}" \
                               --fullname="${MOODLE_FULLNAME}" \
                               --shortname="${MOODLE_SHORTNAME}" \
                               --agree-license \
                               --non-interactive

# Check if database backup exists and restore it if it does
 BACKUP_FILE="moodle/moodle_backup.sql"
 if [ -f "$BACKUP_FILE" ]; then
 #    docker-compose exec mariadb apt-get update && apt-get install -y mysql-client && rm -rf /var/lib/apt/lists/*
     docker-compose exec mariadb bash -c "apt-get update && apt-get install -y mysql-client && rm -rf /var/lib/apt/lists/*"
     echo "Database backup found. Restoring..."
     docker-compose exec -T mariadb mysql -u ${MOODLE_DATABASE_USER} -p${MOODLE_DATABASE_PASSWORD} ${MOODLE_DATABASE_NAME} < moodle/moodle_backup.sql
     echo "Database restored from backup."
 else
     echo "No database backup found. Skipping restore."
 fi

# Locale setting inside the Docker container
docker-compose exec -u root moodle bash -c "apt-get update && apt-get install -y locales && \
    echo 'en_AU.UTF-8 UTF-8' >> /etc/locale.gen && \
    locale-gen && \
    update-locale"

# Composer installation to run phpunit tests
docker-compose exec moodle bash -c "
    php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\" &&
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer &&
    php -r \"unlink('composer-setup.php');\" &&
    cd /app &&
    /usr/local/bin/composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist &&
    /usr/local/bin/composer dump-autoload
"

# Next, configure PHPUnit for Moodle:
 docker-compose exec moodle php admin/tool/phpunit/cli/init.php

# Define the path to the phpunit.xml file
PHPUNIT_XML_PATH="/var/www/html/moodle/phpunit.xml"

# Define the content to insert for your plugin's test suite
TEST_SUITE_ENTRY='<testsuite name="Unit Tests">
    <directory>local/asystgrade/tests</directory>
</testsuite>'

# Check if the plugin suite is already defined in phpunit.xml
if grep -q "local/asystgrade/tests" "$PHPUNIT_XML_PATH"; then
    echo "Test suite for asystgrade plugin already exists in phpunit.xml."
else
    # Use sed to insert the test suite entry right before @plugin_suites_end@
    sed -i "/@plugin_suites_end@/i $TEST_SUITE_ENTRY" "$PHPUNIT_XML_PATH"
    echo "Test suite for asystgrade plugin added to phpunit.xml."
fi

 # Ensure correct ownership and permissions after installation
 docker-compose exec moodle chown -R www-data:www-data ${MOODLE_BASE_DIR}
 docker-compose exec moodle chmod -R 755 ${MOODLE_BASE_DIR}

 # Set correct access rules for the plugin
 docker-compose exec moodle chown -R www-data:www-data ${MOODLE_BASE_DIR}/local/asystgrade
 docker-compose exec moodle chmod -R 775 ${MOODLE_BASE_DIR}/local/asystgrade
 sudo chown -R $(whoami):$(whoami) ./asystgrade

# Create the run_sag script file
docker-compose exec flask bash -c 'cat <<EOF > /usr/local/bin/run_sag
#!/bin/bash
. /opt/myenv/bin/activate
#cd ${MOODLE_BASE_DIR}/asyst/Source/Skript/german
cd /app/asyst/Source/Skript/german
/opt/myenv/bin/python3 /app/api.py
EOF'

# Make the script executable & run it
docker-compose exec flask chmod +x /usr/local/bin/run_sag
docker-compose exec flask /usr/local/bin/run_sag

# Adding cron-record at the Moodle container
docker-compose exec -u root moodle bash -c "echo '* * * * * /usr/bin/php ${MOODLE_BASE_DIR}/admin/cli/cron.php >/dev/null 2>&1' >> /etc/crontabs/root && crontab /etc/crontabs/root"
