# Title

## Tech Stask

-   Laravel
-   Filament
-   TailwindCSS

## Getting Started

## Pre-requisites

-   [TablePlus](https://tableplus.com/) Database management tool or similar
-   [Docker Desktop](https://www.docker.com/products/docker-desktop/) installation
-   Setup .git/config user

```
[user]
    name = "YOUR NAME"
    email = "YOUR COMPANY EMAIL"
```

-   Make APP_PORT=8000 in .env to avoid collisions with other services that use port 80 by default
-   config Laravel Sail shell alias https://laravel.com/docs/11.x/sail#configuring-a-shell-alias
-   copy .env.example to .env and ask for .env details

### Installation process

### With Sail <- USE THIS

[Laravel Sail](https://laravel.com/docs/10.x/sail#introduction)

For Unix

1. `valet use` -> it will use the `.valetrc` file
2. `composer global update`
3. `composer install`
4. `sail up -d` or `./vendor/bin/sail up -d`
5. `sail composer install`
6. `sail artisan migrate`
7. `sail artisan db:seed`
8. `sail npm install`
9. `sail npm run dev`
10. Access `http://safeye.test:8000/`

For Windows
Pre-Requisites (php 8.2.x)
(error can appear on php.ini, some extensions could be commented)

1. `composer install`
2. `wsl`
3. `cd vendor/bin`
4. `./sail up -d`
5. `./sail composer install`
6. `./sail artisan migrate`
7. `./sail artisan db:seed`
8. `./sail npm install`
9. `./sail npm run dev`

### Without Sail

1. `nvm use 20` -> install nvm is needed
2. `npm install`
3. `valet use` -> it will use the `.valetrc` file
4. `valet link safeye`
5. `valet secure safeye`
6. `composer global update` -> to update the composer dependencies
7. `composer install` -> to install the dependencies
8. `npm run dev` -> keep this running
9. `php artisan serve`

## Important Links

-   https://www.youtube.com/watch?v=ag7HXbgJtuk
-   https://github.com/marclou/stripe-sub
-   https://github.com/stripe/stripe-php
-   https://docs.stripe.com/api
