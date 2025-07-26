<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation de commande</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            color: #374151;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 40px 0 20px;
        }

        .header img {
            max-height: 60px;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }

        .card-header {
            background: linear-gradient(135deg, #fce7f3, #fef3c7);
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .card-body {
            padding: 30px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #ec4899, #f59e0b);
            color: white !important;
            text-decoration: none;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(236, 72, 153, 0.25);
            margin: 15px 0;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(236, 72, 153, 0.3);
        }

        .order-summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .order-items {
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 16px;
        }

        .order-items h2 {
            font-size: 18px;
            color: #111827;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 14px;
        }

        h1 {
            color: #111827;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        h2 {
            color: #111827;
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-pink {
            color: #ec4899;
        }

        .text-bold {
            font-weight: 600;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <!-- Remplacez par votre logo -->
            <img src="https://via.placeholder.com/180x60?text={{ urlencode(config('app.name')) }}"
                alt="{{ config('app.name') }}">
        </div>

        <div class="card">
            <div class="card-header">
                <h1>Merci pour votre commande !</h1>
                <p>Votre commande #{{ $order->order_number }} est confirmée</p>
            </div>

            <div class="card-body">
                <!-- Ajoutez cette section pour les produits -->
                <div class="order-items mb-6">
                    <h2 class="mb-4">Produits commandés</h2>

                    @foreach ($order->items as $item)
                        <div
                            style="display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                            <div style="width: 60px; height: 60px; margin-right: 16px;">
                                <img src="{{ $item->product->image_url ?? 'https://via.placeholder.com/60' }}"
                                    alt="{{ $item->product->name }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 4px; font-size: 16px;">{{ $item->product->name }}</h3>
                                <p style="margin: 0; color: #6b7280;">Quantité: {{ $item->quantity }}</p>
                            </div>
                            <div style="text-align: right;">
                                <p style="margin: 0; font-weight: 600;">
                                    {{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</p>
                                <p style="margin: 0; color: #6b7280; font-size: 14px;">
                                    {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA × {{ $item->quantity }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="order-summary">
                    <h2>Récapitulatif de commande</h2>

                    <div class="summary-row">
                        <span>Numéro de commande</span>
                        <span class="text-bold">{{ $order->order_number }}</span>
                    </div>

                    <div class="summary-row">
                        <span>Date</span>
                        <span>{{ now()->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="summary-row">
                        <span>Montant total</span>
                        <span class="text-bold text-pink">{{ number_format($order->total_price, 0, ',', ' ') }}
                            FCFA</span>
                    </div>
                </div>

                <p class="text-center mb-4">Vous pouvez télécharger votre facture en cliquant sur le bouton ci-dessous :
                </p>

                <div class="text-center">
                    <a href="{{ route('order.invoice', $order->id) }}" class="btn">
                        Télécharger la facture
                    </a>
                </div>

                <div class="mt-4"
                    style="background-color: #f0fdf4; padding: 12px; border-radius: 8px; border-left: 4px solid #34d399;">
                    <p style="margin: 0; color: #065f46;">
                        Votre commande est en cours de préparation. Vous recevrez un email lorsque votre colis sera
                        expédié.
                    </p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Pour toute question, contactez notre service client à <a href="mailto:contact@votreentreprise.com"
                    style="color: #3b82f6;">contact@votreentreprise.com</a></p>
            <p class="mt-4">© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>
