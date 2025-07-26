<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $isAdminCopy;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, bool $isAdminCopy = false)
    {
        //
        $this->order = $order;
        $this->isAdminCopy = $isAdminCopy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isAdminCopy
            ? "Commande #{$this->order->order_number} mise à jour (Copie Admin)"
            : "Votre commande #{$this->order->order_number} a été mise à jour",
            from: new Address('test@gmail.com', 'Test'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->isAdminCopy
            ? 'emails.order-status-updated-admin'
            : 'emails.order-status-updated',
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
