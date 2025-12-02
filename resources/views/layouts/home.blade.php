@extends('layouts.app')
@section('title', 'Home')

@section('content')
<section class="site-hero rounded-2xl p-8 card-shadow">
    <div class="grid md:grid-cols-2 gap-6 items-center">
        <div>
            <h2 class="text-3xl font-extrabold mb-4">Buat Acara Tak Terlupakan — Mudah & Terpercaya</h2>
            <p class="text-gray-600 mb-6">Pilih Event Organizer terbaik, kustom vendor, dan kelola seluruh kebutuhan acara Anda dalam satu platform.</p>

            <div class="flex gap-3">
                @auth
                    @if(auth()->user()->isUser())
                        <a href="{{ route('order.create') }}" class="px-5 py-3 rounded-lg text-white font-semibold bg-accent hover:opacity-90">Pesan Sekarang</a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="px-5 py-3 rounded-lg text-white font-semibold bg-accent hover:opacity-90">Pesan Sekarang</a>
                @endauth
                <a href="{{ route('about') }}" class="px-5 py-3 rounded-lg border text-primary border-primary hover:bg-primary/10">Pelajari Lebih Lanjut</a>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3 text-sm text-gray-500">
                <div class="p-3 bg-white rounded-lg card-shadow text-center">
                    <div class="font-bold text-lg text-primary">{{ $stats['projects'] }}+</div>
                    <div>Proyek</div>
                </div>
                <div class="p-3 bg-white rounded-lg card-shadow text-center">
                    <div class="font-bold text-lg text-primary">{{ $stats['rating'] }}★</div>
                    <div>Rata-rata rating</div>
                </div>
                <div class="p-3 bg-white rounded-lg card-shadow text-center">
                    <div class="font-bold text-lg text-primary">{{ $stats['vendors'] }}+</div>
                    <div>Vendor</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden">
            <img src="{{ asset('assets/HomeBG.jpg') }}" alt="hero" class="w-full h-64 object-cover rounded-xl" onerror="this.src='https://placehold.co/600x300/1F6B7E/fff?text=Eventia'">
        </div>
    </div>
</section>

<section class="mt-8">
    <h3 class="text-2xl font-bold mb-6 text-primary">Event Organizer Terpercaya</h3>
    
    @if($eos->isEmpty())
    <div class="bg-white rounded-xl p-12 card-shadow text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <p>Belum ada Event Organizer yang tersedia</p>
    </div>
    @else
    <div class="grid md:grid-cols-3 gap-6">
        @foreach($eos as $eo)
        <div class="bg-white rounded-xl p-4 card-shadow hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <img src="{{ asset($eo->logo) }}" class="w-20 h-20 object-cover rounded-md" onerror="this.src='https://placehold.co/100x100/1F6B7E/fff?text={{ substr($eo->name, 0, 2) }}'">
                <div>
                    <div class="font-bold text-lg">{{ $eo->name }}</div>
                    <div class="text-xs text-gray-500 mb-1">{{ $eo->description }}</div>
                    
                    {{-- Rating Display --}}
                    <div class="flex items-center gap-1">
                        <span class="text-amber-500 text-lg">{{ $eo->star_display }}</span>
                        <span class="text-sm text-gray-600">
                            ({{ $eo->average_rating > 0 ? number_format($eo->average_rating, 1) : '0' }})
                        </span>
                    </div>
                    @if($eo->total_ratings > 0)
                    <div class="text-xs text-gray-400">{{ $eo->total_ratings }} ulasan</div>
                    @else
                    <div class="text-xs text-gray-400">Belum ada ulasan</div>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                @if($eo->city)
                <span>📍 {{ $eo->city }}</span>
                @endif
                @if($eo->experience_years > 0)
                <span>• {{ $eo->experience_years }} tahun pengalaman</span>
                @endif
            </div>
            
            <div class="mt-3 flex gap-2">
                <button onclick="openEOModal({{ $eo->id }})" class="flex-1 px-3 py-2 rounded-lg text-sm text-white bg-accent hover:opacity-90">
                    Lihat Detail
                </button>
                @auth
                    @if(auth()->user()->isUser())
                    <a href="{{ route('order.create', ['eo_id' => $eo->id]) }}" class="px-3 py-2 rounded-lg text-sm border border-primary text-primary hover:bg-primary/10">
                        Pilih
                    </a>
                    @endif
                @endauth
            </div>
        </div>
        @endforeach
    </div>
    @endif
