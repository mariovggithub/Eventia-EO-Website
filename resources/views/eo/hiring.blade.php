@extends('layouts.app')
@section('title', 'EO - Hiring')

@section('content')
<section class="bg-white rounded-xl p-6 card-shadow mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-xl font-bold text-primary">Kelola Lowongan Kerja</h3>
            <p class="text-sm text-gray-500 mt-1">Buat lowongan dan kelola pelamar</p>
        </div>
        <button onclick="openJobModal()" class="px-4 py-2 rounded text-white text-sm bg-accent hover:opacity-90">
            ➕ Buat Lowongan Baru
        </button>
    </div>
</section>

<div class="space-y-6">
    @forelse($jobs as $job)
    <div class="bg-white rounded-xl card-shadow overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/4">
                <img src="{{ asset($job->image) }}" class="w-full h-48 object-cover" 
                    onerror="this.src='https://placehold.co/400x200/925E30/fff?text=Job'">
            </div>
            <div class="md:w-3/4 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-xl font-bold text-amber-900">{{ $job->role }}</h4>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                            <span>📊 <strong>{{ $job->slots }}</strong> slot tersedia</span>
                            <span>👥 <strong>{{ $job->applications->count() }}</strong> pelamar</span>
                            <span class="text-gray-400">• Dibuat {{ $job->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="toggleApplicants({{ $job->id }})" 
                            class="px-4 py-2 rounded-lg text-sm bg-blue-600 text-white hover:bg-blue-700">
                            👁️ Lihat Pelamar
                        </button>
                        <form action="{{ route('eo.hiring.destroy', $job) }}" method="POST" 
                            onsubmit="return confirm('Yakin ingin menghapus lowongan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-red-600 text-white hover:bg-red-700">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Applicants List (Hidden by default) --}}
                <div id="applicants-{{ $job->id }}" class="hidden mt-4 border-t pt-4">
                    <h5 class="font-bold text-lg mb-3 text-amber-900">
                        Daftar Pelamar ({{ $job->applications->count() }})
                    </h5>
                    
                    @if($job->applications->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pengalaman</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal Lamar</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($job->applications as $index => $application)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $application->name }}</div>
                                                @if($application->user)
                                                    <div class="text-xs text-gray-500">User ID: {{ $application->user_id }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <a href="mailto:{{ $application->email }}" 
                                                    class="text-blue-600 hover:underline">
                                                    {{ $application->email }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3 max-w-md">
                                                @if($application->experience)
                                                    <div class="text-gray-700">
                                                        {{ Str::limit($application->experience, 100) }}
                                                    </div>
                                                    @if(strlen($application->experience) > 100)
                                                        <button onclick="showFullExperience({{ $application->id }})" 
                                                            class="text-xs text-blue-600 hover:underline mt-1">
                                                            Lihat selengkapnya
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $application->created_at->format('d M Y, H:i') }}
                                            </td>
                                        </tr>

                                        {{-- Hidden Modal for Full Experience --}}
                                        @if($application->experience && strlen($application->experience) > 100)
                                        <tr id="experience-{{ $application->id }}" class="hidden bg-gray-50">
                                            <td colspan="5" class="px-4 py-3">
                                                <div class="p-4 bg-white rounded-lg border">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <h6 class="font-semibold text-gray-900">
                                                            Pengalaman Lengkap - {{ $application->name }}
                                                        </h6>
                                                        <button onclick="hideFullExperience({{ $application->id }})" 
                                                            class="text-gray-500 hover:text-gray-700">✕</button>
                                                    </div>
                                                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $application->experience }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Export Button --}}
                        <div class="mt-4 flex justify-end">
                            <button onclick="exportApplicants({{ $job->id }})" 
                                class="px-4 py-2 rounded-lg text-sm bg-green-600 text-white hover:bg-green-700">
                                📥 Export ke CSV
                            </button>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-lg">Belum ada pelamar untuk posisi ini</p>
                            <p class="text-sm mt-2">Pelamar akan muncul di sini setelah mereka melamar</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl p-12 card-shadow text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-500 text-lg">Belum ada lowongan kerja yang dibuat</p>
        <p class="text-gray-400 text-sm mt-2">Buat lowongan pertama Anda menggunakan tombol di atas</p>
    </div>
    @endforelse
</div>

{{-- Create Job Modal --}}
<div id="job-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-bold text-lg">Buat Lowongan Pekerjaan Baru</h4>
            <button onclick="closeJobModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <form method="POST" action="{{ route('eo.hiring.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Posisi Pekerjaan</label>
                    <input type="text" name="role" placeholder="Contoh: Crew Produksi, MC/Host" 
                        class="w-full border rounded-lg p-2 focus:border-teal-600 focus:ring-teal-600" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Slot</label>
                    <input type="number" name="slots" placeholder="3" min="1" 
                        class="w-full border rounded-lg p-2 focus:border-teal-600 focus:ring-teal-600" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL Gambar (Opsional)</label>
                    <input type="text" name="image" placeholder="https://..." 
                        class="w-full border rounded-lg p-2 focus:border-teal-600 focus:ring-teal-600">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeJobModal()" 
                    class="px-4 py-2 rounded-lg border hover:bg-gray-50">Batal</button>
                <button type="submit" 
                    class="px-4 py-2 rounded-lg text-white bg-accent hover:opacity-90">Post Lowongan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleApplicants(jobId) {
    const element = document.getElementById('applicants-' + jobId);
    element.classList.toggle('hidden');
}

function showFullExperience(applicationId) {
    document.getElementById('experience-' + applicationId).classList.remove('hidden');
}

function hideFullExperience(applicationId) {
    document.getElementById('experience-' + applicationId).classList.add('hidden');
}

function exportApplicants(jobId) {
    const table = document.querySelector(`#applicants-${jobId} table`);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let text = cols[j].innerText;
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        
        csv.push(row.join(','));
    }

    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `applicants_job_${jobId}_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

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

@if(session('success'))
<div class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="successAlert">
    {{ session('success') }}
</div>
<script>
    setTimeout(() => {
        document.getElementById('successAlert')?.remove();
    }, 3000);
</script>
@endif
@endsection