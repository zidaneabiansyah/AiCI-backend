<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation untuk cancel enrollment
 * 
 * Validasi pembatalan enrollment:
 * - Reason wajib diisi (untuk audit & improvement)
 * - Confirmation untuk prevent accidental cancellation
 */
class CancelEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization check di controller (owner only)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:500'],
            'confirm' => ['required', 'boolean', 'accepted'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancellation_reason.max' => 'Alasan pembatalan maksimal 500 karakter.',
            'confirm.required' => 'Konfirmasi pembatalan wajib diisi.',
            'confirm.accepted' => 'Anda harus mengkonfirmasi pembatalan.',
        ];
    }
}
