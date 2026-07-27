<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteWorkoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'difficulte_ressentie' => ['nullable', Rule::in(['facile', 'moderee', 'difficile'])],
            'retour_membre' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