</section>

{{-- EO Detail Modal --}}
<div id="eo-modal" class="fixed inset-0 z-50 hidden items-end md:items-center justify-center bg-black/40">
    <div class="bg-white rounded-t-xl md:rounded-xl p-6 w-full md:w-3/4 max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start gap-4 mb-4">
            <div class="flex gap-4">
                <img id="modal-eo-logo" src="" class="w-28 h-28 object-cover rounded">
                <div>
                    <div id="modal-eo-name" class="font-bold text-2xl mb-1"></div>
                    <div id="modal-eo-desc" class="text-sm text-gray-500 mb-2"></div>
                    <div id="modal-eo-rating" class="flex items-center gap-2 mb-2"></div>
                    <div id="modal-eo-location" class="text-sm text-gray-600"></div>
                </div>
            </div>
            <button onclick="closeEOModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="modal-eo-portfolio" class="text-gray-600 mb-4"></div>
        
        <div id="modal-eo-reviews" class="mb-4"></div>
        
        <div class="flex gap-3">
            @auth
                @if(auth()->user()->isUser())
                <a id="modal-eo-select" href="#" class="flex-1 text-center px-6 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                    Pilih EO Ini
                </a>
                @endif
            @endauth
            <button onclick="closeEOModal()" class="px-6 py-3 rounded-lg border hover:bg-gray-50">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const eos = @json($eos);

function openEOModal(id) {
    const eo = eos.find(e => e.id === id);
    if (!eo) return;
    
    document.getElementById('modal-eo-logo').src = '{{ asset("") }}' + eo.logo;
    document.getElementById('modal-eo-name').textContent = eo.name;
    document.getElementById('modal-eo-desc').textContent = eo.description;
    
    // Rating
    const ratingHtml = `
        <span class="text-amber-500 text-2xl">${eo.star_display}</span>
        <span class="text-gray-600">(${eo.average_rating > 0 ? eo.average_rating.toFixed(1) : '0'})</span>
        <span class="text-sm text-gray-400">• ${eo.total_ratings} ulasan</span>
    `;
    document.getElementById('modal-eo-rating').innerHTML = ratingHtml;
    
    // Location
    let locationText = '';
    if (eo.city) locationText += '📍 ' + eo.city;
    if (eo.experience_years > 0) locationText += ' • ' + eo.experience_years + ' tahun pengalaman';
    document.getElementById('modal-eo-location').textContent = locationText;
    
    // Portfolio
    document.getElementById('modal-eo-portfolio').textContent = eo.portfolio || 'Belum ada informasi portfolio.';
    
    // Reviews (will be populated if available)
    document.getElementById('modal-eo-reviews').innerHTML = '<div class="text-sm text-gray-500 italic">Ulasan pelanggan akan ditampilkan di sini</div>';
    
    // Select button
    const selectBtn = document.getElementById('modal-eo-select');
    if (selectBtn) {
        selectBtn.href = '{{ route("order.create") }}?eo_id=' + id;
    }
    
    document.getElementById('eo-modal').classList.remove('hidden');
    document.getElementById('eo-modal').classList.add('flex');
}

function closeEOModal() {
    document.getElementById('eo-modal').classList.add('hidden');
    document.getElementById('eo-modal').classList.remove('flex');
}

// Close on outside click
document.getElementById('eo-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEOModal();
});
</script>
@endpush
@endsection