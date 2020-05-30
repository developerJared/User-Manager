## How to build it with Docker:

#### Prerequiste

duplicate 
- .env.example to .env
- .config.json.example to .config.json

#### Setup

Execute below commands inside `docker` folder of that repository.

#### Build

```bash
docker-compose build
```

#### Run containers

```bash
docker-compose up -d
```

#### Load data into couch the first time

```bash
sh load-couch.sh
```

#### Install all dependencies using Composer:

```bash
docker-compose run app php composer.phar install
```

#### create laravel keys

```bash
docker-compose run app php artisan key:generate
```

#### Create database and run the seeds (data)

```bash
docker-compose run app php artisan migrate --seed
```

if you have an existing database and want to refresh everything

```bash
docker-compose run app php artisan migrate:reset
docker-compose run app php artisan migrate --seed
```


#### API running on

http://localhost:9000/app/users/1

#### PHPMyAdmin running on

http://localhost:8001
- use .env information to log in (hostname is name of container)

#### Couch DB

http://localhost:8000/
- couchdb_user / couchdb_user

### Fauxton

http://localhost:8000/#database/netlab_test/

### Recommended. View docker log

https://github.com/amir20/dozzle

------------------------------------------------------------

#### useful command

```bash
docker-compose run app php artisan config:clear
docker-compose run app php artisan cache:clear
``` 

#### [should not be needed] create db user

```bash  sourceCode: ../  
docker-compose exec mysql mysql -u root -proot
```
then
```sql
GRANT ALL PRIVILEGES ON tracking_api.* to tracking_api@'%' identified by 'tracking_api';
```
