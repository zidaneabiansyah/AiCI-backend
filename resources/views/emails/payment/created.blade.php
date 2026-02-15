@extends('emails.layout', [
    'headerTitle' => 'Invoice Pembayaran',
    'headerSubtitle' => 'Silakan selesaikan pembayaran Anda'
])

@section('content')
    <h2>Halo, {{ $payment->enrollment->student_name }}!</h2>
    
    <p>
        Invoice pembayaran untuk pendaftaran kelas <strong>{{ $payment->enrollment->class->name }}</strong> 
        telah dibuat. Silakan selesaikan pembayaran Anda untuk mengamankan tempat di kelas.
    </p>

    <div class="alert alert-warning">
        <strong>⏰ Batas Waktu Pembayaran:</strong><br>
        {{ $payment->expired_at_formatted }}<br>
        <small>Invoice akan kadaluarsa jika pembayaran tidak diselesaikan sebelum batas waktu.</small>
    </div>

    <div class="info-box">
        <h3>Detail Invoice</h3>
        <div class="info-row">
            <span class="info-label">Nomor Invoice</span>
            <span class="info-value"><strong>{{ $payment->invoice_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Invoice</span>
            <span class="info-value">{{ $payment->created_at_formatted }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value"><span style="color: #ffc107; font-weight: 600;">⏳ Menunggu Pembayaran</span></span>
        </div>
    </div>

    <div class="info-box">
        <h3>Rincian Pembayaran</h3>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">{{ $payment->enrollment->class->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Biaya Kelas</span>
            <span class="info-value">{{ $payment->amount_formatted }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Biaya Admin</span>
            <span class="info-value">{{ $payment->admin_fee_formatted }}</span>
        </div>
        <div class="info-row" style="border-top: 2px solid #255d74; padding-top: 12px; margin-top: 8px;">
            <span class="info-label"><strong>Total Pembayaran</strong></span>
            <span class="info-value"><strong style="color: #ff4d30; font-size: 18px;">{{ $payment->total_amount_formatted }}</strong></span>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $payment->xendit_invoice_url }}" class="button">
            Bayar Sekarang
        </a>
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Cara Pembayaran</h3>
    <ol style="color: #555555; padding-left: 20px; line-height: 2;">
        <li>Klik tombol "Bayar Sekarang" di atas</li>
        <li>Pilih metode pembayaran yang Anda inginkan (Transfer Bank, E-Wallet, dll)</li>
        <li>Ikuti instruksi pembayaran yang diberikan</li>
        <li>Setelah pembayaran berhasil, Anda akan menerima konfirmasi via email</li>
    </ol>

    <div class="alert alert-info">
        <strong>💳 Metode Pembayaran:</strong> Kami menerima pembayaran melalui Transfer Bank, 
        E-Wallet (OVO, GoPay, Dana), dan Virtual Account.
    </div>

    <p style="color: #666666; font-size: 14px; margin-top: 30px;">
        Jika Anda mengalami kesulitan dalam proses pembayaran, jangan ragu untuk menghubungi kami.
    </p>
@endsection
