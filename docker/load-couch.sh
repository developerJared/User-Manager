#!/usr/bin/env bash

# never change to live database!!
couchurl="http://localhost:5984"
database="netlab_test"
adminurl="http://couchdb_user:couchdb_user@localhost:5984/netlab_test"

echo "Start loading local couchdb"

if curl --output /dev/null --silent --head --fail "$couchurl"; then
    echo "Couch is running on $couchurl"
else
    echo "Couch is not running on $couchurl"
    echo "Please start docker (docker-compose up -d) or check the configuration (docker-compose.yml)"
    exit;
fi 

if curl --output /dev/null --silent --head --fail "$adminurl"; then
    while true; do
        read -p "Do you wish to reset/clean the couchdb database first?" yn
        case $yn in
            [Yy]* ) curl -X DELETE "$adminurl"; break;;
            [Nn]* ) break;;
            * ) echo "Please answer yes or no.";;
        esac
    done
fi 

if curl --output /dev/null --silent --head --fail "$adminurl"; then
    echo "$database exists"
else
    echo "$database database does not exist. Create it now"
    curl -X PUT "$adminurl"; 
fi 

echo "Download database"
curl http://10.64.28.85:5984/netlab_template/_all_docs?include_docs=true > netlab_template.json

node transform.js

curl -X PUT http://couchdb_user:couchdb_user@127.0.0.1:5984/netlab_test
curl -d @netlab_template.json -H "Content-type: application/json" -X POST "$adminurl/_bulk_docs"
curl -d @data/local_lab.json -H "Content-type: application/json" -X POST "$adminurl"
curl -d @data/sample_kw_2067231.json -H "Content-type: application/json" -X POST "$adminurl"
curl -d @data/sample_ap_2067232.json -H "Content-type: application/json" -X POST "$adminurl"
curl -d @data/bulk_crop_ap.json -H "Content-type: application/json" -X POST "$adminurl/_bulk_docs"

rm -f netlab_template.json

echo "Database loaded"