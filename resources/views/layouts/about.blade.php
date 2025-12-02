@extends('layouts.app')
@section('title', 'About')

@section('content')
<section class="bg-white rounded-xl p-8 card-shadow">
    <div class="md:flex gap-6">
        <div class="md:w-1/2">
            <h3 class="text-2xl font-bold mb-3 text-primary">Tentang Eventia</h3>
            <p class="text-gray-600 mb-4">Eventia lahir dari sebuah visi sederhana: menjadikan proses perencanaan dan pelaksanaan event lebih mudah, efisien, dan menyenangkan bagi semua pihak.</p>
            <p class="text-gray-600 mb-4">Sebagai Event Organizer terkemuka, tim Eventia terdiri dari para profesional berpengalaman yang berdedikasi untuk menciptakan pengalaman yang unik dan berkesan, disesuaikan dengan tujuan spesifik Anda.</p>
            <p class="text-gray-600 mb-4">Platform kami berfungsi sebagai pusat kustomisasi vendor yang tak tertandingi. Kami menghubungkan Anda dengan beragam penyedia layanan berkualitas tinggi – mulai dari katering, dekorasi, audio visual, hingga hiburan.</p>
            <p class="text-gray-600 mb-4">Eventia juga berkomitmen untuk mengembangkan talenta industri melalui modul lowongan kerja kami.</p>

            <ul class="grid gap-2 text-sm text-gray-600">
                <li>• Dinobatkan sebagai "Platform EO Terinovatif" oleh Asosiasi Event Nasional tahun 2023.</li>
                <li>• Meningkatkan kepuasan klien hingga 95% berdasarkan survei independen.</li>
                <li>• Berhasil menyelenggarakan 3 konferensi internasional besar.</li>
                <li>• Memfasilitasi penempatan kerja bagi 80% lulusan program pelatihan event.</li>
            </ul>
        </div>
        <div class="md:w-1/2 mt-6 md:mt-0">
            <img src="{{ asset('assets/AboutBG.jpg') }}" alt="about" class="w-full h-56 my-3 object-cover rounded-lg">
            <img src="{{ asset('assets/About2.jpg') }}" alt="about" class="w-full h-56 my-3 object-cover rounded-lg">
            <img src="{{ asset('assets/About3.jpg') }}" alt="about" class="w-full h-56 my-3 object-cover rounded-lg">
        </div>
    </div>
</section>
@endsection