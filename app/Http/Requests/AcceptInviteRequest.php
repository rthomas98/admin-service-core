<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AcceptInviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data before validation
        $this->merge([
            'name' => strip_tags(trim($this->name ?? '')),
            'email' => filter_var(strtolower(trim($this->email ?? '')), FILTER_SANITIZE_EMAIL),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'\.]+$/', // Allow letters, spaces, hyphens, apostrophes, periods
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'terms' => [
                'required',
                'accepted',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your full name.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'password.required' => 'Please provide a secure password.',
            'password.min' => 'Password must be at least 12 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.uncompromised' => 'This password has been compromised in data breaches. Please choose a different password.',
            'terms.required' => 'You must accept the terms and conditions.',
            'terms.accepted' => 'You must accept the terms and conditions to continue.',
        ];
    }

    /**
     * Get sanitized data for safe storage.
     */
    public function sanitized(): array
    {
        $validated = $this->validated();

        return [
            'name' => htmlspecialchars($validated['name'], ENT_QUOTES, 'UTF-8'),
            'password' => $validated['password'],
            'terms' => $validated['terms'],
        ];
    }
}
