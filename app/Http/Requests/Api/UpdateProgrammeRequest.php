<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'sessions' => ['required', 'array', 'min:1'],
            'sessions.*.jour' => ['required', 'string', 'max:50'],
            'sessions.*.notes' => ['nullable', 'string'],
            'sessions.*.exercices' => ['required', 'array', 'min:1'],
            'sessions.*.exercices.*.exercice_id' => ['required', 'integer', Rule::exists('exercises', 'id')],
            'sessions.*.exercices.*.series' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sessions.*.exercices.*.repetitions' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'sessions.*.exercices.*.repos' => ['nullable', 'string', 'max:255'],
            'sessions.*.exercices.*.charge' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'sessions.*.exercices.*.duree_cardio' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'sessions.*.exercices.*.notes' => ['nullable', 'string'],
        ];
    }
}
