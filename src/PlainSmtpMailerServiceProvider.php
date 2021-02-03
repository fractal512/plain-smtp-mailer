<?php


namespace Fractal512\PlainSmtpMailer;


use Fractal512\PlainSmtpMailer\Transport\PlainSmtpMailerAddedTransportManager;
use Illuminate\Mail\MailServiceProvider;

class PlainSmtpMailerServiceProvider extends MailServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/mailer.php' => config_path('mailer.php')
        ], 'mailerconfig');
    }

    protected function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new PlainSmtpMailerAddedTransportManager($app);
        });
    }
}