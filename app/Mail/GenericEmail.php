<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    /**
     * Create a new message instance.
     *
     * @internal param $message
     * @param $emailMessage
     */
    public function __construct($emailMessage)
    {
        $this->email = $emailMessage;
        $this->content = $emailMessage['content'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->from($this->email['no_reply'], $this->email['sender_name'])
            ->subject($this->email['subject'])
            ->view('email.email')
            ->text('email.emailText');

        $this->withSwiftMessage(function ($message) {

            $headers = $message->getHeaders();
            $headers->addTextHeader('Return-Path', $this->email['no_reply']);
            $headers->addTextHeader('X-Return-Path', $this->email['no_reply']);
            $headers->addTextHeader('Reply-To', $this->email['no_reply']);
        });
        return $this;
    }
}
