<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Bulletin;

class BulletinDisponible extends Mailable
{
    use Queueable, SerializesModels;

    public $bulletin;

    public function __construct(Bulletin $bulletin)
    {
        $this->bulletin = $bulletin;
    }

    public function build()
    {
        return $this->subject('Nouveau bulletin disponible')
            ->view('emails.bulletin_disponible');
    }
}