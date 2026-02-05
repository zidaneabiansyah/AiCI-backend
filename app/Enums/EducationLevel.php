<?php

namespace App\Enums;

enum EducationLevel: string
{
    case SD_MI = 'sd_mi';
    case SMP_MTS = 'smp_mts';
    case SMA_MA_SMK = 'sma_ma_smk';
    case UNIVERSITY = 'university';
    case OTHER = 'other';

    /**
     * Get all level values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get level label
     */
    public function label(): string
    {
        return match($this) {
            self::SD_MI => 'SD/MI',
            self::SMP_MTS => 'SMP/MTs',
            self::SMA_MA_SMK => 'SMA/MA/SMK',
            self::UNIVERSITY => 'Perguruan Tinggi',
            self::OTHER => 'Lainnya',
        };
    }

    /**
     * Get age range for this level
     */
    public function ageRange(): array
    {
        return match($this) {
            self::SD_MI => [6, 12],
            self::SMP_MTS => [13, 15],
            self::SMA_MA_SMK => [16, 18],
            self::UNIVERSITY => [19, 25],
            self::OTHER => [0, 100],
        };
    }
}
