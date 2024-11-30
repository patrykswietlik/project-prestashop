@echo off

docker-compose up -d

docker cp prestashop:/var/www/html ../prestashop

pause

