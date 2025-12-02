@extends('layouts.app')
@section('title', 'Vendor - Orders')

@section('content')
<section class="bg-white rounded-xl p-6 card-shadow">
    <h3 class="text-xl font-bold mb-4 text-primary">Pesanan Masuk (Vendor Dashboard)</h3>
    
    <div class="space-y-4">
        @forelse($orders as $order)
        <div class="p-4 border rounded-lg hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div class="font-bold text-lg mb-2">
                    {{ $order->eventType->name ?? 'Event Tidak Spesifik' }}
                    <span class="ml-2 px-2 py-1 text-xs rounded 
                        @if($order->status == 'booked') bg-blue-100 text-blue-700
                        @elseif($order->status == 'ongoing') bg-yellow-100 text-yellow-700
                        @elseif($order->status == 'completed') bg-green-100 text-green-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="text-xs text-gray-400">Order #{{ $order->id }}</div>
            </div>
            
            <div class="text-sm text-gray-700">
                <span class="font-semibold">Tanggal Acara:</span> {{ \Carbon\Carbon::parse($order->event_date)->format('d M Y') }}
            </div>
            
            <div class="mt-3">
                <span class="font-semibold text-sm">Produk Anda yang Dipesan:</span>
                <ul class="list-disc ml-5 text-xs text-gray-600">
                    @foreach($order->vendors->whereIn('id', $productIds) as $vendor)
                        <li>{{ $vendor->name }} ({{ $vendor->quantity }} {{ $vendor->category->name == 'Souvenir' ? 'pcs' : 'paket' }}) — Rp {{ number_format($vendor->price, 0, ',', '.') }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div class="mt-4 pt-3 border-t">
                <div class="text-sm">Diurus oleh EO: <span class="font-semibold">{{ $order->eventOrganizer->name }}</span></div>
                <div class="text-sm">Customer: <span class="font-semibold">{{ $order->user->name }}</span></div>
            </div>
        </div>
        @empty
        <div class="text-gray-500 p-8 border rounded-lg text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p>Belum ada pesanan untuk produk Anda</p>
            <p class="text-xs mt-1">Pesanan akan muncul ketika customer memilih produk Anda secara A la Carte</p>
        </div>
        @endforelse
    </div>
</section>
@endsection