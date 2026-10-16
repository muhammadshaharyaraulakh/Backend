<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query'   => ['required', 'string', 'min:3', 'max:500'],
            'court'   => ['nullable', 'string', 'exists:courts,short_name'],
            'year'    => ['nullable', 'integer', 'min:1947', 'max:2025'],
            'subject' => ['nullable', 'string', 'max:100'],
            'page'    => ['nullable', 'integer', 'min:1'],
        ];
    }
    public function messages():array{
        return[
            'query.required' => 'The search query is required.',
            'query.string' => 'The search query must be a string.',
            'query.min' => 'The search query must be at least :min characters.',
            'query.max' => 'The search query may not be greater than :max characters.',
            'court.string' => 'The court must be a string.',
            'court.exists' => 'The selected court is invalid.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be at least :min.',
            'year.max' => 'The year may not be greater than :max.',
            'subject.string' => 'The subject must be a string.',
            'subject.max' => 'The subject may not be greater than :max characters.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least :min.',
        ];
    }
}
