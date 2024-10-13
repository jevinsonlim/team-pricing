<?php

namespace App\Http\Requests;

use App\Models\TeamPart;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class DestroyBatchTeamPartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('destroyBatch', TeamPart::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_part_ids' => 'required|array|min:1',
            'team_part_ids.*' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    $teamPart = TeamPart::find($value);

                    if (!$teamPart) {
                        $fail("The {$attribute} is invalid.");
                    }

                    $sessionTeamId = session()->get('session_team')->id;

                    if ($teamPart->team_id != $sessionTeamId) {
                        $fail("The {$attribute} is invalid.");
                    }
                },
            ]
        ];
    }
}
