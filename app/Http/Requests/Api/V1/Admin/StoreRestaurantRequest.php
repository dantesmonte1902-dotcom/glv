<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand_slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
