@extends('layouts.app')
@section('title', 'Detail Pesanan #' . $order->id)

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Order Details --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Order Info --}}
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-primary">{{ $order->eventType->name }}</h3>
                    <p class="text-sm text-gray-500">Order #{{ $order->id }}</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded text-sm font-semibold
                        @if($order->approval_status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($order->approval_status === 'approved') bg-green-100 text-green-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($order->approval_status) }}
                    </span>
                    @if($order->payment_status === 'paid')
                        <span class="ml-2 px-3 py-1 rounded text-sm font-semibold bg-blue-100 text-blue-700">Paid</span>
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Customer</div>
                    <div class="font-semibold">{{ $order->user->name }}</div>
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
                    <div class="text-gray-500">Pengelola</div>
                    <div class="font-semibold">
                        @if($order->self_organized)
                            <span class="text-amber-600">Self-Organized</span>
                        @else
                            {{ $order->eventOrganizer->name }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-gray-500">Pilihan Vendor</div>
                    <div class="font-semibold">{{ $order->vendor_choice === 'package' ? 'Paket EO' : 'A la Carte' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Total</div>
                    <div class="font-bold text-primary text-lg">{{ $order->formatted_total }}</div>
                </div>
            </div>

            @if($order->vendor_choice === 'ala' && $order->vendors->count() > 0)
            <div class="mt-4 pt-4 border-t">
                <div class="font-semibold mb-2">Vendor yang Dipilih:</div>
                <ul class="list-disc ml-5 text-sm text-gray-600">
                    @foreach($order->vendors as $vendor)
                        <li>{{ $vendor->name }} ({{ $vendor->category->name }}) — {{ $vendor->formatted_price }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($order->isRejected())
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="font-semibold text-red-700">Alasan Penolakan:</div>
                <p class="text-sm text-red-600 mt-1">{{ $order->rejection_reason }}</p>
            </div>
            @endif

            {{-- Action Buttons for Customer --}}
            @if(auth()->user()->isUser() && auth()->id() === $order->user_id)
                @if($order->isApproved() && !$order->isPaid())
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('order.payment', $order) }}" class="block w-full text-center px-4 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                        Lanjut ke Pembayaran
                    </a>
                </div>
                @endif

                @if($order->canRequestRevision())
                <div class="mt-3">
                    <button onclick="openRevisionModal()" class="w-full px-4 py-2 rounded-lg border border-primary text-primary hover:bg-primary/10">
                        Ajukan Revisi
                    </button>
                </div>
                @endif
            @endif

            {{-- Action Buttons for EO --}}
            @if(auth()->user()->isEO() && $order->eo_id === auth()->user()->eo_id && $order->isPending())
            <div class="mt-4 pt-4 border-t flex gap-3">
                <form method="POST" action="{{ route('eo.orders.approve', $order) }}" class="flex-1">
                    @csrf
                    <button type="submit" onclick="return confirm('Setujui pesanan ini?')" class="w-full px-4 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700">
                        ✓ Setujui Pesanan
                    </button>
                </form>
                <button onclick="openRejectModal()" class="flex-1 px-4 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">
                    ✗ Tolak Pesanan
                </button>
            </div>
            @endif
        </div>

        {{-- Revision History --}}
        @if($order->revisions->count() > 0)
        <div class="bg-white rounded-xl p-6 card-shadow">
            <h4 class="font-bold text-lg mb-4 text-primary">Riwayat Revisi</h4>
            <div class="space-y-3">
                @foreach($order->revisions as $revision)
                <div class="p-4 border rounded-lg @if($revision->isPending()) border-yellow-300 bg-yellow-50 @elseif($revision->isApproved()) border-green-300 bg-green-50 @else border-red-300 bg-red-50 @endif">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-semibold">Revisi dari {{ $revision->user->name }}</div>
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            @if($revision->isPending()) bg-yellow-200 text-yellow-800
                            @elseif($revision->isApproved()) bg-green-200 text-green-800
                            @else bg-red-200 text-red-800 @endif">
                            {{ ucfirst($revision->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 mb-2">{{ $revision->revision_note }}</p>
                    @if($revision->response_note)
                    <div class="mt-2 pt-2 border-t border-gray-300">
                        <div class="text-xs text-gray-500">Respon EO:</div>
                        <p class="text-sm text-gray-700">{{ $revision->response_note }}</p>
                    </div>
                    @endif

                    @if(auth()->user()->isEO() && $revision->isPending() && $order->eo_id === auth()->user()->eo_id)
                    <div class="mt-3 flex gap-2">
                        <button onclick="respondRevision({{ $revision->id }}, 'approved')" class="px-3 py-1 rounded bg-green-600 text-white text-sm">Setujui</button>
                        <button onclick="respondRevision({{ $revision->id }}, 'rejected')" class="px-3 py-1 rounded bg-red-600 text-white text-sm">Tolak</button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Chat Section --}}
        @if($order->isApproved())
        <div class="bg-white rounded-xl p-6 card-shadow">
            <h4 class="font-bold text-lg mb-4 text-primary">💬 Komunikasi Tim</h4>
            
            <div id="chat-container" class="h-96 overflow-y-auto border rounded-lg p-4 mb-4 bg-gray-50">
                <div id="chat-messages" class="space-y-3">
                    @foreach($order->chats as $chat)
                    <div class="flex {{ $chat->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs">
                            <div class="text-xs text-gray-500 mb-1">
                                {{ $chat->user->name }} 
                                <span class="px-1 py-0.5 rounded text-xs 
                                    @if($chat->user->role === 'eo') bg-teal-100 text-teal-700
                                    @elseif($chat->user->role === 'vendor') bg-purple-100 text-purple-700
                                    @else bg-blue-100 text-blue-700 @endif">
                                    {{ ucfirst($chat->user->role) }}
                                </span>
                            </div>
                            <div class="p-3 rounded-lg {{ $chat->user_id === auth()->id() ? 'bg-teal-600 text-white' : 'bg-white border' }}">
                                {{ $chat->message }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">{{ $chat->created_at->format('d M H:i') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <form id="chat-form" onsubmit="sendMessage(event)">
                @csrf
                <div class="flex gap-2">
                    <input type="text" id="chat-message" placeholder="Ketik pesan..." class="flex-1 border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required>
                    <button type="submit" class="px-6 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl p-6 card-shadow">
            <h4 class="font-bold mb-3">Timeline</h4>
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></div>
                    <div>
                        <div class="font-semibold">Pesanan Dibuat</div>
                        <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
                @if($order->isApproved())
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5"></div>
                    <div>
                        <div class="font-semibold">Disetujui EO</div>
                        <div class="text-xs text-gray-500">{{ $order->approved_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
                @endif
                @if($order->isPaid())
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-teal-500 mt-1.5"></div>
                    <div>
                        <div class="font-semibold">Pembayaran Berhasil</div>
                        <div class="text-xs text-gray-500">{{ $order->paid_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <h4 class="font-bold mb-3">Kontak</h4>
            <div class="space-y-3 text-sm">
                <div>
                    <div class="text-gray-500">Customer</div>
                    <div class="font-semibold">{{ $order->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $order->user->email }}</div>
                </div>
                @if(!$order->self_organized && $order->eventOrganizer)
                <div>
                    <div class="text-gray-500">Event Organizer</div>
                    <div class="font-semibold">{{ $order->eventOrganizer->name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Revision Request Modal --}}
<div id="revision-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Ajukan Revisi</h4>
        <form method="POST" action="{{ route('order.revision.store', $order) }}">
            @csrf
            <textarea name="revision_note" rows="4" placeholder="Jelaskan perubahan yang Anda inginkan..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRevisionModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-accent text-white hover:opacity-90">Kirim Revisi</button>
            </div>
        </form>
    </div>
</div>

{{-- EO Reject Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Tolak Pesanan</h4>
        <form method="POST" action="{{ route('eo.orders.reject', $order) }}">
            @csrf
            <textarea name="rejection_reason" rows="4" placeholder="Jelaskan alasan penolakan..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Tolak Pesanan</button>
            </div>
        </form>
    </div>
</div>

{{-- Revision Response Modal --}}
<div id="revision-response-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Respon Revisi</h4>
        <form id="revision-response-form" method="POST">
            @csrf
            <input type="hidden" name="status" id="revision-status">
            <textarea name="response_note" rows="4" placeholder="Berikan respon Anda..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRevisionResponseModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" id="revision-submit-btn" class="px-4 py-2 rounded-lg text-white">Kirim</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Chat functionality
function sendMessage(e) {
    e.preventDefault();
    const message = document.getElementById('chat-message').value;
    
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
            document.getElementById('chat-message').value = '';
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
                        <div class="text-xs text-gray-500 mb-1">
                            ${chat.user_name}
                            <span class="px-1 py-0.5 rounded text-xs ${
                                chat.user_role === 'eo' ? 'bg-teal-100 text-teal-700' :
                                chat.user_role === 'vendor' ? 'bg-purple-100 text-purple-700' :
                                'bg-blue-100 text-blue-700'
                            }">${chat.user_role.toUpperCase()}</span>
                        </div>
                        <div class="p-3 rounded-lg ${chat.is_own ? 'bg-teal-600 text-white' : 'bg-white border'}">
                            ${chat.message}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">${chat.created_at}</div>
                    </div>
                </div>
            `).join('');
            
            container.scrollTop = container.scrollHeight;
        });
}

// Auto-refresh chat every 5 seconds
@if($order->isApproved())
setInterval(loadMessages, 5000);
@endif

// Modal functions
function openRevisionModal() {
    document.getElementById('revision-modal').classList.remove('hidden');
    document.getElementById('revision-modal').classList.add('flex');
}
function closeRevisionModal() {
    document.getElementById('revision-modal').classList.add('hidden');
}

function openRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
    document.getElementById('reject-modal').classList.add('flex');
}
function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}

function respondRevision(revisionId, status) {
    document.getElementById('revision-status').value = status;
    document.getElementById('revision-response-form').action = `/eo/revision/${revisionId}/respond`;
    
    const btn = document.getElementById('revision-submit-btn');
    btn.textContent = status === 'approved' ? 'Setujui' : 'Tolak';
    btn.className = `px-4 py-2 rounded-lg text-white ${status === 'approved' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}`;
    
    document.getElementById('revision-response-modal').classList.remove('hidden');
    document.getElementById('revision-response-modal').classList.add('flex');
}
function closeRevisionResponseModal() {
    document.getElementById('revision-response-modal').classList.add('hidden');
}
</script>
@endpush
@endsection