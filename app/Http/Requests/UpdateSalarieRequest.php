<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalarieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_entreprise' => 'nullable|exists:ADHERANT,IDADHERANT',
            'matricule' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('PARTICIPANT', 'MATRICULE')->ignore($this->route('salarie')->id ?? $this->route('salarie'), 'IDPARTICIPANT')
            ],
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'sexe' => 'nullable|in:M,F',
            'date_embauche' => 'nullable|date',
            'date_naissance' => 'nullable|date|before:date_embauche',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'salaire' => 'nullable|numeric|min:0',
            'statut' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}
