@extends('emails.layout', [
    'headerTitle' => 'Pembayaran Gagal',
    'headerSubtitle' => 'Informasi pembayaran Anda'
])

@section('content')
    <h2>Halo, {{ $payment->enrollment->student_name }}</h2>
    
    <div class="alert alert-danger">
        <strong>❌ Pembayaran Anda tidak berhasil diproses</strong><br>
        Nomor Invoice: {{ $payment->invoice_number }}
    </div>

    <p>
        Kami informasikan bahwa pembayaran untuk kelas <strong>{{ $payment->enrollment->class->name }}</strong> 
        tidak berhasil diproses. Hal ini bisa terjadi karena beberapa alasan:
    </p>

    <ul style="color: #555555; padding-left: 20px; line-height: 2; margin: 20px 0;">
        <li>Saldo tidak mencukupi</li>
        <li>Transaksi dibatalkan</li>
        <li>Masalah teknis pada sistem pembayaran</li>
        <li>Batas waktu pembayaran terlewat</li>
    </ul>

    <div class="info-box">
        <h3>Detail Pembayaran</h3>
        <div class="info-row">
            <span class="info-label">Nomor Invoice</span>
            <span class="info-value">{{ $payment->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">{{ $payment->enrollment->class->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Pembayaran</span>
            <span class="info-value">{{ $payment->total_amount_formatted }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value"><span style="color: #dc3545; font-weight: 600;">✗ Gagal</span></span>
        </div>
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Apa yang Harus Dilakukan?</h3>
    
    <p>
        Jangan khawatir! Anda masih dapat menyelesaikan pembayaran dan mengamankan tempat Anda di kelas. 
        Silakan coba lagi dengan mengikuti langkah berikut:
    </p>

    <ol style="color: #555555; padding-left: 20px; line-height: 2;">
        <li>Pastikan saldo Anda mencukupi</li>
        <li>Kunjungi dashboard Anda</li>
        <li>Buat invoice pembayaran baru</li>
        <li>Selesaikan pembayaran sebelum batas waktu</li>
    </ol>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/dashboard/enrollments" class="button">
            Coba Bayar Lagi
        </a>
    </div>

    <div class="alert alert-warning">
        <strong>⚠️ Penting:</strong> Tempat di kelas terbatas. Segera selesaikan pembayaran untuk 
        memastikan Anda tidak kehilangan kesempatan bergabung dengan kelas ini.
    </div>

    <p style="color: #666666; font-size: 14px; margin-top: 30px;">
        Jika Anda mengalami kesulitan atau memiliki pertanyaan tentang pembayaran, 
        silakan hubungi tim support kami. Kami siap membantu Anda!
    </p>
@endsection
