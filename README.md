# Plain SMTP Mailer for Laravel 5+
Custom mail driver for [Laravel 5+](http://www.laravel.com/) working with SMTP server via a socket.

## Installation
The Plain SMTP Mailer can be installed via [Composer](http://getcomposer.org) by requiring the
`fractal512/plain-smtp-mailer` package:
```
composer require fractal512/plain-smtp-mailer
```
or add `fractal512/plain-smtp-mailer` to `require` section and set the `minimum-stability` to `dev` (required for Laravel 5) in your project's `composer.json`:
```json
{
    "require": {
        "laravel/framework": "5.0.*",
        "fractal512/plain-smtp-mailer": "~1.0"
    },
    "minimum-stability": "dev"
}
```
then update your packages with ```composer update``` or install with ```composer install```.

## Registration in Laravel
No need in versions with auto discovery (Laravel 5.5+).
Register the Plain SMTP Mailer Service Provider in the `providers` key in `config/app.php`.

```php
    'providers' => [
        // ...
        'Fractal512\PlainSmtpMailer\PlainSmtpMailerServiceProvider',
    ]
```
for Laravel 5.1+
```php
    'providers' => [
        // ...
        Fractal512\PlainSmtpMailer\PlainSmtpMailerServiceProvider::class,
    ]
```

## Configuration
Publish the package `mailer.php` config file to apply your own settings.
```shell script
$ php artisan vendor:publish --tag=mailerconfig
```
or run (Laravel 8+)
```shell script
$ php artisan vendor:publish
```
then enter the number of the `fractal512\plain-smtp-mailer` service provider.

Options in config file refer to options for mail driver in the app `.env` file:
```text
MAIL_DRIVER=PlainSmtpMailer
MAIL_DOMAIN=example.com
MAIL_HOST=smtp.example.com
MAIL_PORT=25
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=12345678
MAIL_CLIENT="Desired mail client full name like: Outlook Express (v1.0)...|The Bat! (v1.0)...|Mozilla Thunderbird (v1.0)... etc."
MAIL_ENCRYPTION=null
```

## Usage
You need configured SMTP server with account created on your hosting.
You can use built in Laravel mail functionality with Plain SMTP Mailer, just enable `PlainSmtpMailer` driver in `.env` by setting:
```text
MAIL_DRIVER=PlainSmtpMailer
```
and adding all the other needed options in MAIL section as shown in `Configuration` section above.
