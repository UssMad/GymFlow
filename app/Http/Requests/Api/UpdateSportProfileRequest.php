<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSportProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objectif' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'in:debutant,intermediaire,avance'],
            'poids' => ['nullable', 'numeric', 'between:0,999.99'],
            'taille' => ['nullable', 'numeric', 'between:0,999.99'],
            'blessures' => ['nullable', 'string'],
            'jours_disponibles' => ['required', 'array', 'min:1'],
            'jours_disponibles.*' => ['string', 'max:30'],
            'preferences' => ['required', 'string'],
        ];
    }
}
