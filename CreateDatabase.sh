#!/bin/bash
sudo apt-get --force-yes --yes install jq
sudo composer install
echo "initializing..."
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LAB="$(jq -r '.Configuration.Lab' $DIR'/CONFIG.json')"
PACKHOUSE="$(jq -r '.Configuration.Packhouse' $DIR'/CONFIG.json')"

echo "LAB: "$LAB
echo "Packhouse: "$PACKHOUSE
DATABASE_NAME='netlab_'${PACKHOUSE,,}'_'${LAB,,}
echo "Creating database and tables..."
mysql --user="root" --password="password" --execute="create database if not exists $DATABASE_NAME;"
echo "DatabaseName:  "$DATABASE_NAME
echo $DIR

sudo sed -i "s/^DB_DATABASE.*$/DB_DATABASE=${DATABASE_NAME}/g" $DIR/.env

sudo php artisan migrate
sudo php artisan db:seed

mysql --user="root" --password="password" $DATABASE_NAME < containerData.sql
#sudo rm CreateDatabase.sh -f