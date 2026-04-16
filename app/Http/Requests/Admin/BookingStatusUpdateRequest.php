<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BookingStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,contacted,confirmed,completed,cancelled'],
            'deposit_status' => ['required', 'in:not_required,pending,paid,waived'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
