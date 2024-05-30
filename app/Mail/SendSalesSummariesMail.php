<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSalesSummariesMail extends Mailable
{
    public $managerEmails = ['eh2002cc415@ueh.edu.mx'];
    public $pdfs;
    public $reason;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($managerEmails, $pdfs, $reason)
    {
        $this->managerEmails = array_merge($this->managerEmails, $managerEmails);
        $this->pdfs = $pdfs;
        $this->reason = $reason;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from('soporte_hdx@halconesdexalapa.com.mx')
                      ->to($this->managerEmails)
                      ->markdown('Email.sendSalesSummariesMail');
        
        foreach ($this->pdfs as $pdf) {
            $email->attach($pdf);
        }
    
        return $email;
    }
}
