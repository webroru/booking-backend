# booking-backend #

## Installation ##

### Docker ###
1. Install [Docker](https://docs.docker.com/engine/installation/linux/ubuntu/)
2. Install [Docker Compose](https://docs.docker.com/compose/install/)
3. Rename `docker-compose.yml.example` to `docker-compose.yml`
4. Run the project: `docker-compose up -d`
5. Install dependencies: `docker-compose run php composer install`
6. Run migrations `docker-compose exec php bin/console doctrine:migrations:migrate -n`

## Configuration ##
1. `cp .env .env.local`
2. Overwrite options in the .env.local
3. Create `public/uploads` directory, set owner to www-data

## Tests ##
Run all tests `docker-compose run composer.phar test`
