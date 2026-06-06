<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'announcement_text' => ['nullable', 'string', 'max:255'],
            'announcement_cta_label' => ['nullable', 'string', 'max:100'],
            'announcement_cta_href' => ['nullable', 'string', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'default_shipping_care_policy' => ['nullable', 'json'],
        ];
    }
}
