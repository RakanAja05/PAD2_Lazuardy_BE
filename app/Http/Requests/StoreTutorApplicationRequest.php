<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTutorApplicationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'experience' => [
                'required',
                'string',
            ],
            
            'organization' => [
                'required',
                'array',
            ],
            
            'cv' => ['nullable', 'array'],
            'cv.*.name' => ['required', 'string', 'max:255'],
            'cv.*.path_url' => ['required', 'string', 'url'],
            
            'id_card' => ['nullable', 'array'],
            'id_card.*.name' => ['required', 'string', 'max:255'],
            'id_card.*.path_url' => ['required', 'string', 'url'],
            
            'diploma' => ['nullable', 'array'],
            'diploma.*.name' => ['required', 'string', 'max:255'],
            'diploma.*.path_url' => ['required', 'string', 'url'],
            
            'certificate' => ['nullable', 'array'],
            'certificate.*.name' => ['required', 'string', 'max:255'],
            'certificate.*.path_url' => ['required', 'string', 'url'],
            
            'portfolio' => ['nullable', 'array'],
            'portfolio.*.name' => ['required', 'string', 'max:255'],
            'portfolio.*.path_url' => ['required', 'string', 'url'],
        ];
    }
}
