<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('update', $this->route('employee'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'hire_date' => 'required|date|before_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'job_title.required' => 'The job title field is required.',
            'job_title.max' => 'The job title may not be greater than 255 characters.',
            'department.required' => 'The department field is required.',
            'department.max' => 'The department may not be greater than 255 characters.',
            'hire_date.required' => 'The hire date field is required.',
            'hire_date.date' => 'The hire date must be a valid date.',
            'hire_date.before_or_equal' => 'The hire date must be today or earlier.',
        ];
    }
}