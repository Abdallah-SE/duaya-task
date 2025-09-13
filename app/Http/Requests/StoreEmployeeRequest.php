<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('create', \App\Models\Employee::class);
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
            'user_id.required' => 'The user field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'job_title.required' => 'The job title field is required.',
            'job_title.max' => 'The job title may not be greater than 255 characters.',
            'department.required' => 'The department field is required.',
            'department.max' => 'The department may not be greater than 255 characters.',
            'hire_date.required' => 'The hire date field is required.',
            'hire_date.date' => 'The hire date must be a valid date.',
            'hire_date.before_or_equal' => 'The hire date must be today or earlier.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if user already has an employee record
            if ($this->input('user_id') && \App\Models\Employee::where('user_id', $this->input('user_id'))->exists()) {
                $validator->errors()->add('user_id', 'This user already has an employee record.');
            }
        });
    }
}