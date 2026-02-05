<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PUBLIC = 'public';

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role label
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::PUBLIC => 'Public User',
        };
    }
}
