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

            {{-- Price Offer Section --}}
            @if($order->isApproved() && $order->negotiated_price && !$order->isPaid() && auth()->user()->isUser() && auth()->id() === $order->user_id)
            <div class="mt-4 pt-4 border-t">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h4 class="font-bold text-amber-900 mb-2">💰 Penawaran Harga dari EO</h4>
                    <div class="text-2xl font-bold text-amber-900 mb-2">{{ $order->formatted_total }}</div>
                    
                    @if($order->price_breakdown)
                    <div class="mb-3">
                        <div class="font-semibold text-sm text-amber-800 mb-1">Rincian Biaya:</div>
                        <div class="text-sm text-gray-700 whitespace-pre-line bg-white p-3 rounded border border-amber-200">{{ $order->price_breakdown }}</div>
                    </div>
                    @endif

                    @if($order->price_agreed)
                    <div class="flex items-center gap-2 text-green-600 font-semibold mb-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Anda telah menyetujui harga ini pada {{ $order->price_agreed_at->format('d M Y H:i') }}</span>
                    </div>
                    <a href="{{ route('order.payment', $order) }}" class="block w-full text-center px-6 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                        Lanjut ke Pembayaran
                    </a>
                    @else
                    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-3 text-sm text-blue-800">
                        <strong>Perhatian:</strong> Silakan review penawaran harga ini. Jika setuju, klik tombol "Setujui Harga" untuk melanjutkan pembayaran.
                    </div>
                    
                    <div class="flex gap-2">
                        <form action="{{ route('order.agree-price', $order) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-6 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700">
                                ✓ Setujui Harga
                            </button>
                        </form>
                        <button type="button" onclick="openRejectPriceModal()" class="flex-1 px-6 py-3 rounded-lg border border-red-600 text-red-600 font-semibold hover:bg-red-50">
                            ✗ Tolak & Diskusi
                        </button>
                    </div>
                    @endif
                </div>
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
                @if($order->canRequestRevision())
                <div class="mt-3">
                    <button type="button" onclick="openRevisionModal()" class="w-full px-4 py-2 rounded-lg border border-primary text-primary hover:bg-primary/10">
                        📝 Ajukan Revisi (Sisa: {{ $order->getRemainingRevisions() }}x)
                    </button>
                </div>
                @endif

                @if($order->isPaid() && $order->isCompleted() && $order->canRate() && !$order->hasBeenRatedBy(auth()->id()))
                <div class="mt-4 pt-4 border-t">
                    <button type="button" onclick="openRatingModal()" class="w-full px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700">
                        ⭐ Berikan Rating
                    </button>
                </div>
                @elseif($order->hasBeenRatedBy(auth()->id()))
                <div class="mt-4 pt-4 border-t">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-green-600 font-semibold">✓ Anda sudah memberikan rating untuk order ini</div>
                        <div class="text-sm text-gray-600 mt-1">Terima kasih atas penilaian Anda!</div>
                    </div>
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
                <button type="button" onclick="openRejectModal()" class="flex-1 px-4 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">
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
                        <button type="button" onclick="respondRevision({{ $revision->id }}, 'approved')" class="px-3 py-1 rounded bg-green-600 text-white text-sm">Setujui</button>
                        <button type="button" onclick="respondRevision({{ $revision->id }}, 'rejected')" class="px-3 py-1 rounded bg-red-600 text-white text-sm">Tolak</button>
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
                @if($order->price_agreed)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5"></div>
                    <div>
                        <div class="font-semibold">Harga Disetujui</div>
                        <div class="text-xs text-gray-500">{{ $order->price_agreed_at->format('d M Y H:i') }}</div>
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

{{-- Reject Price Modal --}}
<div id="reject-price-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Tolak Penawaran Harga</h4>
        <form method="POST" action="{{ route('order.reject-price', $order) }}">
            @csrf
            <textarea name="reason" rows="4" placeholder="Jelaskan alasan Anda menolak harga ini..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRejectPriceModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Kirim & Diskusi di Chat</button>
            </div>
        </form>
    </div>
</div>

{{-- Revision Request Modal --}}
<div id="revision-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <h4 class="font-bold mb-4">Ajukan Revisi</h4>
        <form method="POST" action="{{ route('order.revision.store', $order) }}">
            @csrf
            <textarea name="revision_note" rows="4" placeholder="Jelaskan perubahan yang Anda inginkan..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" required></textarea>
            <div class="text-xs text-gray-500 mt-2">
                Sisa kesempatan revisi: {{ $order->getRemainingRevisions() - 1 }} kali setelah ini
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeRevisionModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-accent text-white hover:opacity-90">Kirim Revisi</button>
            </div>
        </form>
    </div>
</div>

