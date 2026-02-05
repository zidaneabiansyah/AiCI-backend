<?php

namespace App\Enums;

enum TestCategory: string
{
    case LOGICAL_THINKING = 'logical_thinking';
    case BASIC_PROGRAMMING = 'basic_programming';
    case AI_AWARENESS = 'ai_awareness';
    case ROBOTICS_FUNDAMENTALS = 'robotics_fundamentals';
    case INTEREST_MAPPING = 'interest_mapping';

    /**
     * Get all category values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get category label
     */
    public function label(): string
    {
        return match($this) {
            self::LOGICAL_THINKING => 'Logical Thinking',
            self::BASIC_PROGRAMMING => 'Basic Programming',
            self::AI_AWARENESS => 'AI Awareness',
            self::ROBOTICS_FUNDAMENTALS => 'Robotics Fundamentals',
            self::INTEREST_MAPPING => 'Interest Mapping',
        };
    }

    /**
     * Get category weight in scoring
     */
    public function weight(): float
    {
        return match($this) {
            self::LOGICAL_THINKING => 0.25,
            self::BASIC_PROGRAMMING => 0.25,
            self::AI_AWARENESS => 0.20,
            self::ROBOTICS_FUNDAMENTALS => 0.20,
            self::INTEREST_MAPPING => 0.10,
        };
    }
}
