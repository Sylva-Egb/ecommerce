<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected function getGuestCartId(Request $request)
    {
        $guestCartId = $request->cookie('guest_cart_id');
        if (!$guestCartId) {
            $guestCartId = uniqid('guest_', true);
            Cookie::queue('guest_cart_id', $guestCartId, 60*24*30); // 30 jours
        }
        return $guestCartId;
    }

    protected function getCartKey(Request $request)
    {
        if (Auth::check()) {
            return 'user_cart_'.Auth::id();
        }

        $guestCartId = $this->getGuestCartId($request);
        return 'guest_cart_'.$guestCartId;
    }

    public function index(Request $request)
    {
        if (Auth::check()) {
            // Utilisateur connecté : récupérer depuis la base de données
            $cartItems = Auth::user()->cartItems()->with('product')->get();

            $items = $cartItems->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'image_url' => $item->product->image_url,
                    'quantity' => $item->quantity
                ];
            });

            return response()->json([
                'items' => $items,
                'total' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
            ]);
        }

        // Utilisateur non connecté : récupérer depuis la session
        $cartKey = $this->getCartKey($request);
        $sessionCart = $request->session()->get($cartKey, []);

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

        $response = response()->json([
            'items' => $items,
            'total' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
        ]);

        // S'assurer que le cookie guest_cart_id est défini
        $guestCartId = $this->getGuestCartId($request);
        return $response->cookie('guest_cart_id', $guestCartId, 60*24*30);
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $product = Product::findOrFail($request->input('product_id'));
        $quantity = $request->input('quantity', 1);

        if (Auth::check()) {
            // Utilisateur connecté
            $cartItem = Auth::user()->cartItems()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => DB::raw('quantity + '.$quantity)]
            );

            return $this->index($request);
        }

        // Utilisateur non connecté : utiliser la session
        $cartKey = $this->getCartKey($request);
        $sessionCart = $request->session()->get($cartKey, []);

        if (isset($sessionCart[$product->id])) {
            $sessionCart[$product->id]['quantity'] += $quantity;
        } else {
            $sessionCart[$product->id] = [
                'quantity' => $quantity,
                'added_at' => now()->toDateTimeString()
            ];
        }

        $request->session()->put($cartKey, $sessionCart);

        // Retourner la réponse avec les items mis à jour
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

        $guestCartId = $this->getGuestCartId($request);

        return response()->json([
            'items' => $items,
            'total' => $items->sum(fn($item) => $item['price'] * $item['quantity'])
        ])->cookie('guest_cart_id', $guestCartId, 60*24*30);
    }

    public function updateItem(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($productId);
        $quantity = $request->input('quantity');

        if (Auth::check()) {
            Auth::user()->cartItems()
                ->where('product_id', $product->id)
                ->update(['quantity' => $quantity]);
        } else {
            $cartKey = $this->getCartKey($request);
            $sessionCart = $request->session()->get($cartKey, []);

            if (isset($sessionCart[$product->id])) {
                $sessionCart[$product->id]['quantity'] = $quantity;
                $request->session()->put($cartKey, $sessionCart);
            }
        }

        return $this->index($request);
    }

    public function removeItem(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        if (Auth::check()) {
            Auth::user()->cartItems()->where('product_id', $product->id)->delete();
        } else {
            $cartKey = $this->getCartKey($request);
            $sessionCart = $request->session()->get($cartKey, []);
            unset($sessionCart[$product->id]);
            $request->session()->put($cartKey, $sessionCart);
        }

        return $this->index($request);
    }

    public function clearCart(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->cartItems()->delete();
        } else {
            $cartKey = $this->getCartKey($request);
            $request->session()->forget($cartKey);
        }

        $response = response()->json([
            'items' => [],
            'total' => 0,
            'message' => 'Cart cleared'
        ]);

        if (!Auth::check()) {
            $guestCartId = $this->getGuestCartId($request);
            $response->cookie('guest_cart_id', $guestCartId, 60*24*30);
        }

        return $response;
    }

    public function migrateGuestCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $guestCartId = $request->cookie('guest_cart_id');
        if (!$guestCartId) {
            return $this->index($request);
        }

        $guestCartKey = 'guest_cart_'.$guestCartId;
        $sessionCart = $request->session()->get($guestCartKey, []);

        if (empty($sessionCart)) {
            return $this->index($request);
        }

        // Migrer chaque item vers la base de données
        foreach ($sessionCart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                Auth::user()->cartItems()->updateOrCreate(
                    ['product_id' => $productId],
                    ['quantity' => DB::raw('COALESCE(quantity, 0) + '.$item['quantity'])]
                );
            }
        }

        // Nettoyer la session invité
        $request->session()->forget($guestCartKey);

        $response = $this->index($request);
        return $response->withoutCookie('guest_cart_id');
    }
}