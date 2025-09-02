#!/bin/bash
set -e
exec > >(tee -a /tmp/deploy.log) 2>&1
echo "Deploy started at $(date)"

cd /home/ubuntu/GovSpendingContracts
sudo chown -R ubuntu:ubuntu /home/ubuntu/GovSpendingContracts
git pull origin master
echo "Git Pull done"

composer install --no-dev --optimize-autoloader --no-scripts
echo "Composer done"

# Just fix permissions, skip the caching bullshit for now
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite
sudo chown www-data:www-data database/
sudo chmod 775 database/
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
echo "Deploy completed successfully at $(date)"
