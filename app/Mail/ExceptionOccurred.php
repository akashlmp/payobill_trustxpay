<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionOccurred extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $css;

    public function __construct($content, $css)
    {
        $subject = env('ERROR_MAIL_SUBJECT', 'Trustxpay ERROR');

        $this->subject = $subject;
        $this->content = $content;
        $this->css = $css;
    }

    public function build()
    {
        $this->content .= '<br><br> <strong> REQUEST INPUTS : </strong> <br>' . (string)json_encode(\Request::all());

        return $this->view('mail.exception')
            ->with('content', $this->content)->with('css', $this->css);
    }
}
