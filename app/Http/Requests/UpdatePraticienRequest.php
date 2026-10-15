<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePraticienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'sometimes|required|string|max:255',
            'code_praticien' => 'nullable|string|max:50',
            'compte_comptable' => 'nullable|string|max:50',
            'a_convention' => 'nullable|boolean',
            'date_enregistrement' => 'nullable|date',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'specialite' => 'nullable|string|max:255',
        ];
    }
}
