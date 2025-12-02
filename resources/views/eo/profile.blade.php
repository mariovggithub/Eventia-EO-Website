@extends('layouts.app')
@section('title', 'Profile Event Organizer')

@section('content')
<div class="max-w-4xl mx-auto">
    @if(!$eo->isProfileComplete())
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="font-bold text-yellow-800">Profile Belum Lengkap</h4>
                <p class="text-sm text-yellow-700 mt-1">Lengkapi profile EO Anda agar dapat menerima pesanan dari customer. Profile lengkap akan ditampilkan di halaman pemesanan.</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl p-8 card-shadow">
        <h2 class="text-2xl font-bold mb-6 text-primary">Profile Event Organizer</h2>

        <form method="POST" action="{{ route('eo.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Basic Info --}}
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Informasi Dasar</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama EO <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $eo->name) }}" 
                                class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('name') border-red-500 @enderror" 
                                placeholder="Contoh: EventPro Indonesia" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $eo->phone) }}" 
                                class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('phone') border-red-500 @enderror" 
                                placeholder="08123456789" required>
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi/Tagline <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="3" 
                            class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('description') border-red-500 @enderror" 
                            placeholder="Jelaskan tentang EO Anda dalam 1-2 kalimat..." required>{{ old('description', $eo->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Location --}}
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Lokasi</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                            <input type="text" name="city" value="{{ old('city', $eo->city) }}" 
                                class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" 
                                placeholder="Surabaya">
                            @error('city')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman (Tahun)</label>
                            <input type="number" name="experience_years" value="{{ old('experience_years', $eo->experience_years) }}" 
                                min="0" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" 
                                placeholder="5">
                            @error('experience_years')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea name="address" rows="2" 
                            class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" 
                            placeholder="Jalan, Nomor, Kecamatan...">{{ old('address', $eo->address) }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Harga Paket</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Harga Minimum <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="number" name="price_min" value="{{ old('price_min', $eo->price_min) }}" 
                                    min="0" step="1000000" 
                                    class="w-full border rounded-lg p-3 pl-12 focus:border-teal-600 focus:ring-teal-600 @error('price_min') border-red-500 @enderror" 
                                    placeholder="50000000" required>
                            </div>
                            @error('price_min')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Harga Maximum <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                                <input type="number" name="price_max" value="{{ old('price_max', $eo->price_max) }}" 
                                    min="0" step="1000000" 
                                    class="w-full border rounded-lg p-3 pl-12 focus:border-teal-600 focus:ring-teal-600 @error('price_max') border-red-500 @enderror" 
                                    placeholder="150000000" required>
                            </div>
                            @error('price_max')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Range harga paket event yang Anda tawarkan</p>
                </div>

                {{-- Portfolio & Logo --}}
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Portfolio & Branding</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Logo (Optional)</label>
                        <input type="text" name="logo" value="{{ old('logo', $eo->logo) }}" 
                            class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" 
                            placeholder="https://example.com/logo.png atau assets/logo.png">
                        @error('logo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @if($eo->logo)
                        <div class="mt-2">
                            <img src="{{ asset($eo->logo) }}" alt="Logo Preview" class="w-32 h-20 object-contain border rounded" onerror="this.src='https://placehold.co/200x120/1F6B7E/fff?text=Logo'">
                        </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Portfolio / Pengalaman</label>
                        <textarea name="portfolio" rows="4" 
                            class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" 
                            placeholder="Ceritakan tentang project-project yang pernah Anda tangani, klien-klien besar, dll...">{{ old('portfolio', $eo->portfolio) }}</textarea>
                        @error('portfolio')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Status --}}
                @if($eo->is_active)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-green-700">Profile Aktif - Visible untuk Customer</span>
                    </div>
                </div>
                @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-gray-700">Profile Tidak Aktif - Lengkapi untuk aktivasi</span>
                    </div>
                </div>
                @endif

                {{-- Submit Button --}}
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-accent text-white font-semibold hover:opacity-90">
                        Simpan Profile
                    </button>
                    @if($eo->isProfileComplete())
                    <a href="{{ route('eo.orders') }}" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Kembali
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection