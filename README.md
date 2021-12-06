# Starter kit

# Content table

- [Services](#services)
    - [app](#app)
    - [horizon](#horizon)
    - [laravel-websockets](#websockets)
    - [prometheus](#prometheus)
    - [grafana](#grafana)
    - [redis exporter](#redis_exporter)
    - [MySQL exporter](#mysql_exporter)
    - [Nginx](#web)
    - [redis](#cache)
    - [Database](#db)
    - [adminer](#adminer)
- [basic commands](#basic-commands)
    - [some basic docker commands](#some-useful-commands-for-working-with-docker)
    - [some useful artisan commands](#artisan-commands-may-you-need)
- [starting up](#starting-up)
    - [server profile](#server-profile)
    - [develop profile](#develop-profile)
    - [front-end profile](#front-end-profile)
- [Google Services](#google-services)
- [Mailtrap](#mailtrap)
- [Helpers](#helpers)
- [Packages](#packages)
    - [sanctum](#sanctum)
    - [laravel permissions](#laravel_permissions)
    - [laravel debugbar](#laravel_debugbar)
    - [laravel ide helper](#laravel_ide_helper)
    - [laravel activitylog](#laravel_activitylog)

## Services

first of all, we're going to review docker services and introduce each one.

#### app

first service is `app` that contain the laravel project.

- _environment_ : has some environment variables to configure database and app environment
- _volumes_ : binding the `./core` to the `/var/www/starter` folder. this able us to edit the project files in app
  container. in another binding we add Xdebug configuration to the `/usr/local/etc/php/conf.d`
  folder that Xdebug searching for its configuration file there and in the end saving the whole php configuration in a
  docker volume.
- _ports_ : we export the port `9005` to access the xdebug from IDE.

#### horizon

in this service we run the [horizon](https://laravel.com/docs/7.x/horizon) package which installed in laravel app.
horizon is a queue manager that provides a dashboard to easily monitor the metrics. horizon dashboard path
is [/horozon](localhost:8080/horizon)

- _command_ : horizon start working with `php /var/www/starter/artisan horizon` command
- _restart_ : every time the horizon failed `on-failure` option make the container restart.
- _volumes_ : for accessing the artisan file and laravel app it's necessary.

#### websockets

[laravel-websockets](https://beyondco.de/docs/laravel-websockets/getting-started/introduction) package is a socket
server written in php that running on this service.

- _command_ : socket server stating with `php /var/www/starter/artisan websockets:serve`
  command.
- _ports_ : laravel-websockets working on port `6001`.

the rest of configs is similar to horizon service.

#### prometheus

[Prometheus](https://github.com/prometheus/prometheus) is an open-source systems monitoring and alerting toolkit.

- _ports_ : prometheus running on port `9090`.
- _command_ : `--web.enable-lifecycle --config.file=/etc/prometheus/prometheus.yml` command start prometheus with custom
  config file.
- _volumes_ : first, we bind `./prometheus/conf.d/prometheus.yml` to the `/etc/prometheus/prometheus.yml` file into the
  container which is the config file that prometheus loaded in start up time. new exporters defined there. second, we
  store prometheus configuration files and data in `prometheus-data` volume.

#### grafana

[grafana](https://github.com/grafana/grafana) is an open-source system that virtualize data. prometheus provides the
data.

- _ports_ : grafana accessible from port `3000`

#### redis_exporter

[redis_exporter](https://github.com/oliver006/redis_exporter) is a prometheus exporter for redis metrics.

- _ports_ : this exports redis metrics data on port `9121`

#### mysql_exporter

[mysql_exporter](https://github.com/prometheus/mysqld_exporter) is another prometheus exporter that collect mysql server
metrics.

- _ports_ : mysql exporter export collected data on port `9104`
- _environment_ : we define a database connection using `DATA_SOURCE_NAME` environment variable to used by mysql
  exporter.

#### web

[Nginx](https://nginx.org/en/) is a web server that we used in this project.

- _ports_ : binding the port `80` that is default port of `app` service to the port `8080`.
- _volumes_ : first, binding `./nginx/conf.d` folder that contain our defined server to the `/etc/nginx/conf.d`
  folder which Nginx scans for servers. second, we need to bind destination folder of our servers in Nginx service to be
  accessible from it.

#### cache

[Redis](https://redis.io/) is an im-memory key-value database that used as cache server in this project.

- _command_ : redis server in running up using `redis-server --appendonly yes --requirepass ${REDIS_PASS}` and we can
  pass the password if we set a password.
- _environment_ : define a password for redis server using `REDIS_PASS` variable.
- _volumes_ : store the redis data in `cache` volume.

#### db

[MariaDB](https://github.com/MariaDB) is a community-developed fork of MySQL which used in this project.

- _environment_ : initialize the database. create a user with password and also set a password for root user.
- _volumes_ : we bind a file in `./database/config.d/setup.sql` folder to the `/docker-entrypoint-initdb.d/setup.sql`
  path in the container. this file executed every time the mysql server initialize. we store db service data in a volume
  called `database`.

#### adminer

[Adminer](https://www.adminer.org/) is a tool for managing content in MySQL databases.

- _ports_ : bind the adminer default port `8080` to the `8082`. we can access Adminer panel
  from [localhost:8082](http://localhost:8082)

## basic commands

we're going to introduce some basic commands that are useful for developing the project.

#### some useful commands for working with docker

- `docker ps` : get the running containers list
- `docker logs CONTAINER` : fetch the logs of a container
- `docker inspect CONTAINER` : return low-level information of a docker container
- `docker inspect CONTAINER | grep --word-regexp IPAddress` : return just IP Address of the container
- `docker-compose up -d` : builds, create or recreate, starts, attaches to containers for a service.
- `docker-compose down` : stops and removes containers, networks, volumes and images created by `docker-compose up`
  command
- `docker-compose exec SERVICE bash` : you can open a terminal inside of the _SERVICE_
- `docker-compose restart` : restarts all stopped and running services.

#### artisan commands may you need

- `jsonapi:all` : generate all files needed to settings up a schema
- `optimize:clear` : clear all cached data such as views, routs ...
- `migrate:fresh` : drop all database tables and re-run all migrations
- `websockets:restart` : restart the websockets
- `horizon:status` : get the current status of horizon
- `horizon:purge` : terminate any rogue horizon processes
- `tinker` : interact with the project from CLI

## starting up

there's several predefined profile for using is the different situation.

##### _SERVER_ profile

used on production server.

instructions:

- `docker-compose up -d --build` : starts the services (_--build_ is just for the first time).
- `docker-compose exec app bash` : open a terminal inside of `app` service.
- `chown -R www-data:www-data bootstrap/cache storage resources/lang` : change the owner of folders and files to the
  docker's default user.
- `cp .env.server .env` : create a copy from `.env.server` file and named the new file `.env`
- `composer update --no-dev --optimize-autoloader` : install necessary composer dependencies and optimize autoloader
- `php artisan key:generate` : generate a new unique key for laravel that used for encode and decode seasons, cookies
  etc.
- `php artisan migrate:fresh --seed --force` : create tables and seeds the database
- `php artisan storage:link` : create a symbolic link between the `public` and `storage/app/public` folder.
- `npm i` : install npm dependencies.
- `npm run prod` : builds and compresses `.scss` and `.js` files and place the built files into the public directory.
- `exit` : exit from the container bash.

##### _DEVELOP_ profile

this profile recommended for developing. using this profile developer can access all features and tool-kits.

instructions :

- `docker-compose up -d --build` : starts the services (_--build_ is just for the first time).
- `docker-compose exec app bash` : open a terminal inside of `app` service.
- `chown -R www-data:www-data bootstrap/cache storage resources/lang` : change the owner of folders and files to the
  docker's default user.
- `cp .env.develop .env` : create a copy from `.env.develop` file and named the new file `.env`
- `composer update` : install all composer dependencies
- `php artisan key:generate` : generate a new unique key for laravel that used for encode and decode seasons, cookies
  etc.
- `php artisan migrate:fresh --seed ` : create tables and seeds the database
- `php artisan storage:link` : create a symbolic link between the `public` and `storage/app/public` folder.
- `npm i` : install npm dependencies
- `npm run dev` : builds `.scss` and `.js` files and place the built files into the public directory in `css` and `js`
  folder
- `exit` : exit from the container bash.
- open [localhost:8080](http://localhost:8080/)

##### _FRONT-END_ profile

Local profile used for front-end developer. in this profile developer don't have to deal with Docker and just can reach
the UI and main features.

requirements :

- redis : runs on port `6379`
- web server : xampp or wampp
- composer
- npm

instructions :

- run your web server.
- create a database named `starter`.
- `cd core/` : change the current directory of terminal to the `./core` folder.
- `cp .env.frontend .env` : create a copy of `.env.frontend` and named it `.env`.
- `composer update --ignore-platform-reqs` : install the composer dependencies.
- `php artisan key:generate` : generate a unique key for laravel.
- `php artisan migrate:fresh --seed ` : create tables and seeds the database.
- `php artisan storage:link` : create a symbolic link between the `public` and `storage/app/public` folder.
- `npm i` : install npm dependencies from package.json file
- `npm run watch` : builds `.scss` and `.js` files in resource directory and watch them for new changes.
- `php artisan horizon` : starts the horizon (_make sure keep this command running_).
- `php artisan websockets:serve` : starts the websockets (_make sure keep this command running_).
- `php artisan serve` : serves the laravel app (_make sure keep this command running_).
- open [localhost:8000](http://localhost:8000/)

## google services

for managing `Google Analytics` and `Google Tag Manager` for this project you can use this gmail :

- email address: `example@gmail.com`
- password: `password`

and there are direct links to :

- [Google Analytics](https://analytics.google.com/analytics/web/)
- [Google Tag Manager](https://tagmanager.google.com/#/home)

## mailtrap

[mailtrap](https://mailtrap.io/) is a Fake SMTP server for email testing from the development & staging environments
without spamming real customers.

mailtrap config is placed in `.env` file in root project directory

``` @php
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=starter@support.com
MAIL_FROM_NAME="${APP_NAME}"
```

to configuring up the mailtrap, you just need to register and replace these values

```@php
MAIL_USERNAME=
MAIL_PASSWORD=
```

with yours.

## helpers

helpers created for defining global constants, functions and arrays.

## packages

we are going to introduce the packages used in starter-kit.

### sanctum

Laravel [Sanctum](https://laravel.com/docs/8.x/sanctum) provides a featherweight authentication system for SPAs, mobile
applications, and simple, token based APIs.

### laravel permissions

[laravel permissions](https://spatie.be/docs/laravel-permission/v3/introduction) allows you to manage user permissions
and roles in a database.

### laravel debugbar

This is a [package](https://github.com/barryvdh/laravel-debugbar) to integrate PHP Debug Bar with Laravel.

### laravel ide helper

This [package](https://github.com/barryvdh/laravel-ide-helper) generates helper files that enable your IDE to provide
accurate autocompletion. Generation is done based on the files in your project, so they are always up-to-date.

ide helper configured in `post-update-cmd` hook that mean every time that you run composer commands, ide helper
generates helpers files based on your project files, also you can do this manually with artisan ide helper commands.

### laravel activitylog

The [spatie/laravel-activitylog package](https://spatie.be/docs/laravel-activitylog/v4/introduction) provides easy to
use functions to log the activities of the users of your app. It can also automatically log model events. All activity
will be stored in the activity_log table.

there is example snippet code to record an activity

```@php
activity()
   ->performedOn($anEloquentModel)
   ->causedBy($user)
   ->withProperties(['customProperty' => 'customValue'])
   ->log('Look, I logged something');
```

to record model event activity, just add `Spatie\Activitylog\Traits\LogsActivity` trait to you model. this trait force
you to implement a method. there is a simple implementation

```@php
public function getActivitylogOptions(): LogOptions {
    return LogOptions::defaults()->logFillable();
}
```

this means, log fillable attributes only.
