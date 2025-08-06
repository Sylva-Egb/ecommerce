<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem; // Nouveau modèle
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB; // Pour les requêtes raw

class CartController extends Controller
{
    protected function getCartKey(Request $request)
    {
        if (Auth::check()) {
            return 'user_cart_'.Auth::id();
        }

        // Pour les invités, utilisez toujours le même cookie guest_cart_id
        $guestCartId = $request->cookie('guest_cart_id');
        if (!$guestCartId) {
            $guestCartId = uniqid();
        }

        // Stockez l'ID dans un cookie long terme
        Cookie::queue('guest_cart_id', $guestCartId, 60*24*30); // 30 jours

        return 'guest_cart_'.$guestCartId;
    }

    public function index(Request $request)
    {
        $cartKey = $this->getCartKey($request);

        // Pour les utilisateurs authentifiés, vérifier la base de données
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->with('product')->get();

            return response()->json([
                'items' => $cartItems->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'image_url' => $item->product->image_url,
                        'quantity' => $item->quantity
                    ];
                }),
                'total' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity)
            ]);
        }

        // Pour les invités, vérifier session + cookie
        $sessionCart = $request->session()->get($cartKey, []);
        $cookieCart = json_decode($request->cookie('cart_items', '[]'), true);

        // Fusionner les paniers (priorité à la session)
        $mergedCart = array_merge($cookieCart, $sessionCart);

        $items = collect($mergedCart)->map(function ($item, $productId) {
            $product = Product::find($productId);
            return $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'quantity' => $item['quantity']
            ] : null;
        })->filter()->values();

        $response = response()->json([
            'items' => $items,
            'total' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
        ]);


        // Stocker dans un cookie si invité
        if (!Auth::check()) {
            $response->cookie('cart_items', json_encode($mergedCart), 60*24*30)
                    ->cookie('guest_cart_id', explode('_', $cartKey)[2], 60*24*30);
        }

        return $response;
    }

    public function addItem(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));
        $cartKey = $this->getCartKey($request);

        // Pour les utilisateurs authentifiés
        if (Auth::check()) {
            $cartItem = Auth::user()->cartItems()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => DB::raw('quantity + '.$request->input('quantity', 1))]
            );

            return $this->index($request);
        }

        // Pour les invités
        $sessionCart = $request->session()->get($cartKey, []);

        $quantity = $request->input('quantity', 1);

        if (isset($sessionCart[$product->id])) {
            $sessionCart[$product->id]['quantity'] += $quantity;
        } else {
            $sessionCart[$product->id] = [
                'quantity' => $quantity,
                'added_at' => now()->toDateTimeString()
            ];
        }

        $request->session()->put($cartKey, $sessionCart);

        // Retournez directement les items de la session
        $items = collect($sessionCart)->map(function ($item, $productId) {
            $product = Product::find($productId);
            return $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'quantity' => $item['quantity']
            ] : null;
        })->filter()->values();

        return response()->json([
            'items' => $items,
            'total' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
        ])->cookie('cart_items', json_encode($sessionCart), 60*24*30)
        ->cookie('guest_cart_id', explode('_', $cartKey)[2], 60*24*30);
    }

    public function updateItem(Request $request, Product $product)
    {
        $cartKey = $this->getCartKey($request);

        if (Auth::check()) {
            Auth::user()->cartItems()
                ->where('product_id', $product->id)
                ->update(['quantity' => max(1, $request->input('quantity'))]);

            return $this->index($request);
        }

        $sessionCart = $request->session()->get($cartKey, []);
        $cookieCart = json_decode($request->cookie('cart_items', '[]'), true);

        if (isset($sessionCart[$product->id])) {
            $sessionCart[$product->id]['quantity'] = max(1, $request->input('quantity'));
            $request->session()->put($cartKey, $sessionCart);
        }

        $response = $this->index($request);
        return $response->cookie('cart_items', json_encode($sessionCart), 60*24*30);
    }

    public function removeItem(Request $request, Product $product)
    {
        $cartKey = $this->getCartKey($request);

        if (Auth::check()) {
            Auth::user()->cartItems()->where('product_id', $product->id)->delete();
            return $this->index($request);
        }

        $sessionCart = $request->session()->get($cartKey, []);
        $cookieCart = json_decode($request->cookie('cart_items', '[]'), true);

        unset($sessionCart[$product->id]);
        $request->session()->put($cartKey, $sessionCart);

        $response = $this->index($request);
        return $response->cookie('cart_items', json_encode($sessionCart), 60*24*30);
    }

    public function clearCart(Request $request)
    {
        $cartKey = $this->getCartKey($request);

        if (Auth::check()) {
            Auth::user()->cartItems()->delete();
            return response()->json(['message' => 'Cart cleared']);
        }

        $request->session()->forget($cartKey);
        $response = response()->json(['message' => 'Cart cleared']);
        return $response->withoutCookie('cart_items');
    }

    public function migrateGuestCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $guestCartId = $request->cookie('guest_cart_id');
        if ($guestCartId) {
            $sessionCart = $request->session()->get($guestCartId, []);
            $cookieCart = json_decode($request->cookie('cart_items', '[]'), true);
            $mergedCart = array_merge($cookieCart, $sessionCart);

            foreach ($mergedCart as $productId => $item) {
                Auth::user()->cartItems()->updateOrCreate(
                    ['product_id' => $productId],
                    ['quantity' => DB::raw('quantity + '.$item['quantity'])]
                );
            }

            $request->session()->forget($guestCartId);
            $response = $this->index($request);
            return $response->withoutCookie('cart_items')->withoutCookie('guest_cart_id');
        }

        return $this->index($request);
    }
}
