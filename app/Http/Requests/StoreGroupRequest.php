<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:groups,name',
            'type' => 'required|in:itsinda,ikimina,association,cooperative',
            'member_numbers' => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Group name is required',
            'name.unique' => 'A group with this name already exists',
            'name.max' => 'Group name cannot exceed 255 characters',
            'type.required' => 'Group type is required',
            'type.in' => 'Group type must be one of: itsinda, ikimina, association, or cooperative',
            'member_numbers.required' => 'Expected member count is required',
            'member_numbers.integer' => 'Member count must be a whole number',
            'member_numbers.min' => 'Member count must be at least 1',
            'member_numbers.max' => 'Member count cannot exceed 1000',
            'description.max' => 'Description cannot exceed 1000 characters',
        ];
    }
}
