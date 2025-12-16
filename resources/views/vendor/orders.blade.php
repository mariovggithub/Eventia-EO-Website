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
            
            <div class="mt-4 pt-3 border-t flex justify-between items-center">
                <div>
                    <div class="text-sm">Diurus oleh EO: <span class="font-semibold">{{ $order->eventOrganizer->name }}</span></div>
                    <div class="text-sm">Customer: <span class="font-semibold">{{ $order->user->name }}</span></div>
                </div>
                
                @if($order->isApproved())
                <button onclick="openChatModal({{ $order->id }}, '{{ $order->user->name }} & {{ $order->eventOrganizer->name }}')" 
                    class="px-4 py-2 rounded-lg bg-accent text-white text-sm hover:opacity-90">
                    💬 Buka Chat
                </button>
                @endif
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

{{-- Chat Modal --}}
<div id="chat-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl w-full max-w-2xl mx-4 flex flex-col" style="max-height: 80vh;">
        <div class="flex justify-between items-center p-4 border-b">
            <h4 class="font-bold text-lg" id="chat-modal-title">Chat Order</h4>
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
// Chat Modal Functions
let currentOrderId = null;
let chatRefreshInterval = null;

function openChatModal(orderId, participants) {
    currentOrderId = orderId;
    document.getElementById('chat-modal-title').textContent = `Chat dengan ${participants}`;
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
                        <div class="p-3 rounded-lg ${chat.is_own ? 'bg-purple-600 text-white' : 'bg-white border border-gray-200'}">
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