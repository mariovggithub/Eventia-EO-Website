@extends('layouts.app')
@section('title', 'Pembayaran')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl p-8 card-shadow">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-primary">Pembayaran</h2>
            <p class="text-gray-500 mt-2">Order #{{ $order->id }} - {{ $order->eventType->name }}</p>
        </div>

        {{-- Order Summary --}}
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="font-bold mb-4">Ringkasan Pesanan</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Tanggal Event</span>
                    <span class="font-semibold">{{ $order->event_date->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kapasitas</span>
                    <span class="font-semibold">{{ $order->capacity }} orang</span>
                </div>
                @if(!$order->self_organized)
                <div class="flex justify-between">
                    <span>Event Organizer</span>
                    <span class="font-semibold">{{ $order->eventOrganizer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Jasa EO</span>
                    <span class="font-semibold">Rp {{ number_format($order->eventOrganizer->price_min, 0, ',', '.') }}</span>
                </div>
                @endif
                
                @if($order->vendor_choice === 'ala' && $order->vendors->count() > 0)
                <div class="pt-2 border-t">
                    <div class="font-semibold mb-2">Vendor A la Carte:</div>
                    @foreach($order->vendors as $vendor)
                    <div class="flex justify-between text-xs ml-4">
                        <span>{{ $vendor->name }}</span>
                        <span>{{ $vendor->formatted_price }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="pt-3 border-t flex justify-between text-lg font-bold text-primary">
                    <span>Total Pembayaran</span>
                    <span>{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Form --}}
        <form method="POST" action="{{ route('order.payment.process', $order) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="bank_transfer" class="mr-3" required>
                            <div class="flex-1">
                                <div class="font-semibold">Transfer Bank</div>
                                <div class="text-sm text-gray-500">BCA, Mandiri, BNI, BRI</div>
                            </div>
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                            </svg>
                        </label>

                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="credit_card" class="mr-3" required>
                            <div class="flex-1">
                                <div class="font-semibold">Kartu Kredit/Debit</div>
                                <div class="text-sm text-gray-500">Visa, Mastercard, JCB</div>
                            </div>
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                            </svg>
                        </label>

                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="e_wallet" class="mr-3" required>
                            <div class="flex-1">
                                <div class="font-semibold">E-Wallet</div>
                                <div class="text-sm text-gray-500">GoPay, OVO, Dana, ShopeePay</div>
                            </div>
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                    <strong>Catatan:</strong> Ini adalah pembayaran simulasi (mock). Anda akan langsung diarahkan ke halaman konfirmasi setelah klik tombol di bawah.
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('order.show', $order) }}" class="flex-1 px-6 py-3 rounded-lg border text-center hover:bg-gray-50">
                        Kembali
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                        Bayar {{ $order->formatted_total }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection