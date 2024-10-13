<?php

namespace App\Http\Requests;

use App\Models\TeamPart;
use Illuminate\Foundation\Http\FormRequest;

class StoreBatchTeamPartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', TeamPart::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'part_ids' => 'required|array',
            'part_ids.*' => 'exists:parts,id'
        ];
    }
}
