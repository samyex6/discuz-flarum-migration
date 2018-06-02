./generate-formatter.php
php migrate-users.php
php migrate-forums.php

sudo chmod -R 777 ../flarum/assets/files/ < ./password
php migrate-posts.php
