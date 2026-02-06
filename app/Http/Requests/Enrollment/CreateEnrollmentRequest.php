<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation untuk create enrollment
 * 
 * Validasi data pendaftaran:
 * - Student information (nama, email, phone, age)
 * - Parent/Guardian info (untuk minor)
 * - Class & schedule selection
 * - Special requirements
 * 
 * Business Rules:
 * - Email harus valid & unique per enrollment
 * - Phone number format Indonesia
 * - Age harus sesuai dengan class requirement
 * - Parent info wajib jika student < 17 tahun
 */
class CreateEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User harus authenticated
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Class & Schedule
            'class_id' => ['required', 'exists:classes,id'],
            'class_schedule_id' => ['nullable', 'exists:class_schedules,id'],
            'test_result_id' => ['nullable', 'exists:test_results,id'], // Optional: dari placement test
            
            // Student Information
            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['required', 'email', 'max:255'],
            'student_phone' => [
                'required', 
                'string', 
                'regex:/^(\+62|62|0)[0-9]{9,12}$/' // Format Indonesia: 08xx, 62xx, +62xx
            ],
            'student_age' => ['required', 'integer', 'min:6', 'max:100'],
            'student_grade' => ['nullable', 'string', 'max:50'], // e.g., "Kelas 5 SD"
            
            // Parent/Guardian Information (conditional: required if age < 17)
            'parent_name' => ['required_if:student_age,<,17', 'nullable', 'string', 'max:255'],
            'parent_phone' => [
                'required_if:student_age,<,17',
                'nullable',
                'string',
                'regex:/^(\+62|62|0)[0-9]{9,12}$/'
            ],
            'parent_email' => ['required_if:student_age,<,17', 'nullable', 'email', 'max:255'],
            
            // Additional Information
            'special_requirements' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'class_schedule_id.exists' => 'Jadwal tidak ditemukan.',
            
            'student_name.required' => 'Nama siswa wajib diisi.',
            'student_email.required' => 'Email siswa wajib diisi.',
            'student_email.email' => 'Format email tidak valid.',
            'student_phone.required' => 'Nomor telepon siswa wajib diisi.',
            'student_phone.regex' => 'Format nomor telepon tidak valid. Contoh: 081234567890',
            'student_age.required' => 'Usia siswa wajib diisi.',
            'student_age.min' => 'Usia minimal 6 tahun.',
            
            'parent_name.required_if' => 'Nama orang tua wajib diisi untuk siswa di bawah 17 tahun.',
            'parent_phone.required_if' => 'Nomor telepon orang tua wajib diisi untuk siswa di bawah 17 tahun.',
            'parent_phone.regex' => 'Format nomor telepon orang tua tidak valid.',
            'parent_email.required_if' => 'Email orang tua wajib diisi untuk siswa di bawah 17 tahun.',
            'parent_email.email' => 'Format email orang tua tidak valid.',
        ];
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'student_name' => 'nama siswa',
            'student_email' => 'email siswa',
            'student_phone' => 'nomor telepon siswa',
            'student_age' => 'usia siswa',
            'student_grade' => 'kelas siswa',
            'parent_name' => 'nama orang tua',
            'parent_phone' => 'nomor telepon orang tua',
            'parent_email' => 'email orang tua',
            'special_requirements' => 'kebutuhan khusus',
            'notes' => 'catatan',
        ];
    }

    /**
     * Prepare data for validation
     * 
     * Clean phone numbers: remove spaces, dashes
     */
    protected function prepareForValidation(): void
    {
        // Clean phone numbers
        if ($this->has('student_phone')) {
            $this->merge([
                'student_phone' => preg_replace('/[\s\-\(\)]/', '', $this->student_phone),
            ]);
        }

        if ($this->has('parent_phone')) {
            $this->merge([
                'parent_phone' => preg_replace('/[\s\-\(\)]/', '', $this->parent_phone),
            ]);
        }
    }
}
