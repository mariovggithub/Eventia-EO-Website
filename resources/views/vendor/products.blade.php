@extends('layouts.app')
@section('title', 'Vendor - Products')

@section('content')
<section class="bg-white rounded-xl p-6 card-shadow">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-primary">Daftar Produk (Vendor Dashboard)</h3>
        <button onclick="openProductModal()" class="px-4 py-2 rounded text-white text-sm bg-accent hover:opacity-90">Tambah Produk Baru</button>
    </div>
    
    @if($products->isEmpty())
        <div class="text-gray-500 p-8 border rounded-lg text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p>Belum ada produk yang diupload</p>
            <button onclick="openProductModal()" class="mt-3 px-4 py-2 rounded bg-accent text-white hover:opacity-90">Upload Produk Pertama</button>
        </div>
    @else
        @php
            $groupedProducts = $products->groupBy(function($product) {
                return $product->category->name ?? 'Uncategorized';
            });
        @endphp
        
        <div class="space-y-6">
            @foreach($groupedProducts as $categoryName => $categoryProducts)
            <div>
                <h4 class="font-semibold mb-3 text-primary">{{ $categoryName }}</h4>
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach($categoryProducts as $product)
                    <div class="p-4 border rounded-lg hover:shadow-md transition-shadow">
                        <img src="{{ asset($product->image) }}" class="w-full h-36 object-cover rounded mb-3" onerror="this.src='https://placehold.co/400x200/1F6B7E/fff?text=Product'">
                        <div class="font-semibold">{{ $product->name }}</div>
                        <div class="text-sm text-primary font-semibold">{{ $product->formatted_price }}</div>
                        <div class="text-xs text-gray-500">Kuantitas: {{ $product->quantity }} {{ $categoryName == 'Souvenir' ? 'pcs' : 'paket' }}</div>
                        <div class="mt-3">
                            <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('Yakin hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 rounded bg-secondary text-white text-xs hover:opacity-90">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</section>

{{-- Create Product Modal --}}
<div id="product-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold">Tambah Produk Vendor Baru</h4>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('vendor.products.store') }}">
            @csrf
            <div class="mt-4 space-y-3">
                <select name="vendor_category_id" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="name" placeholder="Nama Produk (Ex: Paket Gold Catering)" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <input type="number" name="price" placeholder="Harga (IDR)" min="0" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <input type="number" name="quantity" placeholder="Kuantitas (Ex: 100 untuk souvenir, 1 untuk paket)" min="1" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <input type="text" name="image" placeholder="Link Gambar (opsional)" value="https://placehold.co/400x200/925E30/fff?text=New+Product" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeProductModal()" class="px-4 py-2 rounded border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded text-white bg-accent hover:opacity-90">Upload Produk</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openProductModal() {
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
}

function closeProductModal() {
    document.getElementById('product-modal').classList.add('hidden');
    document.getElementById('product-modal').classList.remove('flex');
}

document.getElementById('product-modal').addEventListener('click', function(e) {
    if (e.target === this) closeProductModal();
});
</script>
@endpush
@endsection