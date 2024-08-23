### How to wrap up and run Moodle Server:

To use ASYST with universal BERT model based on German language just Run these commands at CLI.

~~~bash
docker-compose up -d --build && ./install_moodle.sh
~~~

Infrastructure rolls up a Brand New Moodle instance. If you already have Moodle LMS, you could use it's DB backup at this project. Just paste it at root folder and rename it to moodle_backup.sql 

Use these creds to access Moodle admin page
admin:rootpassword
These creds could be easily changed as other environmental variables at .env

## Development tips
To facilitate DB monitoring at IDE set such a Database connection URL: 
~~~bash
jdbc:mariadb://localhost:3306/moodle
~~~

It is suggested to use our moodle plugin to communicate with Flask-based ASYST script using such a
route http://127.0.0.1:5000/api/autograde

Now the preinstalled MOODLE LMS is available at https://www.moodle.loc

**Note**: Bind https://www.moodle.loc to your localhost at **hosts** file depending on your OS.

## Running Unit Tests
To run only Plugin's Test please run at project's CLI:
~~~bash
vendor/bin/phpunit --testsuite local_asystgrade_testsuite
~~~

