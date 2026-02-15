@extends('emails.layout', [
    'headerTitle' => 'Pendaftaran Berhasil!',
    'headerSubtitle' => 'Terima kasih telah mendaftar di AICI'
])

@section('content')
    <h2>Halo, {{ $enrollment->student_name }}!</h2>
    
    <p>
        Terima kasih telah mendaftar di <strong>AICI - Artificial Intelligence Center Indonesia</strong>. 
        Pendaftaran Anda telah kami terima dan sedang dalam proses verifikasi.
    </p>

    <div class="alert alert-info">
        <strong>📋 Status Pendaftaran:</strong> Menunggu Verifikasi<br>
        Kami akan mengirimkan email konfirmasi setelah pendaftaran Anda diverifikasi oleh tim kami.
    </div>

    <div class="info-box">
        <h3>Detail Pendaftaran</h3>
        <div class="info-row">
            <span class="info-label">Nomor Pendaftaran</span>
            <span class="info-value"><strong>{{ $enrollment->enrollment_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Nama Siswa</span>
            <span class="info-value">{{ $enrollment->student_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ $enrollment->student_email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nomor Telepon</span>
            <span class="info-value">{{ $enrollment->student_phone }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Usia</span>
            <span class="info-value">{{ $enrollment->student_age }} tahun</span>
        </div>
    </div>

    <div class="info-box">
        <h3>Kelas yang Dipilih</h3>
        <div class="info-row">
            <span class="info-label">Nama Kelas</span>
            <span class="info-value"><strong>{{ $enrollment->class->name }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kode Kelas</span>
            <span class="info-value">{{ $enrollment->class->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Level</span>
            <span class="info-value">{{ $enrollment->class->level }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Durasi</span>
            <span class="info-value">{{ $enrollment->class->duration_hours }} jam ({{ $enrollment->class->total_sessions }} sesi)</span>
        </div>
        <div class="info-row">
            <span class="info-label">Biaya</span>
            <span class="info-value"><strong>{{ $enrollment->class->price_formatted }}</strong></span>
        </div>
        @if($enrollment->schedule)
        <div class="info-row">
            <span class="info-label">Jadwal</span>
            <span class="info-value">{{ $enrollment->schedule->batch_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Hari & Waktu</span>
            <span class="info-value">{{ $enrollment->schedule->day_of_week }}, {{ $enrollment->schedule->start_time }} - {{ $enrollment->schedule->end_time }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Langkah Selanjutnya</h3>
    <ol style="color: #555555; padding-left: 20px; line-height: 2;">
        <li>Tim kami akan memverifikasi pendaftaran Anda dalam 1x24 jam</li>
        <li>Anda akan menerima email konfirmasi setelah pendaftaran disetujui</li>
        <li>Lakukan pembayaran sesuai instruksi yang akan dikirimkan</li>
        <li>Setelah pembayaran dikonfirmasi, Anda dapat mulai mengikuti kelas</li>
    </ol>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/dashboard/enrollments" class="button">
            Lihat Status Pendaftaran
        </a>
    </div>

    <p style="color: #666666; font-size: 14px; margin-top: 30px;">
        Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami melalui email atau WhatsApp.
    </p>
@endsection
