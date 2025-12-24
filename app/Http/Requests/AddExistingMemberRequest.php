<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddExistingMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|exists:users,email',
            'role' => 'required|in:member,treasurer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.exists' => 'No user found with this email address',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either member or treasurer',
        ];
    }
}
