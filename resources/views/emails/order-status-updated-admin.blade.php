<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commande mise à jour (Admin)</title>
    <style type="text/css">
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .email-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .email-body {
            padding: 25px;
        }

        .order-info {
            margin-bottom: 20px;
        }

        .info-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eeeeee;
        }

        .info-label {
            font-weight: bold;
            color: #555555;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #222222;
        }

        .status-value {
            font-weight: bold;
            color: #3b82f6;
        }

        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white !important;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 6px;
            text-align: center;
            margin-top: 15px;
        }

        .button:hover {
            background-color: #2563eb;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }

            .info-label {
                width: 100%;
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1 class="email-title">Mise à jour de commande</h1>
        </div>

        <div class="email-body">
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">N° Commande:</span>
                    <span class="info-value">#{{ $order->order_number }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Client:</span>
                    <span class="info-value">{{ $order->user?->name ?? $order->guest?->name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Nouveau statut:</span>
                    <span class="info-value status-value">{{ ucfirst($order->status) }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Montant total:</span>
                    <span class="info-value">{{ number_format($order->total_price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('orders.show', $order->id) }}" class="button">
                    Voir les détails de la commande
                </a>
            </div>
        </div>
    </div>
</body>
</html>