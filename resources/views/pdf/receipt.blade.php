@extends('pdf.layout')

@section('title', 'Kuitansi Pembayaran - ' . $payment->invoice_number)

@section('content')
    <h1 style="font-size: 24px; color: #111827; margin-bottom: 8px;">Kuitansi Pembayaran</h1>
    <p style="color: #6b7280; font-size: 14px; margin-bottom: 32px;">Nomor Invoice: #{{ $payment->invoice_number }}</p>

    <div style="margin-bottom: 40px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h3 style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px;">Detail Siswa</h3>
                    <p class="font-bold" style="margin: 0;">{{ $payment->enrollment->student_name }}</p>
                    <p style="margin: 0; color: #4b5563;">{{ $payment->enrollment->student_email }}</p>
                    <p style="margin: 0; color: #4b5563;">{{ $payment->enrollment->student_phone }}</p>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <h3 style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px;">Detail Pembayaran</h3>
                    <p style="margin: 0;">Tanggal: {{ $payment->paid_at->format('d F Y') }}</p>
                    <p style="margin: 0;">Metode: {{ $payment->payment_method }}</p>
                    <div style="margin-top: 8px;">
                        <span class="status-badge status-paid">Lunas</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Deskripsi Item</th>
                <th class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <p class="font-bold" style="margin: 0;">{{ $payment->enrollment->class->name }}</p>
                    <p style="margin: 0; font-size: 12px; color: #6b7280;">{{ $payment->enrollment->class->program->name }}</p>
                </td>
                <td class="text-right">{{ formatCurrency($payment->amount) }}</td>
            </tr>
            <tr>
                <td>Biaya Layanan/Admin</td>
                <td class="text-right">{{ formatCurrency($payment->admin_fee) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="font-bold" style="font-size: 18px; padding-top: 20px;">Total Pembayaran</td>
                <td class="font-bold text-right" style="font-size: 18px; color: #1a56db; padding-top: 20px;">
                    {{ formatCurrency($payment->total_amount) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="mt-10" style="background-color: #f3f4f6; padding: 20px; border-radius: 8px;">
        <p style="margin: 0; font-size: 12px; color: #4b5563;">
            <strong style="color: #111827;">Catatan:</strong><br>
            Kuitansi ini adalah bukti pembayaran yang sah untuk pendaftaran program di AICI-UMG. 
            Mohon simpan dokumen ini sebagai referensi di masa mendatang. 
            Jika ada pertanyaan, silakan hubungi tim administrasi kami.
        </p>
    </div>
@endsection
