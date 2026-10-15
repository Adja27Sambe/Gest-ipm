<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAyantDroitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('id_salarie') && $this->route('salarie')) {
            $salarie = $this->route('salarie');
            $this->merge([
                'id_salarie' => is_object($salarie) ? ($salarie->id_salarie ?? $salarie->IDPARTICIPANT) : $salarie
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'id_salarie' => 'required|exists:PARTICIPANT,IDPARTICIPANT',
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'lien_parente' => 'required|string|in:conjoint,enfant',
            'date_naissance' => 'nullable|date',
            'date_mariage' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'statut' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'id_salarie.required' => 'Le participant (salarié) est requis.',
            'id_salarie.exists' => 'Le participant sélectionné est introuvable.',
        ];
    }
}
