## About Superban Package

This package will add the ability to ban a client completely for a period of time. With this package, the user of the package can do the following:

-   Define Number of requests that clients can make to specific apps within a defined duration.
-   Determine for how long a client is banned is they exceed the rate limit.
-   Determine which User Identifier will be used for rate limiting. For example, ipaddress, userid or useremail.
-   Define specific routes or group all routes for rate limiting.

## Activating Superban

To use Superban package, Follow these steps:

-   This package should be placed inside the 'packages' directory in your project root
-   In the .env file of your app, specify which User Identifier you wish for rate limiting. By default 'ipaddress' is used but to configure another identifier set the RATE_LIMIT_ID in your .env to any of the following ipaddress, userid or useremail 
-   Add ... Superban\Providers\SuperbanProvider::class ... to the providers array in config/app.php
-   Add ... 'superban' => \Superban\Middleware\SuperbanMiddleware::class ... to the middlewareAliases in app/Http/Kernel.php
-   Add ... "Superban\\\": "packages/superban/src/ ... to the autoload->psr4 array in the composer.json file in your laravel project root
-   run command composer dump-autoload
-   Finally run 'php artisan superban:activate' command.

## Usage

Sample usage:

Route::middleware(['superban:200,2,1440'])->group(function () {
Route::post('/thisroute', function () {
// ...
});

Route::post('anotherroute', function () {
// ...
});

});

## Dev Support

In case of difficulties using this package, please contact developer via email oyebanjioladapo1@gmail.com
