<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailforProduct extends Mailable
{
    use Queueable, SerializesModels;
    private  $name;
    private $product_id;
    private $product_name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $product_id, $product_name)
    {
        //
        $this->name = $name;
        $this->product_id = $product_id;
        $this->product_name = $product_name;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Notification for product deletion.',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'email.mailforProduct',
            with:[
                'name'=> $this->name,
                'product_id'=> $this->product_id,
                'product_name' => $this->product_name
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
