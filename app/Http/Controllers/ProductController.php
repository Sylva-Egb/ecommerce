<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(4);

        if(!$request->user() || $request->user()->role !== 'admin') {
            return Inertia::render('Public/Products/Index', [
                'products' => $products,
                'canLogin' => app('router')->has('login'),
                'canRegister' => app('router')->has('register'),
            ]);
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'categorie_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'slug' => 'required|string|max:255',
            'is_vedette' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        Product::create($validated);

        return redirect()->route('products.create')->with('success', 'Produit ajouté !');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_url'] = "/storage/$path";
        }

        $product->update($validated);

        return back()->with('success', 'Produit mis à jour avec succès.');
    }

    public function show(Product $product)
    {
        // Charger la catégorie pour l'afficher aussi si besoin
        $product->load('category');

        return inertia('Public/ProductShow', [
            'product' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé !');
    }
}
?>