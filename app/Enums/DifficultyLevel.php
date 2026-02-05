<?php

namespace App\Enums;

enum DifficultyLevel: string
{
    case BEGINNER = 'beginner';
    case ELEMENTARY = 'elementary';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

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
            self::BEGINNER => 'Beginner',
            self::ELEMENTARY => 'Elementary',
            self::INTERMEDIATE => 'Intermediate',
            self::ADVANCED => 'Advanced',
        };
    }

    /**
     * Get level description
     */
    public function description(): string
    {
        return match($this) {
            self::BEGINNER => 'Belum pernah belajar AI/Robotics',
            self::ELEMENTARY => 'Pernah belajar dasar',
            self::INTERMEDIATE => 'Siap untuk praktek',
            self::ADVANCED => 'Siap untuk proyek & riset',
        };
    }

    /**
     * Get score weight multiplier
     */
    public function weight(): float
    {
        return match($this) {
            self::BEGINNER => 1.0,
            self::ELEMENTARY => 1.2,
            self::INTERMEDIATE => 1.5,
            self::ADVANCED => 2.0,
        };
    }

    /**
     * Get minimum score threshold
     */
    public function minScore(): int
    {
        return match($this) {
            self::BEGINNER => 0,
            self::ELEMENTARY => 40,
            self::INTERMEDIATE => 60,
            self::ADVANCED => 80,
        };
    }
}
