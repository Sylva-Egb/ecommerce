<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statut de commande mis à jour</title>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 16px;
        }

        .card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .status-message {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .status-value {
            font-size: 20px;
            font-weight: 700;
            color: #ec4899;
            margin: 10px 0;
        }

        .button {
            display: inline-block;
            background-color: #ec4899;
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #db2777;
        }

        @media only screen and (max-width: 600px) {
            .container {
                padding: 15px;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Statut de votre commande mis à jour</h1>
            <p class="subtitle">Commande #{{ $order->order_number }}</p>
        </div>

        <div class="card">
            <div class="status-message">
                <p>Le statut de votre commande est maintenant :</p>
                <p class="status-value">{{ ucfirst($order->status) }}</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('orders.show', $order->id) }}" class="button">
                    Voir ma commande
                </a>
            </div>
        </div>
    </div>
</body>
</html>