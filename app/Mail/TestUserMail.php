<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class TestUserMail extends Mailable
{
    use Queueable, SerializesModels;
    private $name;
    private $customer;
    private $quantity;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$customer,$quantity)
    {
        $this->name = $name;
        $this->customer = $customer;
        $this->quantity = $quantity;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function envelope()
    {
        return new Envelope(
            subject: 'Notifications for requested quote from supplier',
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
            view: 'email.testCustomerMail',
            with: ['name' => $this->name,
                    'customer' => $this->customer,
                    'quantity' => $this->quantity,   
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