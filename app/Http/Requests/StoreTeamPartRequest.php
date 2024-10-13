<?php

namespace App\Http\Requests;

use App\Models\TeamPart;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user->can('create', TeamPart::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'part_id' => 'exists:parts,id'
        ];
    }
}
