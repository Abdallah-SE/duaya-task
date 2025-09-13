<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdleSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'idle_timeout' => 'required|integer|min:1|max:300',
            'max_idle_warnings' => 'required|integer|min:1|max:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'idle_timeout.required' => 'The idle timeout field is required.',
            'idle_timeout.integer' => 'The idle timeout must be an integer.',
            'idle_timeout.min' => 'The idle timeout must be at least 1 second.',
            'idle_timeout.max' => 'The idle timeout may not be greater than 300 seconds.',
            'max_idle_warnings.required' => 'The max idle warnings field is required.',
            'max_idle_warnings.integer' => 'The max idle warnings must be an integer.',
            'max_idle_warnings.min' => 'The max idle warnings must be at least 1.',
            'max_idle_warnings.max' => 'The max idle warnings may not be greater than 10.',
        ];
    }
}