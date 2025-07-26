<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 20px; }
        table { width: 100%; line-height: inherit; text-align: left; }
        table td { padding: 5px; vertical-align: top; }
        table tr.top table td { padding-bottom: 20px; }
        table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        table tr.item td { border-bottom: 1px solid #eee; }
        .total { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h2>Facture #{{ $order->order_number }}</h2>
            <p>Date: {{ $order->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="info">
            <table>
                <tr class="top">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>
                                    <strong>De:</strong><br>
                                    Votre Entreprise<br>
                                    123 Rue Commerciale<br>
                                    Abidjan, Côte d'Ivoire
                                </td>
                                <td>
                                    <strong>À:</strong><br>
                                    {{ $order->address->full_name }}<br>
                                    {{ $order->address->address_line }}<br>
                                    {{ $order->address->zip_code }} {{ $order->address->city }}<br>
                                    {{ $order->address->country }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <tr class="heading">
                <td>Produit</td>
                <td>Prix unitaire</td>
                <td>Quantité</td>
                <td>Total</td>
            </tr>

            @foreach($order->items as $item)
            <tr class="item">
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->unit_price }} FCFA</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->quantity * $item->unit_price }} FCFA</td>
            </tr>
            @endforeach

            <tr class="total">
                <td colspan="3"></td>
                <td>Total: {{ $order->total_price }} FCFA</td>
            </tr>
        </table>
    </div>
</body>
</html>