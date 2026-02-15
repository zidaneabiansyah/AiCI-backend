@extends('emails.layout', [
    'headerTitle' => 'Pendaftaran Dibatalkan',
    'headerSubtitle' => 'Informasi pembatalan pendaftaran'
])

@section('content')
    <h2>Halo, {{ $enrollment->student_name }}</h2>
    
    <div class="alert alert-danger">
        <strong>❌ Pendaftaran Anda telah dibatalkan</strong><br>
        Nomor Pendaftaran: {{ $enrollment->enrollment_number }}
    </div>

    <p>
        Kami informasikan bahwa pendaftaran Anda untuk kelas <strong>{{ $enrollment->class->name }}</strong> 
        telah dibatalkan.
    </p>

    <div class="info-box">
        <h3>Detail Pembatalan</h3>
        <div class="info-row">
            <span class="info-label">Nomor Pendaftaran</span>
            <span class="info-value">{{ $enrollment->enrollment_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">{{ $enrollment->class->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Pembatalan</span>
            <span class="info-value">{{ $enrollment->cancelled_at_formatted }}</span>
        </div>
        @if($enrollment->cancellation_reason)
        <div class="info-row">
            <span class="info-label">Alasan</span>
            <span class="info-value">{{ $enrollment->cancellation_reason }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <h3 style="color: #255d74; font-size: 18px; margin-bottom: 15px;">Ingin Mendaftar Lagi?</h3>
    
    <p>
        Jika Anda masih tertarik untuk bergabung dengan kelas kami, Anda dapat mendaftar kembali 
        kapan saja melalui website kami. Kami memiliki berbagai kelas menarik yang mungkin sesuai 
        dengan kebutuhan Anda.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/classes" class="button">
            Lihat Kelas Tersedia
        </a>
    </div>

    <div class="alert alert-info">
        <strong>💡 Catatan:</strong> Jika pembatalan ini adalah kesalahan atau Anda memiliki pertanyaan, 
        silakan hubungi kami segera melalui email atau WhatsApp.
    </div>

    <p style="color: #666666; font-size: 14px; margin-top: 30px;">
        Terima kasih atas minat Anda terhadap program kami. Kami berharap dapat melayani Anda di kesempatan lain.
    </p>
@endsection
