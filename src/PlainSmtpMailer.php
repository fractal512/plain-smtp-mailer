<?php

namespace Fractal512\PlainSmtpMailer;

/**
 * Plain SMTP Mailer for Laravel 5+
 *
 * @copyright Copyright (c) 2021 Fractal512
 * @version 1.x
 * @author fractal512
 * @contact fractal512.web.dev@gmail.com
 * @web https://github.com/fractal512/plain-smtp-mailer
 * @date 2021-02-03
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

use Swift_Mime_HeaderSet;

/**
 * Class PlainSmtpMailer
 * @package Fractal512\PlainSmtpMailer
 */
class PlainSmtpMailer
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $mailserver;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $client;

    /**
     * @var string
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $headers;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var boolean
     */
    protected $socketError = false;

    /**
     * @var string
     */
    protected $socketErrorCode;

    /**
     * @var string
     */
    protected $socketErrorMessage;

    /**
     * @var string
     */
    protected $socketResponse;

    /**
     * Constructor
     */
    public function __construct() {
        $this->domain = config( 'mailer.domain' );
        $this->mailserver = config( 'mailer.mailserver' );
        $this->port = config( 'mailer.port' );
        $this->username = config( 'mailer.username' );
        $this->password = config( 'mailer.password' );
        $this->client = config( 'mailer.client' );
    }

    /**
     * @param string $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @param Swift_Mime_HeaderSet $headers
     * @return PlainSmtpMailer
     */
    public function setHeaders(Swift_Mime_HeaderSet $headers)
    {
        $headersStr = $headers->get('date')->toString();
        //$headersStr .= $headers->get('from')->toString();
        $headersStr .= "From: =?UTF-8?Q?".str_replace("+","_",str_replace("%","=",urlencode(config('app.name'))))."?= <".$this->username.">\r\n";
        $headersStr .= "X-Mailer: " . $this->client . "\r\n";
        $headersStr .= "Reply-To: =?UTF-8?Q?".str_replace("+","_",str_replace("%","=",urlencode(config('app.name'))))."?= <".$this->username.">\r\n";
        $headersStr .= "X-Priority: 3 (Normal)\r\n";
        $headersStr .= $headers->get('message-id')->toString();
        //$headersStr .= $headers->get('to')->toString();
        $to = $headers->get('to')->getFieldBody();
        $mailSlug = substr( $to, 0, strpos($to, '@') );
        $headersStr .= "To: =?UTF-8?Q?".str_replace("+","_",str_replace("%","=",urlencode( $mailSlug )))."?= <" . $to . ">\r\n";
        //$headersStr .= $headers->get('subject')->toString();
        $subject = $headers->get('subject')->getFieldBody();
        $headersStr .= "Subject: =?UTF-8?Q?".str_replace("+","_",str_replace("%","=",urlencode($subject)))."?=\r\n";
        $headersStr .= $headers->get('mime-version')->toString();
        $headersStr .= $headers->get('content-type')->toString();
        //$headersStr .= $headers->get('content-transfer-encoding')->toString();
        $headersStr .= "Content-Transfer-Encoding: 8bit\r\n";
        $this->headers = $headersStr;
        return $this;
    }

    /**
     * @param string $body
     * @return PlainSmtpMailer
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Send mail via smtp mail server
     */
    public function send()
    {
        $this->socket = fsockopen(
            $this->mailserver,
            $this->port,
            $this->socketErrorCode,
            $this->socketErrorMessage,
            10
        );

        if ( ! $this->socket ) {
            $this->socketError = true;
            return $this;
        }

        $commands = [
            [
                "message" => "EHLO " . $this->domain . "\r\n",
                "response-codes" => [ 250 ]
            ],
            [
                "message" => "AUTH LOGIN\r\n",
                "response-codes" => [ 334 ]
            ],
            [
                "message" => base64_encode($this->username) . "\r\n",
                "response-codes" => [ 334 ]
            ],
            [
                "message" => base64_encode($this->password) . "\r\n",
                "response-codes" => [ 235 ]
            ],
            [
                "message" => "MAIL FROM:" . $this->username . "\r\n",
                "response-codes" => [ 250 ]
            ],
            [
                "message" => "RCPT TO:" . $this->recipient . "\r\n",
                "response-codes" => [ 250, 251 ]
            ],
            [
                "message" => "DATA\r\n",
                "response-codes" => [ 354 ]
            ],
            [
                "message" => $this->headers . "\r\n" . $this->body . "\r\n.\r\n",
                "response-codes" => [ 250 ]
            ],
            [
                "message" => "QUIT\r\n",
                "response-codes" => []
            ],
        ];

        foreach ($commands as $command) {
            $this->executeCommand($command);
            if( $this->socketError ) {
                return $this;
            }
        }

        fclose($this->socket);

        return $this;
    }

    /**
     * @param array $command
     * @return void
     */
    protected function executeCommand($command)
    {
        fputs($this->socket, $command['message']);
        $this->socketResponse = $this->getResponse();
        $code = substr($this->socketResponse,0,3);
        foreach ($command['response-codes'] as $expectedCode){
            if($code != $expectedCode){
                $this->closeOnError();
            }
        }
    }

    /**
     * Get socket response
     *
     * @return string
     */
    protected function getResponse()
    {
        $data="";
        while($str = fgets($this->socket,515))
        {
            $data .= $str;
            if(substr($str,3,1) == " ") { break; }
        }
        return $data;
    }

    /**
     * Disconnect form socket and maybe log or do whatever on error.
     *
     * @return void
     */
    protected function closeOnError()
    {
        // maybe additionally log something...
        $this->socketError = true;
        fclose($this->socket);
    }

    /**
     * @return string
     */
    public function getSocketErrorCode()
    {
        return $this->socketErrorCode;
    }

    /**
     * @return string
     */
    public function getSocketErrorMessage()
    {
        return $this->socketErrorMessage;
    }

    /**
     * @return string
     */
    public function getSocketResponse()
    {
        return $this->socketResponse;
    }
}
