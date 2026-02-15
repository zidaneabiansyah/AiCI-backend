@extends('emails.layout', [
    'headerTitle' => 'Pembayaran Berhasil! 🎉',
    'headerSubtitle' => 'Terima kasih atas pembayaran Anda'
])

@section('content')
    <h2>Selamat, {{ $payment->enrollment->student_name }}!</h2>
    
    <div class="alert alert-success">
        <strong>✅ Pembayaran Anda telah berhasil dikonfirmasi!</strong><br>
        Anda sekarang terdaftar resmi sebagai peserta kelas.
    </div>

    <p>
        Terima kasih telah menyelesaikan pembayaran untuk kelas <strong>{{ $payment->enrollment->class->name }}</strong>. 
        Kami sangat menantikan kehadiran Anda di kelas!
    </p>

    <div class="info-box">
        <h3>Bukti Pembayaran</h3>
        <div class="info-row">
            <span class="info-label">Nomor Invoice</span>
            <span class="info-value"><strong>{{ $payment->invoice_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Pembayaran</span>
            <span class="info-value">{{ $payment->paid_at_formatted }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Metode Pembayaran</span>
            <span class="info-value">{{ strtoupper($payment->payment_method) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Dibayar</span>
            <span class="info-value"><strong style="color: #28a745;">{{ $payment->total_amount_formatted }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value"><span style="color: #28a745; font-weight: 600;">✓ Lunas</span></span>
        </div>
    </div>

    <div class="info-box">
        <h3>Detail Kelas Anda</h3>
        <div class="info-row">
            <span class="info-label">Nama Kelas</span>
            <span class="info-value"><strong>{{ $payment->enrollment->class->name }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Kode Kelas</span>
            <span class="info-value">{{ $payment->enrollment->class->code }}</span>
        </div>
        @if($payment->enrollment->schedule)
        <div class="info-row">
            <span class="info-label">Batch</span>
            <span class="info-value">{{ $payment->enrollment->schedule->batch_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jadwal</span>
            <span class="info-value">{{ $payment->enrollment->schedule->day_of_week }}, {{ $payment->enrollment->schedule->start_time }} - {{ $payment->enrollment->schedule->end_time }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lokasi</span>
            <span class="info-value">{{ $payment->enrollment->schedule->location }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Mulai Kelas</span>
            <span class="info-value"><strong>{{ \Carbon\Carbon::parse($payment->enrollment->schedule->start_date)->format('d M Y') }}</strong></span>
        </div>
        @endif
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/dashboard" class="button">
            Lihat Dashboard Saya
        </a>
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Langkah Selanjutnya</h3>
    <ol style="color: #555555; padding-left: 20px; line-height: 2;">
        <li>Simpan email ini sebagai bukti pembayaran</li>
        <li>Bergabung dengan grup WhatsApp kelas (link akan dikirim terpisah)</li>
        <li>Siapkan peralatan belajar Anda</li>
        <li>Datang tepat waktu pada hari pertama kelas</li>
    </ol>

    <div class="alert alert-info">
        <strong>📚 Materi Kelas:</strong> Materi pembelajaran dan informasi lebih lanjut akan dibagikan 
        melalui grup WhatsApp kelas sebelum kelas dimulai.
    </div>

    <p style="color: #666666; font-size: 14px; margin-top: 30px;">
        Selamat belajar! Kami berkomitmen untuk memberikan pengalaman belajar terbaik untuk Anda. 
        Jika ada pertanyaan, tim kami siap membantu.
    </p>
@endsection
