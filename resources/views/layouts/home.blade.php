@extends('layouts.app')
@section('title', 'Home')

@section('content')
<section class="site-hero rounded-2xl p-8 card-shadow">
    <div class="grid md:grid-cols-2 gap-6 items-center">
        <div>
            <h2 class="text-3xl font-extrabold mb-4">Buat Acara Tak Terlupakan — Mudah & Terpercaya</h2>
            <p class="text-gray-600 mb-6">Pilih Event Organizer, kustom vendor, dan kelola seluruh kebutuhan acara Anda dalam satu platform.</p>

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
            <img src="{{ asset('assets/HomeBG.jpg') }}" alt="hero" class="w-full h-64 object-cover rounded-xl">
        </div>
    </div>
</section>

<section class="mt-8 grid md:grid-cols-3 gap-6">
    @foreach($eos as $eo)
    <div class="bg-white rounded-xl p-4 card-shadow">
        <div class="flex items-center gap-4">
            <img src="{{ asset($eo->logo) }}" class="w-20 h-12 object-cover rounded-md">
            <div>
                <div class="font-bold">{{ $eo->name }}</div>
                <div class="text-xs text-gray-500">{{ $eo->description }}</div>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Perkiraan: <span class="font-bold text-primary">{{ $eo->formatted_price }}</span>
            </div>
            <button onclick="openEOModal({{ $eo->id }})" class="px-3 py-2 rounded-lg text-sm text-white bg-accent hover:opacity-90">Lihat</button>
        </div>
    </div>
    @endforeach
</section>

{{-- EO Detail Modal --}}
<div id="eo-modal" class="fixed inset-0 z-50 hidden items-end md:items-center justify-center bg-black/40">
    <div class="bg-white rounded-t-xl md:rounded-xl p-6 w-full md:w-3/4 max-w-2xl">
        <div class="flex justify-between items-start gap-4">
            <div class="flex gap-4">
                <img id="modal-eo-logo" src="" class="w-28 h-20 object-cover rounded">
                <div>
                    <div id="modal-eo-name" class="font-bold text-xl"></div>
                    <div id="modal-eo-desc" class="text-sm text-gray-500"></div>
                </div>
            </div>
            <div class="text-right">
                <div id="modal-eo-price" class="text-sm text-gray-500"></div>
                @auth
                    @if(auth()->user()->isUser())
                        <a id="modal-eo-select" href="#" class="mt-3 inline-block px-3 py-2 rounded bg-accent text-white hover:opacity-90">Pilih EO</a>
                    @endif
                @endauth
            </div>
        </div>
        <div class="mt-4 text-gray-600">Portofolio & testimoni. Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>
        <div class="mt-4 flex justify-end">
            <button onclick="closeEOModal()" class="px-4 py-2 border rounded">Tutup</button>
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
    document.getElementById('modal-eo-price').textContent = 'Est. Rp ' + Number(eo.price_min).toLocaleString('id-ID') + '+';
    
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
</script>
@endpush
@endsection