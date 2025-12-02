@extends('layouts.app')
@section('title', 'Buat Pesanan')

@section('content')
<form method="POST" action="{{ route('order.store') }}" id="order-form">
    @csrf
    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            {{-- Step 1: Event Type --}}
            <div class="bg-white rounded-xl p-6 card-shadow">
                <h4 class="font-bold text-lg mb-3 text-primary">1. Pilih Jenis Acara</h4>
                <div class="grid sm:grid-cols-4 gap-4">
                    @foreach($eventTypes as $type)
                    <label class="cursor-pointer">
                        <input type="radio" name="event_type_id" value="{{ $type->id }}" class="hidden peer" required>
                        <div class="p-2 rounded-lg border text-sm text-left transition-all peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:ring-2 peer-checked:ring-amber-500 hover:shadow-md">
                            <img src="{{ asset($type->image) }}" alt="{{ $type->name }}" class="w-full h-16 object-cover rounded-md mb-2">
                            {{ $type->name }}
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('event_type_id')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- NEW: Self-Organized Option --}}
            <div class="bg-white rounded-xl p-6 card-shadow">
                <h4 class="font-bold text-lg mb-3 text-primary">2. Pilih Pengelola Event</h4>
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="self_organized" value="0" class="hidden peer" checked>
                        <div class="p-4 rounded-lg border text-left transition-all peer-checked:bg-teal-50 peer-checked:border-teal-600 peer-checked:ring-2 peer-checked:ring-teal-600 hover:shadow-md">
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <div>
                                    <div class="font-bold">Gunakan Event Organizer</div>
                                    <div class="text-xs text-gray-500">Biarkan profesional menangani event Anda</div>
                                </div>
                            </div>
                            <ul class="text-xs text-gray-600 space-y-1 ml-11">
                                <li>✓ Tim profesional berpengalaman</li>
                                <li>✓ Paket vendor lengkap tersedia</li>
                                <li>✓ Konsultasi & koordinasi</li>
                            </ul>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="self_organized" value="1" class="hidden peer">
                        <div class="p-4 rounded-lg border text-left transition-all peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:ring-2 peer-checked:ring-amber-500 hover:shadow-md">
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <div>
                                    <div class="font-bold">Kelola Sendiri (Self-Organized)</div>
                                    <div class="text-xs text-gray-500">Atur event Anda secara mandiri</div>
                                </div>
                            </div>
                            <ul class="text-xs text-gray-600 space-y-1 ml-11">
                                <li>✓ Hemat biaya jasa EO</li>
                                <li>✓ Kontrol penuh atas vendor</li>
                                <li>✓ Fleksibilitas tinggi</li>
                            </ul>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Step 3: Event Details --}}
            <div class="bg-white rounded-xl p-6 card-shadow">
                <h4 class="font-bold text-lg mb-3 text-primary">3. Detail Acara</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="text-sm">
                        Tanggal
                        <input type="date" name="event_date" id="event-date" value="{{ old('event_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="mt-1 block w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                        @error('event_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </label>
                    <label class="text-sm">
                        Kapasitas (Orang)
                        <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 100) }}" min="1" class="mt-1 block w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                        @error('capacity')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </label>
                    <div class="text-sm">
                        <div class="mb-2">Paket Vendor</div>
                        <select name="vendor_choice" id="vendor-choice" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600">
                            <option value="package">Gunakan Paket EO</option>
                            <option value="ala">Pilih Vendor Sendiri (A la Carte)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 4: Select EO (hidden if self-organized) --}}
            <div id="eo-panel" class="bg-white rounded-xl p-6 card-shadow">
                <h4 class="font-bold text-lg mb-3 text-primary">4. Pilih Event Organizer</h4>
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach($eos as $eo)
                    <label class="cursor-pointer eo-option">
                        <input type="radio" name="eo_id" value="{{ $eo->id }}" class="hidden peer" data-price="{{ $eo->price_min }}">
                        <div class="p-4 border rounded-lg hover:shadow-lg transition-all peer-checked:border-amber-500 peer-checked:ring-2 peer-checked:ring-amber-500 peer-checked:bg-amber-50">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset($eo->logo) }}" class="w-20 h-12 object-cover rounded-md">
                                <div>
                                    <div class="font-bold">{{ $eo->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $eo->description }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-sm text-gray-600">Est. <span class="text-primary font-semibold">Rp {{ number_format($eo->price_min, 0, ',', '.') }}</span></div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('eo_id')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Step 5: Select Vendors (A la Carte) --}}
            <div id="vendor-panel" class="bg-white rounded-xl p-6 card-shadow hidden">
                <h4 class="font-bold text-lg mb-3 text-primary">5. Pilih Vendor A la Carte</h4>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($vendorCategories as $category)
                    <div>
                        <div class="font-semibold mb-2 text-primary">{{ $category->name }}</div>
                        @foreach($category->products as $product)
                        <label class="cursor-pointer block">
                            <input type="checkbox" name="vendors[]" value="{{ $product->id }}" class="hidden peer vendor-checkbox" data-price="{{ $product->price }}">
                            <div class="p-3 border rounded mb-3 transition-all peer-checked:bg-teal-50 peer-checked:border-teal-600 peer-checked:ring-1 peer-checked:ring-teal-600 hover:shadow-sm">
                                <img src="{{ asset($product->image) }}" class="w-full h-20 object-cover rounded-md mb-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $product->formatted_price }}</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <aside class="bg-white rounded-xl p-6 card-shadow h-fit sticky top-24">
            <h4 class="font-bold text-lg text-primary">Ringkasan Pesanan</h4>
            <div class="mt-3 space-y-3 text-sm text-gray-700">
                <div>Jenis: <span id="summary-type" class="font-medium">-</span></div>
                <div>Tanggal: <span id="summary-date" class="font-medium">-</span></div>
                <div>Kapasitas: <span id="summary-cap" class="font-medium">-</span></div>
                <div>Pengelola: <span id="summary-organizer" class="font-medium">-</span></div>
                <div id="summary-eo-section">EO: <span id="summary-eo" class="font-medium">-</span></div>
                <div id="summary-vendors-section" class="hidden">
                    <div>Vendors:</div>
                    <ul id="summary-vendors" class="list-disc ml-5 text-gray-600 text-xs"></ul>
                </div>
                <div class="pt-3 border-t mt-3">
                    <div class="flex justify-between text-base font-semibold text-primary">
                        <span>Total Est.</span>
                        <span id="summary-total">Rp 0</span>
                    </div>
                    <button type="submit" class="mt-4 w-full px-4 py-2 rounded text-white bg-accent hover:opacity-90">Buat Pesanan</button>
                    <p class="text-xs text-gray-500 mt-2 text-center" id="payment-note">Pembayaran setelah persetujuan EO</p>
                </div>
            </div>
        </aside>
    </div>
</form>

@push('scripts')
<script>
const eventTypes = @json($eventTypes);
const eos = @json($eos);
const vendorCategories = @json($vendorCategories);

function updateSummary() {
    // Event Type
    const selectedType = document.querySelector('input[name="event_type_id"]:checked');
    document.getElementById('summary-type').textContent = selectedType ? 
        eventTypes.find(t => t.id == selectedType.value)?.name : '-';

    // Date & Capacity
    document.getElementById('summary-date').textContent = document.getElementById('event-date').value || '-';
    document.getElementById('summary-cap').textContent = document.getElementById('capacity').value || '-';

    // Self Organized
    const isSelfOrganized = document.querySelector('input[name="self_organized"]:checked')?.value === '1';
    const eoPanel = document.getElementById('eo-panel');
    const eoSection = document.getElementById('summary-eo-section');
    const paymentNote = document.getElementById('payment-note');
    
    document.getElementById('summary-organizer').textContent = isSelfOrganized ? 'Self-Organized' : 'Event Organizer';
    
    if (isSelfOrganized) {
        eoPanel.classList.add('hidden');
        eoSection.classList.add('hidden');
        paymentNote.textContent = 'Langsung ke pembayaran';
        document.querySelectorAll('.eo-option input').forEach(inp => inp.required = false);
    } else {
        eoPanel.classList.remove('hidden');
        eoSection.classList.remove('hidden');
        paymentNote.textContent = 'Pembayaran setelah persetujuan EO';
        document.querySelectorAll('.eo-option input').forEach(inp => inp.required = true);
    }

    // EO
    let eoPrice = 0;
    if (!isSelfOrganized) {
        const selectedEO = document.querySelector('input[name="eo_id"]:checked');
        if (selectedEO) {
            const eo = eos.find(e => e.id == selectedEO.value);
            document.getElementById('summary-eo').textContent = eo?.name || '-';
            eoPrice = eo ? parseFloat(eo.price_min) : 0;
        } else {
            document.getElementById('summary-eo').textContent = '-';
        }
    }

    // Vendors
    const vendorChoice = document.getElementById('vendor-choice').value;
    const vendorPanel = document.getElementById('vendor-panel');
    const vendorSection = document.getElementById('summary-vendors-section');
    let vendorTotal = 0;

    if (vendorChoice === 'ala') {
        vendorPanel.classList.remove('hidden');
        vendorSection.classList.remove('hidden');
        
        const checkedVendors = document.querySelectorAll('.vendor-checkbox:checked');
        let vendorHtml = '';
        checkedVendors.forEach(v => {
            vendorTotal += parseFloat(v.dataset.price);
            for (let cat of vendorCategories) {
                const prod = cat.products.find(p => p.id == v.value);
                if (prod) {
                    vendorHtml += `<li>${prod.name} — Rp ${Number(prod.price).toLocaleString('id-ID')}</li>`;
                    break;
                }
            }
        });
        document.getElementById('summary-vendors').innerHTML = vendorHtml || '<li>Belum ada vendor dipilih</li>';
    } else {
        vendorPanel.classList.add('hidden');
        vendorSection.classList.add('hidden');
        document.querySelectorAll('.vendor-checkbox').forEach(cb => cb.checked = false);
    }

    // Total
    const total = eoPrice + vendorTotal;
    document.getElementById('summary-total').textContent = 'Rp ' + Number(total).toLocaleString('id-ID');
}

// Event Listeners
document.querySelectorAll('input[name="event_type_id"]').forEach(el => el.addEventListener('change', updateSummary));
document.querySelectorAll('input[name="self_organized"]').forEach(el => el.addEventListener('change', updateSummary));
document.querySelectorAll('input[name="eo_id"]').forEach(el => el.addEventListener('change', updateSummary));
document.querySelectorAll('.vendor-checkbox').forEach(el => el.addEventListener('change', updateSummary));
document.getElementById('event-date').addEventListener('change', updateSummary);
document.getElementById('capacity').addEventListener('input', updateSummary);
document.getElementById('vendor-choice').addEventListener('change', updateSummary);

// Init
updateSummary();
</script>
@endpush
@endsection