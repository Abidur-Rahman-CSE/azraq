<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderPersonalizationReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personalization_status' => ['required', Rule::in(['awaiting_proof', 'proof_approved', 'changes_requested', 'proof_regenerated'])],
            'template_id' => ['nullable', 'exists:personalization_templates,id'],
            'mockup_id' => ['nullable', 'exists:personalization_mockups,id'],
            'internal_note' => ['nullable', 'string', 'max:1000'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
