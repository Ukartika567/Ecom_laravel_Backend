<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserApprovalMail extends Mailable
{
    use Queueable, SerializesModels;
    private $userid;
    private $name;
    private $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userid, $name, $status)
    {
        $this->userid = $userid;
        $this->name = $name;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Account '.$this->status.' Notification',
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
            view: 'email.sendApprovalMail',
            with:[
                'userid'=>$this->userid,
                'name'=>$this->name,
                'status'=>$this->status
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
