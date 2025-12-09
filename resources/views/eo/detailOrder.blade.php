@extends('layouts.app')
@section('title', 'Detail Order #' . $order->id)

@section('content')
<div class="mb-6">
    <a href="{{ route('eo.orders') }}" class="text-amber-700 hover:text-amber-900 flex items-center gap-2">
        ← Kembali ke Dashboard Orders
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Order Details --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Order Info Card --}}
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-primary">{{ $order->eventType->name }}</h3>
                    <p class="text-sm text-gray-500">Order #{{ $order->id }}</p>
                </div>
                <div class="flex flex-col gap-2">
                    <span class="px-3 py-1 rounded text-sm font-semibold
                        @if($order->approval_status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($order->approval_status === 'approved') bg-green-100 text-green-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($order->approval_status) }}
                    </span>
                    @if($order->payment_status === 'paid')
                        <span class="px-3 py-1 rounded text-sm font-semibold bg-blue-100 text-blue-700">Paid</span>
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 text-sm mb-4">
                <div>
                    <div class="text-gray-500">Customer</div>
                    <div class="font-semibold">{{ $order->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $order->user->email }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Tanggal Event</div>
                    <div class="font-semibold">{{ $order->event_date->format('d F Y') }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Kapasitas</div>
                    <div class="font-semibold">{{ $order->capacity }} orang</div>
                </div>
                <div>
                    <div class="text-gray-500">Pilihan Vendor</div>
                    <div class="font-semibold">{{ $order->vendor_choice === 'package' ? 'Paket EO' : 'A la Carte' }}</div>
                </div>
            </div>

            @if($order->vendor_choice === 'ala' && $order->vendors->count() > 0)
            <div class="mt-4 pt-4 border-t">
                <div class="font-semibold mb-2">Vendor yang Dipilih:</div>
                <ul class="list-disc ml-5 text-sm text-gray-600">
                    @foreach($order->vendors as $vendor)
                        <li>{{ $vendor->name }} ({{ $vendor->category->name }})</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($order->negotiated_price)
            <div class="mt-4 p-4 bg-amber-50 rounded-lg">
                <div class="font-semibold mb-2">Penawaran Harga:</div>
                <div class="text-2xl font-bold text-amber-900">{{ $order->formatted_total }}</div>
                @if($order->price_breakdown)
                    <div class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ $order->price_breakdown }}</div>
                @endif
                @if($order->price_agreed)
                    <p class="mt-2 text-green-600 font-semibold">✓ Harga telah disetujui customer</p>
                @endif
            </div>
            @endif

            {{-- Action Buttons for Pending Orders --}}
            @if($order->isPending())
            <div class="mt-4 pt-4 border-t">
                <h4 class="font-semibold mb-3">Aksi Order</h4>
                <form action="{{ route('eo.orders.approve', $order) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penawaran Harga (Rp)</label>
                        <input type="number" name="negotiated_price" required min="1000000" step="1000"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                            placeholder="50000000">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rincian Biaya (Opsional)</label>
                        <textarea name="price_breakdown" rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                            placeholder="Contoh:&#10;- Venue: Rp 20.000.000&#10;- Catering: Rp 15.000.000"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        ✓ Setujui & Kirim Penawaran
                    </button>
                </form>

                <button onclick="openRejectModal()" class="w-full bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                    ✗ Tolak Order
                </button>
            </div>
            @endif

            {{-- Update Price --}}
            @if($order->isApproved() && !$order->isPaid())
            <div class="mt-4 pt-4 border-t">
                <h4 class="font-semibold mb-3">Update Penawaran Harga</h4>
                <form action="{{ route('eo.orders.update-price', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Baru (Rp)</label>
                        <input type="number" name="negotiated_price" required min="1000000" step="1000"
                            value="{{ $order->negotiated_price }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rincian Biaya</label>
                        <textarea name="price_breakdown" rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">{{ $order->price_breakdown }}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700">
                        Update Harga
                    </button>
                </form>
            </div>
            @endif

            {{-- Update Status --}}
            @if($order->isPaid())
            <div class="mt-4 pt-4 border-t">
                <h4 class="font-semibold mb-3">Update Status Order</h4>
                <form action="{{ route('eo.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="ongoing" {{ $order->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Update Status
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- Chat Section --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl p-6 card-shadow sticky top-4">
            <h3 class="text-xl font-bold mb-4 text-primary">💬 Chat</h3>
            
            @if($order->canChat())
                <div id="chat-messages" class="h-96 overflow-y-auto mb-4 p-4 bg-gray-50 rounded-lg space-y-3">
                    @foreach($order->chats as $chat)
                    <div class="flex {{ $chat->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs">
                            <div class="text-xs text-gray-600 mb-1 {{ $chat->user_id === auth()->id() ? 'text-right' : '' }}">
                                <span class="font-semibold">{{ $chat->user->name }}</span>
                                <span class="text-gray-400">({{ strtoupper($chat->user->role) }})</span>
                            </div>
                            <div class="px-4 py-2 rounded-lg {{ $chat->user_id === auth()->id() ? 'bg-amber-600 text-white' : 'bg-white border border-gray-200' }}">
                                <p class="text-sm whitespace-pre-line">{{ $chat->message }}</p>
                            </div>
                            <div class="text-xs text-gray-400 mt-1 {{ $chat->user_id === auth()->id() ? 'text-right' : '' }}">
                                {{ $chat->created_at->format('d M H:i') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <form id="chat-form" onsubmit="sendMessage(event)">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" id="message-input" name="message" required
                            class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                            placeholder="Tulis pesan...">
                        <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700">
                            Kirim
                        </button>
                    </div>
                </form>
            @else
                <p class="text-gray-500 text-center py-8">Chat akan tersedia setelah order disetujui</p>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Tolak Pesanan</h4>
        <form method="POST" action="{{ route('eo.orders.reject', $order) }}">
            @csrf
            <textarea name="rejection_reason" rows="4" placeholder="Jelaskan alasan penolakan kepada customer..." 
                class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Tolak Pesanan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
@if($order->canChat())
function sendMessage(e) {
    e.preventDefault();
    const message = document.getElementById('message-input').value;
    
    fetch('{{ route("order.chat.store", $order) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('message-input').value = '';
            loadMessages();
        }
    });
}

function loadMessages() {
    fetch('{{ route("order.chat.messages", $order) }}')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('chat-messages');
            container.innerHTML = data.chats.map(chat => `
                <div class="flex ${chat.is_own ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-xs">
                        <div class="text-xs text-gray-600 mb-1 ${chat.is_own ? 'text-right' : ''}">
                            <span class="font-semibold">${chat.user_name}</span>
                            <span class="text-gray-400">(${chat.user_role.toUpperCase()})</span>
                        </div>
                        <div class="px-4 py-2 rounded-lg ${chat.is_own ? 'bg-amber-600 text-white' : 'bg-white border border-gray-200'}">
                            <p class="text-sm whitespace-pre-line">${chat.message}</p>
                        </div>
                        <div class="text-xs text-gray-400 mt-1 ${chat.is_own ? 'text-right' : ''}">
                            ${chat.created_at}
                        </div>
                    </div>
                </div>
            `).join('');
            
            container.scrollTop = container.scrollHeight;
        });
}

// Auto-refresh every 5 seconds
setInterval(loadMessages, 5000);

// Scroll to bottom on load
document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
@endif

function openRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
    document.getElementById('reject-modal').classList.add('flex');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}
</script>
@endpush
@endsection