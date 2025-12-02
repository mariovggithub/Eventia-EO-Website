@extends('layouts.app')
@section('title', 'Lowongan Kerja')

@section('content')
<section class="bg-white rounded-xl p-6 card-shadow">
    <div class="flex items-center justify-between mb-4">
        <h4 class="font-bold text-lg text-primary">Lowongan Tersedia</h4>
    </div>

    @if($jobs->isEmpty())
        <div class="text-gray-500 p-4 border rounded-lg text-center">
            Belum ada lowongan tersedia saat ini.
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($jobs as $job)
            <div class="p-4 border rounded-lg">
                <img src="{{ asset($job->image) }}" class="w-full h-36 object-cover rounded mb-3" onerror="this.src='https://placehold.co/400x200/1F6B7E/fff?text=Job'">
                <div class="font-semibold">{{ $job->role }}</div>
                <div class="text-xs text-gray-500">Slot: {{ $job->slots }}</div>
                <div class="text-xs text-gray-500">Oleh: {{ $job->eventOrganizer->name ?? 'Eventia EO' }}</div>
                <div class="mt-3">
                    @auth
                        @if(auth()->user()->isUser())
                            <button onclick="openApplyModal({{ $job->id }}, '{{ $job->role }}')" class="px-3 py-2 rounded bg-accent text-white hover:opacity-90">Apply</button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded bg-accent text-white hover:opacity-90 inline-block">Login to Apply</a>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>
    @endif
</section>

{{-- Apply Modal --}}
@auth
@if(auth()->user()->isUser())
<div id="apply-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold">Formulir Pendaftaran <span id="modal-job-role"></span></h4>
            <button onclick="closeApplyModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="apply-form" method="POST" action="">
            @csrf
            <div class="mt-4 space-y-3">
                <input type="text" name="name" placeholder="Nama" value="{{ auth()->user()->name }}" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <input type="email" name="email" placeholder="Email" value="{{ auth()->user()->email }}" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <textarea name="experience" placeholder="Pengalaman singkat (opsional)" rows="4" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600"></textarea>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeApplyModal()" class="px-4 py-2 rounded border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded text-white bg-accent hover:opacity-90">Kirim Lamaran</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openApplyModal(jobId, role) {
    document.getElementById('modal-job-role').textContent = role;
    document.getElementById('apply-form').action = '/jobs/' + jobId + '/apply';
    document.getElementById('apply-modal').classList.remove('hidden');
    document.getElementById('apply-modal').classList.add('flex');
}

function closeApplyModal() {
    document.getElementById('apply-modal').classList.add('hidden');
    document.getElementById('apply-modal').classList.remove('flex');
}

// Close on outside click
document.getElementById('apply-modal').addEventListener('click', function(e) {
    if (e.target === this) closeApplyModal();
});
</script>
@endpush
@endif
@endauth
@endsection