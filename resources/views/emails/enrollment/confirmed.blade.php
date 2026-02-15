@extends('emails.layout', [
    'headerTitle' => 'Pendaftaran Dikonfirmasi! 🎉',
    'headerSubtitle' => 'Selamat! Pendaftaran Anda telah disetujui'
])

@section('content')
    <h2>Selamat, {{ $enrollment->student_name }}!</h2>
    
    <div class="alert alert-success">
        <strong>✅ Pendaftaran Anda telah dikonfirmasi!</strong><br>
        Anda sekarang dapat melanjutkan ke proses pembayaran untuk mengamankan tempat Anda di kelas.
    </div>

    <p>
        Kami dengan senang hati mengkonfirmasi bahwa pendaftaran Anda untuk kelas 
        <strong>{{ $enrollment->class->name }}</strong> telah disetujui oleh tim kami.
    </p>

    <div class="info-box">
        <h3>Informasi Pendaftaran</h3>
        <div class="info-row">
            <span class="info-label">Nomor Pendaftaran</span>
            <span class="info-value"><strong>{{ $enrollment->enrollment_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value"><span style="color: #28a745; font-weight: 600;">✓ Dikonfirmasi</span></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Konfirmasi</span>
            <span class="info-value">{{ $enrollment->confirmed_at_formatted }}</span>
        </div>
    </div>

    <div class="info-box">
        <h3>Detail Kelas</h3>
        <div class="info-row">
            <span class="info-label">Nama Kelas</span>
            <span class="info-value"><strong>{{ $enrollment->class->name }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Level</span>
            <span class="info-value">{{ $enrollment->class->level }}</span>
        </div>
        @if($enrollment->schedule)
        <div class="info-row">
            <span class="info-label">Batch</span>
            <span class="info-value">{{ $enrollment->schedule->batch_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jadwal</span>
            <span class="info-value">{{ $enrollment->schedule->day_of_week }}, {{ $enrollment->schedule->start_time }} - {{ $enrollment->schedule->end_time }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lokasi</span>
            <span class="info-value">{{ $enrollment->schedule->location }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periode</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($enrollment->schedule->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($enrollment->schedule->end_date)->format('d M Y') }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Biaya Kelas</span>
            <span class="info-value"><strong style="color: #ff4d30; font-size: 16px;">{{ $enrollment->class->price_formatted }}</strong></span>
        </div>
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Langkah Selanjutnya: Pembayaran</h3>
    
    <p>
        Untuk mengamankan tempat Anda di kelas, silakan lakukan pembayaran melalui dashboard Anda. 
        Kami menyediakan berbagai metode pembayaran yang mudah dan aman.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/dashboard/enrollments" class="button">
            Lakukan Pembayaran Sekarang
        </a>
    </div>

    <div class="alert alert-warning">
        <strong>⏰ Penting:</strong> Silakan selesaikan pembayaran dalam 3x24 jam untuk memastikan tempat Anda tidak diambil oleh peserta lain.
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Yang Perlu Disiapkan</h3>
    <ul style="color: #555555; padding-left: 20px; line-height: 2;">
        <li>Laptop/komputer untuk mengikuti kelas</li>
        <li>Koneksi internet yang stabil</li>
        <li>Buku catatan dan alat tulis</li>
        <li>Semangat belajar yang tinggi! 🚀</li>
    </ul>

    <p style="color: #666666; font-size: 14px; margin-top: 30px;">
        Kami sangat menantikan kehadiran Anda di kelas. Jika ada pertanyaan, jangan ragu untuk menghubungi kami!
    </p>
@endsection
