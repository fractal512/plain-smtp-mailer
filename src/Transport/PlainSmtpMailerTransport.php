<?php


namespace Fractal512\PlainSmtpMailer\Transport;


use Fractal512\PlainSmtpMailer\PlainSmtpMailer;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_Message;

class PlainSmtpMailerTransport extends Transport
{
    /**
     * @param Swift_Mime_Message $message
     * @param null $failedRecipients
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $recipient = $message->getHeaders()->get('to')->getFieldBody();
        $headers = $message->getHeaders();
        $body = $message->getBody();

        $mailer = new PlainSmtpMailer;
        $mailer->setRecipient($recipient)
            ->setHeaders($headers)
            ->setBody($body)
            ->send();

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Plain SMTP Mailer can send mail to only one recipient.
     *
     * @param  \Swift_Mime_Message  $message
     * @return int
     */
    protected function numberOfRecipients(Swift_Mime_Message $message)
    {
        return 1;
    }
}