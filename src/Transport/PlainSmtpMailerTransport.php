<?php


namespace Fractal512\PlainSmtpMailer\Transport;


use Fractal512\PlainSmtpMailer\PlainSmtpMailer;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_Message;

class PlainSmtpMailerTransport extends Transport
{
    //protected $publicKey;
    //protected $secretKey;

    public function __construct()
    {
        //$this->publicKey = config('mailjet.public_key');
        //$this->secretKey = config('mailjet.secret_key');
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {

        $this->beforeSendPerformed($message);

        /* $mj = new \Mailjet\Client($this->publicKey, $this->secretKey,
            true,['version' => 'v3.1']);

        $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $this->getBody($message)]); */

        //dd($message);
        $recipient = $message->getHeaders()->get('to')->getFieldBody();
        $headers = $message->getHeaders();
        $body = $message->getBody();
        //dd($headers,$body);
        $mailer = new PlainSmtpMailer();
        $mailer->setRecipient($recipient)->setHeaders($headers)->setBody($body)->send();
        dd($mailer);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get body for the message.
     *
     * @param Swift_Mime_Message $message
     * @return array
     */

    protected function getBody(Swift_Mime_Message $message)
    {
        return [
            'Messages' => [
                [
                    'From' => [
                        'Email' => config('mail.from.address'),
                        'Name' => config('mail.from.name')
                    ],
                    'To' => $this->getTo($message),
                    'Subject' => $message->getSubject(),
                    'HTMLPart' => $message->getBody(),
                ]
            ]
        ];
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param Swift_Mime_Message $message
     * @return array
     */
    protected function getTo(Swift_Mime_Message $message)
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? [
                'Email' => $address,
                'Name' =>$display
            ] : [
                'Email' => $address,
            ];

        })->values()->toArray();
    }

    /**
     * Get all of the contacts for the message.
     *
     * @param Swift_Mime_Message $message
     * @return array
     */
    protected function allContacts(Swift_Mime_Message $message)
    {
        return array_merge(
            (array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
        );
    }
}