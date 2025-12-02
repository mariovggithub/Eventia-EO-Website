@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<div class="bg-white rounded-xl p-6 card-shadow">
    <h3 class="text-2xl font-bold mb-6 text-primary">Pesanan Saya</h3>

    @if($orders->isEmpty())
    <div class="text-center py-12 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p>Belum ada pesanan</p>
        <a href="{{ route('order.create') }}" class="mt-4 inline-block px-6 py-3 rounded-lg bg-accent text-white hover:opacity-90">Buat Pesanan Baru</a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-lg">{{ $order->eventType->name }}</h4>
                    <p class="text-sm text-gray-500">Order #{{ $order->id }} • {{ $order->event_date->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded text-sm font-semibold
                        @if($order->approval_status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($order->approval_status === 'approved') bg-green-100 text-green-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($order->approval_status) }}
                    </span>
                    @if($order->payment_status === 'paid')
                    <span class="ml-2 px-3 py-1 rounded text-sm font-semibold bg-blue-100 text-blue-700">
                        Paid
                    </span>
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-4 gap-3 text-sm text-gray-600 mb-3">
                <div>
                    <span class="font-semibold">Kapasitas:</span> {{ $order->capacity }} orang
                </div>
                <div>
                    <span class="font-semibold">Pengelola:</span> 
                    @if($order->self_organized)
                        <span class="text-amber-600">Self-Organized</span>
                    @else
                        {{ $order->eventOrganizer->name }}
                    @endif
                </div>
                <div>
                    <span class="font-semibold">Vendor:</span> {{ $order->vendor_choice === 'package' ? 'Paket' : 'A la Carte' }}
                </div>
                <div>
                    <span class="font-semibold">Total:</span> <span class="text-primary font-bold">{{ $order->formatted_total }}</span>
                </div>
            </div>

            <div class="flex gap-2 pt-3 border-t">
                <a href="{{ route('order.show', $order) }}" class="px-4 py-2 rounded border border-primary text-primary hover:bg-primary/10">
                    Lihat Detail
                </a>
                
                @if($order->isApproved() && !$order->isPaid())
                <a href="{{ route('order.payment', $order) }}" class="px-4 py-2 rounded bg-accent text-white hover:opacity-90">
                    Bayar Sekarang
                </a>
                @endif

                @if($order->isApproved())
                <a href="{{ route('order.show', $order) }}#chat" class="px-4 py-2 rounded border text-gray-700 hover:bg-gray-50">
                    💬 Chat
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection