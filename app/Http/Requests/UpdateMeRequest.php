<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateMeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            // Common fields
            'name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'telephone_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:15',
                Rule::unique('users', 'telephone_number')->ignore($this->user()?->id),
            ],
            'gender' => ['sometimes', 'nullable', new Enum(GenderEnum::class)],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'religion' => ['sometimes', 'nullable', new Enum(ReligionEnum::class)],
            'profile_photo_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

            // Address (preferred nested object)
            'address' => ['sometimes', 'nullable', 'array'],
            'address.province' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'address.regency' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'address.district' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'address.subdistrict' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'address.street' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],

            // Address (legacy flat keys)
            'province' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'regency' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'district' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'subdistrict' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'street' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
        ];

        $role = $this->user()?->role;

        if ($role === RoleEnum::STUDENT) {
            $rules = array_merge($rules, [
                // Store as student.class_id via lookup
                'class_name' => ['sometimes', 'string', 'min:1', 'max:255', Rule::exists('classes', 'name')],
            ]);
        }

        if ($role === RoleEnum::TUTOR) {
            $rules = array_merge($rules, [
                'description' => ['sometimes', 'nullable', 'string'],
                'education' => ['sometimes', 'nullable', 'array'],

                // Tutor payout info
                'bank_code' => ['sometimes', 'nullable', 'string', 'max:50'],
                'account_number' => ['sometimes', 'nullable', 'string', 'max:50'],

                // Legacy aliases
                'bank' => ['sometimes', 'nullable', 'string', 'max:50'],
                'rekening' => ['sometimes', 'nullable', 'string', 'max:50'],

                // Array of subject IDs
                'subjects' => ['sometimes', 'nullable', 'array'],
                'subjects.*' => ['integer', Rule::exists('subjects', 'id')],

                // Stored into tutors.learning_method (array of 'online'|'offline')
                'teaching_mode' => [
                    'sometimes',
                    'nullable',
                    function (string $attribute, mixed $value, \Closure $fail) {
                        $allowed = ['online', 'offline'];

                        if (is_string($value)) {
                            if (!in_array($value, $allowed, true)) {
                                $fail('The teaching_mode must be online or offline.');
                            }
                            return;
                        }

                        if (is_array($value)) {
                            foreach ($value as $v) {
                                if (!is_string($v) || !in_array($v, $allowed, true)) {
                                    $fail('Each teaching_mode value must be online or offline.');
                                    return;
                                }
                            }
                            return;
                        }

                        $fail('The teaching_mode must be a string or an array.');
                    },
                ],
            ]);
        }

        return $rules;
    }
}
