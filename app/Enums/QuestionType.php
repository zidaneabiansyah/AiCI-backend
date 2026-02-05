<?php

namespace App\Enums;

enum QuestionType: string
{
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case SCENARIO = 'scenario';

    /**
     * Get all type values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get type label
     */
    public function label(): string
    {
        return match($this) {
            self::MULTIPLE_CHOICE => 'Multiple Choice',
            self::TRUE_FALSE => 'True/False',
            self::SCENARIO => 'Scenario Based',
        };
    }

    /**
     * Get maximum options allowed
     */
    public function maxOptions(): int
    {
        return match($this) {
            self::MULTIPLE_CHOICE => 5,
            self::TRUE_FALSE => 2,
            self::SCENARIO => 4,
        };
    }
}
