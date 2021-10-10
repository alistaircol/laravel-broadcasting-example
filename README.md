# Terrible example of Laravel Broadcasting

**TL;DR**:

* [`app/Events/ExampleEvent.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/app/Events/ExampleEvent.php#L25) a very simple event with broadcasting enabled
* [`routes/console.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/routes/console.php#L28) for dispatching a broadcast event with `php artisan broadcast`
* [`resources/views/welcome.blade.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/resources/views/welcome.blade.php#L18) for alpine component listening for Echo events and updating state

---

I wanted to learn Laravel broadcasting.

Here I have documented setting up new project and MVP to have server-side event handler on front-end. Some things may be missing, but hopefully nothing too major.

## Fresh Project with Docker

```bash
docker run \
  --rm \
  --tty \
  --interactive \
  --user=$(id -u) \
  --volume="$(pwd):/app" \
  --volume="${COMPOSER_HOME:-$HOME/.composer}:/tmp" \
  composer:2.0 create-project laravel/laravel src

cd src

docker run \
  --rm \
  --tty \
  --interactive \
  --user=$(id -u) \
  --volume="$(pwd):/app" \
  --volume="${COMPOSER_HOME:-$HOME/.composer}:/tmp" \
  composer:2.0 \
  --with-all-dependencies \
  --ignore-platform-reqs \
  require \
  livewire/livewire \
  laravel/slack-notification-channel \
  beyondcode/laravel-websockets
  
docker run \
  --rm \
  --tty \
  --interactive \
  --user=$(id -u) \
  --volume="$(pwd):/app" \
  --volume="${COMPOSER_HOME:-$HOME/.composer}:/tmp" \
  composer:2.0 \
  --ignore-platform-reqs \
  require \
  --dev \
  barryvdh/laravel-ide-helper \
  laravel/sail 
```

Add to `composer.json:scripts`:

```json
"ide-helper": [
    "@php artisan ide-helper:meta",
    "@php artisan ide-helper:generate --helpers",
    "@php artisan ide-helper:eloquent",
    "@php artisan ide-helper:models --nowrite"
]
```

Options for sail:

```text
  [0] mysql
  [1] pgsql
  [2] mariadb
  [3] redis
  [4] memcached
  [5] meilisearch
  [6] minio
  [7] mailhog
  [8] selenium
```

Install sail:

```bash
docker run --rm \
  -it \
  -u $(id -u) \
  -v "$(pwd):/app" \
  -w /app \
  php:7.4-cli \
  php artisan sail:install --with=redis
```

## Database

Not really needed for this, but configuring for sqlite is here:

```bash
touch database/database.sqlite
```

Update `.env`:

```diff
-DB_CONNECTION=mysql
+DB_CONNECTION=sqlite
-DB_DATABASE=laravel
+DB_DATABASE=/var/www/html/database/database.sqlite

-QUEUE_CONNECTION=sync
+QUEUE_CONNECTION=redis
```

## Debugging

Debugging (Laravel sail options TBD - doesn't quite work):

`.env`:

```diff
+SAIL_XDEBUG_MODE=debug
+SAIL_XDEBUG_CONFIG="client_host=172.17.0.1 idekey=phpstorm start_with_request=yes"
```

`172.17.0.1` is the equivalent of `host.docker.internal` on Linux.

[Alternatively](https://stackoverflow.com/a/48547074) could add the following to `/etc/hosts` and replace `client_host=172.17.0.1` `client_host=host.docker.internal` to achieve parity with Mac on Linux:

```
cat <<EOF >> /etc/hosts
172.17.0.1 host.docker.internal
EOF
```

Verify interpolation is correct from `.env` with:

```bash
./vendor/bin/sail config
```

## Getting Started

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm install --save-dev laravel-echo
./vendor/bin/sail artisan vendor:publish \
  --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" \
  --tag="migrations"
./vendor/bin/sail artisan vendor:publish \
  --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" \
  --tag="config"
# not entirely necessary
./vendor/bin/sail artisan migrate

./vendor/bin/sail up
./vendor/bin/sail shell
```

## Broadcasting

```diff
-BROADCAST_DRIVER=log
+BROADCAST_DRIVER=pusher

-PUSHER_APP_ID=
+PUSHER_APP_ID=whatever
-PUSHER_APP_KEY=
+PUSHER_APP_KEY=whatever
-PUSHER_APP_SECRET=
+PUSHER_APP_SECRET=whatever
 PUSHER_APP_CLUSTER=mt1
```

`config/broadcasting.php`:

```diff
         'pusher' => [
             'driver' => 'pusher',
             'key' => env('PUSHER_APP_KEY'),
             'secret' => env('PUSHER_APP_SECRET'),
             'app_id' => env('PUSHER_APP_ID'),
             'options' => [
                 'cluster' => env('PUSHER_APP_CLUSTER'),
-                'useTLS' => true,
+                'encrypted' => true,
+                'host' => '127.0.0.1',
+                'port' => 6001,
+                'scheme' => 'http'
             ],
         ],
```

**NOTE:** Expose `'6001:6001'` on `docker-compose.yml` server.

```
./vendor/bin/sail artisan websockets:serve
```

## Jetstream & Other UI Niceties

```bash
./vendor/bin/sail up
./vendor/bin/sail composer require laravel/jetstream
./vendor/bin/sail artisan jetstream:install livewire
./vendor/bin/sail npm install; ./vendor/bin/sail npm run dev
./vendor/bin/sail artisan migrate 
```

## Example

* [`app/Events/ExampleEvent.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/app/Events/ExampleEvent.php#L25) a very simple event with broadcasting enabled
* [`routes/console.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/routes/console.php#L28) for dispatching a broadcast event with `php artisan broadcast`
* [`resources/views/welcome.blade.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/resources/views/welcome.blade.php#L18) for alpine component listening for Echo events and updating state

## Bonus Slack Notification

* [`app/Notifications/SlackNotification.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/app/Notifications/SlackNotification.php#L48) a rich Slack notification
* [`routes/console.php`](https://github.com/alistaircol/laravel-broadcasting-example/blob/main/routes/console.php#L22) for dispatching notification

![slack notification](https://raw.githubusercontent.com/alistaircol/laravel-broadcasting-example/main/.github/bonus-cheems-notification.png)
