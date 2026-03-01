<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Data untuk email.
     *
     * @var array
     */
    public $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
        // Gunakan public_path untuk mengakses file gambar lokal
        $this->emailData['logo_url'] = public_path('images/logo-vokasi-ub.png');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = match($this->emailData['status']) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'pending-return' => 'Menunggu Verifikasi Pengembalian',
            'overdue' => 'Melewati Deadline',
            'completed' => 'Selesai',
            default => 'Update',
        };
        
        return new Envelope(
            subject: "Lab IoT - Status Peminjaman {$this->emailData['itemName']}: {$status}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.borrow-status',
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