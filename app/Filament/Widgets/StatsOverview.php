<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\ClassModel;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Cache stats untuk 5 menit
        $stats = Cache::remember('dashboard.stats', 300, function () {
            // Optimize dengan single query menggunakan DB facade
            $enrollmentStats = DB::table('enrollments')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed
                ")
                ->whereNull('deleted_at')
                ->first();
            
            $paymentStats = DB::table('payments')
                ->selectRaw("
                    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments
                ")
                ->whereNull('deleted_at')
                ->first();
            
            $programStats = DB::table('programs')
                ->selectRaw("
                    COUNT(*) as total_programs
                ")
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();
            
            $classStats = DB::table('classes')
                ->selectRaw("
                    COUNT(*) as total_classes
                ")
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();
            
            return [
                'total_enrollments' => $enrollmentStats->total ?? 0,
                'pending_enrollments' => $enrollmentStats->pending ?? 0,
                'confirmed_enrollments' => $enrollmentStats->confirmed ?? 0,
                'total_revenue' => $paymentStats->total_revenue ?? 0,
                'pending_payments' => $paymentStats->pending_payments ?? 0,
                'total_programs' => $programStats->total_programs ?? 0,
                'total_classes' => $classStats->total_classes ?? 0,
            ];
        });
        
        return [
            Stat::make('Total Pendaftaran', $stats['total_enrollments'])
                ->description($stats['pending_enrollments'] . ' menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->chart([7, 12, 15, 18, 22, 25, $stats['total_enrollments']]),
            
            Stat::make('Pendaftaran Aktif', $stats['confirmed_enrollments'])
                ->description('Siswa terkonfirmasi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Total Pendapatan', 'Rp ' . number_format($stats['total_revenue'], 0, ',', '.'))
                ->description($stats['pending_payments'] . ' pembayaran pending')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([100000, 250000, 500000, 750000, 1000000, $stats['total_revenue']]),
            
            Stat::make('Program Aktif', $stats['total_programs'])
                ->description($stats['total_classes'] . ' kelas tersedia')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
        ];
    }
}
