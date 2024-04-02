<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class OrderDelivered extends Mailable
{
    use Queueable, SerializesModels;
    private $name;
    private $supp;
    private $location;
 
    public function __construct($name,$supp,$location)
    {
        $this->name = $name;
        $this->supp = $supp;
        $this->location = $location;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function envelope()
    {
        return new Envelope(
            subject: 'Notifications for Order Delivered',
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
            view: 'email.orderDeliveredMail',
            with: ['name' => $this->name,
                    'supp' => $this->supp,
                    'location' => $this->location,
                    ],
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