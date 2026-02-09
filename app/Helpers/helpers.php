<?php

/**
 * Global helper functions
 */

if (!function_exists('formatCurrency')) {
    /**
     * Format number as Indonesian Rupiah
     */
    function formatCurrency(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date to Indonesian format
     */
    function formatDate($date, string $format = 'd F Y'): string
    {
        return \Carbon\Carbon::parse($date)->translatedFormat($format);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime to Indonesian format
     */
    function formatDateTime($datetime): string
    {
        return \Carbon\Carbon::parse($datetime)->translatedFormat('d F Y H:i');
    }
}

if (!function_exists('generateInvoiceNumber')) {
    /**
     * Generate unique invoice number
     */
    function generateInvoiceNumber(string $prefix = 'INV'): string
    {
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

if (!function_exists('sanitizeFileName')) {
    /**
     * Sanitize filename for safe storage
     */
    function sanitizeFileName(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 200);
    }
}

/**
 * ============================================
 * DATA PRIVACY & MASKING FUNCTIONS
 * ============================================
 * 
 * Security functions untuk protect PII (Personally Identifiable Information)
 * Digunakan di admin panel untuk mask sensitive data
 */

if (!function_exists('maskEmail')) {
    /**
     * Mask email address for privacy
     * 
     * Examples:
     * - john.doe@example.com → j***@example.com
     * - admin@test.co.id → a***@test.co.id
     * 
     * @param string|null $email
     * @return string
     */
    function maskEmail(?string $email): string
    {
        if ($email === null || $email === '') {
            return '-';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $username = $parts[0];
        $domain = $parts[1];

        // Show first character + *** + @domain
        $maskedUsername = substr($username, 0, 1) . '***';

        return $maskedUsername . '@' . $domain;
    }
}

if (!function_exists('maskPhone')) {
    /**
     * Mask phone number for privacy
     * 
     * Examples:
     * - 081234567890 → 0812****7890
     * - +6281234567890 → +6281****7890
     * - 08123456789 → 0812***6789
     * 
     * @param string|null $phone
     * @return string
     */
    function maskPhone(?string $phone): string
    {
        if ($phone === null || $phone === '') {
            return '-';
        }

        $length = strlen($phone);
        
        if ($length < 8) {
            return '***';
        }

        // Show first 4 digits + **** + last 4 digits
        $start = substr($phone, 0, 4);
        $end = substr($phone, -4);
        $middle = str_repeat('*', min(4, $length - 8));

        return $start . $middle . $end;
    }
}

if (!function_exists('maskName')) {
    /**
     * Mask name for privacy (partial masking)
     * 
     * Examples:
     * - John Doe → John D***
     * - Ahmad Rizki Pratama → Ahmad R*** P***
     * 
     * @param string|null $name
     * @return string
     */
    function maskName(?string $name): string
    {
        if ($name === null || $name === '') {
            return '-';
        }

        $words = explode(' ', $name);
        
        if (count($words) === 1) {
            // Single name: show first 3 chars
            return substr($name, 0, 3) . '***';
        }

        // Multiple words: show first word fully, mask others
        $masked = [$words[0]];
        
        for ($i = 1; $i < count($words); $i++) {
            $word = $words[$i];
            $masked[] = substr($word, 0, 1) . '***';
        }

        return implode(' ', $masked);
    }
}

if (!function_exists('maskAccountNumber')) {
    /**
     * Mask bank account or card number
     * 
     * Examples:
     * - 1234567890123456 → ****-****-****-3456
     * - 1234567890 → ******7890
     * 
     * @param string|null $accountNumber
     * @return string
     */
    function maskAccountNumber(?string $accountNumber): string
    {
        if ($accountNumber === null || $accountNumber === '') {
            return '-';
        }

        $length = strlen($accountNumber);
        
        if ($length < 8) {
            return '***';
        }

        // Show only last 4 digits
        $masked = str_repeat('*', $length - 4);
        $visible = substr($accountNumber, -4);

        // Format with dashes for readability if long number
        if ($length >= 12) {
            return '****-****-****-' . $visible;
        }

        return $masked . $visible;
    }
}

if (!function_exists('maskSensitiveData')) {
    /**
     * Generic function to mask any sensitive data
     * 
     * @param string|null $data
     * @param int $visibleStart Number of visible characters at start
     * @param int $visibleEnd Number of visible characters at end
     * @return string
     */
    function maskSensitiveData(?string $data, int $visibleStart = 2, int $visibleEnd = 2): string
    {
        if ($data === null || $data === '') {
            return '-';
        }

        $length = strlen($data);
        
        if ($length <= ($visibleStart + $visibleEnd)) {
            return str_repeat('*', $length);
        }

        $start = substr($data, 0, $visibleStart);
        $end = substr($data, -$visibleEnd);
        $middle = str_repeat('*', $length - $visibleStart - $visibleEnd);

        return $start . $middle . $end;
    }
}

if (!function_exists('shouldMaskData')) {
    /**
     * Check if current user should see masked data
     * 
     * Logic:
     * - Admin can see full data
     * - Non-admin users see masked data
     * - Unauthenticated users see masked data
     * 
     * @return bool
     */
    function shouldMaskData(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return true; // Mask for unauthenticated
        }

        // Admin can see full data
        return $user->role !== \App\Enums\UserRole::ADMIN;
    }
}
