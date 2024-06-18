#!/bin/bash

# Import environment variables from .env
set -a
. .env
set +a

# Install Moodle
docker-compose exec moodle php ${MOODLE_BASE_DIR}/admin/cli/install.php \
                               --wwwroot="${MOODLE_WWWROOT}" \
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

#docker-compose exec moodle chown -R www-data:www-data /var/www/html/moodle
docker-compose exec moodle chmod -R 755 /var/www/html/moodle

# Set correct access rules for the plugin
#docker-compose exec moodle chown -R www-data:www-data /var/www/html/moodle/mod/yourplugin
#docker-compose exec moodle chmod -R 775 /var/www/html/moodle/mod/yourplugin

# Create the run_sag script file
docker-compose exec moodle bash -c 'echo "#!/bin/bash" > /usr/local/bin/run_sag'
docker-compose exec moodle bash -c 'echo ". /opt/myenv/bin/activate" >> /usr/local/bin/run_sag'
docker-compose exec moodle bash -c 'echo "cd /var/www/html/moodle/asyst/Source/Skript/german" >> /usr/local/bin/run_sag'
docker-compose exec moodle bash -c 'echo "/opt/myenv/bin/python3 /var/www/html/moodle/asyst/Source/Skript/german/run_LR_SBERT.py" >> /usr/local/bin/run_sag'

# Make the script executable & run it
docker-compose exec moodle chmod +x /usr/local/bin/run_sag
docker-compose exec moodle /usr/local/bin/run_sag
