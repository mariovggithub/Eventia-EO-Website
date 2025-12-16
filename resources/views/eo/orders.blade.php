@extends('layouts.app')
@section('title', 'EO - Orders')

@section('content')
<section class="bg-white rounded-xl p-6 card-shadow">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-primary">Orders Masuk (EO Dashboard)</h3>
        <div class="flex gap-2 text-sm">
            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded">Pending: {{ $orders->where('approval_status', 'pending')->count() }}</span>
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded">Approved: {{ $orders->where('approval_status', 'approved')->count() }}</span>
        </div>
    </div>

    {{-- Tabs for filtering --}}
    <div class="flex gap-2 mb-6 border-b" x-data="{ tab: 'all' }">
        <button @click="tab = 'all'" :class="tab === 'all' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-500'" class="px-4 py-2 font-semibold">
            Semua ({{ $orders->count() }})
        </button>
        <button @click="tab = 'pending'" :class="tab === 'pending' ? 'border-b-2 border-yellow-600 text-yellow-600' : 'text-gray-500'" class="px-4 py-2 font-semibold">
            Menunggu Persetujuan ({{ $orders->where('approval_status', 'pending')->count() }})
        </button>
        <button @click="tab = 'approved'" :class="tab === 'approved' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-500'" class="px-4 py-2 font-semibold">
            Disetujui ({{ $orders->where('approval_status', 'approved')->count() }})
        </button>
    </div>

    <div class="space-y-4" x-data="{ tab: 'all' }">
        @forelse($orders as $order)
        <div class="p-4 border rounded-lg hover:shadow-md transition-shadow"
             x-show="tab === 'all' || (tab === 'pending' && '{{ $order->approval_status }}' === 'pending') || (tab === 'approved' && '{{ $order->approval_status }}' === 'approved')">
            
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="font-bold text-lg">
                        {{ $order->eventType->name ?? 'Event Tidak Spesifik' }}
                        @if($order->self_organized)
                            <span class="ml-2 px-2 py-1 text-xs rounded bg-amber-100 text-amber-700">Self-Organized</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">Order #{{ $order->id }} • {{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                <div class="flex flex-col gap-2 items-end">
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
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-gray-700 mb-3">
                <div>
                    <span class="font-semibold">Customer:</span> {{ $order->user->name }}
                </div>
                <div>
                    <span class="font-semibold">Tanggal Event:</span> {{ \Carbon\Carbon::parse($order->event_date)->format('d M Y') }}
                </div>
                <div>
                    <span class="font-semibold">Kapasitas:</span> {{ $order->capacity }} orang
                </div>
                <div>
                    <span class="font-semibold">Vendor:</span> {{ $order->vendor_choice == 'package' ? 'Paket EO' : 'A la Carte' }}
                </div>
            </div>
            
            @if($order->vendor_choice == 'ala' && $order->vendors->count() > 0)
            <div class="mb-3">
                <span class="font-semibold text-sm">Vendors A la Carte:</span>
                <ul class="list-disc ml-5 text-xs text-gray-600">
                    @foreach($order->vendors as $vendor)
                        <li>{{ $vendor->name }} ({{ $vendor->category->name ?? 'N/A' }}) — Rp {{ number_format($vendor->price, 0, ',', '.') }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            @if($order->isRejected())
            <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm">
                <span class="font-semibold text-red-700">Alasan Penolakan:</span> {{ $order->rejection_reason }}
            </div>
            @endif

            <div class="pt-3 border-t flex justify-between items-center">
    <div class="font-bold text-primary">Total: {{ $order->formatted_total }}</div>
    <div class="flex gap-2">
        <a href="{{ route('order.show', $order) }}" class="px-3 py-2 rounded bg-secondary text-white text-sm hover:opacity-90">
            Lihat Detail
        </a>
        
        @if($order->isPending())
        <button onclick="openApproveModal({{ $order->id }})" class="px-3 py-2 rounded bg-green-600 text-white text-sm hover:bg-green-700">
            ✓ Setujui
        </button>
        <button onclick="openRejectModal({{ $order->id }})" class="px-3 py-2 rounded bg-red-600 text-white text-sm hover:bg-red-700">
            ✗ Tolak
        </button>
        @endif

        @if($order->isApproved())
        <button onclick="openChatModal({{ $order->id }}, '{{ $order->user->name }}')" 
            class="px-3 py-2 rounded border text-gray-700 text-sm hover:bg-gray-50">
            💬 Chat
        </button>
        @endif

        @if($order->isPaid() && $order->status !== 'completed')
        <form method="POST" action="{{ route('eo.orders.complete', $order) }}" class="inline" onsubmit="return confirm('Tandai order ini sebagai selesai?')">
            @csrf
            <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                ✓ Tandai Selesai
            </button>
        </form>
        @endif

        @if($order->isCompleted())
        <span class="px-3 py-2 rounded bg-green-100 text-green-700 text-sm font-semibold">
            ✓ Selesai
        </span>
        @endif
    </div>
</div>
        </div>
        @empty
        <div class="text-gray-500 p-8 border rounded-lg text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p>Belum ada order masuk</p>
        </div>
        @endforelse
    </div>
</section>

{{-- Approve Modal --}}
<div id="approve-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Setujui Pesanan & Tentukan Harga</h4>
        <form id="approve-form" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Penawaran Harga (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="negotiated_price" required min="1000000" step="100000"
                        class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600"
                        placeholder="Contoh: 50000000">
                    <p class="text-xs text-gray-500 mt-1">Minimal Rp 1.000.000</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Rincian Biaya (Opsional)
                    </label>
                    <textarea name="price_breakdown" rows="4"
                        class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600"
                        placeholder="Contoh:&#10;- Venue: Rp 20.000.000&#10;- Catering: Rp 15.000.000&#10;- Dekorasi: Rp 10.000.000&#10;- Dokumentasi: Rp 5.000.000"></textarea>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeApproveModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                    Setujui & Kirim Penawaran
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Tolak Pesanan</h4>
        <form id="reject-form" method="POST">
            @csrf
            <textarea name="rejection_reason" rows="4" placeholder="Jelaskan alasan penolakan kepada customer..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Tolak Pesanan</button>
            </div>
        </form>
    </div>
</div>

{{-- Chat Modal --}}
<div id="chat-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl w-full max-w-2xl mx-4 flex flex-col" style="max-height: 80vh;">
        <div class="flex justify-between items-center p-4 border-b">
            <h4 class="font-bold text-lg" id="chat-modal-title">Chat dengan Customer</h4>
            <button onclick="closeChatModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="chat-messages-container" class="flex-1 overflow-y-auto p-4 bg-gray-50" style="min-height: 400px;">
            <div id="chat-messages" class="space-y-3"></div>
        </div>
        
        <div class="p-4 border-t bg-white">
            <form id="chat-form" onsubmit="sendChatMessage(event)" class="flex gap-2">
                <input type="hidden" id="chat-order-id">
                <input type="text" id="chat-message-input" 
                    class="flex-1 border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" 
                    placeholder="Ketik pesan..." required>
                <button type="submit" class="px-6 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                    Kirim
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Approve Modal Functions
function openApproveModal(orderId) {
    document.getElementById('approve-form').action = `/eo/orders/${orderId}/approve`;
    document.getElementById('approve-modal').classList.remove('hidden');
    document.getElementById('approve-modal').classList.add('flex');
}

function closeApproveModal() {
    document.getElementById('approve-modal').classList.add('hidden');
    document.getElementById('approve-modal').classList.remove('flex');
}

document.getElementById('approve-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeApproveModal();
});

// Reject Modal Functions
function openRejectModal(orderId) {
    document.getElementById('reject-form').action = `/eo/orders/${orderId}/reject`;
    document.getElementById('reject-modal').classList.remove('hidden');
    document.getElementById('reject-modal').classList.add('flex');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.getElementById('reject-modal').classList.remove('flex');
}

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

// Chat Modal Functions
let currentOrderId = null;
let chatRefreshInterval = null;

function openChatModal(orderId, customerName) {
    currentOrderId = orderId;
    document.getElementById('chat-modal-title').textContent = `Chat dengan ${customerName}`;
    document.getElementById('chat-order-id').value = orderId;
    document.getElementById('chat-modal').classList.remove('hidden');
    document.getElementById('chat-modal').classList.add('flex');
    
    loadChatMessages(orderId);
    
    // Auto-refresh every 5 seconds
    chatRefreshInterval = setInterval(() => {
        loadChatMessages(orderId);
    }, 5000);
}

function closeChatModal() {
    document.getElementById('chat-modal').classList.add('hidden');
    document.getElementById('chat-modal').classList.remove('flex');
    currentOrderId = null;
    document.getElementById('chat-message-input').value = '';
    
    if (chatRefreshInterval) {
        clearInterval(chatRefreshInterval);
        chatRefreshInterval = null;
    }
}

function loadChatMessages(orderId) {
    fetch(`/order/${orderId}/chat/messages`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('chat-messages');
            container.innerHTML = data.chats.map(chat => `
                <div class="flex ${chat.is_own ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-xs">
                        <div class="text-xs text-gray-500 mb-1 ${chat.is_own ? 'text-right' : ''}">
                            ${chat.user_name}
                            <span class="px-1 py-0.5 rounded text-xs ${
                                chat.user_role === 'eo' ? 'bg-teal-100 text-teal-700' :
                                chat.user_role === 'vendor' ? 'bg-purple-100 text-purple-700' :
                                'bg-blue-100 text-blue-700'
                            }">${chat.user_role.toUpperCase()}</span>
                        </div>
                        <div class="p-3 rounded-lg ${chat.is_own ? 'bg-teal-600 text-white' : 'bg-white border border-gray-200'}">
                            <p class="text-sm whitespace-pre-line">${chat.message}</p>
                        </div>
                        <div class="text-xs text-gray-400 mt-1 ${chat.is_own ? 'text-right' : ''}">
                            ${chat.created_at}
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Scroll to bottom
            const messagesContainer = document.getElementById('chat-messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function sendChatMessage(e) {
    e.preventDefault();
    const orderId = document.getElementById('chat-order-id').value;
    const message = document.getElementById('chat-message-input').value;
    
    fetch(`/order/${orderId}/chat`, {
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
            document.getElementById('chat-message-input').value = '';
            loadChatMessages(orderId);
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Gagal mengirim pesan. Silakan coba lagi.');
    });
}

// Close modal on outside click
document.getElementById('chat-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeChatModal();
});
</script>
@endpush
@endsection