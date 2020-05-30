1. get the data from ESB
(use postman)

curl -X GET \
  'Sample Number URL' \
  -H 'Accept: */*' \
  -H 'Cache-Control: no-cache' \
  -H 'Connection: keep-alive' \
  -H 'Host: Host' \
  -H 'Postman-Token: Token' \
  -H 'User-Agent: PostmanRuntime/7.15.0' \
  -H 'Season: 2019' \
  -H 'accept-encoding: gzip, deflate' \
  -H 'api.response_schema: Schema' \
  -H 'cache-control: no-cache'


  2. transform data by adding _id and use the sample key as body and save into data/sample_kw_2067231.json
  
  3. import in couch

  curl -d @data/sample_kw_2067231.json -H "Content-type: application/json" -X POST http://couchdb_user:couchdb_user@127.0.0.1:5984/netlab_test
