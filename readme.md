
How to run Moodle Server:

~~~bash
docker-compose exec moodle php /var/www/html/moodle/admin/cli/install.php --wwwroot=http://0.0.0.0 --dataroot=/var/www/html/moodledata --dbtype=mariadb --dbname=moodle  --dbuser=moodleuser --dbpass=moodlepassword --dbhost=mariadb --adminpass=rootpassword --fullname="Moodle Site" --shortname="Moodle" --agree-license  --non-interactive
docker-compose exec moodle chown -R www-data:www-data /var/www/html/moodle
docker-compose exec moodle chmod -R 755 /var/www/html/moodle
~~~

