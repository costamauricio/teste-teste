#!/bin/bash

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install

cd server
php socket.php
