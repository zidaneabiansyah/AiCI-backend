<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case VIRTUAL_ACCOUNT = 'virtual_account';
    case EWALLET = 'ewallet';
    case QRIS = 'qris';
    case CREDIT_CARD = 'credit_card';

    /**
     * Get all method values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get method label
     */
    public function label(): string
    {
        return match($this) {
            self::BANK_TRANSFER => 'Bank Transfer',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
            self::EWALLET => 'E-Wallet',
            self::QRIS => 'QRIS',
            self::CREDIT_CARD => 'Credit Card',
        };
    }

    /**
     * Get available channels for this method
     */
    public function channels(): array
    {
        return match($this) {
            self::BANK_TRANSFER => ['BCA', 'BNI', 'BRI', 'Mandiri'],
            self::VIRTUAL_ACCOUNT => ['BCA', 'BNI', 'BRI', 'Mandiri', 'Permata'],
            self::EWALLET => ['OVO', 'GoPay', 'Dana', 'LinkAja', 'ShopeePay'],
            self::QRIS => ['QRIS'],
            self::CREDIT_CARD => ['Visa', 'Mastercard'],
        };
    }
}