{{-- Rating Modal --}}
<div id="rating-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40" style="display: none;">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-bold text-xl">⭐ Berikan Rating & Ulasan</h4>
            <button type="button" onclick="closeRatingModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>

        <form method="POST" action="{{ route('order.rating.store', $order) }}" id="rating-form">
            @csrf
            
            {{-- Rating untuk EO --}}
            @if(!$order->self_organized && $order->eventOrganizer)
            <div class="mb-6 p-4 border-2 rounded-lg bg-teal-50 border-teal-200">
                <h5 class="font-bold text-lg mb-3 text-teal-900 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Event Organizer: {{ $order->eventOrganizer->name }}
                </h5>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating EO <span class="text-red-500">*</span></label>
                    <div class="flex gap-2" id="eo-rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating('eo', {{ $i }})" class="text-4xl text-gray-300 hover:text-yellow-500 transition-all transform hover:scale-110 rating-star cursor-pointer" data-rating="eo" data-value="{{ $i }}">
                            ★
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="eo_rating" id="eo_rating">
                    <p class="text-xs text-gray-500 mt-2">Klik bintang untuk memberikan rating</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ulasan untuk EO (Opsional)</label>
                    <textarea name="eo_review" rows="3" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" placeholder="Ceritakan pengalaman Anda dengan EO ini... (Opsional)"></textarea>
                </div>
            </div>
            @endif

            {{-- Rating untuk Vendors --}}
            @if($order->vendors->count() > 0)
            <div class="mb-6">
                <h5 class="font-bold text-lg mb-3 text-purple-900 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Vendor yang Digunakan
                </h5>
                
                @foreach($order->vendors as $index => $vendor)
                <div class="mb-4 p-4 border-2 rounded-lg bg-purple-50 border-purple-200">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-10 h-10 bg-purple-200 rounded-full flex items-center justify-center text-purple-700 font-bold">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <h6 class="font-semibold text-gray-900">{{ $vendor->name }}</h6>
                            <p class="text-xs text-gray-600">{{ $vendor->category->name }}</p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="vendors[{{ $index }}][id]" value="{{ $vendor->id }}">
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                        <div class="flex gap-2" id="vendor-{{ $vendor->id }}-rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" onclick="setRating('vendor-{{ $vendor->id }}', {{ $i }})" class="text-4xl text-gray-300 hover:text-yellow-500 transition-all transform hover:scale-110 rating-star cursor-pointer" data-rating="vendor-{{ $vendor->id }}" data-value="{{ $i }}">
                                ★
                            </button>
                            @endfor
                        </div>
                        <input type="hidden" name="vendors[{{ $index }}][rating]" id="vendor-{{ $vendor->id }}_rating">
                        <p class="text-xs text-gray-500 mt-2">Klik bintang untuk memberikan rating</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ulasan (Opsional)</label>
                        <textarea name="vendors[{{ $index }}][review]" rows="2" class="w-full border rounded-lg p-3 focus:border-purple-600 focus:ring-purple-600" placeholder="Bagaimana kualitas produk/layanan vendor ini? (Opsional)"></textarea>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            
            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" onclick="closeRatingModal()" class="px-6 py-3 rounded-lg border hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" id="submit-rating-btn" class="px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700">
                    Kirim Rating
                </button>
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
            <textarea name="response_note" rows="4" placeholder="Berikan respon Anda..." class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600Continue9:38 PM" required></textarea>
<div class="mt-4 flex justify-end gap-2">
<button type="button" onclick="closeRevisionResponseModal()" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
<button type="submit" id="revision-submit-btn" class="px-4 py-2 rounded-lg text-white">Kirim</button>
</div>
</form>
</div>
</div>
@push('scripts')
<script>
console.log('Script loaded');

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

// Reject Price Modal
function openRejectPriceModal() {
    console.log('Open reject price modal');
    const modal = document.getElementById('reject-price-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectPriceModal() {
    const modal = document.getElementById('reject-price-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Revision Modal
function openRevisionModal() {
    console.log('Open revision modal');
    const modal = document.getElementById('revision-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRevisionModal() {
    const modal = document.getElementById('revision-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Rating Modal
function openRatingModal() {
    console.log('Opening rating modal...');
    const modal = document.getElementById('rating-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Rating modal opened');
    } else {
        console.error('Rating modal not found!');
    }
}

function closeRatingModal() {
    console.log('Closing rating modal...');
    const modal = document.getElementById('rating-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        console.log('Rating modal closed');
    }
}

// Rating Stars dengan validasi
function setRating(type, value) {
    console.log('Setting rating:', type, value);
    const stars = document.querySelectorAll(`[data-rating="${type}"]`);
    const input = document.getElementById(`${type}_rating`);
    
    if (!input) {
        console.error('Input rating tidak ditemukan untuk:', type);
        return;
    }
    
    input.value = value;
    
    stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        if (starValue <= value) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-500');
        } else {
            star.classList.remove('text-yellow-500');
            star.classList.add('text-gray-300');
        }
    });
    
    console.log(`Rating set for ${type}: ${value}`);
}

// Validasi form sebelum submit
const ratingForm = document.getElementById('rating-form');
if (ratingForm) {
    ratingForm.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = '';
        
        // Validasi rating EO jika ada
        const eoRatingInput = document.getElementById('eo_rating');
        if (eoRatingInput && !eoRatingInput.value) {
            isValid = false;
            errorMessage += '- Rating EO wajib diisi\n';
        }
        
        // Validasi rating vendor
        const vendorRatingInputs = document.querySelectorAll('input[name^="vendors"][name$="[rating]"]');
        vendorRatingInputs.forEach((input, index) => {
            if (!input.value) {
                isValid = false;
                errorMessage += `- Rating Vendor ${index + 1} wajib diisi\n`;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi rating berikut:\n\n' + errorMessage);
        }
    });
}

// Reject Modal
function openRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Revision Response
function respondRevision(revisionId, status) {
    document.getElementById('revision-status').value = status;
    document.getElementById('revision-response-form').action = `/eo/revision/${revisionId}/respond`;
    
    const btn = document.getElementById('revision-submit-btn');
    btn.textContent = status === 'approved' ? 'Setujui' : 'Tolak';
    btn.className = `px-4 py-2 rounded-lg text-white ${status === 'approved' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}`;
    
    const modal = document.getElementById('revision-response-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRevisionResponseModal() {
    const modal = document.getElementById('revision-response-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modals on outside click
document.addEventListener('click', function(e) {
    const modals = ['reject-price-modal', 'revision-modal', 'rating-modal', 'reject-modal', 'revision-response-modal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (id === 'rating-modal') {
                modal.style.display = 'none';
            }
        }
    });
});

console.log('All functions loaded');
</script>
@endpush
@endsection