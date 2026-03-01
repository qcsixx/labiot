<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\BorrowRequest;
use App\Models\User;

class ReturnOverdue extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Data peminjaman
     */
    public $borrow;
    
    /**
     * Data user
     */
    public $user;

    /**
     * URL logo
     */
    public $logo_url;

    /**
     * Create a new message instance.
     */
    public function __construct(BorrowRequest $borrow, User $user)
    {
        $this->borrow = $borrow;
        $this->user = $user;
        $this->logo_url = public_path('images/logo-vokasi-ub.png');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Peringatan – Pengembalian Barang Telah Melebihi Tenggat',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.return-overdue',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 