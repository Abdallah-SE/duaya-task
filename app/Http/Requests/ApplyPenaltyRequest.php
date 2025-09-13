<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyPenaltyRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255',
            'count' => 'integer|min:1|max:10',
            'metadata' => 'array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'reason.required' => 'The reason field is required.',
            'reason.max' => 'The reason may not be greater than 255 characters.',
            'count.integer' => 'The count must be an integer.',
            'count.min' => 'The count must be at least 1.',
            'count.max' => 'The count may not be greater than 10.',
            'metadata.array' => 'The metadata must be an array.',
        ];
    }
}