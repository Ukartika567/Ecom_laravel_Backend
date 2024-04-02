<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class SupplierMail extends Mailable
{
    use Queueable, SerializesModels;
    private $requestid;
    private $name;
    private $customer;
    private $product;
    private $quantity;
    private $created;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($requestid,$name,$customer,$product,$quantity,$created)
    {
            $this->requestid = $requestid;
        $this->name = $name;
        $this->customer = $customer;
        $this->product = $product;
        $this->quantity = $quantity;
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
            subject: 'Notifications for requested quote from customer.',
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
            view: 'email.supplierMail',
            with: ['requestid' =>  $this->requestid,
                    'name' => $this->name,
                    'customer' => $this->customer,
                    'product' => $this->product,
                    'quantity' => $this->quantity,
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