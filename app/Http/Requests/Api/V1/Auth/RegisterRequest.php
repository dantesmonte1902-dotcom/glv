<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25', 'unique:users,phone'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
