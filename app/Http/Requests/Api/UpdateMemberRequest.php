<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'prenom' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->user_id)],
            'password' => ['sometimes', 'required', 'confirmed', Password::min(12)],
            'coach_id' => ['sometimes', 'nullable', 'integer', 'exists:coaches,id'],
            'date_inscription' => ['sometimes', 'required', 'date'],
            'statut_abonnement' => ['sometimes', 'required', 'in:actif,expire,suspendu'],
        ];
    }
}
