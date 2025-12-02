@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl p-8 card-shadow">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-primary">Login ke Eventia</h2>
            <p class="text-gray-500 text-sm mt-1">Masuk untuk melanjutkan</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('email') border-red-500 @enderror" placeholder="email@example.com" required autofocus>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" class="w-full border rounded-lg p-3 focus:border-teal-600 focus:ring-teal-600 @error('password') border-red-500 @enderror" placeholder="••••••••" required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-teal-600 focus:ring-teal-600">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-accent text-white py-3 rounded-lg font-semibold hover:opacity-90 transition">
                    Login
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="text-accent font-semibold hover:underline">Daftar sekarang</a>
            </p>
        </div>

        {{-- Demo Accounts Info --}}
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 mb-2">Demo Accounts:</p>
            <div class="text-xs text-gray-500 space-y-1">
                <p><strong>User:</strong> user@demo.com / password</p>
                <p><strong>EO:</strong> eo@demo.com / password</p>
                <p><strong>Vendor:</strong> vendor@demo.com / password</p>
            </div>
        </div>
    </div>
</div>
@endsection