@extends('pdf.layout')

@section('title', 'Hasil Placement Test - ' . $attempt->full_name)

@section('content')
    <h1 style="font-size: 24px; color: #111827; margin-bottom: 8px;">Hasil Placement Test</h1>
    <p style="color: #6b7280; font-size: 14px; margin-bottom: 32px;">ID Percobaan: #{{ $attempt->id }} | Tanggal: {{ $attempt->completed_at->format('d F Y') }}</p>

    <div style="margin-bottom: 40px; background-color: #f9fafb; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <h3 style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px;">Informasi Peserta</h3>
                    <p class="font-bold" style="margin: 0; font-size: 18px;">{{ $attempt->full_name }}</p>
                    <p style="margin: 0; color: #4b5563;">Usia: {{ $attempt->age }} Tahun</p>
                    <p style="margin: 0; color: #4b5563;">Pendidikan: {{ $attempt->education_level }}</p>
                </td>
                <td style="width: 40%; text-align: right;">
                    <h3 style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px;">Skor Akhir</h3>
                    <p class="font-bold" style="margin: 0; font-size: 32px; color: #1a56db;">{{ number_format($attempt->score, 1) }}</p>
                    <p style="margin: 0; color: #4b5563;">Level: <span class="font-bold">{{ strtoupper($attempt->level_result) }}</span></p>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 32px;">
        <h2 style="font-size: 18px; color: #111827; margin-bottom: 16px; border-bottom: 1px solid #eee; padding-bottom: 8px;">Rekomendasi Program</h2>
        <p style="margin-bottom: 20px;">Berdasarkan hasil tes dan profil Anda, kami merekomendasikan program-program berikut yang paling sesuai dengan kemampuan Anda:</p>

        @foreach($recommendations->take(3) as $rec)
            <div style="margin-bottom: 16px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px;">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <p class="font-bold" style="margin: 0; color: #1a56db;">{{ $rec['class']->name }}</p>
                            <p style="margin: 0; font-size: 12px; color: #6b7280;">{{ $rec['class']->program->name }}</p>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-size: 12px; background-color: #ebf5ff; color: #004eeb; padding: 2px 8px; border-radius: 9999px;">
                                Kecocokan: {{ $rec['match_percentage'] }}%
                            </span>
                        </td>
                    </tr>
                </table>
                <p style="margin-top: 8px; font-size: 13px; color: #4b5563;">{{ $rec['reason'] }}</p>
            </div>
        @endforeach
    </div>

    <div style="background-color: #fffaf0; border-left: 4px solid #f6ad55; padding: 16px; margin-top: 40px;">
        <p style="margin: 0; font-size: 13px; color: #744210;">
            <strong>Langkah Selanjutnya:</strong><br>
            Anda dapat mendaftar langsung ke kelas rekomendasi di atas melalui dashboard akun Anda. 
            Hasil tes ini berlaku selama 3 bulan sejak tanggal diterbitkan.
        </p>
    </div>
@endsection
