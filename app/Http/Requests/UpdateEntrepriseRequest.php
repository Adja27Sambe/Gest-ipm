<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntrepriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code_adherent' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('entreprise')->ignore($this->entreprise->id_entreprise ?? $this->route('entreprise'), 'id_entreprise')
            ],
            'code_comptable' => 'nullable|string|max:50',
            'raison_sociale' => 'sometimes|required|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'date_adhesion' => 'nullable|date',
        ];
    }
}
