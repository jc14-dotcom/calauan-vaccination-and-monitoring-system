<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Profile Request
 * 
 * Validates parent profile update data
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated as a parent
        return auth('parents')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_no' => 'required|regex:/^09\d{9}$/',
            'email' => 'required|email',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_no.required' => 'Contact number is required.',
            'contact_no.regex' => 'Contact number must be in format 09XXXXXXXXX (11 digits starting with 09).',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'address.required' => 'Address is required.',
            'address.max' => 'Address must not exceed 255 characters.',
            'barangay.required' => 'Barangay is required.',
            'barangay.max' => 'Barangay must not exceed 255 characters.',
        ];
    }
}
