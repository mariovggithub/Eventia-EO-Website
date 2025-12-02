<?php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use App\Models\VendorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index() {
        $user = Auth::user();
        $categories = VendorCategory::all();
        $products = VendorProduct::where('user_id', $user->id)->with('category')->get();
        return view('vendor.products', compact('products', 'categories'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'vendor_category_id' => 'required|exists:vendor_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|string'
        ]);

        VendorProduct::create([
            'user_id' => Auth::id(),
            'vendor_category_id' => $validated['vendor_category_id'],
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'image' => $validated['image'] ?? 'https://placehold.co/400x200/925E30/fff?text=Product'
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan!');
    }

    public function destroy(VendorProduct $product) {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }
}