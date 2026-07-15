<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntrepriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code_adherent' => 'nullable|string|max:50|unique:entreprise,code_adherent',
            'code_comptable' => 'nullable|string|max:50',
            'raison_sociale' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'date_adhesion' => 'nullable|date',
            'statut' => 'nullable|string|in:actif,suspendu,résilié',
        ];
    }
}
