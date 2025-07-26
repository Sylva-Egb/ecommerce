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
use Illuminate\Support\Facades\Storage;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load([
            'items.product',
            'address'
        ]);

        // Convertir les URLs d'images relatives en absolues
        $this->prepareProductImages();
    }

    protected function prepareProductImages()
    {
        foreach ($this->order->items as $item) {
            if ($item->product->image_url) {
                // Si l'URL commence par /storage/, on la convertit en URL absolue
                if (str_starts_with($item->product->image_url, '/storage/')) {
                    $item->product->image_url = asset($item->product->image_url);
                }
                // Si c'est déjà une URL absolue, on ne fait rien
            }
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de commande #' . $this->order->order_number,
            from: new Address('test@gmail.com', 'Test'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}