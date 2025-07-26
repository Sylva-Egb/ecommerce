<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected function getCartKey(Request $request)
    {
        return Auth::check()
            ? 'user_cart_'.Auth::id()
            : 'guest_cart_'.$request->session()->getId();
    }

    public function index(Request $request)
    {
        $cart = $request->session()->get($this->getCartKey($request), []);

        $products = Product::whereIn('id', array_keys($cart))->get()
            ->map(function ($product) use ($cart) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'quantity' => $cart[$product->id]['quantity']
                ];
            });

        return response()->json([
            'items' => $products,
            'total' => $products->sum(fn($item) => $item['price'] * $item['quantity'])
        ]);
    }

    public function addItem(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));
        $cartKey = $this->getCartKey($request);
        $cart = $request->session()->get($cartKey, []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $request->input('quantity', 1);
        } else {
            $cart[$product->id] = [
                'quantity' => $request->input('quantity', 1),
                'added_at' => now()->toDateTimeString()
            ];
        }

        $request->session()->put($cartKey, $cart);

        return $this->index($request);
    }

    public function updateItem(Request $request, Product $product)
    {
        $cartKey = $this->getCartKey($request);
        $cart = $request->session()->get($cartKey, []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = max(1, $request->input('quantity'));
            $request->session()->put($cartKey, $cart);
        }

        return $this->index($request);
    }

    public function removeItem(Request $request, Product $product)
    {
        $cartKey = $this->getCartKey($request);
        $cart = $request->session()->get($cartKey, []);
        unset($cart[$product->id]);
        $request->session()->put($cartKey, $cart);

        return $this->index($request);
    }

    public function clearCart(Request $request)
    {
        $request->session()->forget($this->getCartKey($request));
        return response()->json(['message' => 'Cart cleared']);
    }
}