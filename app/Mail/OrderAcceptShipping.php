<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class OrderAcceptShipping extends Mailable
{
    use Queueable, SerializesModels;
    private $name;
    private $supp;
    private $product;
    private $quantity;
    private $responseid;
    private $created;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$supp,$product,$quantity,$responseid,$created)
    {
        $this->name = $name;
        $this->supp = $supp;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->responseid = $responseid;
        $this->created = $created;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function envelope()
    {
        return new Envelope(
            subject: 'Notifications for Shipping Order',
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
            view: 'email.orderAcceptShippingMail',
            with: ['name' => $this->name,
                    'supp' => $this->supp,
                    'product' => $this->product,
                    'quantity' => $this->quantity,
                    'responseid' => $this->responseid,
                    'created' => $this->created,
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