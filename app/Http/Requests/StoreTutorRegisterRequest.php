<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTutorRegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $bankCode = $this->input('bank_code') ?? $this->input('bank');
        $accountNumber = $this->input('account_number') ?? $this->input('rekening');

        $this->merge([
            'bank_code' => $bankCode,
            'account_number' => $accountNumber,
        ]);
    }

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
            // tabel user
            'email' => ['required','string','email','max:255','unique:users'],
            'social_temp_token' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'gender' => ['required', new Enum(GenderEnum::class)],
            'religion' => ['nullable', new Enum(ReligionEnum::class)],

            'date_of_birth' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:today'
            ],

            'telephone_number' => [
                'required',
                'string',
                'max:15',
                'unique:users,telephone_number',
            ],

            'profile_photo' => [
                'nullable',
                'file',
                'mimes:png,jpg,pdf,svg,webp',
            ],

            'province' => ['required', 'string', 'min:2', 'max:255'],
            'regency' => ['required', 'string', 'min:2', 'max:255'],
            'district' => ['required', 'string', 'min:2', 'max:255'],
            'subdistrict' => ['required', 'string', 'min:2', 'max:255'],
            'street' => ['required', 'string', 'min:2', 'max:255'],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'bank_code' => [
                'required',
                'string'
            ],
            'account_number' => [
                'required',
                'string'
            ],
        ];
    }
}
