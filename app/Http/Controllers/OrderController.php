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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrderController extends Controller
{
    //
    public function store(Request $request)
    {
        $user = $request->user();
        $isGuest = !$user;

        // Validation adaptée selon le type d'utilisateur
        $validationRules = [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'is_self_receiving' => 'required|boolean',
            'address' => 'array', // Toujours un array, pas de required_if
        ];

        // Validation pour les invités
        if ($isGuest) {
            $validationRules = array_merge($validationRules, [
                'guest' => 'required|array',
                'guest.name' => 'required|string|max:255',
                'guest.email' => 'nullable|email|max:255',
                'guest.phone' => 'required|string|max:255',
                'create_account' => 'nullable|boolean',
                'password' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->input('create_account', false);
                    }),
                    'min:8',
                    'confirmed'
                ],
            ]);
        }

        // Validation conditionnelle pour les champs d'adresse
        // Seulement si is_self_receiving est false
        if (!$request->input('is_self_receiving', true)) {
            $validationRules = array_merge($validationRules, [
                'address.full_name' => 'required|string|max:255',
                'address.address_line' => 'required|string|max:255',
                'address.city' => 'required|string|max:255',
                'address.zip_code' => 'nullable|string|max:255',
                'address.country' => 'required|string|max:255',
                'address.phone' => 'required|string|max:255'
            ]);
        }

        $validated = $request->validate($validationRules);

        return DB::transaction(function () use ($validated, $user, $isGuest, $request) {
            $guest = null;
            $finalUser = $user;

            // Gestion des invités
            if ($isGuest) {
                // Vérifier si un utilisateur existe déjà avec cet email
                $existingUser = null;
                if (!empty($validated['guest']['email'])) {
                    $existingUser = User::where('email', $validated['guest']['email'])->first();
                }

                // Si création de compte demandée et pas d'utilisateur existant
                if (!empty($validated['create_account']) && !$existingUser) {
                    $finalUser = User::create([
                        'name' => $validated['guest']['name'],
                        'email' => $validated['guest']['email'] ?? null,
                        'phone' => $validated['guest']['phone'],
                        'password' => Hash::make($validated['password']),
                        'role' => 'user'
                    ]);

                    // Connecter automatiquement l'utilisateur
                    Auth::login($finalUser);
                    $isGuest = false;

                    // Déclencher l'événement de connexion pour migrer le panier
                    event('user-logged-in', $finalUser);
                } else {
                    // Créer un enregistrement invité
                    $guest = Guest::create([
                        'name' => $validated['guest']['name'],
                        'email' => $validated['guest']['email'] ?? null,
                        'phone' => $validated['guest']['phone'],
                    ]);
                }
            }

            // Créer l'adresse
            $addressData = [
                'user_id' => $finalUser?->id,
                'guest_id' => $guest?->id,
                'country' => $validated['is_self_receiving'] ? 'Côte d\'Ivoire' : ($validated['address']['country'] ?? 'Côte d\'Ivoire')
            ];

            if ($validated['is_self_receiving']) {
                if ($finalUser) {
                    // Utiliser les données de l'utilisateur connecté
                    $addressData = array_merge($addressData, [
                        'full_name' => $finalUser->name,
                        'address_line' => $finalUser->address?->address_line ?? 'Non renseigné',
                        'city' => $finalUser->address?->city ?? 'Non renseigné',
                        'zip_code' => $finalUser->address?->zip_code ?? '',
                        'phone' => $finalUser->phone
                    ]);
                } else {
                    // Utiliser les données de l'invité
                    $addressData = array_merge($addressData, [
                        'full_name' => $guest->name,
                        'address_line' => 'À confirmer par téléphone',
                        'city' => 'À confirmer par téléphone',
                        'zip_code' => '',
                        'phone' => $guest->phone
                    ]);
                }
            } else {
                // Utiliser l'adresse fournie
                $addressData = array_merge($addressData, [
                    'full_name' => $validated['address']['full_name'],
                    'address_line' => $validated['address']['address_line'],
                    'city' => $validated['address']['city'],
                    'zip_code' => $validated['address']['zip_code'] ?? null,
                    'phone' => $validated['address']['phone']
                ]);
            }

            $address = Address::create($addressData);

            // Générer un numéro de commande
            $orderNumber = 'CMD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Créer la commande
            $order = Order::create([
                'user_id' => $finalUser?->id,
                'guest_id' => $guest?->id,
                'address_id' => $address->id,
                'order_number' => $orderNumber,
                'total_price' => 0, // Calculé plus bas
                'status' => 'pending',
            ]);

            $totalPrice = 0;
            $orderItems = [];
            $products = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $totalPrice += $product->price * $item['quantity'];

                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'image_url' => $product->image_url,
                ];
            }

            // Mettre à jour le prix total
            $order->update(['total_price' => $totalPrice]);

            // Créer tous les items en une seule requête
            OrderItem::insert($orderItems);

            // Envoyer l'email de confirmation si email disponible
            $order->load(['items.product', 'address', 'user', 'guest']);

            $recipientEmail = $finalUser?->email ?? $guest?->email;
            $recipientName = $finalUser?->name ?? $guest?->name;

            if ($recipientEmail) {
                try {
                    Mail::to($recipientEmail)->send(new OrderConfirmation($order, $recipientName));
                } catch (\Exception $e) {
                    Log::error('Failed to send order confirmation email: ' . $e->getMessage());
                }
            }

            // Vider le panier après commande réussie
            if ($finalUser) {
                $finalUser->cartItems()->delete();
            } else {
                $guestCartId = $request->cookie('guest_cart_id');
                if ($guestCartId) {
                    $guestCartKey = 'guest_cart_' . $guestCartId;
                    $request->session()->forget($guestCartKey);
                }
            }

            return Inertia::render('Order/Success', [
                'order' => [
                    'order_number' => $orderNumber,
                    'total_price' => $totalPrice,
                    'invoice_url' => route('order.invoice', $order->id),
                    'customer_name' => $recipientName,
                    'customer_email' => $recipientEmail,
                ],
                'products' => $products,
                'user_created' => !$isGuest && !$user,
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
