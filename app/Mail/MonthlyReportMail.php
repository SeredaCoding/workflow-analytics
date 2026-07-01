<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        string $subject,
        public string $htmlBody,
        ?string $csvContent = null,
        string $csvName = 'relatorio-mensal.csv',
    ) {
        $this->subject = $subject;
        if ($csvContent) {
            $this->attachData($csvContent, $csvName, [
                'mime' => 'text/csv; charset=UTF-8',
            ]);
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlBody,
        );
    }
}
