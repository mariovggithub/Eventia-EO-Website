@extends('layouts.app')
@section('title', 'EO - Hiring')

@section('content')
<section class="bg-white rounded-xl p-6 card-shadow">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-primary">Posting Lowongan (EO Dashboard)</h3>
        <button onclick="openJobModal()" class="px-4 py-2 rounded text-white text-sm bg-accent hover:opacity-90">Buat Lowongan Baru</button>
    </div>
    
    <h4 class="font-semibold mb-3">Lowongan Aktif ({{ $jobs->count() }})</h4>
    
    @if($jobs->isEmpty())
        <div class="text-gray-500 p-8 border rounded-lg text-center">
            <p>Belum ada lowongan yang diposting</p>
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($jobs as $job)
            <div class="p-4 border rounded-lg">
                <img src="{{ asset($job->image) }}" class="w-full h-36 object-cover rounded mb-3" onerror="this.src='https://placehold.co/400x200/1F6B7E/fff?text=Job'">
                <div class="font-semibold">{{ $job->role }}</div>
                <div class="text-sm text-gray-600">Slot: {{ $job->slots }}</div>
                <div class="text-xs text-gray-500 mt-1">Applicants: {{ $job->applications->count() }}</div>
                <div class="mt-3 flex gap-2">
                    <form method="POST" action="{{ route('eo.hiring.destroy', $job) }}" onsubmit="return confirm('Yakin hapus lowongan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1 rounded bg-secondary text-white text-xs hover:opacity-90">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</section>

{{-- Create Job Modal --}}
<div id="job-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold">Buat Lowongan Pekerjaan Baru</h4>
            <button onclick="closeJobModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('eo.hiring.store') }}">
            @csrf
            <div class="mt-4 space-y-3">
                <input type="text" name="role" placeholder="Posisi Pekerjaan (Ex: Crew Produksi)" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <input type="number" name="slots" placeholder="Jumlah Slot Tersedia" min="1" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600" required>
                <input type="text" name="image" placeholder="Link Gambar (opsional)" value="https://placehold.co/400x200/925E30/fff?text=New+Job" class="w-full border rounded p-2 focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeJobModal()" class="px-4 py-2 rounded border hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded text-white bg-accent hover:opacity-90">Post Lowongan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openJobModal() {
    document.getElementById('job-modal').classList.remove('hidden');
    document.getElementById('job-modal').classList.add('flex');
}

function closeJobModal() {
    document.getElementById('job-modal').classList.add('hidden');
    document.getElementById('job-modal').classList.remove('flex');
}

document.getElementById('job-modal').addEventListener('click', function(e) {
    if (e.target === this) closeJobModal();
});
</script>
@endpush
@endsection