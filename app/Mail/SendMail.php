<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $from;
    public $subject;
    private $content;
    // private $files;
    public function __construct(
        // $from,
        // $subject,
        // $body
        // $files
        ) {
            // $this->from = $from;
            // $this->subject = $subject;
            // $this->body = $body;
            // $this->files = $files;
        }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        // $this->from = 'wnet@wisecorp.net';
        // $this->subject = 'subject2';
        // $this->body = 'body2';
        $mail = $this->text('mailbody')
        ->from('wnet@wisecorp.net')
        ->subject('subject2')
        ->with(['body' => 'body2']);
        // ->from($this->from)
        // ->subject($this->subject)
        // ->with(['body' => $this->body]);
        // foreach($this->files as $file) {
        //     $mail->attach($file);
        // }
        return $mail;
    }
}
