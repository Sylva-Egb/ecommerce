<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Guest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\OrderConfirmation;
use App\Mail\AccountCreated;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf; // Import correct pour Laravel 8+
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'sometimes|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',

            'product_id' => 'required_without:items|exists:products,id',
            'quantity' => 'required_without:items|integer|min:1',

            'create_account' => 'boolean',
            'password' => 'required_if:create_account,true|min:8|nullable',
            'is_self_receiving' => 'required|boolean',

            'guest.name' => 'required|string|max:255',
            'guest.email' => 'nullable|email|max:255',
            'guest.phone' => 'required|string|max:255',

            'address.full_name' => [
                'required_if:is_self_receiving,false',
                'string',
                'max:255',
                'nullable'
            ],
            'address.address_line' => [
                'required_if:is_self_receiving,false',
                'string',
                'max:255',
                'nullable'
            ],
            'address.city' => [
                'required_if:is_self_receiving,false',
                'string',
                'max:255',
                'nullable'
            ],
            'address.zip_code' => 'nullable|string|max:255',
            'address.country' => [
                'required_if:is_self_receiving,false',
                'string',
                'max:255',
                'nullable'
            ],
            'address.phone' => [
                'required_if:is_self_receiving,false',
                'string',
                'max:255',
                'nullable'
            ],
        ]);

        return DB::transaction(function () use ($validated) {
            $user = null;
            $accountCreated = false;
            $temporaryPassword = null;

            // Créer un compte utilisateur si demandé
            if ($validated['create_account'] && $validated['guest']['email']) {
                $temporaryPassword = $validated['password'] ?? Str::random(10);

                $user = User::create([
                    'name' => $validated['guest']['name'],
                    'email' => $validated['guest']['email'],
                    'password' => Hash::make($temporaryPassword),
                    'phone' => $validated['guest']['phone'],
                ]);

                $accountCreated = true;
            }

            // Créer le guest
            $guest = Guest::create([
                'name' => $validated['guest']['name'],
                'email' => $validated['guest']['email'],
                'phone' => $validated['guest']['phone'],
                'user_id' => $user ? $user->id : null,
            ]);

            // Créer l'adresse
            $addressData = [
                'user_id' => $user ? $user->id : null,
                'guest_id' => $user ? null : $guest->id,
                'country' => $validated['address']['country'] ?? 'Côte d\'Ivoire',
            ];

            if ($validated['is_self_receiving']) {
                $addressData = array_merge($addressData, [
                    'full_name' => $validated['guest']['name'],
                    'address_line' => $validated['address']['address_line'] ?? '',
                    'city' => $validated['address']['city'] ?? '',
                    'zip_code' => $validated['address']['zip_code'] ?? '',
                    'phone' => $validated['guest']['phone'],
                ]);
            } else {
                $addressData = array_merge($addressData, [
                    'full_name' => $validated['address']['full_name'],
                    'address_line' => $validated['address']['address_line'],
                    'city' => $validated['address']['city'],
                    'zip_code' => $validated['address']['zip_code'],
                    'phone' => $validated['address']['phone'],
                ]);
            }

            $address = Address::create($addressData);

            // Générer un numéro de commande
            $orderNumber = 'CMD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Créer la commande
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'guest_id' => $user ? null : $guest->id,
                'address_id' => $address->id,
                'order_number' => $orderNumber,
                'total_price' => 0, // Calculé plus bas
                'status' => 'pending',
            ]);

            $totalPrice = 0;
            $orderItems = [];

            // Gestion des items (nouveau format avec tableau)
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $totalPrice += $product->price * $item['quantity'];

                    $orderItems[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                    ];
                }
            }
            // Rétro-compatibilité avec ancien format mono-produit
            else {
                $product = Product::findOrFail($validated['product_id']);
                $totalPrice = $product->price * $validated['quantity'];

                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'unit_price' => $product->price,
                ];
            }

            // Mettre à jour le prix total
            $order->update(['total_price' => $totalPrice]);

            // Créer tous les items en une seule requête
            OrderItem::insert($orderItems);

            // Envoyer les emails
            if ($validated['guest']['email']) {
                // Reload la commande avec les relations
                $order->load(['items.product', 'address']);

                Mail::to($validated['guest']['email'])->send(new OrderConfirmation($order));

                if ($user) {
                    Mail::to($user->email)->send(new OrderConfirmation($order));
                }

                if ($accountCreated) {
                    Mail::to($validated['guest']['email'])->send(new AccountCreated($user, $temporaryPassword));
                }
            }

            return Inertia::render('Order/Success', [
                'order' => [
                    'order_number' => $orderNumber,
                    'invoice_url' => route('order.invoice', $order->id),
                    'account_created' => $accountCreated
                ],
                'products' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product->name,
                        'image_url' => $item->product->image_url,
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity
                    ];
                })
            ]);
        });
    }

    public function index()
    {
        $orders = Order::with(['user', 'guest', 'address', 'items.product'])
            ->when(Auth::user()->role === 'user', function ($query) {
                return $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'canEdit' => Auth::user()->role === 'admin',
        ]);
    }

    public function show(Order $order)
    {
        // Vérification des permissions
        if (Auth::user()->role === 'user' && $order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['user', 'guest', 'address', 'items.product']);

        $products = Auth::user()->role === 'admin'
            ? Product::select('id', 'name', 'price')->get()
            : [];

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'products' => $products,
            'canEdit' => Auth::user()->role === 'admin',
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Envoi des emails de notification
        if ($oldStatus !== $request->status) {
            $email = $order->user?->email ?? $order->guest?->email;

            if ($email) {
                Mail::to($email)->send(new OrderStatusUpdated($order));
            }

            // Envoi à l'admin
            $adminEmail = config('mail.admin.address');
            if (!empty($adminEmail)) {
                Mail::to($adminEmail)->send(new OrderStatusUpdated($order, true));
            }
        }

        return redirect()->back()->with('success', 'Statut mis à jour');
    }

    public function addItem(Request $request, Order $order)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'unit_price' => $product->price,
        ]);

        // Recalculer le total
        $this->updateOrderTotal($order);

        return redirect()->back()->with('success', 'Article ajouté');
    }

    public function removeItem(OrderItem $item)
    {
        $order = $item->order;
        $item->delete();

        // Recalculer le total
        $this->updateOrderTotal($order);

        return redirect()->back()->with('success', 'Article supprimé');
    }

    protected function updateOrderTotal(Order $order)
    {
        $total = $order->items()->sum(DB::raw('quantity * unit_price'));
        $order->update(['total_price' => $total]);
    }


    public function invoice(Order $order)
    {
        // Générer le PDF de la facture
        $pdf = PDF::loadView('orders.invoice', ['order' => $order]);
        return $pdf->download('facture-' . $order->order_number . '.pdf');
    }

}
