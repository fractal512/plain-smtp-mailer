<?php


namespace Fractal512\PlainSmtpMailer\Transport;


use Illuminate\Mail\TransportManager;

class PlainSmtpMailerAddedTransportManager extends TransportManager
{
    protected function createPlainSmtpMailerDriver()
    {
        return new PlainSmtpMailerTransport;
    }
}