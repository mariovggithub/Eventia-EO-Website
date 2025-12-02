@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl p-8 card-shadow">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-primary">Daftar di Eventia</h2>
            <p class="text-gray-500 text-sm mt-1">Buat akun baru</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('name') border-red-500 @enderror" placeholder="John Doe" required autofocus>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('email') border-red-500 @enderror" placeholder="email@example.com" required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role / Jenis Akun</label>
                    <select name="role" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('role') border-red-500 @enderror" required>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (Pemesan Event)</option>
                        <option value="eo" {{ old('role') == 'eo' ? 'selected' : '' }}>Event Organizer</option>
                        <option value="vendor" {{ old('role') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('password') border-red-500 @enderror" placeholder="Min 8 karakter" required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600" placeholder="Ulangi password" required>
                </div>

                <button type="submit" class="w-full bg-accent text-white py-3 rounded-lg font-semibold hover:opacity-90 transition">
                    Daftar
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="text-accent font-semibold hover:underline">Login</a>
            </p>
        </div>
    </div>
</div>
@endsection