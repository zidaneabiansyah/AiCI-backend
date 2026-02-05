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
