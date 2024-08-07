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
 BACKUP_FILE="moodle_backup.sql"
 if [ -f "$BACKUP_FILE" ]; then
 #    docker-compose exec mariadb apt-get update && apt-get install -y mysql-client && rm -rf /var/lib/apt/lists/*
     docker-compose exec mariadb bash -c "apt-get update && apt-get install -y mysql-client && rm -rf /var/lib/apt/lists/*"
     echo "Database backup found. Restoring..."
     docker-compose exec -T mariadb mysql -u ${MOODLE_DATABASE_USER} -p${MOODLE_DATABASE_PASSWORD} ${MOODLE_DATABASE_NAME} < moodle_backup.sql
     echo "Database restored from backup."
 else
     echo "No database backup found. Skipping restore."
 fi


 # Ensure correct ownership and permissions after installation
 docker-compose exec moodle chown -R www-data:www-data ${MOODLE_BASE_DIR}
 docker-compose exec moodle chmod -R 755 ${MOODLE_BASE_DIR}

 # Set correct access rules for the plugin
 docker-compose exec moodle chown -R www-data:www-data ${MOODLE_BASE_DIR}/local/asystgrade
 docker-compose exec moodle chmod -R 775 ${MOODLE_BASE_DIR}/local/asystgrade
 sudo chown -R $(whoami):$(whoami) ./asystgrade

# Create the run_sag script file
docker-compose exec moodle bash -c 'cat <<EOF > /usr/local/bin/run_sag
#!/bin/bash
. /opt/myenv/bin/activate
cd ${MOODLE_BASE_DIR}/asyst/Source/Skript/german
/opt/myenv/bin/python3 ${MOODLE_BASE_DIR}/api.py
EOF'


# Make the script executable & run it
docker-compose exec moodle chmod +x /usr/local/bin/run_sag
docker-compose exec moodle /usr/local/bin/run_sag
