@echo off

docker-compose up -d

docker exec prestashop mkdir /tmp/project-prestashop

docker cp ./prestashop/html prestashop:/tmp/project-prestashop



docker exec prestashop sh -c "cp -r /tmp/project-prestashop/html/* /var/www/html/"

docker exec prestashop rm -rf /tmp/project-prestashop


docker exec prestashop echo "html content:"

docker exec prestashop ls /var/www/html

pause

