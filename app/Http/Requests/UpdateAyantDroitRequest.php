<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAyantDroitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'lien_parente' => 'required|string|in:conjoint,enfant',
            'date_naissance' => 'nullable|date',
            'date_mariage' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'statut' => 'nullable|string|in:actif,inactif',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}
