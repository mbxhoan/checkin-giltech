# Laravel 10.x blog

The purpose of this repository is to show good development practices on [Laravel](http://laravel.com/) as well as to present cases of use of the framework's features like:

- [Authentication](https://laravel.com/docs/10.x/authentication)
- API
  - [Sanctum](https://laravel.com/docs/10.x/sanctum)
  - [API Resources](https://laravel.com/docs/10.x/eloquent-resources)
  - Versioning
- [Blade](https://laravel.com/docs/10.x/blade)
- [Broadcasting](https://laravel.com/docs/10.x/broadcasting)
- [Cache](https://laravel.com/docs/10.x/cache)
- [Email Verification](https://laravel.com/docs/10.x/verification)
- [Filesystem](https://laravel.com/docs/10.x/filesystem)
- [Helpers](https://laravel.com/docs/10.x/helpers)
- [Horizon](https://laravel.com/docs/10.x/horizon)
- [Localization](https://laravel.com/docs/10.x/localization)
- [Mail](https://laravel.com/docs/10.x/mail)
- [Migrations](https://laravel.com/docs/10.x/migrations)
- [Policies](https://laravel.com/docs/10.x/authorization)
- [Providers](https://laravel.com/docs/10.x/providers)
- [Requests](https://laravel.com/docs/10.x/validation#form-request-validation)
- [Seeding & Factories](https://laravel.com/docs/10.x/seeding)
- [Testing](https://laravel.com/docs/10.x/testing)
- [Homestead](https://laravel.com/docs/10.x/homestead)

Beside Laravel, this project uses other tools like:

- [Bootstrap 5.x](https://getbootstrap.com/)
- [Pint](https://github.com/laravel/pint)
- [Font Awesome](https://fontawesome.com/)
- [Hotwired](https://hotwired.dev/)
- [Redis](https://redis.io/)
- [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary)
- [hotwired-laravel/turbo-laravel](https://github.com/hotwired-laravel/turbo-laravel)
- Many more to discover.

## Some screenshots

You can find some screenshots of the application on : [https://imgur.com/a/Jbnwj](https://imgur.com/a/Jbnwj)

## Installation

To create your development environment [follow these instructions](https://laravel.com/docs/10.x/installation).

Setting up your development environment on your local machine (Remember to set "DEFAULT_MAIL_TO" in your .env):
```bash
$ composer install
$ composer dumpautoload
$ composer update --ignore-platform-reqs
$ composer dump-autoload --ignore-platform-reqs
# $ git clone https://github.com/guillaumebriday/laravel-blog.git
# $ cd laravel-blog
$ cp .env.example .env
$ php artisan key:generate
$ php artisan horizon:install
$ php artisan telescope:install
$ php artisan storage:link
```

### Mailer

You can use [Mailpit](https://github.com/axllent/mailpit) to test your emails in development.

Once installed, open [http://localhost:8025](http://localhost:8025).

## Before starting
You need to run the migrations with the seeds :
```bash
$ php artisan migrate --seed
```

This will create a new user that you can use to sign in :
```yml
email: darthvader@deathstar.ds
password: 4nak1n
```

And then, compile the assets :
```bash
$ yarn add vite --dev
$ yarn install
$ yarn dev # or yarn watch or yarn build (if you use production environment)
```

Starting job for newsletter :
```bash
$ php artisan tinker
> PrepareNewsletterSubscriptionEmail::dispatch();
```

## Useful commands

Start Laravel Horizon (Queue):
```bash
$ php artisan horizon
```

Run queue worker on a VPS with lower memory pressure:
```bash
$ php artisan queue:work --tries=3 --timeout=0 --memory=256 --max-jobs=50 --max-time=3600
```

If you use Supervisor, enable `autorestart=true` so the worker comes back after an OOM kill or unexpected exit.

Generate cards:
```bash
$ php artisan generate:cards {cardId} --clientId={clientId}
```

Tips for card generation on a small server:
- Prefer `gd` instead of `imagick` for the image driver when RAM is limited.
- Lower `cards.scaled` to reduce the output size and the amount of image data processed in memory.
- Reduce image quality in the generator if you want smaller files and faster writes.
- Process clients in smaller batches so the worker can release memory between chunks.
- Avoid running more than one heavy card-generation worker at the same time on the same VPS.

Seeding the database:
```bash
$ php artisan db:seed
```

Running tests :
```bash
$ php artisan test
```

Running Laravel Pint :
```bash
$ ./vendor/bin/pint --verbose --test
```

Generating backup (--only-db or --only-files) :
```bash
$ php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
$ php artisan backup:run
```

Generating fake data :
```bash
$ php artisan db:seed --class="Database\Seeders\dev\DevDatabaseSeeder"
```

Discover package
```bash
$ php artisan package:discover
```

In development environment, rebuild the database :
```bash
$ php artisan migrate:fresh --seed
```

## Accessing the API

Clients can access to the REST API. API requests require authentication via Bearer token.

Generate a new token:

```bash
curl --location --request POST '<app_url>/api/v1/authenticate?email=your_email&password=your_password' \
     --header 'X-Requested-With: XMLHttpRequest'
```

And now you can use the `meta.access_token` key as your `Bearer` token:

```bash
curl --location '<app_url>/api/v1/posts' \
      --header 'X-Requested-With: XMLHttpRequest' \
      --header 'Authorization: Bearer access_token'
```

API are prefixed by `api` and the API version number like so `v1`.

Do not forget to set the `X-Requested-With` header to `XMLHttpRequest`. Otherwise, Laravel won't recognize the call as an AJAX request.

To list all the available routes for API :

```bash
$ php artisan route:list --path=api
```

## Contributing

Do not hesitate to contribute to the project by adapting or adding features ! Bug reports or pull requests are welcome.

## License

<!-- This project is released under the [MIT](http://opensource.org/licenses/MIT) license. -->
