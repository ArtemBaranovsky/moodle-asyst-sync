### How to wrap up and run Moodle Server:

Run these commands at CLI to use ASYST with universal BERT model based on German language.

~~~bash
docker-compose up -d --build
/bin/bash install_moodle.sh
~~~

Use these creds to access Moodle admin page
admin:rootpassword

Database connection URL: jdbc:mariadb://localhost:3306/moodle

It is suggested to use our moodle plugin to communicate with ASYST script using such a
route http://127.0.0.1:5000

Now the preinstalled MOODLE LMS is available at https://www.moodle.loc

**Note**: Bind https://www.moodle.loc to your localhost at **hosts** file depending on your OS.