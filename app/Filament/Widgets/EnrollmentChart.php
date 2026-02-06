<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EnrollmentChart extends ChartWidget
{
    protected ?string $heading = 'Pendaftaran Per Bulan';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Cache chart data untuk 1 jam
        return Cache::remember('dashboard.enrollment_chart', 3600, function () {
            // Optimize dengan raw query + groupBy
            $data = [];
            $labels = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = $month->format('M Y');
            }
            
            // Single query untuk semua 6 bulan
            $results = DB::table('enrollments')
                ->selectRaw("
                    DATE_TRUNC('month', enrolled_at) as month,
                    COUNT(*) as count
                ")
                ->where('enrolled_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
                ->whereNull('deleted_at')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->month)->format('M Y');
                });
            
            // Map results ke labels
            foreach ($labels as $label) {
                $data[] = $results->get($label)->count ?? 0;
            }
            
            return [
                'datasets' => [
                    [
                        'label' => 'Pendaftaran',
                        'data' => $data,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
