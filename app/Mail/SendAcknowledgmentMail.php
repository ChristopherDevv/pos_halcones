<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAcknowledgmentMail extends Mailable
{

    public $managerEmail;
    public $managerName;
    public $reason;
    public $pdf;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($managerEmail, $managerName, $reason, $pdf)
    {
        $this->managerEmail = $managerEmail;
        $this->managerName = $managerName;
        $this->reason = $reason;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('soporte_hdx@halconesdexalapa.com.mx')
                    ->to($this->managerEmail)
                    ->markdown('Email.sendAcknowledgmentMail')
                    ->attach($this->pdf);
    }
}
